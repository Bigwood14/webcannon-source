#include <stdio.h>
#include <ctype.h>
#include <stdlib.h>
#include <string.h>

#include "config.h"
#include "custom.h"
#include "celutil.h"
#include "celcommon.h"
#include "mysqlutil.h"


void check_email(char *inbuf, char *outbuf, int len);
void lowerit(char *instr);
void read_stdin(void);
void add_email( char *inaddr );

char the_emails[100][225];
int cur_email = 0;
mysql_struct mys;

main()
{
  read_stdin();
}

/************************************************************************/

void read_stdin(void)
{
  char s[1024];
  char *email;
  int foober,i,j;
  int unsub = 0;
  char safe[250];
  email = malloc(1024);
  while ((fgets(s, 1023, stdin)) != (char *)0)
  {
    if(strcasestr(s, "subject:") !=0)
    {
      if(strcasestr(s, "remove") !=0 || strcasestr(s, "unsub") !=0 || strcasestr(s, "list") !=0)
      {
        unsub = 1;
      }
    }

    for(foober=0;foober<strlen(s);++foober)
    {
        if ( s[foober] == '@' )
        {
          for(i=foober;i>0;--i ) {
            if ( isspace(s[i]) ) break;
          }
          check_email( &s[i], email, 1023);
        }
      }
  }

  if(unsub == 1)
  {
    cel_init();
    mys_init(&mys);
    mys_open(&mys);
    for(j =0;j < cur_email;j ++)
    {
      memset(safe, 0, 250);
      mysql_real_escape_string(&mys, &safe, &the_emails[j], strlen(the_emails[j]));
      snprintf(mys.SqlBuf, MAX_SQL_BUFF, "INSERT INTO bouncer VALUES ('%s')",safe);
      if (mysql_query(&mys.mysql_conn,mys.SqlBuf))
      {
        printf("Unable to Insert [%s] [%s]\n", mys.SqlBuf,  mysql_error(&mys.mysql_conn));
      }
      printf("Unsub: %s\n", the_emails[j]);
    }
   mys_close(&mys);
  }
}

void check_email( char *inbuf, char *outbuf, int len)
{
 char *tmpstr;
 char *tmpstr1;
 int i, skip;
 int got_one;

  for(tmpstr = inbuf; *tmpstr!=0; ++tmpstr)
  {
    if ( *tmpstr == '@') {
      memset(outbuf, 0, len );
      if ( get_addr( inbuf, tmpstr, outbuf, len, 0) == 0 ) {
        got_one = 1;
      }
    }

    if ( got_one == 1 ) {
      if ( strcasestr(outbuf, "postmaster@") != 0 ) continue;
      if ( strcasestr(outbuf, "mailer-daemon@") != 0 ) continue;

      /*for(i=0,skip=0;skip==0&&i<MaxRejects;++i) {
        if ( strcasestr(outbuf, &rejects[i][0]) != NULL ) skip = 1;
      }
      if ( skip == 1 ) continue;*/
      add_email( outbuf );
    }
  }
}

void add_email( char *inaddr )
{
 int i;

   for(i=0;i<100;++i)
   {
       if ( strcasecmp(the_emails[i], inaddr) == 0 )
       {
           return;
       }
   }

  strncpy( the_emails[cur_email], inaddr, 220);
  ++cur_email;
  if ( cur_email >= 100 ) cur_email = 0;
}
