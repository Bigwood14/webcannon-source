#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <signal.h>
#include <ctype.h>
#include <unistd.h>
#include <errno.h>
#include <pthread.h>

#include <sys/time.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <sys/socket.h>
#include <sys/un.h>

#include <arpa/inet.h>

#include "config.h"
#include "custom.h"
#include "celutil.h"
#include "celcommon.h"
#include "mysqlutil.h"

extern char *strcasestr(char *haystack, char *needle);

#define DEC    ( void *(*)(void*) )
#define TOKENS " ,\r\n\t"
#define TOKENS1 "# ,\r\n\t"
#define MAX_INPUT_BUFF 200

#define MAX_READ_BUF 2000
#define SMAX_BUFF 1000

#define MAX_EMAILS 1000
#define MAX_EMAIL_SIZE 100
#define MAX_URL_SIZE 200
#define MAX_ID_SIZE 10

mysql_struct mys;
/*
#define VERBOSE 1
*/

fd_set RFds;


#define TCP_CONN  1
#define UNIX_CONN 2

typedef struct smtpt_struct {
 fd_set wfds;
 struct timeval tv;
 int    TheSock;
 int    write_ret;
 int    read_ret;
 char   TmpBuf[MAX_INPUT_BUFF];
 char   MailFrom[MAX_INPUT_BUFF];
 char   RcptTo[MAX_INPUT_BUFF];
 char   EmailAddr[MAX_INPUT_BUFF];
 char   ReadBuf[MAX_READ_BUF];
 char   StrTokBuf[MAX_READ_BUF];

 /* incoming smtp connections */
 char Emails[MAX_EMAILS][MAX_EMAIL_SIZE];
 int  Updated[MAX_EMAILS];
 int  CurEmail;
 int  DbEmail;

} smtpt_struct;


#define MAX_REJECTS 100
#define MAX_EMAIL   100
char rejects[MAX_REJECTS][MAX_EMAIL];
char accepts[MAX_REJECTS][MAX_EMAIL];

int MaxRejects;
int MaxAccepts;

int batch_update_smtp( int t, int i);
void read_rejects();
void read_accepts();
void smtp_thread(int fd);
void db_thread(int fd);
void check_email( smtpt_struct *smtpt, char *inbuf, char *outbuf, int len);
void add_email( smtpt_struct *smtpt, char *inaddr );
void add_viewed_email( smtpt_struct *smtpt, char *email);
void add_viewed_mailing(smtpt_struct *smtpt,char *viewed_id,char *mailing_id);
void add_clicked_email( smtpt_struct *smtpt, char *email);
void add_clicked_mailing(smtpt_struct *smtpt,char *clicked_id,char *mailing_id);
int wait_read( smtpt_struct *vqr, char *read_buf, int read_size );
int wait_write( smtpt_struct *vqr, char *write_buf, int write_size );
int check_accepts( char *rcptto );
int bouncer_address(char *outbuf);

char TmpBuf[MAX_INPUT_BUFF];
char TmpBuf1[MAX_INPUT_BUFF];


#define SMAX_THREADS 500 
pthread_t TheThreads[SMAX_THREADS];
pthread_t DBThread;
pthread_mutex_t TheMutex[SMAX_THREADS];
smtpt_struct *smtpt[SMAX_THREADS];
pthread_cond_t  TheCond[SMAX_THREADS];

fd_set Rfds;
int TheSock;

fd_set Clients;
int cs;

int main()
{
 struct sockaddr_un remote;
 socklen_t t;
 int err;
 int i;

  ignore_signals();
  TheSock = open_inet_server("0.0.0.0", 25);

  cel_init();
  read_rejects();
  read_accepts();
  mys_init(&mys);

  if ( SmtpThreads > SMAX_THREADS ) SmtpThreads = SMAX_THREADS;

  printf("starting\n"); fflush(stdout);
  for(i=0;i<SmtpThreads;++i) {

    smtpt[i] = malloc( sizeof(smtpt_struct) );
    memset( smtpt[i],0,sizeof(struct smtpt_struct));
    smtpt[i]->TheSock = -1;

    /* lock the mutex */
    pthread_mutex_init( &TheMutex[i], NULL );
    pthread_cond_init( &TheCond[i], NULL );
  }


  for(i=0;i<SmtpThreads;++i) {
    if((err=pthread_create(&TheThreads[i], NULL,DEC smtp_thread,(void *)i))!=0){
      printf("pthread_create smtp_thread failed: err=%d\n", err); 
      fflush(stdout);
      exit(0);
    }
  }

  if((err=pthread_create(&DBThread, NULL,DEC db_thread,0))!=0){
    printf("pthread_create db_thread failed: err=%d\n", err); fflush(stdout);
    exit(0);
  }
  sleep(5);

  while(1) {


    FD_ZERO(&RFds);
    FD_SET(TheSock, &RFds); 

    if (select(TheSock+1,(fd_set *)&RFds,(fd_set *)0, (fd_set *)0,NULL)<=0) {
      continue;
    }

    if ( FD_ISSET( TheSock, &RFds) ) {
      if ((cs = accept(TheSock, (struct sockaddr *)&remote, &t)) == -1 ) {
        printf("error on accept %d\n", errno); fflush(stdout);
        continue;
      }
    } else {
      continue;
    }

    check_dead_time();

    for(i=0;i<SmtpThreads;++i) {
      if ( smtpt[i]->TheSock == -1 ) {
        pthread_mutex_lock(&TheMutex[i]);
        smtpt[i]->TheSock = cs;
        pthread_cond_signal(&TheCond[i]);
        pthread_mutex_unlock(&TheMutex[i]);
        break;
      }
    }

    /* no available threads? close connection */
    if ( i == SmtpThreads ) {
      close(cs);
    }
  }
}

void read_rejects()
{
 FILE *fs;
 char *tmpstr;
 char *tmpptr;

  MaxRejects = 0;
  memset( rejects, 0, MAX_REJECTS * MAX_EMAIL );

  snprintf(TmpBuf, SMAX_BUFF, "%s/etc/robosmtpd.reject", CELIBERODIR);
  if ( (fs=fopen(TmpBuf, "r")) == NULL ) return;

  while ( fgets(TmpBuf, SMAX_BUFF, fs ) != NULL ) {
    tmpptr = TmpBuf;
    if ( (tmpstr = strsep( &tmpptr, TOKENS1)) == NULL ) continue;
    if ( strlen(tmpstr) == 0 ) continue;
    strncpy( &rejects[MaxRejects][0], TmpBuf, MAX_EMAIL);
    ++MaxRejects;
  }
}

void read_accepts()
{
 int i;
 FILE *fs;

  MaxAccepts = 0;
  memset( accepts, 0, MAX_REJECTS * MAX_EMAIL );

  snprintf(TmpBuf, SMAX_BUFF, "%s/etc/celsmtpd.accept", CELIBERODIR);
  if ( (fs=fopen(TmpBuf, "r")) == NULL ) {
    printf("WARNING: %s/etc/celsmtpd.accept needs to be configured\n",
      CELIBERODIR);
    printf("add all domains you wish to accept mail for\n");
    fflush(stdout);
    exit(-1);
  }

  while ( fgets(TmpBuf, SMAX_BUFF, fs ) != NULL ) {
    for(i=0;TmpBuf[i]!=0;++i) {
      switch (TmpBuf[i] ) {
        case '\n':
        case '\r':
        case '\f':
          TmpBuf[i] = 0;
          break;
      }
    }
    strncpy( &accepts[MaxAccepts][0], TmpBuf, MAX_EMAIL);
    ++MaxAccepts;
  }
}

void db_thread( int tid )
{
 int t;
 int i;
 struct timeval tv;
 int bounce_count;

  ignore_signals();

  mys_open(&mys);

  while(1) {
    tv.tv_sec = 2;
    tv.tv_usec = 0;
    select(0, (fd_set *)0,(fd_set *)0, (fd_set *) 0, &tv);
    bounce_count = 0;

    for(t=0;t<SmtpThreads;++t) {
      for(i=0;i<MAX_EMAILS;++i) {
        bounce_count += batch_update_smtp(t,i);
      }
    }
    if ( bounce_count > 0 )  {
      printf("bounce %d\n", bounce_count); fflush(stdout);
    }
  }
}

void smtp_thread( int tid )
{
	int count;
	int got_data;
	int EStatus = RBA_FAILURE;
	int nomore;
	int foober;
	int read_size;
	int newline;
	int set_close;
	int BadRcptTo;
	int i;

	ignore_signals();

	set_close = -1;
	while(1) {
		if ( set_close == -1 ) { 
			pthread_mutex_lock(&TheMutex[tid]); 
			smtpt[tid]->TheSock = -1;
			pthread_cond_wait(&TheCond[tid],&TheMutex[tid]);
			pthread_mutex_unlock(&TheMutex[tid]);

			snprintf(smtpt[tid]->TmpBuf, MAX_INPUT_BUFF, 
				"220 %s ESMTP\r\n", HeloHost);
			if ( wait_write( smtpt[tid], smtpt[tid]->TmpBuf, 
												strlen(smtpt[tid]->TmpBuf) ) < 0 ) {
				close(smtpt[tid]->TheSock);
	set_close = -1;
				/*smtpt[tid].TheSock = -1;*/
				continue;
			}
		}
		set_close = 0;

		memset(smtpt[tid]->RcptTo, 0, MAX_INPUT_BUFF);
		memset(smtpt[tid]->MailFrom, 0, MAX_INPUT_BUFF);

		nomore=0;
		for(count=0,got_data=0;nomore==0&&count<10 && got_data==0; ++count ) {
			memset(smtpt[tid]->ReadBuf, 0, MAX_READ_BUF);
			if ( wait_read( smtpt[tid], smtpt[tid]->ReadBuf, MAX_READ_BUF) <= 0 ) {
	close(smtpt[tid]->TheSock);
	set_close = -1;
				/*smtpt[tid].TheSock = -1;*/
	nomore = 1;
	continue;
			}
	
			if ( strncasecmp( smtpt[tid]->ReadBuf, "QUIT", 4) == 0 ) {
				snprintf(smtpt[tid]->TmpBuf, MAX_INPUT_BUFF,
					"221 %s\r\n", HeloHost); 
				wait_write( smtpt[tid], smtpt[tid]->TmpBuf, 
					strlen(smtpt[tid]->TmpBuf));
	close(smtpt[tid]->TheSock);
	set_close = -1;
				/*smtpt[tid].TheSock = -1;*/
	nomore = 1;
	
			} else if ( strncasecmp( smtpt[tid]->ReadBuf, "HELO", 4) == 0 ) {
				strncpy(smtpt[tid]->MailFrom, smtpt[tid]->ReadBuf, MAX_INPUT_BUFF);
				snprintf(smtpt[tid]->TmpBuf, MAX_INPUT_BUFF,
					"250 %s\r\n", HeloHost);
				if ( wait_write( smtpt[tid], smtpt[tid]->TmpBuf, 
												 strlen(smtpt[tid]->TmpBuf) ) <= 0 ) {
		close(smtpt[tid]->TheSock);
		set_close = -1;
					/*smtpt[tid].TheSock = -1;*/
		nomore = 1;
				}
	
			} else if ( strncasecmp( smtpt[tid]->ReadBuf, "MAIL FROM", 9) == 0 ) {
				strncpy(smtpt[tid]->MailFrom, smtpt[tid]->ReadBuf, MAX_INPUT_BUFF);
				snprintf(smtpt[tid]->TmpBuf, MAX_INPUT_BUFF, "250 ok\r\n"); 
				if ( wait_write( smtpt[tid], smtpt[tid]->TmpBuf, 
												 strlen(smtpt[tid]->TmpBuf) ) <= 0 ) {
		close(smtpt[tid]->TheSock);
		set_close = -1;
					/*smtpt[tid].TheSock = -1;*/
		nomore = 1;
		continue;
				}
	
			} else if ( strncasecmp( smtpt[tid]->ReadBuf, "HELP", 4) == 0 ) {
				snprintf(smtpt[tid]->TmpBuf, MAX_INPUT_BUFF,
					"214 smtp server\r\n"); 
				if ( wait_write( smtpt[tid], smtpt[tid]->TmpBuf, 
												 strlen(smtpt[tid]->TmpBuf) ) <= 0 ) {
		close(smtpt[tid]->TheSock);
		set_close = -1;
					/*smtpt[tid].TheSock = -1;*/
		nomore = 1;
		continue;
				}
	
			} else if ( strncasecmp( smtpt[tid]->ReadBuf, "RCPT TO", 7) == 0 ) {
	
	BadRcptTo = 0;
				if ( smtpt[tid]->MailFrom[0] == 0 ) {
					snprintf(smtpt[tid]->TmpBuf, MAX_INPUT_BUFF,
						"503 MAIL first (#5.5.1)\r\n");
				} else {
					strncpy(smtpt[tid]->RcptTo, smtpt[tid]->ReadBuf, MAX_INPUT_BUFF);
		if ( check_accepts( smtpt[tid]->RcptTo ) == 0 ) {
						snprintf(smtpt[tid]->TmpBuf, MAX_INPUT_BUFF, "250 ok\r\n"); 
		} else {
						snprintf(smtpt[tid]->TmpBuf, MAX_INPUT_BUFF, 
							"553 sorry, that domain isn't in my list of allowed rcpthosts (#5.7.1)\r\n"); 
			BadRcptTo = 1;
					}
				}
				if ( wait_write( smtpt[tid], smtpt[tid]->TmpBuf, 
												 strlen(smtpt[tid]->TmpBuf) ) <= 0 ) {
		close(smtpt[tid]->TheSock);
		set_close = -1;
		nomore = 1;
				}
	if ( BadRcptTo == 1 ) {
		close(smtpt[tid]->TheSock);
		set_close = -1;
		nomore = 1;
	}
	
			} else if ( strncasecmp( smtpt[tid]->ReadBuf, "DATA", 4) == 0 ) {
				if ( smtpt[tid]->MailFrom[0] == 0 ) {
					snprintf(smtpt[tid]->TmpBuf, MAX_INPUT_BUFF, 
					 "503 MAIL first (#5.5.1)\r\n"); 
				} else if ( smtpt[tid]->RcptTo[0] == 0 ) {
					snprintf(smtpt[tid]->TmpBuf, MAX_INPUT_BUFF,
						"503 RCPT first (#5.5.1)\r\n"); 
				} else {
					snprintf(smtpt[tid]->TmpBuf, MAX_INPUT_BUFF,
						"354 go ahead\r\n"); 
					got_data = 1;
				}
				if ( wait_write( smtpt[tid], smtpt[tid]->TmpBuf, 
												 strlen(smtpt[tid]->TmpBuf) ) <= 0 ) {
		close(smtpt[tid]->TheSock);
		set_close = -1;
					/*smtpt[tid].TheSock = -1;*/
		nomore = 1;
				}
	
			} else {
				snprintf(smtpt[tid]->TmpBuf, MAX_INPUT_BUFF, 
					"502 unimplemented (#5.5.1)\r\n"); 
				if ( wait_write( smtpt[tid], smtpt[tid]->TmpBuf, 
												 strlen(smtpt[tid]->TmpBuf) ) <= 0 ) {

		close(smtpt[tid]->TheSock);
		set_close = -1;
					/*smtpt[tid].TheSock = -1;*/
		nomore = 1;
				}
			}
		}
		if ( nomore == 1 ) {
			continue;
		}

		if ( got_data == 0 ) {
			close(smtpt[tid]->TheSock);
			set_close = -1;
			/*smtpt[tid].TheSock = -1;*/
			continue;
		}

		check_email( smtpt[tid], 
								 smtpt[tid]->MailFrom, smtpt[tid]->EmailAddr, MAX_INPUT_BUFF);
	
		newline=1;
		/* don't let them send more than 50000 lines */

		memset(smtpt[tid]->ReadBuf, 0, MAX_READ_BUF);
		nomore = 0;
		EStatus = RBA_DEFER;
		for(count=0;
				nomore==0 && 
				(read_size=wait_read(smtpt[tid],smtpt[tid]->ReadBuf,MAX_READ_BUF))>=0 
	&& count<50;++count) {

			lowerit( smtpt[tid]->ReadBuf );
			//if ( strcasestr( smtpt[tid]->ReadBuf, "remove ")!=NULL ) EStatus = CEL_UNSUB; 

			if(strcasestr(smtpt[tid]->ReadBuf, "subject:") !=0)
			{
				if(strcasestr(smtpt[tid]->ReadBuf, "remove") !=0 || strcasestr(smtpt[tid]->ReadBuf, "unsub") !=0 || strcasestr(smtpt[tid]->ReadBuf, "list") !=0)
					EStatus = CEL_UNSUB;
			}

			for(foober=0;nomore==0&&foober<read_size;++foober) {

	if ( newline == 1 && smtpt[tid]->ReadBuf[foober]=='.' && 
			 (smtpt[tid]->ReadBuf[foober+1]== '\r'|| 
							smtpt[tid]->ReadBuf[foober+2]=='\n') ) {
		nomore = 1;
					snprintf(smtpt[tid]->TmpBuf, MAX_INPUT_BUFF, 
						"250 ok %ld %d\r\n", time(NULL), getpid()); fflush(stdout);
					if ( wait_write( smtpt[tid], smtpt[tid]->TmpBuf, 
													 strlen(smtpt[tid]->TmpBuf) ) <= 0 ) {
			close(smtpt[tid]->TheSock);
						set_close = -1;
						/*smtpt[tid].TheSock = -1;*/
			nomore = 1;
			continue;
					}
	} else if ( smtpt[tid]->ReadBuf[foober] == '@'	||
							smtpt[tid]->ReadBuf[foober] == '^' ) {
		for(i=foober;i>0;--i ) {
						if ( isspace(smtpt[tid]->ReadBuf[i]) ) break;
		}
					check_email( smtpt[tid], &smtpt[tid]->ReadBuf[i], 
						smtpt[tid]->EmailAddr, MAX_INPUT_BUFF);
				}

	if ( smtpt[tid]->ReadBuf[foober]=='\n' ) {
		newline = 1;
	} else {
					newline = 0;
	}
			}
			memset(smtpt[tid]->ReadBuf, 0, MAX_READ_BUF);
	
		} 
		close(smtpt[tid]->TheSock);
		set_close = -1;
	}
}

void check_email( smtpt_struct *smtpt, char *inbuf, char *outbuf, int len)
{
 char *tmpstr;
 char *tmpstr1;
 int i, skip;
 int got_one;

  for(tmpstr = inbuf; *tmpstr!=0; ++tmpstr) {
    if ( *tmpstr == '@') {
      memset(outbuf, 0, len );
      if ( get_addr( inbuf, tmpstr, outbuf, len, 0) == 0 ) {
        got_one = 1;
      }
    } else if ( *tmpstr == '^') {
      memset(outbuf, 0, len );
      if ( get_addr( inbuf, tmpstr, outbuf, len, 1) == 0 ) {
        got_one = 1;
      }
    }

    if ( got_one == 1 ) {
      if ( strcasestr(outbuf, "postmaster@") != NULL ) continue;
      if ( strcasestr(outbuf, "mailer-daemon@") != NULL ) continue;
      if ( bouncer_address(outbuf) == 1 ) continue;

      for(i=0,skip=0;skip==0&&i<MaxRejects;++i) {
        if ( strcasestr(outbuf, &rejects[i][0]) != NULL ) skip = 1;
      }
      if ( skip == 1 ) continue;
      add_email( smtpt, outbuf );
    }
  }
}

void add_email( smtpt_struct *smtpti, char *inaddr )
{
 int i;
 int j;

  for(j=0;j<SmtpThreads;++j) {
    for(i=0;i<MAX_EMAILS;++i) {
      if ( strcasecmp(smtpt[j]->Emails[i], inaddr) == 0 ) {
        return;
      }
    }
  }
  strncpy( smtpti->Emails[smtpti->CurEmail], inaddr, MAX_EMAIL_SIZE);
  smtpti->Updated[smtpti->CurEmail] = 1; 
  ++smtpti->CurEmail;
  if ( smtpti->CurEmail >= MAX_EMAILS ) smtpti->CurEmail = 0;

}

int wait_write( smtpt_struct *vqr, char *write_buf, int write_size ) 
{
  vqr->tv.tv_sec = IRWTimeout;
  vqr->tv.tv_usec = 0;

  FD_ZERO(&vqr->wfds);
  FD_SET(vqr->TheSock, &vqr->wfds);

  if (select(vqr->TheSock + 1,(fd_set *) 0,
        &vqr->wfds,(fd_set *) 0,&vqr->tv) <= 0) {
    return(-1);
  }

  if (FD_ISSET(vqr->TheSock,&vqr->wfds)) {
    vqr->write_ret = write(vqr->TheSock,write_buf, write_size);
    return(vqr->write_ret);
  }

  errno = ETIMEDOUT; 
  return(-1);
}

int wait_read( smtpt_struct *vqr, char *read_buf, int read_size ) 
{

  vqr->tv.tv_sec = IRWTimeout;
  vqr->tv.tv_usec = 0; 

  FD_ZERO(&vqr->wfds);
  FD_SET(vqr->TheSock,&vqr->wfds);

  if (select(vqr->TheSock + 1,&vqr->wfds,(fd_set *) 0,
                           (fd_set *) 0,&vqr->tv) <= 0) {
    return -1;
  }
  if (FD_ISSET(vqr->TheSock,&vqr->wfds)) {
    vqr->read_ret = read(vqr->TheSock,read_buf,read_size);
    return(vqr->read_ret);
  }
  errno = ETIMEDOUT; 
  return(-1);
}


int check_accepts( char *rcptto ) 
{
 int i;
 int len;
 char *tmpstr;
 char *tmpstr2;

  tmpstr = strchr(rcptto,'@');
  if ( tmpstr == NULL ) return(-1);
  ++tmpstr;
  if ( tmpstr == NULL ) return(-1);

  len = 0;
  for(tmpstr2=tmpstr;*tmpstr2!=0;++tmpstr2, ++len) {
    if ( *tmpstr2 == 45 && *tmpstr2 == 46 &&
         (*tmpstr2 >= 48 && *tmpstr2 <= 57 ) ||
         (*tmpstr2 >= 65 && *tmpstr2 <= 90 ) ||
         (*tmpstr2 >= 97 && *tmpstr2 <= 122 )) {
      ;
    } else {
      break;
    }
  }

  for(i=0;i<MaxAccepts;++i) {
    if (strncasecmp(tmpstr, accepts[i],len) == 0 ) {
      return(0);
    }
  }
  return(-1);
}

int batch_update_smtp( int t, int i)
{
 int count = 0;

  /* incoming tcp/ip smtp connections for bounce processing */
  if ( smtpt[t]->Updated[i] == 1 ) {
    printf("%s\n", smtpt[t]->Emails[i] );
    update_emails(&mys, smtpt[t]->Emails[i], RBA_FAILURE, NULL, 1);
    count = 1;
  }
  memset(smtpt[t]->Emails[i], 0, MAX_EMAIL_SIZE );
  smtpt[t]->Updated[i] = 0;
  return(count);
}

int bouncer_address(char *outbuf)
{
 int i;
 int num_count;

  for(i=0,num_count=0;outbuf[i]!=0;++i)  {
    if ( isdigit(outbuf[i]) ) ++num_count;
  }
  if  ( num_count > 10 ) return(1);
  return(0);
}
