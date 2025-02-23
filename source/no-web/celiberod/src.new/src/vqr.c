#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <fcntl.h>
#include <signal.h>
#include <pthread.h>
#include <ctype.h>
#include <errno.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <sys/time.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <sys/un.h>
#include <sys/stat.h>
#include <unistd.h>
#include <netdb.h>
#include <netinet/in.h>
#include <resolv.h>
#include "dns.h"
#include "celcommon.h"
#include "mqa.h"
#include "rba.h"
#include "vqr.h"
#include "celutil.h"
#include "custom.h"
#include "xmailer.h"
#include "config.h"
#include "dc.h"

extern char *DayStr[7];
extern char * MonStr[12];
extern char *strcasestr(char *haystack, char *needle);

char *spam_words[] = {
  "storage",
  "quota",
  "abuse",
  "access", 
  "badmailfrom", 
  "spam", 
  "spews", 
  "content", 
  "blacklist",
  "connection",
  "block",
  "dns",
  "size",
  "full",
  "syntax",
  "hvub", 
  NULL };
  
char *MailFromSites[] = {
 "hotmail.com",
 "mindspring.com",
 "aol.com",
 "cs.com",
 NULL
};

char *YahooDomains[] = {
	"yahoo.com",
	"yahoo.com.tw",
	"yahoo.com.hk",
	"yahoo.com.mx",
	"yahoo.com.cn",
	"yahoo.com.sg",
	"yahoo.com.au",
	"yahoo.com.br",
	"yahoo.com.ar",
	"yahoo.com.pk",
	"yahoo.com.net",
	"yahoo.com.vn",
	"yahoo.com.ph",
	"yahoo.ca",
	"yahoo.co.uk",
	"yahoo.com.tr",
	"yahoo.com.ul",
	"yahoo.com.my",
	"rocketmail.com",
	"geocities.com",
	NULL
};

char *AOLDomains[] = {
	"aol.com",
	"aim.com",
	"netscape.net",
	"wmconnect.com",
	"wild4music.com",
	"techfour.co.uk",
	NULL
};

/* 
 * Read the smtp code from the returned status
 */ 
void smtp_code(vqr_struct *vqr)
{
	int i;

	vqr_clean_return(vqr);

	vqr->return_code = 0;
	vqr->tmp_i = 0;
	while ( vqr->smtpbuf[vqr->tmp_i] != 0 )
	{
		for(vqr->tmp_i=0; 
				vqr->tmp_i < TMPBUF_SIZE && 
				vqr->smtpbuf[vqr->tmp_i]!=0 && isdigit(vqr->smtpbuf[vqr->tmp_i]) != 0; 
				++vqr->tmp_i )
		{
			vqr->return_code = vqr->return_code * 10 + 
												 (vqr->smtpbuf[vqr->tmp_i] - '0');
		}

		while( vqr->smtpbuf[vqr->tmp_i]!=0 && vqr->smtpbuf[vqr->tmp_i] != '\n' )
			++vqr->tmp_i;

		if ( vqr->smtpbuf[vqr->tmp_i] != 0 )
		{
			++vqr->tmp_i;
			if ( vqr->smtpbuf[vqr->tmp_i] != 0 )
				vqr->return_code = 0;
		}
	} 

	if ( vqr->return_code >= 500 )
	{
		vqr->real_status = RBS_FAILURE;
		for(i=0;spam_words[i]!=NULL;++i)
		{
			if ( strcasestr( vqr->smtpbuf, spam_words[i]) != 0	)
			{
				vqr->return_code = 440;
				break;
			}
		}
	}
	else if ( vqr->return_code >= 400 )
	{
		vqr->real_status = RBS_DEFERRAL;
	}
	else
		vqr->real_status = RBS_SUCCESS;

	if (vqr->mqa->aol_rotate > 0)
	{
		/*if (strcasestr(vqr->smtpbuf, "DYN:T1") != NULL)
			vqr->aol_reject = 1;
		else if (strcasestr(vqr->smtpbuf, "RLY:B1") != NULL)
			vqr->aol_reject = 1;*/
	
		if (strcasestr(vqr->smtpbuf, "http://postmaster.info.aol.com/errors/") != NULL)
			vqr->aol_reject = 1;
	}
}

/* 
 * parse out an email address from an input line
 *
 * On success return 0
 * On error   return -1
 */
int get_addr_vqr(vqr_struct *vqr, int check_from )
{

  if ( check_from == 1 ) {
    vqr->tmpstr  = strcasestr(vqr->mess, "From:");
    /*vqr->tmpstr1 = vqr->mess;*/
    vqr->outbuf  = vqr->from_addr_tmp;
    return(get_addr( vqr->tmpstr, NULL, vqr->outbuf, TMPBUF_SIZE,0 ));
  } else {
    vqr->tmpstr  = &vqr->Vars[0][0];
    /*vqr->tmpstr1 = &vqr->Vars[0][0];*/
    vqr->outbuf  = vqr->to_addr;
    if ( get_addr( vqr->tmpstr, NULL, vqr->outbuf, TMPBUF_SIZE,0 )==-1 ) {
      return(-1);
    }

    vqr->tmpstr = vqr->to_addr;
    while( *vqr->tmpstr != '@' && *vqr->tmpstr != 0 ) ++vqr->tmpstr;
    if ( *vqr->tmpstr == 0 ) return(-1);
    ++vqr->tmpstr;
    if ( *vqr->tmpstr == 0 ) return(-1);
    /*if ( strcmp( vqr->mail_domain, vqr->tmpstr ) == 0 ) return(-1);*/

    memset(vqr->mail_domain, 0, MALLOC_TMPBUF_SIZE);
    strncpy(vqr->mail_domain,vqr->tmpstr, MALLOC_TMPBUF_SIZE);
  }
  return(0);

}

/* Setup the date header including
 * the offset from GMT
 */
void get_date_header( vqr_struct *vqr ) 
{
	if (vqr->is_yahoo == 1)
	{
		if (vqr->mqa->yahoo_date[3] != NULL)
		{
			snprintf( vqr->date_str, TMPBUF_SIZE, "Date: %s", vqr->mqa->yahoo_date);
			return;
		}
	}

	/* set the date header */
	vqr->start_time = time(0);
	localtime_r(&vqr->start_time, &vqr->mytm);
	gettimeofday( &vqr->start_timeval, (struct timezone *) 0);

	snprintf( vqr->date_str, TMPBUF_SIZE,
		"Date: %s, %d %s %d %02d:%02d:%02d %03ld00",
		DayStr[vqr->mytm.tm_wday], vqr->mytm.tm_mday, MonStr[vqr->mytm.tm_mon],
		1900+vqr->mytm.tm_year, vqr->mytm.tm_hour, vqr->mytm.tm_min, 
		vqr->mytm.tm_sec, -(timezone/60/60));
	/*vqr->mytm->tm_sec, (vqr->mytm->tm_gmtoff/60/60));*/
}

/*
 * Get the IP of the mail server
 */
int get_mail_ip( vqr_struct *vqr ) 
{
 int count;
 int choice;


  /* do a dns query using tmpstr as the host */
  vqr->ret = dns_query(vqr->dns, T_MX, 0, vqr->mail_domain);
  dns_reset(vqr->dns);

  /* on failure save the hostname in vqr->mail_host */ 
  if ( vqr->ret == 0 ) {
    strncpy( vqr->mail_host, vqr->tmpstr, TMPBUF_SIZE);

  /* pick out a hostname from the results */
  } else {

    /* if we got back an answer */
    if ( vqr->dns->ans_cnt >  0 ) {
      vqr->tmp_j = vqr->dns->answers[0]->data;
      vqr->tmp_k = 0;

      /* find the lowest distance from all the results */
      for(vqr->tmp_i=0;vqr->tmp_i<vqr->dns->ans_cnt;++vqr->tmp_i){
         if ( vqr->dns->answers[vqr->tmp_i]->data < vqr->tmp_j ) {
             vqr->tmp_j = vqr->dns->answers[vqr->tmp_i]->data;
             vqr->tmp_k = vqr->tmp_i;
         }
      }

      /* we have now found the lowest distance, count how many there are */
      for(count=0, vqr->tmp_i=0;vqr->tmp_i<vqr->dns->ans_cnt;++vqr->tmp_i) {
         if ( vqr->dns->answers[vqr->tmp_i]->data == vqr->tmp_j ) {
           ++count;
         }
      }

      /* now we have the count, now pick one of them randomly */
      choice = random()%count;

      /* go find the lucky host */
      for(count=0, vqr->tmp_i=0;vqr->tmp_i<vqr->dns->ans_cnt;++vqr->tmp_i) {
         if ( vqr->dns->answers[vqr->tmp_i]->data == vqr->tmp_j ) {
           ++count;
           if ( count == choice ) {
             vqr->tmp_k = vqr->tmp_i;
             break;
           }
         }
      }

      /* save the result in the vqr->mail_host field */
      strncpy( vqr->mail_host, (char *)vqr->dns->answers[vqr->tmp_k]->answer, 
        TMPBUF_SIZE);

    /* if there is no answer then just try the hostname */
    } else {
      strncpy( vqr->mail_host, vqr->tmpstr, TMPBUF_SIZE);
    }
  }

  /* Okay, lets get the IP address of the lucky mail_host */
  vqr->ret = dns_query(vqr->dns, T_A, 0, (char *)vqr->mail_host);
  dns_reset(vqr->dns);

  /* return any error */
  if ( vqr->ret == 0 ) {
    /* attempt a CNAME lookup */
    vqr->ret = dns_query(vqr->dns, T_CNAME, 0, (char *)vqr->mail_host);
    dns_reset(vqr->dns);
    if ( vqr->ret == 0 ) return(-1);
    strncpy( vqr->mail_host, (char *)vqr->dns->answers[0]->answer, 
        TMPBUF_SIZE);
    /* Okay, lets get the IP address of the lucky mail_host */
    vqr->ret = dns_query(vqr->dns, T_A, 0, (char *)vqr->mail_host);
    dns_reset(vqr->dns);
    if ( vqr->ret == 0 ) {
      return(-1);
    }
  }


  /* no answers, that's a problem */
  if ( vqr->dns->ans_cnt == 0 ) {
    printf("FATAL ERROR!!! ans_cnt == 0\n");
    return(-1);
  }

  /* return okay */
  return(0);
}

int parse_vars( vqr_struct *vqr ) 
{
 int i;
 int var_num;
 int var_len;

  var_num = 0;
  var_len = 0;

  /* clear all the mem to zero so any of the error returns
   * below will have null terminated strings
   */
  memset(vqr->Vars, 0, sizeof(vqr->Vars));

  vqr->MaxVars = 0;
  for(i=0;vqr->VarsLine[i]!=0;++i){
    if ( vqr->VarsLine[i] == '|' || vqr->VarsLine[i] == '\n' || 
         vqr->VarsLine[i] == '\r' ) { 
      vqr->Vars[var_num][var_len] = 0;
      vqr->VarsLen[var_num] = strlen(&vqr->Vars[var_num][0]);
      ++var_num;
      if ( var_num >= MAX_VARS-1 ) return(-1);
      var_len = 0;
    } else {
      if (var_len >= MAX_VARS_LEN-1 ) return(-1);
      vqr->Vars[var_num][var_len] = vqr->VarsLine[i];
      ++var_len;
    }
  }
  vqr->Vars[var_num][var_len] = 0;
  vqr->VarsLen[var_num] = strlen(&vqr->Vars[var_num][0]);
  vqr->MaxVars = var_num;

  return(0);
}

void vqr_clean_return(vqr_struct *vqr ) 
{
 int i;

  for(i=0;i<TMPBUF_SIZE;++i) {
    if ( vqr->smtpbuf[i] == '\r' ) vqr->smtpbuf[i] = 0;
    if ( vqr->smtpbuf[i] == '\n' ) vqr->smtpbuf[i] = 0;
  }
}

inline int wait_write( vqr_struct *vqr, char *write_buf, int write_size ) 
{
  vqr->tv.tv_sec = IRWTimeout;
  vqr->tv.tv_usec = 0;
  FD_ZERO(&vqr->wfds);
  FD_SET(vqr->sock, &vqr->wfds);

  /*if ( write_size < 100 ) printf("wait write: [%d]: %s", vqr->vqridx, write_buf);*/
  vqr->write_ret = 0;
  if (select(vqr->sock+1,(fd_set *)0, &vqr->wfds,(fd_set *)0,&vqr->tv) >= 1) {
    vqr->write_ret = write(vqr->sock,write_buf, write_size);
    if ( vqr->write_ret > 0 ) vqr->bytes_sent += vqr->write_ret;
    /*printf("write [%i]: %s\n", vqr->vqridx, write_buf);*/
    return(vqr->write_ret);
  }
  vqr->connecterr = ETIMEDOUT; 
  return(-1);
}

inline int wait_read(vqr_struct *vqr)
{

	vqr->tv.tv_sec 	= IRWTimeout;
	vqr->tv.tv_usec = 0; 

	FD_ZERO(&vqr->wfds);
	FD_SET(vqr->sock,&vqr->wfds);

	vqr->read_ret 	= 0;

	memset(vqr->smtpbuf,0,MALLOC_TMPBUF_SIZE);

	if (select(vqr->sock+1,&vqr->wfds,(fd_set *) 0,(fd_set *)0,&vqr->tv)>=1)
	{
		vqr->read_ret = read(vqr->sock,vqr->smtpbuf,TMPBUF_SIZE);
		//printf("wait read %d: %s (%d)\n", vqr->vqridx, vqr->smtpbuf, vqr->read_ret);
		return(vqr->read_ret);
	}
	vqr->connecterr = ETIMEDOUT; 
	return(-1);
}

void vqr_loop(vqr_struct *vqr)
{
	int i,j,l,m,n,ret;

	signal(SIGPIPE,SIG_IGN);

	vqr->mess 			= NULL;
	vqr->have_status 	= RBS_NO_STATUS;
	vqr->prior_status 	= RBS_NO_STATUS;
	vqr->dns	 		= dns_init(F_NONE);
	vqr->dns1			= dns_init(F_NONE);
	vqr->sock			= -1;
	vqr->yahoo_tries 	= 0;
	vqr->skip_2 		= 0;
	vqr->aol_reject 	= 0;
	vqr->force_new_connect = 0;
	vqr->dk.dk_flags 	= 0;
	vqr->dk.dk_lib 		= dk_init(&vqr->dk.dk_stat);

	if (vqr->dk.dk_stat != DK_STAT_OK)
	{
		printf("dk init failed\n");
	}

	memset(vqr->mail_domain_prior, 0, MALLOC_TMPBUF_SIZE);

	printf("launched with IP %s\n", IPS[vqr->OurIP]->ip);

	while ( 1 )
	{
		vqr->bytes_sent = 0;

		pthread_mutex_lock(&vqr->rba->TheMutex);

		/* setup the IP */
		/*vqr->PrevIP = vqr->OurIP;
		++vqr->rba->CurrentIP;
		if ( vqr->rba->CurrentIP >= MaxIPS ) vqr->rba->CurrentIP = 0;
		vqr->OurIP = vqr->rba->CurrentIP;*/
	
		if (IPS[vqr->OurIP]->removed == 1 && vqr->mqa->aol_rotate > 0)
		{
			j = 0;
			for (i=0;i<MaxIPS;i++)
			{
				if (IPS[i]->removed == 1)
					j++;
			}

			if (j == MaxIPS)
			{
				vqr->mqa->run_state = MQA_CANCEL;
				mysql_update_schedule(vqr->mqa->mail_id, 8);
			}

			break;
		}
	
		++vqr->rba->CurMessId;
		if ( vqr->rba->CurMessId > 10000 ) vqr->rba->CurMessId = 1;
	
		/* setup the XMailer */
		++vqr->rba->CurrentXMailer;
	
		if ( vqr->rba->CurrentXMailer>=MAX_XMAILER ) vqr->rba->CurrentXMailer = 0;
	
		vqr->OurXMailer = vqr->rba->CurrentXMailer;
	
		if(vqr->skip_2 > 0)
		{
			memset(vqr->mail_domain_prior, 0, MALLOC_TMPBUF_SIZE );
			pthread_mutex_unlock(&vqr->rba->TheMutex);
		}

		if(vqr->skip_2 < 1)
		{
			vqr->yahoo_tries = 0;

			/* setup the From */
			vqr->PrevFrom = vqr->OurFrom;
			++vqr->rba->CurrentFrom;
			if ( vqr->rba->CurrentFrom >= MaxFroms ) vqr->rba->CurrentFrom = 0;
			vqr->OurFrom = vqr->rba->CurrentFrom;

			/* write our last status to stdout / log */
			if ( vqr->have_status != RBS_NO_STATUS && vqr->status_line[0] != 0 )
				puts(vqr->status_line);

			
			/* write status to results file */
			switch(vqr->have_status)
			{
				case RBS_SUCCESS:
					++vqr->mqa->total_success;
					fprintf(vqr->mqa->TheSuccess,"%s\n", vqr->status_line); 
					fprintf(vqr->mqa->TheLookup, "%s;%i;%s\n", vqr->status_line, vqr->mta, vqr->dk.dk_sig);
					break;
	
				case RBS_FAILURE:
					++vqr->mqa->total_failure;
					fprintf(vqr->mqa->TheFailure, "%s\n", vqr->status_line); 
					break;
	
				case RBS_DEFERRAL:
					++vqr->mqa->total_deferral;
					fprintf(vqr->mqa->TheDeferral, "%s\n", vqr->status_line); 
	
					/* write the vars line out to the deferral retry
					 * file so we can run this list again later
					 */
					if ( vqr->mqa->DeferralRetry != NULL )	
						fputs(vqr->VarsLine, vqr->mqa->DeferralRetry);
					break;
	
				default:
					break;
	
			}
			/*memset(vqr->mail_domain_prior, 0, MALLOC_TMPBUF_SIZE);*/
			memcpy(vqr->mail_domain_prior, vqr->mail_domain, MALLOC_TMPBUF_SIZE);
			vqr->prior_status = vqr->real_status;
	
			/* clear status for new run */
			vqr->have_status = RBS_NO_STATUS;
			pthread_mutex_lock(&vqr->rba->TheMqaMutex);
	
			/* get new mqa below */
			//vqr->mqa = NULL;
			/* get a new mqa */
			if ( mqa_next_mailing( vqr, vqr->rba ) == 1 )
			{
				if ( rba_all_done(vqr->rba) == 1 )
				{
					pthread_mutex_unlock(&vqr->rba->TheMqaMutex);
					if (vqr->sock != -1)
					{
						// close with a quit
						snprintf(vqr->smtpbuf, TMPBUF_SIZE, "QUIT\r\n");
						vqr->write_size = strlen(vqr->smtpbuf);
						wait_write( vqr, vqr->smtpbuf, vqr->write_size);
						if ( vqr->write_ret >0 && vqr->write_size == vqr->write_ret )
							wait_read(vqr);
						close(vqr->sock);
						vqr->sock = -1;
					}
					continue;
				}
				pthread_mutex_unlock(&vqr->rba->TheMqaMutex);
				pthread_mutex_unlock(&vqr->rba->TheMutex);
				memset(vqr->mail_domain_prior, 0, MALLOC_TMPBUF_SIZE);

				if (vqr->sock != -1)
				{
					// close with a quit
					snprintf(vqr->smtpbuf, TMPBUF_SIZE, "QUIT\r\n");
					vqr->write_size = strlen(vqr->smtpbuf);
					wait_write( vqr, vqr->smtpbuf, vqr->write_size);
					if ( vqr->write_ret >0 && vqr->write_size == vqr->write_ret )
						wait_read(vqr);
					close(vqr->sock);
					vqr->sock = -1;
				}

				vqr->tv.tv_sec = 10;
				vqr->tv.tv_usec = 0; 
				select(0, (fd_set *)0,(fd_set *)0, (fd_set *) 0, &vqr->tv);
				continue;
			}

			/* get the new to line for this mqa */
			memset(vqr->VarsLine, 0, MAX_VARS_LINE);
			vqr->yahoo_tries = 0;
			if ( fgets(vqr->VarsLine, MAX_VARS_LINE-1, vqr->mqa->TheAddr)==NULL )
			{
				mqa_end(vqr->mqa);
				vqr->mqa = NULL;
				mqa_check_end();
				pthread_mutex_unlock(&vqr->rba->TheMqaMutex);
				fflush(stdout);
				memset(vqr->mail_domain_prior, 0, MALLOC_TMPBUF_SIZE);
				if ( rba_all_done(vqr->rba) == 1 ) continue;
				pthread_mutex_unlock(&vqr->rba->TheMutex);
				vqr->tv.tv_sec = 10;
				vqr->tv.tv_usec = 0; 
				select(0, (fd_set *)0,(fd_set *)0, (fd_set *) 0, &vqr->tv);
				continue;
			}
	
			/* setup the content rotation */
			for (i=0;i<vqr->mqa->rotated_content_count;i++)
			{
				vqr->mqa->rotated_content[i].position++;
		
				if (vqr->mqa->rotated_content[i].position >= vqr->mqa->rotated_content[i].count)
					vqr->mqa->rotated_content[i].position = 0;
		
				vqr->our_rotation[i] = vqr->mqa->rotated_content[i].position;
			}
		
			pthread_mutex_unlock(&vqr->rba->TheMqaMutex);

			/* This report thread is running a message */
			vqr->have_status = RBS_RUNNING;
	
			/* all done with the crital section */
			/* release the mutex so other threads can read a message */
			pthread_mutex_unlock(&vqr->rba->TheMutex);
	
	
			/* parse the variables out of the list */
			if ( parse_vars(vqr) == -1 )
			{
				print_status( vqr, "failure", "parse_vars: invalid input\n"); 
				vqr->have_status = RBS_FAILURE;
				continue;
			}
	
			/* clear out status field */
			memset(vqr->status_line, 0, MALLOC_TMPBUF_SIZE);
			memset(vqr->smtpbuf, 0, MALLOC_TMPBUF_SIZE);
			memset(vqr->to_addr, 0, MALLOC_TMPBUF_SIZE);

			if ( get_addr_vqr(vqr, 0) == -1 )
			{
				print_status( vqr, "failure", "invalid email address format\n");
				vqr->have_status = RBS_FAILURE;
				continue;
			}

			memset(vqr->user, 0, USER_DOMAIN_SIZE);
			memset(vqr->domain, 0, USER_DOMAIN_SIZE);
			parse_email( vqr->to_addr, vqr->user, vqr->domain, USER_DOMAIN_SIZE );
			
		 	vqr->is_yahoo = 0; 
		 
			/*for	(i=0; YahooDomains[i]!=NULL; ++i)
			{
				if ((strcmp(vqr->domain, YahooDomains[i]) == 0))
				{
					vqr->is_yahoo = 1;
					break;
				}
			}*/
		

			vqr->is_aol = 0; 
			/* are we aol? */
			for (i=0; AOLDomains[i]!=NULL; ++i)
			{
				if((strcmp(vqr->domain, AOLDomains[i]) == 0))
				{
					vqr->is_aol 		= 1;
					break;
				}
			}

//			if((strcmp(vqr->domain, "hotmail.com") == 0) || (strcmp(vqr->domain, "msn.com") == 0) || (strcmp(vqr->domain, "live.com") == 0))
//				continue;				

			/* are we yahoo? */
			if (vqr->is_aol == 1)
			{
				vqr->bodymem_size 	= vqr->mqa->bodymem_size_aol;
				vqr->bodymem		= vqr->mqa->bodymem_aol;
			}
			else if(vqr->is_yahoo == 1)
			{
				vqr->bodymem_size 	= vqr->mqa->bodymem_size_yahoo;
				vqr->bodymem 		= vqr->mqa->bodymem_yahoo;
			}
			/* no aol plain msg */
			else
			{
				vqr->bodymem_size 	= vqr->mqa->bodymem_size;
				vqr->bodymem		= vqr->mqa->bodymem;
			}
	
			/* first time, get memory for the message body */
			if ( vqr->mess == NULL )
			{
				/* first time through we just get memory */
				vqr->malloc_mess_size 	= vqr->bodymem_size + (vqr->mqa->max_vars * MAX_VARS_LEN) + 1000;
				vqr->mess 				= malloc(vqr->malloc_mess_size);
				vqr->mess_tmp 			= malloc(vqr->malloc_mess_size);
			/* if we already have memory, but if this message is bigger
			 * than what we already have, get new memory
			 */
			}
			else if ( vqr->malloc_mess_size < (vqr->bodymem_size + (vqr->mqa->max_vars * MAX_VARS_LEN) + 1000) )
			{
				vqr->malloc_mess_size 	= vqr->bodymem_size + (vqr->mqa->max_vars * MAX_VARS_LEN) + 1000;
				vqr->mess 				= realloc(vqr->mess, vqr->malloc_mess_size);
				vqr->mess_tmp 			= realloc(vqr->mess_tmp, vqr->malloc_mess_size);
			}
	
			/* If we could not get memory, report error and exit thread */
			if ( vqr->mess == NULL )
			{
				print_status( vqr, "failure", "malloc malloc_mess_size failed\n");
				vqr->have_status = RBS_DEFERRAL;
				continue;
			}
	

			/* format the date string */
			memset(vqr->date_str, 0, MALLOC_TMPBUF_SIZE);
			get_date_header(vqr);
	
			/* clear the message memory and put in the 
			 * To: Date: and Message-ID: headers 
			 */
			memset(vqr->mess,0,vqr->malloc_mess_size);
			memset(vqr->mess_tmp,0,vqr->malloc_mess_size);
	
			vqr->malloc_mess_max = vqr->malloc_mess_size-5;
	
			/*snprintf(vqr->mess, vqr->malloc_mess_size, "To: %s\r\n%s\r\nMessage-ID: <%ld.%ld@%s>\r\n", 
				&vqr->Vars[0][0], vqr->date_str, vqr->start_time, vqr->rba->CurMessId, IPS[vqr->OurIP]->host);*/
	
			snprintf(vqr->mess, vqr->malloc_mess_size, "%s\r\nTo: <%s>\r\n", vqr->date_str, &vqr->Vars[0][0]);
	
			vqr->mess_size = strlen(vqr->mess); 

			/* Format the message and substitute variables */
			vars_do(vqr, 1);

			/* Sign message with domain keys */
			if (vqr->is_aol != 1)
				vqr_sign(vqr);

			/* SMTP wants DATA command to end with ".\r\n" */
			vqr->mess[vqr->mess_size++] = '\r';
			vqr->mess[vqr->mess_size++] = '\n';
			vqr->mess[vqr->mess_size++] = '.';
			vqr->mess[vqr->mess_size++] = '\r';
			vqr->mess[vqr->mess_size++] = '\n';
	
			//printf("End message: [%s]\n", vqr->mess);
			//continue;
		// end of skip_2
		}
		else
			memset(vqr->mail_domain_prior, 0, MALLOC_TMPBUF_SIZE );
	
		vqr->skip_2 = 0;

		if ( vqr->cur_sends_per_conn >= 1 )
		{
			// close with a quit
			if (vqr->sock != -1)
			{
				snprintf(vqr->smtpbuf, TMPBUF_SIZE, "QUIT\r\n");
				vqr->write_size = strlen(vqr->smtpbuf);
				wait_write( vqr, vqr->smtpbuf, vqr->write_size);
				if ( vqr->write_ret >0 && vqr->write_size == vqr->write_ret )
				{
					wait_read(vqr);
				}
			}
			close(vqr->sock); 
			vqr->sock = -1;
			memset(vqr->mail_domain_prior, 0, MALLOC_TMPBUF_SIZE );
			vqr->tv.tv_sec = vqr->rba->sleep;
			vqr->tv.tv_usec = 0; 
			select(0, (fd_set *)0,(fd_set *)0, (fd_set *) 0, &vqr->tv);
		}


		if ( strcmp(vqr->mail_domain, vqr->mail_domain_prior) != 0 || vqr->sock == -1 || vqr->force_new_connect == 1)
		{
			vqr->force_new_connect = 0;
			/* if it's a different domain and the socket is still open */
			if ( vqr->sock != -1 )
			{ 
				// close with a quit
				snprintf(vqr->smtpbuf, TMPBUF_SIZE, "QUIT\r\n");
				vqr->write_size = strlen(vqr->smtpbuf);
				wait_write( vqr, vqr->smtpbuf, vqr->write_size);
				if ( vqr->write_ret >0 && vqr->write_size == vqr->write_ret )
				{
					wait_read(vqr);
				}
				memset(vqr->mail_domain_prior, 0, MALLOC_TMPBUF_SIZE );
				close(vqr->sock); 
				vqr->sock = -1;
			}
	
			/* connect to the remote smtp server */
			if ( vqr_connect_it( vqr ) != 0 )
			{ 
				if(vqr->yahoo_tries < 10 && vqr->is_yahoo == 1)
				{
					printf("connect trying again\n");
					memset(vqr->mail_domain_prior, 0, MALLOC_TMPBUF_SIZE );
					vqr->skip_2 = 1;
					++ vqr->yahoo_tries;
				}
				continue;
			}
	
			/* read the first received smtp message */
			smtp_code(vqr);
			if ( vqr->return_code >= 400 && vqr->return_code < 500	)
			{
				if(vqr->yahoo_tries < 10 && vqr->is_yahoo == 1)
				{
					printf("trying again\n");
					vqr->skip_2 = 1;
					++ vqr->yahoo_tries;
				}
				else
				{
					print_status( vqr, "deferral 23 ", vqr->smtpbuf); 
					vqr->have_status = RBS_DEFERRAL;
				}

				continue;
			}
			else if ( vqr->return_code != 220 )
			{
				close_status( vqr, "failure 1", vqr->smtpbuf); 
				vqr->have_status = RBS_FAILURE;
				continue;
			}
	
			if (vqr->read_ret == 46)
			{
				//printf("gunna read some more");
				wait_read(vqr);
			} 
	
			/* Send the HELO string */
			vqr->mta = rand()%9 + 1;
			//printf("rand: %d\n", ret);	
			snprintf(vqr->smtpbuf, TMPBUF_SIZE, "HELO mta%d.%s\r\n", vqr->mta, IPS[vqr->OurIP]->host );
			/*snprintf(vqr->smtpbuf, TMPBUF_SIZE, "HELO %s\r\n", vqr->Vars[3] );*/
			vqr->write_size = strlen(vqr->smtpbuf);
			wait_write( vqr, vqr->smtpbuf, vqr->write_size);
			if ( vqr->write_ret <=0 || vqr->write_size != vqr->write_ret )
			{
				close_status( vqr, "deferral 22", "write helo error");
				vqr->have_status = RBS_DEFERRAL;
				continue;
			}
	
			/* they may send back multiple lines of 220 */
			if ( wait_read( vqr ) <= 0 )
			{
				close_status( vqr, "deferral 21", "read helo error"); 
				vqr->have_status = RBS_DEFERRAL;
				continue;
			}
			smtp_code(vqr);

	
			/* if we got back 220 it is solaris returning more of the greeting
			 * so lets try once more 
			 */
			if ( vqr->return_code == 220 )
			{
				if ( wait_read( vqr ) <= 0 )
				{
					close_status( vqr, "deferral 20", "read helo error2"); 
					vqr->have_status = RBS_DEFERRAL;
					continue;
				}
				smtp_code(vqr);
			}

			if ( vqr->return_code >= 400 && vqr->return_code < 500	)
			{
				if(vqr->yahoo_tries < 10 && vqr->is_yahoo == 1)
				{
					printf("trying again\n");
					vqr->skip_2 = 1;
					++ vqr->yahoo_tries;
				}
				else
				{
					print_status( vqr, "deferral helo", vqr->smtpbuf); 
					vqr->have_status = RBS_DEFERRAL;
				}
				
				continue;
			}
			else if ( vqr->return_code != 250 )
			{
				print_status( vqr, "failure 2", vqr->smtpbuf); 
				vqr->have_status = RBS_DEFERRAL;
				continue;
			}
		// end domain prior
		}

		++vqr->cur_sends_per_conn;

		/* Send the MAIL FROM string */
		memset(vqr->smtpbuf, 0, MALLOC_TMPBUF_SIZE );

		if (FROMS[vqr->OurFrom]->domain[0] != '{')
			snprintf(vqr->smtpbuf, TMPBUF_SIZE, "MAIL FROM: <%s@%s>\r\n", FROMS[vqr->OurFrom]->local, FROMS[vqr->OurFrom]->domain);		
		else
			snprintf(vqr->smtpbuf, TMPBUF_SIZE, "MAIL FROM: <%s@%s>\r\n", FROMS[vqr->OurFrom]->local, IPS[vqr->OurIP]->host);

		vqr->write_size = strlen(vqr->smtpbuf);
		wait_write( vqr, vqr->smtpbuf, vqr->write_size);
		if ( vqr->write_ret <=0 || vqr->write_size != vqr->write_ret )
		{
			if(vqr->yahoo_tries < 10 && vqr->is_yahoo == 1)
			{
				 vqr->skip_2 = 1;
				++ vqr->yahoo_tries;
			}
			else
			{
				close_status( vqr, "deferral 19", "write mail from error"); 
				vqr->have_status = RBS_DEFERRAL;
			}
			continue;
		}
	
		/* read the result */
		if ( wait_read( vqr ) <=0 )
		{
			if(vqr->yahoo_tries < 10 && vqr->is_yahoo == 1)
			{
				 vqr->skip_2 = 1;
				++ vqr->yahoo_tries;
			}
			else
			{
				close_status( vqr, "deferral 18", "read mail from error"); 
				vqr->have_status = RBS_DEFERRAL;
			}
			continue;
		}

		smtp_code(vqr);

		if ( vqr->return_code >= 500 && vqr->return_code!=503)
		{
			print_status( vqr, "failure 3", vqr->smtpbuf); 
			vqr->have_status = RBS_FAILURE;
			continue;
		}
		if ( vqr->return_code >= 400 )
		{
			print_status( vqr, "deferral 2", vqr->smtpbuf);
			if(vqr->yahoo_tries < 10 && vqr->is_yahoo == 1)
			{
				 vqr->skip_2 = 1;
				++ vqr->yahoo_tries;
			}
			else 
				vqr->have_status = RBS_DEFERRAL;

			continue;
		}
		

		/* send the RCPT TO string */
		memset(vqr->smtpbuf, 0, MALLOC_TMPBUF_SIZE );
		snprintf(vqr->smtpbuf, TMPBUF_SIZE, "RCPT TO: <%s>\r\n", vqr->to_addr);
		vqr->write_size = strlen(vqr->smtpbuf);
		wait_write( vqr, vqr->smtpbuf, vqr->write_size);

		if ( vqr->write_ret <=0 || vqr->write_size != vqr->write_ret )
		{
			if(vqr->yahoo_tries < 10 && vqr->is_yahoo == 1)
			{
				printf("trying again\n");
				vqr->skip_2 = 1;
				++ vqr->yahoo_tries;
			}
			else
			{
				close_status( vqr, "deferral 17", "write rcpt to error"); 
				vqr->have_status = RBS_DEFERRAL;
			}

			continue;
		}

		/* read the result */
		if ( wait_read( vqr ) <=0 )
		{
			if(vqr->yahoo_tries < 10 && vqr->is_yahoo == 1)
			{
				printf("trying again\n");
				vqr->skip_2 = 1;
				++ vqr->yahoo_tries;
			}
			else
			{
				close_status( vqr, "deferral 16", "read rcpt to error"); 
				vqr->have_status = RBS_DEFERRAL;
			}

			continue;
		}

		smtp_code(vqr);
		if ( vqr->return_code >= 500 ) {
			vqr->have_status = RBS_FAILURE;
			print_status( vqr, "failure 4", vqr->smtpbuf); 
			continue;
		}
		if ( vqr->return_code >= 400 )
		{
			if(vqr->yahoo_tries < 10 && vqr->is_yahoo == 1)
			{
				printf("trying again\n");
				vqr->skip_2 = 1;
				++ vqr->yahoo_tries;
			}
			else
			{
				print_status( vqr, "deferral 3", vqr->smtpbuf); 
				vqr->have_status = RBS_DEFERRAL;
			}

			continue;
		}

		/* Send the DATA string */
		memset(vqr->smtpbuf,0,MALLOC_TMPBUF_SIZE);
		snprintf(vqr->smtpbuf, TMPBUF_SIZE, "DATA\r\n");
		vqr->write_size = strlen(vqr->smtpbuf);
		wait_write( vqr, vqr->smtpbuf, vqr->write_size);
		if ( vqr->write_ret <=0 || vqr->write_size != vqr->write_ret )
		{
			close_status( vqr, "deferral 4", "write data error"); 
			vqr->have_status = RBS_DEFERRAL;
			continue;
		}

		/* read the result */
		if ( wait_read( vqr ) <=0 )
		{
			if(vqr->yahoo_tries < 10 && vqr->is_yahoo == 1)
			{
				 vqr->skip_2 = 1;
				 ++ vqr->yahoo_tries;
			}
			else
			{
				 close_status( vqr, "deferral 15", "read data error"); 
				 vqr->have_status = RBS_DEFERRAL;
			}
			continue;
		}

		smtp_code(vqr);

		if ( vqr->return_code >= 500 )
		{
			print_status( vqr, "failure 5", vqr->smtpbuf); 
			vqr->have_status = RBS_FAILURE;
			continue;
		}
		else if ( vqr->return_code >= 400 )
		{
			if(vqr->yahoo_tries < 10 && vqr->is_yahoo == 1)
			{
				printf("trying again\n");
				vqr->skip_2 = 1;
				++ vqr->yahoo_tries;
			}
			else
			{
				print_status( vqr, "deferral 5", vqr->smtpbuf); 
				vqr->have_status = RBS_DEFERRAL;
			}
			continue;
		}

		/* send the message */
		for( i=0, j=0; i<vqr->mess_size && j!=-1;)
		{
			if ( (j = wait_write( vqr, &vqr->mess[i],	vqr->mess_size-i)) == -1 )
			{
				if(vqr->yahoo_tries < 10 && vqr->is_yahoo == 1)
				{
					printf("trying again\n");
					vqr->skip_2 = 1;
					++ vqr->yahoo_tries;
				}
				else
				{
					close_status( vqr, "deferral 14", "connected but write failed"); 
					vqr->have_status = RBS_DEFERRAL;
				}
				break;
			}
			else
				i += j;
		}

		if(vqr->have_status == RBS_DEFERRAL || vqr->skip_2 == 1)
			continue;

		/* read the final result */
		if ( wait_read( vqr ) <= 0 )
		{
			if(vqr->yahoo_tries < 10 && vqr->is_yahoo == 1)
			{
				printf("trying again\n");
				vqr->skip_2 = 1;
				++ vqr->yahoo_tries;
			}
			else
			{
				close_status( vqr, "deferral 12", "read data error"); 
				vqr->have_status = RBS_DEFERRAL;
			}
			continue;
		}
		
		smtp_code(vqr);

		if ( vqr->return_code >= 500 )
		{
			vqr->have_status = RBS_FAILURE;
			print_status( vqr, "failure 6", vqr->smtpbuf); 
		}
		else if ( vqr->return_code >= 400 )
		{
			if(vqr->yahoo_tries < 10 && vqr->is_yahoo == 1)
			{
				printf("trying again\n");
				vqr->skip_2 = 1;
				++ vqr->yahoo_tries;
			}
			else
			{
				//printf("deferral 6 [%i] [%s] [%s] %s\n", vqr->vqridx, IPS[vqr->OurIP], vqr->mail_domain_prior, vqr->smtpbuf); 
				print_status( vqr, "deferral 6", vqr->smtpbuf); 
				vqr->have_status = RBS_DEFERRAL;
			}
		}
		else
		{
			print_status( vqr, "success", vqr->smtpbuf); 
			vqr->have_status = RBS_SUCCESS;
		}
	}

	pthread_exit(0);
}

int get_dc_ip( vqr_struct *vqr )
{
 struct in_addr in;

	vqr->tmpstr = vqr->to_addr;
	while( *vqr->tmpstr != '@' && *vqr->tmpstr != 0 ) ++vqr->tmpstr;
	if ( *vqr->tmpstr == 0 ) {
		return(-1);
	}

	++vqr->tmpstr;
	if ( *vqr->tmpstr == 0 ) {
		return(-1);
	}

	vqr->myin = dc_check( vqr, vqr->tmpstr ); 
	if ( vqr->myin == NULL ) return(-1);
	memcpy(&in,vqr->myin, sizeof( struct in_addr ) );
	if ( strcmp(inet_ntoa(in), "0.0.0.0" ) == 0 ) {
		return(-1);
	}

	if ( vqr->myin == NULL ) {
		return(-1);
	}
	return(0);
}


/*
 * Get the IP of the mail server
 */
int fill_mail_ip( vqr_struct *vqr ) 
{
 int i;
 int done;
 short tmpdata;
	
	vqr->tmpstr = vqr->to_addr;
	while( *vqr->tmpstr != '@' && *vqr->tmpstr != 0 ) ++vqr->tmpstr;
	if ( *vqr->tmpstr == 0 ) return(-1);

	++vqr->tmpstr;
	if ( *vqr->tmpstr == 0 ) return(-1);

	/* clear out the mail host variable */
	memset(vqr->mail_host, 0, MALLOC_TMPBUF_SIZE);
	memset(vqr->mail_domain, 0, MALLOC_TMPBUF_SIZE);

	strncpy(vqr->mail_domain,vqr->tmpstr, MALLOC_TMPBUF_SIZE);

	/* do a dns query using tmpstr as the host */
	vqr->ret = dns_query(vqr->dns, T_MX, 0, vqr->tmpstr);
	dns_reset(vqr->dns);

	/* on failure save the hostname in vqr->mail_host */ 
	if ( vqr->ret == 0 ) {
		vqr->dns->ans_cnt = 1;
		if ( vqr->dns->answers == NULL ) dns_malloc(vqr->dns);
		strncpy( (char *)vqr->dns->answers[0]->answer, vqr->tmpstr, TMPBUF_SIZE);
		vqr->dns->answers[0]->data = 0;
	}
	if ( vqr->dns->ans_cnt <= 1 ) return(0);

	/* bubble sort */
	for(done=0;done==0;) {
		done = 1;
		for(i=1;i<vqr->dns->ans_cnt;++i) {
			if ( vqr->dns->answers[i]->data < vqr->dns->answers[i-1]->data ) {
				done = 0;
	tmpdata = vqr->dns->answers[i-1]->data;
				strncpy((char *)vqr->dnstmp,
					(char *)vqr->dns->answers[i-1]->answer,TMPBUF_SIZE);

	vqr->dns->answers[i-1]->data = vqr->dns->answers[i]->data;
				strncpy((char *)vqr->dns->answers[i-1]->answer,
								(char *)vqr->dns->answers[i]->answer,TMPBUF_SIZE);

	vqr->dns->answers[i]->data = tmpdata; 
				strncpy((char *)vqr->dns->answers[i]->answer,
								(char *)vqr->dnstmp,TMPBUF_SIZE);
			}
		}
	}
	return(0);
}

int get_host_ip( vqr_struct *vqr, char *answer )
{
  vqr->ret = dns_query(vqr->dns1, T_A, 0, (char *)answer );
  dns_reset(vqr->dns1);

  /* return any error */
  if ( vqr->ret == 0 ) return(-1);

  /* no answers, that's a problem */
  if ( vqr->dns1->ans_cnt == 0 ) {
    printf("FATAL ERROR!!! ans_cnt == 0\n");
    return(-1);
  }
  return(0);
}

int vqr_connect_it (vqr_struct *vqr) 
{
	int i,j;
	int dcip;

	/*printf("connect: %d %s\n", vqr->vqridx, vqr->Vars[0]);*/
	for(j=0;j<1;++j)
	{
		/* get the socket */
		if ( (vqr->sock = socket(AF_INET,SOCK_STREAM,0)) == -1 )
		{
			printf("socket create error err=%s\n",strerror(errno));
			close_status( vqr, "deferral", "socket socket error"); 
			vqr->have_status = RBS_DEFERRAL;
			break;
		}

		vqr->cur_sends_per_conn = 0;

		/* bind to the current IP maintained by rba structure */
		memset(&vqr->sin, 0, sizeof(struct sockaddr_in));
		vqr->sin.sin_family = AF_INET;
		inet_aton( IPS[vqr->OurIP]->ip, (struct in_addr *)&vqr->sin.sin_addr.s_addr);
		/*vqr->sin.sin_addr.s_addr = inet_addr(IPS[vqr->OurIP]->ip);*/

		if (bind(vqr->sock,(struct sockaddr *) &vqr->sin, sizeof(struct sockaddr_in)) == -1)
		{
			close_status( vqr, "deferral", "socket bind error"); 
			vqr->have_status = RBS_DEFERRAL;
			break;
		}

		if ( fcntl(vqr->sock,F_SETFL, fcntl(vqr->sock,F_GETFL,0) | O_NONBLOCK) == -1 )
		{
			close_status( vqr, "deferral", "fcntl nonblock error"); 
			vqr->have_status = RBS_DEFERRAL;
			break;
		}

		/* setup the destination address */
		memset(&vqr->sin,0,sizeof(struct sockaddr_in));	
		vqr->sin.sin_family = AF_INET;
		vqr->sin.sin_port = htons(25);

		// check dns cache
		if ( get_dc_ip( vqr ) == 0 )
		{
			memcpy(&vqr->sin.sin_addr,vqr->myin, sizeof( struct in_addr ) );
			dcip = 1;
		}
		else
		{
			dcip = 0;

			// Get the IP of the Mail Server to send this one too
			if ( get_mail_ip( vqr ) != 0 )
			{
				close_status( vqr, "deferral", "dns error 1"); 
				vqr->have_status = RBS_DEFERRAL;
				break;
			}
	
			// pick random one of multi ip addresses for the remote mail server
			if ( vqr->dns->ans_cnt == 0 )
			{
				close_status( vqr, "deferral", "dns error 2"); 
				vqr->have_status = RBS_DEFERRAL;
				break;
			}

			i = random()%vqr->dns->ans_cnt;
			memcpy(&vqr->sin.sin_addr,vqr->dns->answers[i]->answer, sizeof(struct in_addr));
		}

		// debug mode
		//inet_aton("127.0.0.1", &vqr->sin.sin_addr);

		FD_ZERO(&vqr->wfds);
		FD_SET(vqr->sock,&vqr->wfds);

		vqr->tv.tv_sec 	= IConnectTimeout; 
		vqr->tv.tv_usec = 0;
		vqr->connecterr = 0;

		if ( connect(vqr->sock, (struct sockaddr *) &vqr->sin, sizeof(struct sockaddr_in)) == -1 )
		{
			if ( errno != EINPROGRESS && errno != EWOULDBLOCK )
			{
				close_status( vqr, "deferral", "connect error"); 
				vqr->have_status = RBS_DEFERRAL;
				break;
			}
		}

		/* wait for the connect */
		if ((i=select(vqr->sock+1,(fd_set *)0,&vqr->wfds,(fd_set *)0,&vqr->tv)) == -1)
		{
			close_status( vqr, "deferral", "select error"); 
			vqr->have_status = RBS_DEFERRAL;
			break;
		}
		if (FD_ISSET(vqr->sock,&vqr->wfds)==0)
		{
			close_status( vqr, "deferral", "connect timeout"); 
			vqr->have_status = RBS_DEFERRAL;
			
			if ( dcip == 1 )
				continue;
			else
				break; 
		}

		if ( wait_read( vqr ) <=0 )
		{
			close_status( vqr, "deferral", "connect read timeout"); 
			vqr->have_status = RBS_DEFERRAL;
			break;
		}
		else 
			return(0);
	}

	memset(vqr->mail_domain_prior, 0, sizeof(vqr->mail_domain_prior)); 
	return(-1);
}

int vqr_sign (vqr_struct *vqr)
{
	/* Sign it with domain keys */
	vqr->dk.dk_dk = dk_sign(vqr->dk.dk_lib, &vqr->dk.dk_stat, DK_CANON_NOFWS);

	if (vqr->dk.dk_stat != DK_STAT_OK)
	{
		printf("dk sign failed\n");
		return -1;
	}

	vqr->dk.dk_stat = dk_message(vqr->dk.dk_dk, (unsigned char *)vqr->mess, vqr->mess_size);

	if (vqr->dk.dk_stat != DK_STAT_OK)
	{
		printf("dk message failed\n");
		dk_free(vqr->dk.dk_dk, 1);
		return -1;
	}

	vqr->dk.dk_stat = dk_end(vqr->dk.dk_dk, &vqr->dk.dk_flags);

	if (vqr->dk.dk_stat != DK_STAT_OK)
	{
		printf("dk eom failed\n");
		dk_free(vqr->dk.dk_dk, 1);
		return -1;
	}

	vqr->dk.dk_stat = dk_getsig(vqr->dk.dk_dk, vqr->mqa->dk_keymem, vqr->dk.dk_sig, sizeof(vqr->dk.dk_sig));

	if (vqr->dk.dk_stat != DK_STAT_OK)
	{
		printf("dk getsig failed\n");
		dk_free(vqr->dk.dk_dk, 1);
		return -1;
	}

	dk_free(vqr->dk.dk_dk, 1);

	snprintf(vqr->mess_tmp, vqr->malloc_mess_size, "DomainKey-Signature: a=rsa-sha1; q=dns; c=nofws;\r\n"
												 "  s=gamma; d=%s;\r\n"
												 "  h=To:Date:%s;\r\n"
												 "  b=%s;\r\n%s", IPS[vqr->OurIP]->host, vqr->mqa->dk_headers, vqr->dk.dk_sig, vqr->mess);
	strcpy(vqr->mess, vqr->mess_tmp);

	vqr->mess_size  = strlen(vqr->mess);

	return 0;
}

void vars_do (vqr_struct *vqr, int first)
{
	int i,j,k,l,m,n,found, mess_size;
	char *mess_tmp;

	if (first == 1)
	{
		mess_tmp 	= vqr->bodymem;
		mess_size 	= vqr->bodymem_size;
	}
	else
	{
		mess_tmp 		= vqr->mess_tmp;
		mess_size 		= vqr->mess_size;
		vqr->mess_size 	= 0;
		memset(vqr->mess,0,vqr->malloc_mess_size);
	}

	found = 0;
	for(i=0;i<mess_size; ++i)
	{
		if ( vqr->mess_size >= vqr->malloc_mess_max)
		{
			printf("format error, size too big\n");
			break;
		}

		if ( mess_tmp[i]=='{' && mess_tmp[i+1]=='{' && mess_tmp[i+4]=='}' && mess_tmp[i+5]=='}' )
		{
			vqr->tmpbuf[0] = mess_tmp[i+2];
			vqr->tmpbuf[1] = mess_tmp[i+3];
			vqr->tmpbuf[2] = 0;

			/* the rot13 function */
			if ( vqr->tmpbuf[0] == 'x' && vqr->tmpbuf[1] == '0' )
			{
				for(j=0;j<vqr->VarsLen[0] && vqr->mess_size<vqr->malloc_mess_max;++j)
				{
					vqr->mess[vqr->mess_size++] = rot13(vqr->Vars[0][j]);
			  	}
			}
			/* The IP function */
			else if ( vqr->tmpbuf[0] == 'i' && vqr->tmpbuf[1] == 'p' )
			{
				for(j=0;IPS[vqr->OurIP]->ip[j]!=0 && vqr->mess_size<vqr->malloc_mess_max;++j)
				{
					vqr->mess[vqr->mess_size++] = IPS[vqr->OurIP]->ip[j];
				}
			}
			/* The host function */
			else if ( vqr->tmpbuf[0] == 'd' && vqr->tmpbuf[1] == 'n' )
			{
				for(j=0;IPS[vqr->OurIP]->host[j]!=0 && vqr->mess_size<vqr->malloc_mess_max;++j)
				{
					vqr->mess[vqr->mess_size++] = IPS[vqr->OurIP]->host[j];
				}
			}
			/* The from function */
			else if ( vqr->tmpbuf[0] == 'f' && vqr->tmpbuf[1] == 'l' )
			{
				 vqr->mess[vqr->mess_size++] = '"';

				for(j=0;FROMS[vqr->OurFrom]->name[j]!=0 && vqr->mess_size<vqr->malloc_mess_max;++j)
				{
					vqr->mess[vqr->mess_size++] = FROMS[vqr->OurFrom]->name[j];
				}

				vqr->mess[vqr->mess_size++] = '"';
				vqr->mess[vqr->mess_size++] = ' ';
				vqr->mess[vqr->mess_size++] = '<';

				for(j=0;FROMS[vqr->OurFrom]->local[j]!=0 && vqr->mess_size<vqr->malloc_mess_max;++j)
				{
					vqr->mess[vqr->mess_size++] = FROMS[vqr->OurFrom]->local[j];
				}

				vqr->mess[vqr->mess_size++] = '@';
				
				if (FROMS[vqr->OurFrom]->domain[0] != '{')
				{
					for(j=0;FROMS[vqr->OurFrom]->domain[j]!=0 && vqr->mess_size<vqr->malloc_mess_max;++j)
					{
						vqr->mess[vqr->mess_size++] = FROMS[vqr->OurFrom]->domain[j];
					}
				}
				else
				{
					for(j=0;IPS[vqr->OurIP]->host[j]!=0 && vqr->mess_size<vqr->malloc_mess_max;++j)
					{
						vqr->mess[vqr->mess_size++] = IPS[vqr->OurIP]->host[j];
					}
				}

				vqr->mess[vqr->mess_size++] = '>';
			}
			/* The mail id function */
			else if ( vqr->tmpbuf[0] == 'm' && vqr->tmpbuf[1] == '0' )
			{
				snprintf(vqr->mailid_buf, MAX_VARS_LEN, "%d", vqr->mqa->mail_id);
				for (j=0;vqr->mailid_buf[j]!=0 && vqr->mess_size<vqr->malloc_mess_max;++j)
				{
					vqr->mess[vqr->mess_size++] = vqr->mailid_buf[j];
				}
			}
			/* the random number function */
			else if ( vqr->tmpbuf[0] == 'y' )
			{
				l = vqr->tmpbuf[1] - '0';
				for(j=0;j<l && vqr->mess_size<vqr->malloc_mess_max;++j)
				{
					vqr->mess[vqr->mess_size++] = 'a' + random()%25;
		 		}
			}
			/* The "to user" function */
			else if ( vqr->tmpbuf[0] == 'u' && vqr->tmpbuf[1] == 'u' )
			{
				for(j=0;vqr->user[j]!=0 && vqr->mess_size<vqr->malloc_mess_max;++j)
				{
					vqr->mess[vqr->mess_size++] = vqr->user[j]; 
			 	}
			}
			/* The "to domain" function */
			else if ( vqr->tmpbuf[0] == 'd' && vqr->tmpbuf[1] == 'd' )
			{
				for(j=0; vqr->domain[j]!=0 && vqr->mess_size<vqr->malloc_mess_max; ++j)
				{
					vqr->mess[vqr->mess_size++] = vqr->domain[j]; 
				}
			}
	        /* vars substitution */
			else
			{
	        	l = atoi(vqr->tmpbuf);
				l -= 1;
	          	for(j=0;j<vqr->VarsLen[l]&&vqr->mess_size<vqr->malloc_mess_max;++j)
				{
					vqr->mess[vqr->mess_size++] = vqr->Vars[l][j];
	          	}
	        }
	        /* skip over the {{**}} */
	        i += 5;
			found = 1;	
		} 
		/* random string - random length */
		else if ( mess_tmp[i]=='{' && mess_tmp[i+1]=='{' && mess_tmp[i+7]=='}' && mess_tmp[i+8]=='}' )
		{
			vqr->tmpbuf[0] = mess_tmp[i+2];
			vqr->tmpbuf[1] = mess_tmp[i+3];
			vqr->tmpbuf[2] = mess_tmp[i+4];
			vqr->tmpbuf[3] = mess_tmp[i+5];
			vqr->tmpbuf[4] = mess_tmp[i+6];
			vqr->tmpbuf[5] = 0;
	
			if (vqr->tmpbuf[0] == 'y')
			{
				vqr->low[0] = vqr->tmpbuf[1];
				vqr->low[1] = vqr->tmpbuf[2];
				vqr->low[2] = 0;

				l = atoi(vqr->low);
				vqr->low[0] = vqr->tmpbuf[3];
				vqr->low[1] = vqr->tmpbuf[4];
				vqr->low[2] = 0;

				m = atoi(vqr->low);
				n = random()%((m+1)-l)+l;

				for (j=1;j<n&&vqr->mess_size<vqr->malloc_mess_max;j++)
				{
					vqr->mess[vqr->mess_size++] = 'a' + random()%25;
				}
			}

			i += 8;
			found = 1;
		}
		/* strftime */
		else if (mess_tmp[i]=='{' && mess_tmp[i+1]=='s' && mess_tmp[i+2]=='f'  && mess_tmp[i+3]=='t' && mess_tmp[i+4]=='{')
		{
			l = 6;
			vqr->date_str[0] = 0;
			/* loop the next 50 chars assuming they are the format untill we hit }} or max out */
			for (j=0;j<50;j++)
			{
				if (mess_tmp[i+(5+j)] == '}' && mess_tmp[i+(6+j)] == '}')
				{
					vqr->date_str[j] = 0;
					break;
				}
				l++;
				vqr->date_str[j] = mess_tmp[i+(5+j)];
			}

			vqr->tmpbuf[0] = 0;
			strftime(vqr->tmpbuf, MALLOC_TMPBUF_SIZE, vqr->date_str, &vqr->mytm);

			for (j=0;j<MALLOC_TMPBUF_SIZE&&vqr->mess_size<vqr->malloc_mess_max;j++)
			{
				if (vqr->tmpbuf[j] == 0)
					break;

				vqr->mess[vqr->mess_size++] = vqr->tmpbuf[j];
			}

			i += l;
			found = 1;
		}
		/* rotation */
		else if (mess_tmp[i]=='{' && mess_tmp[i+1]=='r' && mess_tmp[i+2]=='o'  && mess_tmp[i+3]=='{')
		{
			l 					= 5;
			vqr->date_str[0] 	= 0;

			/* loop next 50 chars assuming they the name untill }} or max out */
			for (j=0;j<50;j++)
			{
				if (mess_tmp[i+(4+j)] == '}' && mess_tmp[i+(5+j)] == '}')
				{
					vqr->date_str[j] = 0;					
					break;
				}
				l++;
				vqr->date_str[j] = mess_tmp[i+(4+j)];
			}

			for (j=0;j<vqr->mqa->rotated_content_count;j++)
			{
				if (strcmp(vqr->date_str, vqr->mqa->rotated_content[j].name) == 0)
				{
					for (m=0;m<=1000;m++)
					{
						if (vqr->mqa->rotated_content[j].data[vqr->our_rotation[j]][m] == 0)
							break;

						vqr->mess[vqr->mess_size++] = vqr->mqa->rotated_content[j].data[vqr->our_rotation[j]][m];
					}
					break;
				}
			}
			i += l;
			found = 1;
		}
		/* otherwise, just plain old text to copy */
		else
		{
			//printf("copying %c\n", mess_tmp[i]);
			vqr->mess[vqr->mess_size++] = mess_tmp[i];
		}
	}

	// if we had replacments go over it again untill we dont
	if (found == 1)
	{
		strncpy(vqr->mess_tmp, vqr->mess, vqr->malloc_mess_max);
		vars_do(vqr, 0);
	}
}

/* substitute {{XX}} vars into the from header
 * input:
 *   vqr vqr_struct pointer
 *   inmem char pointer to incoming pre parsed string
 *   insize int size of inmem string
 *   outmem char pointer to outgoing post parsed string
 *   outsize int size of outmem data to stop before buffer overruns
 * return:
 *   void, no status return
 */
void var_subs( vqr_struct *vqr, char *inmem,  int insize, char *outmem, int outsize ) 
{
	int in_idx,j,l,out_idx,m,n,o;
	char tmpbuf[3];

	memset(outmem,0,outsize);
  	for( in_idx=0,out_idx=0; out_idx<outsize && in_idx<insize && inmem[in_idx]!=0; ++in_idx)
	{
		if ( inmem[in_idx]=='{' && in_idx+1<insize && inmem[in_idx+1]=='{' && in_idx+4<insize && inmem[in_idx+4]=='}' && in_idx+5<insize && inmem[in_idx+5]=='}' )
		{
			tmpbuf[0] = inmem[in_idx+2];
			tmpbuf[1] = inmem[in_idx+3];
			tmpbuf[2] = 0;

			if ( tmpbuf[0] == 'y' )
			{
				l = tmpbuf[1] - '0';
				for(j=0;out_idx<outsize && j<l;++j)
				{
					outmem[out_idx++] = 'a' + random()%25;
				}
			/* skip over the {{**}} */
			}
			else if ( tmpbuf[0] == 'd' && tmpbuf[1] == 'n' )
			{
				for(j=0;out_idx<outsize && IPS[vqr->OurIP]->host[j]!=0 ;++j)
				{
					outmem[out_idx++] = IPS[vqr->OurIP]->host[j];
				}
			}
			else if ( tmpbuf[0] == 'x' && tmpbuf[1] == '0' )
			{
				for(j=0;out_idx<outsize && j<vqr->VarsLen[0];++j)
				{
					outmem[out_idx++] = rot13(vqr->Vars[0][j]);
				}
			}
			else if ( tmpbuf[0] == 'i' && tmpbuf[1] == 'p' )
			{
				for(j=0;out_idx<outsize && IPS[vqr->OurIP]->ip[j]!=0;++j)
				{
					outmem[out_idx++] = IPS[vqr->OurIP]->ip[j];
				}
			}
			else if ( isdigit(tmpbuf[0]) != 0 && isdigit(tmpbuf[1]) != 0 )
			{
				l = atoi(tmpbuf);
				l -= 1;
				for(j=0;out_idx<outsize&&j<vqr->VarsLen[l];++j)
				{
					outmem[out_idx++] = vqr->Vars[l][j];
				}
			}
			else if ( tmpbuf[0] == 'u' && tmpbuf[1] == 'u' )
			{
				for(j=0;out_idx<outsize && vqr->user[j]!=0;++j)
				{
					outmem[out_idx++] = vqr->user[j];
				}
			}
			else if ( tmpbuf[0] == 'd' && tmpbuf[1] == 'd' )
			{
				for(j=0;out_idx<outsize && vqr->domain[j]!=0;++j)
				{
					outmem[out_idx++] = vqr->domain[j];
				}
			}

			in_idx += 5;
		}
		/* random string between xx and xx 
		else if ( inmem[in_idx]=='{' && in_idx+1<insize && inmem[in_idx+1]=='{' && in_idx+7<insize && inmem[in_idx+7]=='}' && in_idx+8<insize && inmem[in_idx+8]=='}' )
		{
			printf("anything?\n");

			tmpbuf[0] = inmem[in_idx+2];
			tmpbuf[1] = inmem[in_idx+3];
			tmpbuf[2] = inmem[in_idx+4];
			tmpbuf[3] = inmem[in_idx+5];
			tmpbuf[4] = inmem[in_idx+6];
			tmpbuf[5] = 0;

			if (tmpbuf[0] == 'y')
			{
				strcpy(low, tmpbuf[0]);
				strcat(low, tmpbuf[1]);

				printf("catted string %s\n", low);
			}

			in_idx += 8;
		}
		/* otherwise, just plain old text to copy */
		else
		{
			outmem[out_idx++] = inmem[in_idx];
		}
	}
	outmem[out_idx] = 0;
}

/*
 * Print the status of an email delivery to standard out.
 * Standard out gets time stamped by multilog so we can time
 * mail deliveries.
 *
 * input: vqr vqr_struct pointer to the thread private data
 * output: to standard out
 * return: void, no value returned on the stack
 */
void print_status( vqr_struct *vqr, char *status, char *status_text ) 
{

  /*snprintf(vqr->status_line, TMPBUF_SIZE, 
    "%s: %s : %s",
    status, vqr->to_addr, status_text); */

  /*snprintf(vqr->status_line, TMPBUF_SIZE, 
    "%s: %d : %d : %s : %ld.%ld : %d : %s : %s : %s : %s : %s : %s",
    status, vqr->vqridx, vqr->mqa->mail_id, vqr->to_addr, secs, usecs, vqr->bytes_sent,
    IPS[vqr->OurIP]->ip, IPS[vqr->OurIP]->host, 
    inet_ntoa(vqr->sin.sin_addr), 
    vqr->mail_host, vqr->mail_domain,
    status_text); */

	snprintf(vqr->status_line, TMPBUF_SIZE, 
    "%s: %s : %s : %s : %s : %s : %s : %d : %s",
    status, vqr->to_addr, 
    IPS[vqr->OurIP]->ip, IPS[vqr->OurIP]->host, 
    inet_ntoa(vqr->sin.sin_addr), 
    vqr->mail_host, vqr->mail_domain,
	(int) vqr->start_time,
    status_text); 

    
}

inline void close_status(struct vqr_struct *vqr, char *status, char *mesg )
{
  print_status( vqr, status, mesg );
  if ( vqr->sock != -1 ) close(vqr->sock);
  vqr->sock = -1;
  vqr->prior_status = RBS_NO_STATUS;
  memset(vqr->mail_domain_prior, 0, MALLOC_TMPBUF_SIZE);
  memset(vqr->mail_domain, 0, MALLOC_TMPBUF_SIZE);
}

int in_mail_from_sites(struct vqr_struct *vqr) 
{
 int i;

  for(i=0;MailFromSites[i]!=NULL;++i) {
    if ( strcmp(vqr->mail_domain, MailFromSites[i]) == 0 ) return(1);
  }
  return(0);
}
