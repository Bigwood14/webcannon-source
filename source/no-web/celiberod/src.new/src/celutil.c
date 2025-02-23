#include <stdlib.h>
#include <stdio.h>
#include <unistd.h>
#include <string.h>
#include <time.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <signal.h>
#include <arpa/inet.h>
#include <sys/un.h>
#include <net/if.h>
#include <sys/ioctl.h>
#include <sys/socket.h>
#include <linux/if_ether.h>
#include <errno.h>
#include <ctype.h>
#include "../config.h"
#include "config.h"
#include "celcommon.h"
#include "celutil.h"
#include "custom.h"

extern char *strcasestr(char *haystack, char *needle);


void lowerit(char *instr);

char *DayStr[7] = { "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" };

char * MonStr[12] = { "Jan", "Feb", "Mar", "Apr", "May", "Jun",
                      "Jul", "Aug", "Sep", "Oct", "Nov", "Dec" };

#define TOKENS " ,:\n\t\r"

#define CONF_BUFF 100
char *MaxThreads = NULL;
char *SockFile = NULL;
char *SmtpSockFile = NULL;
char *HeloHost = NULL;
char *RWTimeout = NULL;
char *ConnectTimeout = NULL;
char *MysqlUser = NULL;
char *MysqlPasswd = NULL;
char *MysqlServer = NULL;
char *MysqlDatabase = NULL;
char *CloneIp = NULL;
char *CelDNS = NULL;

int IMaxThreads;
int IRWTimeout = 30;
int IConnectTimeout = 15;
int MailingWait;
int MailingWaitMicro;
int ServerID;
int SmtpThreads;
int CacheThreads;
int InetSocket;
int UnsubDeleteAll;
int DeleteOnBounce;
int MaxSendsPerConn = 1000;

int cel_init()
{
  
	check_cpuid();
	if ( strcmp( MACID, get_mac()) != 0 )
	{
		printf("Wrong MAC\n");
		exit(-17); 
	}

	if ( strcmp( CPUID, get_cpuid()) != 0 )
	{
		printf("Wrong CPU\n");
		exit(-15);
	}
  
  check_dead_time();
  /*set_perms();*/
  read_config();
  return(0);
}

void ignore_signals()
{
  signal(SIGPIPE,SIG_IGN);
  signal(SIGURG,SIG_IGN);
  signal(SIGIO,SIG_IGN);
}

/* 
 * parse out an email address
 *
 * instr  = string to parse for email address
 * outstr = memory to write parsed email address
 * outlen = len of outstr memory
 *
 * Return: 0 on success
 *        -1 on errors
 */
int get_addr(char *instr, char *startstr, char *outstr, int outlen, int rot )
{
 char *tmpstr;
 char *tmpstr1;
 char *outstr1;
 int   i;
 int   count;
 int   found_dot;
 char  atchar = '@';

   /* check for valid inputs */
   if ( instr == NULL || outstr == NULL || outlen < 3 ) {
     printf("get_addr: error 1\n");
     return(-1);
   }

   if ( rot == 1 ) atchar = '^';

   if ( *instr == atchar ) {
     printf("get_addr: error 2\n");
     return(-1);
   }

   tmpstr  = instr; 
   tmpstr1 = instr; 
   outstr1 = outstr;
   memset(outstr,0,outlen);

   /* find the @ character */
   if ( startstr == NULL ) {
     for(; *tmpstr != 0 ; ++tmpstr ) {
       if  ( rot ) *tmpstr = rot13(*tmpstr); 
       if ( *tmpstr == atchar ) break;
     }
     if ( *tmpstr == 0 ) {
       /*printf("get_addr: error 3 |%s|\n", instr);*/
       return(-1);
     }
   } else {
     tmpstr = startstr;
   }

   --tmpstr;
   while(1) {
     if  ( rot ) *tmpstr = rot13(*tmpstr); 
     if  ( tmpstr!=tmpstr1-1 && *tmpstr!='?' && *tmpstr!='=' &&
       ( *tmpstr == 33 || *tmpstr == 35 || 
	 *tmpstr == 36 || *tmpstr == 38 || *tmpstr == 42 || 
         /**tmpstr == 36 || *tmpstr == 38 || *tmpstr == 39 || *tmpstr == 42 || */
         *tmpstr == 43 || *tmpstr == 61 || *tmpstr == 63 ||
         (*tmpstr >= 45 && *tmpstr <=57 ) ||
         (*tmpstr >= 65 && *tmpstr <=90 ) ||
         (*tmpstr >= 94 && *tmpstr <=126 )) ) {
       ;
     } else {
       break;
     }
     --tmpstr;
   }

   i = 0;
   ++tmpstr;
   while(1) {
     if ( i<outlen-2 && *tmpstr!=atchar && *tmpstr!=0 ) {
       *outstr = *tmpstr;
     } else {
       break;
     }
     ++tmpstr,++outstr;++i;
   }
   if ( i == 0 ) {
     return(-1);
   }

   /* write out the @ character if we still have space */
   if ( i < outlen-2 ) {
     if ( rot ) *outstr++ = rot13(*tmpstr++);
     else *outstr++ = *tmpstr++;
     ++i;
   } 

   /* if we are at the end of the string already that is an error */
   if ( *tmpstr == 0 ) {
     *outstr = 0;
     /*printf("get_addr: error 5\n");*/
     return(-1);
   }

   /* walk forward writing valid hostname characters */
   found_dot=0;count=0; 
   while( 1 ) {
     if  ( rot ) *tmpstr = rot13(*tmpstr); 

     if ( i<outlen-2 && *tmpstr!=0 && 
        ( *tmpstr == 45 || *tmpstr == 46 || *tmpstr=='{' || *tmpstr=='}' ||
         (*tmpstr >= 48 && *tmpstr <= 57 ) ||
         (*tmpstr >= 65 && *tmpstr <= 90 ) ||
         (*tmpstr >= 97 && *tmpstr <= 122 )) ) {
       *outstr = *tmpstr;
     } else {
       break;
     }

     if ( count == 0 && *tmpstr == '.' ) {
       /*printf("get_addr: error 6\n");*/
       return(-1);
     }
     if ( *tmpstr == '.' ) found_dot = 1;
     ++tmpstr; ++outstr; ++i; ++count;
   }

   /* terminate the string with a 0 */
   *outstr = 0;
   if ( found_dot == 0 ) {
     return(-1);
   }

   if ( *(outstr-1) == '.' ) {
     return(-1); 
   }

   /* make it all lower case */
   lowerit(outstr1);
   if (count<=1) {
     /*printf("get_addr: error 8\n");*/
     return(-1);
   }
   return(0);
}


/*
 * Keep
 * Subject:
 * Mime-Version: 
 * Content-Type: multiline
 * Content-Transfer-Encoding: 
 * 
 * Add
 * From:
 * Return-Path:
 * 
 */
int strip_headers( char *filename, char *from_address, char *reply_to )
{
 FILE *fs1;
 FILE *fs2;
 char tmpfile[FILENAME_MAX];
 char tmpbuf[MAX_BUFF];
 char email_addr[MAX_BUFF];
 int keep_going;
 int get_nextline;
 int inner_going;

  if ( get_addr(from_address, NULL, email_addr, MAX_BUFF,0 ) == -1 ) {
    printf("error: from_address is invalid format %s\n", from_address);
  }

  /* open the file */
  if ( (fs1 = fopen(filename, "r")) == NULL ) {
    printf("error: could not open %s\n", filename);
    return(-1);
  }

  /* open a temp file */
  snprintf(tmpfile, FILENAME_MAX, "%s.tmp", filename);
  if ( (fs2 = fopen(tmpfile, "w")) == NULL ) {
    printf("error: could not open tmp file %s\n", tmpfile);
    return(-1);
  }


  /* we use keep_going = 1 to stay in the loop
   * we use get_nextline = 1 to call fgets 
   */
  for(keep_going=1,get_nextline=1; keep_going==1; ) {
    if ( get_nextline == 1 ) {
      if ( fgets(tmpbuf, MAX_BUFF, fs1) == NULL ) {
        keep_going = 0;
        continue;
      }
    }
    get_nextline = 1;

    /* a line starting with a blank is the end of the headers */
    if ( isspace(tmpbuf[0]) != 0 ) {
      keep_going = 0;
      fprintf( fs2, "%s", tmpbuf); 

    /* replace the from header with our own */
    } else if ( strncasecmp( "From:", tmpbuf, 5 ) == 0 ) {
      fprintf(fs2, "From: %s\r\n", from_address);
      fprintf(fs2, "Sender: <%s>\r\n", email_addr);
      fprintf(fs2, "Return-Path: <%s>\r\n", email_addr);
      fprintf(fs2, "X-Sender: <%s>\r\n", email_addr);
      if ( reply_to == NULL || reply_to[0] == 0 ) {
        fprintf(fs2, "Reply-To: <%s>\r\n", email_addr);
      } else {
        fprintf(fs2, "Reply-To: <%s>\r\n", reply_to);
      }

    /* we want the Subject header */
    } else if ( strncasecmp( "Subject:", tmpbuf, 8 ) == 0 ) {
      fprintf( fs2, "%s", tmpbuf); 

    /* we want the mime-version header */
    } else if ( strncasecmp( "Mime-Version:", tmpbuf, 13 ) == 0 ) {
      fprintf( fs2, "%s", tmpbuf); 

    /* we want the content transfer encoding header */
    } else if ( strncasecmp( "Content-Transfer-Encoding:", tmpbuf, 26 )==0) {
      fprintf( fs2, "%s", tmpbuf); 

    /* skip the multi line received header */
    } else if ( strncasecmp( "Received:", tmpbuf, 9 )==0) {
      for(inner_going=1;inner_going==1; ) {
        if ( fgets(tmpbuf, MAX_BUFF, fs1) == NULL ) {
          inner_going = 0;
          keep_going = 0;
        } else if ( tmpbuf[0] != ' ' && tmpbuf[0] != '\t' ) {
          get_nextline = 0;
          inner_going = 0;
        }
      }

    } else if ( strncasecmp( "Content-Type:", tmpbuf, 13 )==0) {
      fprintf( fs2, "%s", tmpbuf); 

      for(inner_going=1;inner_going==1; ) {
        if ( fgets(tmpbuf, MAX_BUFF, fs1) == NULL ) {
          inner_going = 0;
          keep_going = 0;

        /* if we don't have a space or tab in the first column
         * then this is the end of the multiline header 
         */ 
        } else if ( tmpbuf[0] != ' ' && tmpbuf[0] != '\t' ) {
          get_nextline = 0;
          inner_going = 0;
        } else {
          fprintf( fs2, "%s", tmpbuf); 
        }
      }
    }
  }

  /* continue through the rest of the file */
  while( fgets(tmpbuf, MAX_BUFF, fs1) != NULL ) {
    fputs(tmpbuf, fs2); 
  }


  /* all done, close the files */
  if ( fclose(fs1) != 0 ) {
    printf("error: closing file %s\n", filename);
    return(-1);
  }

  if ( fclose(fs2) != 0 ) {
    printf("error: closing tmp file %s\n", tmpfile );
    return(-1);
  }

  /* move the tmp file over the file */
  if ( rename(tmpfile, filename) != 0 ) {
    printf("error: renaming file %s to %s\n", tmpfile, filename);
  }

  return(0);
}

int fix_crlf(char *filename )
{
 FILE *fs1;
 FILE *fs2;
 char tmpfile[FILENAME_MAX];
 int c1;
 int lastc;
 int linesize;

  /* open the file */
  if ( (fs1 = fopen(filename, "r")) == NULL ) {
    printf("error: could not open %s\n", filename);
    return(-1);
  }

  /* open a temp file */
  snprintf(tmpfile, FILENAME_MAX, "%s.tmp", filename);
  if ( (fs2 = fopen(tmpfile, "w")) == NULL ) {
    printf("error: could not open tmp file %s\n", tmpfile);
    fclose(fs1);
    return(-1);
  }

  linesize = 0;
  lastc = 0;
  while ( (c1 = fgetc(fs1)) != EOF ) {

    /* attempt to limit line sizes
     * we decided to leave that up to the originator
    ++linesize;
    if ( linesize > 78 ) {
      fputc('\r', fs2);
      fputc('\n', fs2);
      linesize = 0;
    }
    */

    /* If the end of line does not have CR \r LF \n and
     * Just has LF \n then add a CF in front of the LF 
     */
    if ( c1 == '\n' && lastc != 0 && lastc!='\r') {
      fputc('\r', fs2); 
    }
    if ( c1 == '\n' ) linesize = 0;
    fputc(c1, fs2);
    lastc = c1;
  }

  /* Make sure the body ends with a CR LF */
  if ( lastc != '\n' ) {
    fputc('\r', fs2); 
    fputc('\n', fs2); 
  }

  if ( fclose(fs1) != 0 ) {
    printf("error: closing file %s\n", filename);
    return(-1);
  }

  if ( fclose(fs2) != 0 ) {
    printf("error: closing tmp file %s\n", tmpfile );
    return(-1);
  }

  /* move the tmp file over the file */
  if ( rename(tmpfile, filename) != 0 ) {
    printf("error: renaming file %s to %s\n", tmpfile, filename);
  }

  return(0);
}

void read_config()
{
 FILE *fs;
 char *param;
 char *value;
 char TmpBuf[MAX_BUFF];
 int  max_threads;
 char *tmpptr;

  if ( MaxThreads==NULL) MaxThreads = malloc(CONF_BUFF);
  if ( SockFile==NULL) SockFile = malloc(CONF_BUFF);
  if ( SmtpSockFile==NULL) SmtpSockFile = malloc(CONF_BUFF);
  if ( HeloHost==NULL) HeloHost = malloc(CONF_BUFF);
  if ( RWTimeout==NULL) RWTimeout = malloc(CONF_BUFF);
  if ( ConnectTimeout==NULL) ConnectTimeout = malloc(CONF_BUFF);
  if ( MysqlUser==NULL) MysqlUser = malloc(CONF_BUFF);
  if ( MysqlPasswd==NULL) MysqlPasswd = malloc(CONF_BUFF);
  if ( MysqlServer==NULL) MysqlServer = malloc(CONF_BUFF);
  if ( MysqlDatabase==NULL) MysqlDatabase = malloc(CONF_BUFF);
  if ( CloneIp==NULL) CloneIp = malloc(CONF_BUFF);
  if ( CelDNS==NULL) CelDNS = malloc(CONF_BUFF);

  memset(MaxThreads,0,CONF_BUFF);
  memset(SockFile,0,CONF_BUFF);
  memset(SmtpSockFile,0,CONF_BUFF);
  memset(HeloHost,0,CONF_BUFF);
  memset(RWTimeout,0,CONF_BUFF);
  memset(ConnectTimeout,0,CONF_BUFF);
  memset(MysqlUser,0,CONF_BUFF);
  memset(MysqlPasswd,0,CONF_BUFF);
  memset(MysqlServer,0,CONF_BUFF);
  memset(MysqlDatabase,0,CONF_BUFF);
  memset(CloneIp,0,CONF_BUFF);
  memset(CelDNS,0,CONF_BUFF);

  strncpy(SockFile, SOCK_FILE, CONF_BUFF);
  gethostname(HeloHost, CONF_BUFF);
  snprintf(RWTimeout, CONF_BUFF, "%d", RW_TIMEOUT);
  snprintf(ConnectTimeout, CONF_BUFF, "%d", CONNECT_TIMEOUT);
  snprintf(MaxThreads, CONF_BUFF, "%d", MAX_THREADS);

  strncpy(MysqlUser, MYSQL_USER, CONF_BUFF);
  strncpy(MysqlPasswd, MYSQL_PASSWD, CONF_BUFF);
  strncpy(MysqlServer, MYSQL_SERVER, CONF_BUFF);
  strncpy(MysqlDatabase, MYSQL_DATABASE, CONF_BUFF);

  strncpy(CelDNS, "127.0.0.1", CONF_BUFF);

	IMaxThreads 		= MAX_THREADS;
	IRWTimeout 			= RW_TIMEOUT;
	IConnectTimeout 	= CONNECT_TIMEOUT;
	MailingWait 		= 0;
	MailingWaitMicro 	= 0;
	SmtpThreads 		= SMTP_THREADS;
	CacheThreads 		= CACHE_THREADS;
	InetSocket 			= 0;
	UnsubDeleteAll 		= 1;
	DeleteOnBounce 		= 0;
	MaxSendsPerConn 	= 1000;

  snprintf(TmpBuf, MAX_BUFF, "%s/etc/celibero.conf", CELIBERODIR);

  if ( (fs=fopen(TmpBuf, "r"))==NULL) return;
  while ( fgets(TmpBuf,100,fs) != NULL) {
    tmpptr = TmpBuf;
    if ( (param = strsep(&tmpptr, TOKENS)) == NULL ) continue;
    if ( (value = strsep(&tmpptr, TOKENS)) == NULL ) continue;

    if ( strcmp( param, "max_threads") == 0 ) {
      strncpy(MaxThreads, value, CONF_BUFF);

    } else if ( strcmp( param, "hostname") == 0 ) {
      strncpy(HeloHost, value, CONF_BUFF);

    } else if ( strcmp( param, "rw_timeout") == 0 ) {
      strncpy(RWTimeout, value, CONF_BUFF);

    } else if ( strcmp( param, "connect_timeout") == 0 ) {
      strncpy(ConnectTimeout, value, CONF_BUFF);

    } else if ( strcmp( param, "sock_file") == 0 ) {
      strncpy(SockFile, value, CONF_BUFF);

    } else if ( strcmp( param, "smtp_sock_file") == 0 ) {
      strncpy(SmtpSockFile, value, CONF_BUFF);

    } else if ( strcmp( param, "mysql_user") == 0 ) {
      strncpy(MysqlUser, value, CONF_BUFF);

    } else if ( strcmp( param, "mysql_passwd") == 0 ) {
      strncpy(MysqlPasswd, value, CONF_BUFF);

    } else if ( strcmp( param, "mysql_server") == 0 ) {
      strncpy(MysqlServer, value, CONF_BUFF);

    } else if ( strcmp( param, "mysql_database") == 0 ) {
      strncpy(MysqlDatabase, value, CONF_BUFF);

    } else if ( strcmp( param, "server_id") == 0 ) {
      ServerID = atoi(value);

    } else if ( strcmp( param, "dns") == 0 ) {
      strncpy(CelDNS, value, CONF_BUFF);

    } else if ( strcmp( param, "smtp_threads") == 0 ) {
      SmtpThreads = atoi(value);

    } else if ( strcmp( param, "cache_threads") == 0 ) {
      CacheThreads = atoi(value);

    } else if ( strcmp( param, "max_sends_per_conn") == 0 ) {
      MaxSendsPerConn = atoi(value);

    } else if ( strcmp( param, "inet_socket") == 0 ) {
      if ( strcasecmp(value, "yes") == 0 ) {
        InetSocket = 1;
      }
    } else if ( strcmp( param, "unsub_delete_all") == 0 ) {
      if ( strcasecmp(value, "yes") == 0 ) {
        UnsubDeleteAll = 1;
      } else {
        UnsubDeleteAll = 0;
      }
    } else if ( strcmp( param, "delete_on_bounce") == 0 ) {
      if ( strcasecmp(value, "yes") == 0 ) {
        DeleteOnBounce = 1;
      } else {
        DeleteOnBounce = 0;
      }
    }
  }

  max_threads = 1000;

  IMaxThreads = atoi(MaxThreads);
  if (IMaxThreads > max_threads ) IMaxThreads = max_threads;
  if (IMaxThreads < 2 ) IMaxThreads = 2;
  snprintf(MaxThreads, CONF_BUFF, "%d", IMaxThreads);

  IRWTimeout = atoi(RWTimeout);
  if ( IRWTimeout < 1 ) IRWTimeout = 1;
  snprintf(RWTimeout, CONF_BUFF, "%d", IRWTimeout);

  IConnectTimeout = atoi(ConnectTimeout);
  if ( IConnectTimeout < 0 ) IConnectTimeout = 1;
  snprintf(ConnectTimeout, CONF_BUFF, "%d", IConnectTimeout);

  fclose(fs);

}

char *comma_string(char *instr)
{
 char *tmpbuf;
 int len;
 int num_commas;
 int skip_start;
 int i,j,l;

  tmpbuf = malloc(100);
  memset(tmpbuf,0,100);
  len = strlen(instr);
  num_commas=(len-1)/3;

  l = 0;
  skip_start = len - (num_commas*3);
  for(i=0;i<skip_start;++i) {
    tmpbuf[l++] = instr[i];
  }

  for(j=0;i<len;++i,--j) {
    if ( j==0 ) {
      j = 3;
      tmpbuf[l++] = ','; 
    }
    tmpbuf[l++] = instr[i];
  }
  return(tmpbuf);
}

void lowerit(char *instr)
{
  while( *instr!=0 ) {
    if ( *instr >= 'A' && *instr <= 'Z' ) *instr += 32;
    /**instr = tolower(*instr);*/
    ++instr;
  }
}

char rot13(char c)
{
 char cap;

  /* hide @ and . characters in email addresses */
  if ( c == '@' )  return('^');
  else if ( c == '^' )  return('@');
  else if ( c == '.' )  return('(');
  else if ( c == '(' )  return('.');

  cap = c & 32;
  c &= ~cap;
  if ( (c >='A') && (c<='Z') ) c = (c-'A' + 13 ) %26 + 'A';
  c |= cap;
  return(c);
}

int open_inet_client(char *ip, int port)
{
 struct sockaddr_in server_addr;
 int len;
 int s;

  if ((s = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP)) == -1) {
    printf("unix socket error\n");
    return(-1);
  }

  memset(&server_addr, 0, sizeof(server_addr));
  server_addr.sin_family = AF_INET;
  len = sizeof(struct sockaddr_in);
  server_addr.sin_addr.s_addr = inet_addr(ip);
  server_addr.sin_port = htons(port); 

  if (connect(s, (struct sockaddr *)&server_addr, len) == -1) {
    printf("celiberod is not running\n");
    return(-1);
  }
  return(s);
}

int open_unix_client(char *path)
{
 struct sockaddr_un remote;
 int len;
 int s;

  if ((s = socket(AF_UNIX, SOCK_STREAM, 0)) == -1) {
    printf("unix socket error\n");
    return(-1);
  }

  remote.sun_family = AF_UNIX;
  strncpy(remote.sun_path, path, 108);
  len = strlen(remote.sun_path) + sizeof(remote.sun_family);
  if (connect(s, (struct sockaddr *)&remote, len) == -1) {
    printf("celiberod is not running\n");
    return(-1);
  }
  return(s);
}

char *cel_start_mailing( int sock, char *list, char *body, int retry )
{
 int t;
 char *TmpBuf;

  if ( sock == -1 ) return("connection not open\n");

  TmpBuf = malloc(MAX_BUFF);

  snprintf(TmpBuf, MAX_BUFF, "mailing start %s %s %d\n", 
    list, body, retry );

  if (send(sock, TmpBuf, strlen(TmpBuf), 0) == -1) {
    snprintf(TmpBuf, MAX_BUFF, 
      "unix socket recv error\n");
  } else {
    memset(TmpBuf,0,MAX_BUFF);
    if ((t=recv(sock, TmpBuf, MAX_BUFF, 0)) > 0) {
      TmpBuf[t] = '\0';
    } else {
      if (t < 0) {
        snprintf(TmpBuf, MAX_BUFF,
         "unix socket recv error\n");
      } else {
        strncpy(TmpBuf, "Server closed connection\n", MAX_BUFF);
      }
    }
  }
  return(TmpBuf);
}

int get_email_addrs( char *filename )
{
 FILE *fs;
 char  EStatus;
 char TmpBuf[MAX_BUFF];
 char TmpBuf2[MAX_BUFF];

  return(0);

  if ( (fs=fopen(filename, "r")) == NULL) {
    printf("could not open file %s\n", filename ); 
    return(-1);
  }

  while ( fgets(TmpBuf, MAX_BUFF, fs) != NULL ) {
    EStatus = 'X';
    if ( strncmp( TmpBuf, "success:", 8 ) == 0 ) EStatus = RBA_SUCCESS;
    if ( strncmp( TmpBuf, "failure:", 8 ) == 0 ) EStatus = RBA_FAILURE;
    if ( strncmp( TmpBuf, "deferral:", 9 ) == 0 ) EStatus = RBA_DEFER;

    if ( strcasestr( TmpBuf, "inactive" ) != NULL ) EStatus = RBA_DEFER;
    if ( strcasestr( TmpBuf, "exceeded" ) != NULL ) EStatus = RBA_DEFER;
    if ( strcasestr( TmpBuf, "over quota." ) != NULL ) EStatus = RBA_DEFER;

    if ( EStatus == 'X' ) continue;

    if ( get_addr(TmpBuf, NULL, TmpBuf2, MAX_BUFF,0) == -1 ) continue;
    lowerit(TmpBuf2);

    puts(TmpBuf2);
  }
  fclose(fs);
 
  return(0);
}

/* 
 * Send a signal to a process utility function
 *
 * name    = name of process
 * sig_num = signal number 
 */
int signal_process( char *name, int sig_num)
{
 FILE *ps;
 char *tmpstr;
 int  col;
 pid_t tmppid;
 pid_t mypid;
 int  pid_col=0;
 char pid[MAX_BUFF];
 char TmpBuf[MAX_BUFF];
 char *tmpptr;

  mypid = getpid();
  memset(pid,0,MAX_BUFF);
  if ( (ps = popen(PS_COMMAND, "r")) == NULL ) {
    perror("popen on ps command");
    return(-1);
  }

  if (fgets(TmpBuf, MAX_BUFF, ps)!= NULL ) {
    col=0;
    tmpptr = TmpBuf;
    tmpstr = strsep(&tmpptr, TOKENS);
    while (tmpstr != NULL ) {
      if (strcmp(tmpstr, "PID") == 0 ) pid_col = col;

      tmpstr = strsep(&tmpptr, TOKENS);
      ++col;
    }
  }

  while (fgets(TmpBuf, MAX_BUFF, ps)!= NULL ) {
    if ( strstr( TmpBuf, name ) != NULL && 
         strstr(TmpBuf,"supervise")==NULL) {

      tmpptr = TmpBuf;
      tmpstr = strsep(&tmpptr, TOKENS);
      col = 0;
      do {
        if( col == pid_col ) {
          strncpy(pid, tmpstr, MAX_BUFF);
          break;
        } 
        ++col;
        tmpstr = strsep(&tmpptr, TOKENS);
      } while ( tmpstr!=NULL );
      tmppid = atoi(pid);
      if ( tmppid != mypid ) { 
        kill(tmppid,sig_num);
      }
    }
  }
  pclose(ps);
  return(0);
}

void set_perms()
{
 uid_t uid;
 gid_t gid;

  uid = geteuid();
  gid = getegid();
  if ( uid != CELIBEROUID || gid != CELIBEROGID ) {
    if ( setgid(CELIBEROGID) != 0 ) {
      printf("error: could not set celibero gid\n");
      exit(-1);
    }
    if ( setuid(CELIBEROUID) != 0 ) {
      printf("error: could not set celibero uid\n");
      exit(-1);
    }
  }

}

inline char *get_mac()
{
 static char tmpbuf[100];
 unsigned char *ch;
 int i;
 int fd;
 struct ifreq ifr;
 char tmpbuf1[50];


  memset(tmpbuf,0,100);
  fd = socket(AF_INET, SOCK_STREAM, 0);
  if ( fd < 0 ) {printf("error on socket\n"); return(0L);}
  strncpy(ifr.ifr_name, "eth0",IFNAMSIZ); /* assuming we want eth0 */
  ioctl(fd, SIOCGIFHWADDR, &ifr); /* retrieve MAC address */
  close(fd); /* close socket down */

  ch = ifr.ifr_hwaddr.sa_data;
  for ( i=0; i<=5; ++i, ++ch ) {
    snprintf( tmpbuf1, 50, "%03d", *ch ); 
    strncat(tmpbuf, tmpbuf1, 100 );
  }
  return(tmpbuf);
}

int check_dead_time()
{
 struct tm mytm;
 time_t now_time;
 time_t dead_time;

  if ( DEAD_MONTH == 0 && DEAD_YEAR == 0 && DEAD_DAY == 0 ) return(1);

  now_time = time(NULL);
  memset(&mytm,0,sizeof(struct tm));
  mytm.tm_mon = DEAD_MONTH-1;
  mytm.tm_mday = DEAD_DAY;
  mytm.tm_year = DEAD_YEAR - 1900;
  dead_time = mktime(&mytm);

  if ( now_time > dead_time ) { 
    printf("dead time\n");
    exit(-1);
  }
  return(1);

}

int check_cpuid()
{
  if ( strcmp( CPUID, get_cpuid()) != 0 ) {
    printf("cpu id\n");
    exit(0); 
  }
  return(1);
}

inline char *get_cpuid()
{
 static char tmpbuf[200];
 unsigned long x[4];
 unsigned long y[4];
 int i;
 /*int j;*/
 int l;
 /*char c;*/


  memset(tmpbuf,0,200);
  x[0] = 0;
  x[1] = 0;
  x[2] = 0;
  x[3] = 0;

  asm volatile(".byte 15;.byte 162" : "=a"(x[0]),"=b"(x[1]),"=c"(x[3]),"=d"(x[2]) : "0"(0) );
  if (!x[0]) return 0;
  asm volatile(".byte 15;.byte 162" : "=a"(y[0]),"=b"(y[1]),"=c"(y[2]),"=d"(y[3]) : "0"(1) );

  /*
  for (l=0,i = 1;i < 4;++i) {
    for (j = 0;j < 4;++j,++l) {
      c = x[i] >> (8 * j);
      if (c < 32) c = 32;
      if (c > 126) c = 126;
      tmpbuf[l] = c;
    }
  }
  */
  l=0;

  snprintf(&tmpbuf[l], 200, "%08x%08x%08x%08x",
    (unsigned int)y[0],(unsigned int)y[3], 
    (unsigned int)y[0],(unsigned int)y[3]);
  for(i=0;tmpbuf[i]!=0;++i) tmpbuf[i] = rot13(tmpbuf[i]);
  return(tmpbuf);
}

int open_inet_server(char *ip, int port)
{
 unsigned int s;
 struct sockaddr_in server_addr;
 socklen_t len;
 int on = 1;

  if ( (s = socket(AF_INET, SOCK_STREAM, 0)) == -1 ) {
    printf("unix socket error\n");
    return(-1);
  }

  if (setsockopt(s, SOL_SOCKET, SO_REUSEADDR, (char *)&on, sizeof(on)) < 0) {
    printf("control setsockopt error\n");
    return(-1);
  }
  memset(&server_addr, 0, sizeof(server_addr));
  server_addr.sin_family = AF_INET;
  len = sizeof(struct sockaddr_in);
  server_addr.sin_port = htons(port); 
  inet_aton(ip, &server_addr.sin_addr);

  if ( bind(s, (struct sockaddr *)&server_addr, len) == -1 ) {
    printf("unix socket bind error\n");
    return(-1);
  }

  if ( listen(s, 5) == -1 ) {
    printf("unix socket listen error\n");
    return(-1);
  }
  return(s);

}

int open_unix_server(char *sock_path)
{
 unsigned int s;
 struct sockaddr_un local;
 socklen_t len;

  umask(0000);
  if ( (s = socket(AF_UNIX, SOCK_STREAM, 0)) == -1 ) {
    printf("unix socket error\n");
    return(-1);
  }
  local.sun_family = AF_UNIX;  /* local is declared before socket() ^ */
  strncpy(local.sun_path,sock_path, 108);

  /* 
   * we don't care if this returns an error.
   * we are only doing some garbage collection 
   */ 
  unlink(local.sun_path);

  len = strlen(local.sun_path) + sizeof(local.sun_family);
  if ( bind(s, (struct sockaddr *)&local, len) == -1 ) {
    printf("unix socket bind error\n");
    return(-1);
  }

  if ( listen(s, 5) == -1 ) {
    printf("unix socket listen error\n");
    return(-1);
  }

  umask(0022);
  return(s);

}

inline int parse_email( email, user, domain, buff_size )
 char *email;
 char *user;
 char *domain;
 int  buff_size;
{
 int n;
 char *at = NULL;

  memset(user,0,buff_size);
  memset(domain,0,buff_size);
  lowerit(email);
  if ( (at=strchr(email,'@')) == NULL ) return(-1);

  n = at - email + 1;
  if ( n > buff_size ) n = buff_size;
  strncpy(user, email, n);
  user[n-1] = 0;
  strncpy(domain, ++at, buff_size);
  domain[buff_size-1] = 0;
  if ( is_username_valid( user ) != 0 ) return(-1);
  if ( is_domain_valid( domain ) != 0 ) return(-1);
  return(0);
}

/* support all the valid characters except %
 * which might be exploitable in a printf
 */
int is_username_valid( char *user )
{
  while(*user != 0 ) {
    if ( (*user == 33) || (*user == 35 ) || (*user == 36 ) || (*user == 38 ) ||
         (*user == 39 ) || (*user == 42 ) || (*user == 43) ||
         (*user >= 45 && *user <= 57) ||
         (*user == 61 ) || (*user == 63 ) ||
         (*user >= 65 && *user <= 90) ||
         (*user >= 94 && *user <= 126 ) ) {
         ++user;
    } else {
      return(-1);
    }
  }
  return(0);
}

int is_domain_valid( char *domain )
{
  while(*domain != 0 ) {
    if ( (*domain == 45) || (*domain == 46) ||
         (*domain >= 48 && *domain <= 57) ||
         (*domain >= 65 && *domain <= 90) ||
         (*domain >= 97 && *domain <= 122) ) {
      ++domain;
    } else {
      return(-1);
    }
  }
  return(0);
}
