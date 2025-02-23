#include <stdlib.h>
#include <stdio.h>
#include <unistd.h>
#include <string.h>
#include <errno.h>
#include <ctype.h>
#include "config.h"
#include "celcommon.h"


#include <mysql.h>
#include "celutil.h"
#include "mysqlutil.h"


int TotalLists;
char **Lists;
MYSQL mysql_conn;
MYSQL_RES *mysql_res = NULL;
MYSQL_ROW mysql_row;
int mysql_connected = 0;

MYSQL mysql_conn1;
MYSQL_RES *mysql_res1 = NULL;
MYSQL_ROW mysql_row1;
int mysql_connected1 = 0;

MYSQL mysql_conn2;
MYSQL_RES *mysql_res2 = NULL;
MYSQL_ROW mysql_row2;
int mysql_connected2 = 0;

MYSQL mysql_conn3;
MYSQL_RES *mysql_res3 = NULL;
MYSQL_ROW mysql_row3;
int mysql_connected3 = 0;

MYSQL mysql_conn4;
MYSQL_RES *mysql_res4 = NULL;
MYSQL_ROW mysql_row4;
int mysql_connected4 = 0;

char SqlBuf[MAX_BUFF];
char ParseBuf1[MAX_BUFF];
char ParseBuf2[MAX_BUFF];


int conn_mysql_lists()
{
 int i;

    if ( mysql_connected == 1 ) return(0);

    mysql_init(&mysql_conn);
    if (!(mysql_real_connect(&mysql_conn,MysqlServer, MysqlUser,
              MysqlPasswd, MysqlDatabase, 0,NULL,0))) {
      return(-1);
    }
    mysql_connected = 1;


    snprintf(SqlBuf, MAX_BUFF, "show tables");
    if (mysql_query(&mysql_conn,SqlBuf)) {
      printf("sql error[3]: %s\n", mysql_error(&mysql_conn));
      return(-1);
    }

    if (!(mysql_res = mysql_store_result(&mysql_conn))) {
      printf("sql error[3]: %s\n", mysql_error(&mysql_conn));
      return(-1);
    }

    TotalLists = mysql_num_rows(mysql_res);
    if ( TotalLists == 0 ) {
      mysql_free_result(mysql_res);
      return(-1);
    }
    Lists = malloc(sizeof(char *)*TotalLists);

    for(i = 0;(mysql_row = mysql_fetch_row(mysql_res));++i) {
      Lists[i] = malloc(sizeof(char)*strlen(mysql_row[0]));
      strncpy(Lists[i], mysql_row[0], strlen(mysql_row[0]));
    }
    mysql_free_result(mysql_res);

    return(0);
}

void update_emails(mysql_struct *mys, char *email, 
  char estatus, char *list, int noattempt) 
{

  if ( estatus == RBA_NEW ) { 
    sub_email( mys, email, list,1 );

  } else if ( estatus == CEL_UNSUB ) { 
    unsub_email( mys, email, "" );

  } else if ( estatus == RBA_SUCCESS ){
    if ( noattempt == 0 ) {
      snprintf( mys->SqlBuf, MAX_SQL_BUFF,  
        "update email_stat set success=success+1, sent=sent+1 \
where email = \"%s\"",  
        email );
    } else {
      snprintf( mys->SqlBuf, MAX_SQL_BUFF,  
        "update email_stat set sent=sent+1 where email = \"%s\"",  
        email );
    }
    mysql_query(&mys->mysql_conn,mys->SqlBuf);
    mys->mysql_res = mysql_store_result(&mys->mysql_conn);
    mysql_free_result(mys->mysql_res);

  } else if ( estatus == RBA_FAILURE ){
    if ( DeleteOnBounce == 1 ) {
        snprintf( mys->SqlBuf, MAX_SQL_BUFF,  
          "delete from emails where email = \"%s\"", email );
    } else {
      if ( noattempt == 0 ) {
        snprintf( mys->SqlBuf, MAX_SQL_BUFF,  
          "update email_stat set sent=sent+1, \
failure=failure+1 where email = \"%s\"",  
          email );
      } else {
        snprintf( mys->SqlBuf, MAX_SQL_BUFF,  
          "update email_stat set failure=failure+1 where email = \"%s\"",
          email );
      }
    }
    mysql_query(&mys->mysql_conn,mys->SqlBuf);
    mys->mysql_res = mysql_store_result(&mys->mysql_conn);
    mysql_free_result(mys->mysql_res);

    if ( DeleteOnBounce == 1 ) {
      snprintf( mys->SqlBuf, MAX_SQL_BUFF,  
        "delete from email_stat where email = \"%s\"", email );
      mysql_query(&mys->mysql_conn,mys->SqlBuf);
      mys->mysql_res = mysql_store_result(&mys->mysql_conn);
      mysql_free_result(mys->mysql_res);

      snprintf( mys->SqlBuf, MAX_SQL_BUFF,  
        "delete from email_clickview where email = \"%s\"", email );
      mysql_query(&mys->mysql_conn,mys->SqlBuf);
      mys->mysql_res = mysql_store_result(&mys->mysql_conn);
      mysql_free_result(mys->mysql_res);

      snprintf( mys->SqlBuf, MAX_SQL_BUFF,  
        "delete from email_ip where email = \"%s\"", email );
      mysql_query(&mys->mysql_conn,mys->SqlBuf);
      mys->mysql_res = mysql_store_result(&mys->mysql_conn);
      mysql_free_result(mys->mysql_res);

      snprintf( mys->SqlBuf, MAX_SQL_BUFF,  
        "delete from vars_value where email = \"%s\"", email );
      mysql_query(&mys->mysql_conn,mys->SqlBuf);
      mys->mysql_res = mysql_store_result(&mys->mysql_conn);
      mysql_free_result(mys->mysql_res);
    }
  } else if ( estatus == RBA_DEFER ) { 
    if ( noattempt == 0 ) {
      snprintf( mys->SqlBuf, MAX_SQL_BUFF,  
        "update email_stat set sent=sent+1, deferred=deferred+1 \
where email = \"%s\"",  
        email );
    } else {
      snprintf( mys->SqlBuf, MAX_SQL_BUFF,  
        "update email_stat set deferred=deferred+1 where email = \"%s\"",
        email );
    }
    mysql_query(&mys->mysql_conn,mys->SqlBuf);
    mys->mysql_res = mysql_store_result(&mys->mysql_conn);
    mysql_free_result(mys->mysql_res);
  }
}

void mys_init(mysql_struct *mys)
{
  mys->mysql_connected = 0;
}

int mys_open(mysql_struct *mys)
{
    if ( mys->mysql_connected == 1 ) return(0);

    mysql_init(&mys->mysql_conn);
    if (!(mysql_real_connect(&mys->mysql_conn,MysqlServer, MysqlUser,
              MysqlPasswd, MysqlDatabase, 0,NULL,0))) {
printf("could not connect %s\n", mysql_error(&mys->mysql_conn));
      return(-1);
    }
    mys->mysql_connected = 1;

    return(0);
}

/*
int conn_mysql()
{
    if ( mysql_connected == 1 ) return(0);

    mysql_init(&mysql_conn);
    if (!(mysql_real_connect(&mysql_conn,MysqlServer, MysqlUser,
              MysqlPasswd, MysqlDatabase, 0,NULL,0))) {
      return(-1);
    }
    mysql_connected = 1;

    return(0);
}

int conn_mysql1()
{
    if ( mysql_connected1 == 1 ) return(0);

    mysql_init(&mysql_conn1);
    if (!(mysql_real_connect(&mysql_conn1,MysqlServer, MysqlUser,
              MysqlPasswd, MysqlDatabase, 0,NULL,0))) {
      return(-1);
    }
    mysql_connected1 = 1;

    return(0);
}

int conn_mysql2()
{
    if ( mysql_connected2 == 1 ) return(0);
    mysql_init(&mysql_conn2);
    if (!(mysql_real_connect(&mysql_conn2,MysqlServer, MysqlUser,
              MysqlPasswd, MysqlDatabase, 0,NULL,0))) {
      return(-1);
    }
    mysql_connected2 = 1;
    return(0);
}

int conn_mysql3()
{
    if ( mysql_connected3 == 1 ) return(0);
    mysql_init(&mysql_conn3);
    if (!(mysql_real_connect(&mysql_conn3,MysqlServer, MysqlUser,
              MysqlPasswd, MysqlDatabase, 0,NULL,0))) {
      return(-1);
    }
    mysql_connected3 = 1;
    return(0);
}

int conn_mysql4()
{
    if ( mysql_connected4 == 1 ) return(0);
    mysql_init(&mysql_conn4);
    if (!(mysql_real_connect(&mysql_conn4,MysqlServer, MysqlUser,
              MysqlPasswd, MysqlDatabase, 0,NULL,0))) {
      return(-1);
    }
    mysql_connected4 = 1;
    return(0);
}
*/

int mys_close(mysql_struct *mys)
{
    if ( mys->mysql_connected == 1 ) return(0);
    mysql_close(&mys->mysql_conn);
    mys->mysql_connected = 0;
    return(0);
}

/*
int close_mysql()
{
    if ( mysql_connected == 1 ) return(0);
    mysql_close(&mysql_conn);
    mysql_connected = 0;
    return(0);
}

int close_mysql1()
{
    if ( mysql_connected1 == 1 ) return(0);
    mysql_close(&mysql_conn1);
    mysql_connected1 = 0;
    return(0);
}

int close_mysql2()
{
    if ( mysql_connected2 == 1 ) return(0);
    mysql_close(&mysql_conn2);
    mysql_connected2 = 0;
    return(0);
}

int close_mysql3()
{
    if ( mysql_connected3 == 1 ) return(0);
    mysql_close(&mysql_conn3);
    mysql_connected3 = 0;
    return(0);
}

int close_mysql4()
{
    if ( mysql_connected4 == 1 ) return(0);
    mysql_close(&mysql_conn4);
    mysql_connected4 = 0;
    return(0);
}
*/

int parse_result_file( mysql_struct *mys, char *filename )
{
 FILE *fs;
 char  EStatus;
 int i,j,l;

  mys_open(mys);

  if ( (fs=fopen(filename, "r")) == NULL) {
    printf("could not open file %s\n", filename ); 
    mys_close(mys);
    return(-1);
  }

  i = 0;
  j = 0;
  l = 0;
  while ( fgets(ParseBuf1, MAX_BUFF, fs) != NULL ) {

    if ( strncmp( ParseBuf1, "success:", 8 ) == 0 ) EStatus = RBA_SUCCESS;
    else if ( strncmp( ParseBuf1, "failure:", 8 ) == 0 ) EStatus = RBA_FAILURE;
    else EStatus = RBA_DEFER;

    if ( get_addr(ParseBuf1, NULL, ParseBuf2, MAX_BUFF, 0) == -1 ) {
      ++l;
      continue;
    }

    lowerit(ParseBuf2);

    update_emails(mys, ParseBuf2, EStatus, NULL, 0);

    ++i;
    ++j;
    if ( j >= 1000 ) {
      j = 0;
      printf("%d %d\n", i, l);
    }

  }

  fclose(fs);
  return(0);
}

int del_email(mysql_struct *mys, char *email)
{
 char TmpBuf[MAX_BUFF];

  if ( get_addr(email, NULL, TmpBuf, MAX_BUFF, 0) == -1 ) {
    return(0);
  }
  lowerit(TmpBuf);

  mys_open(mys);
  snprintf(mys->SqlBuf, MAX_SQL_BUFF, 
    "delete from emails where email = \"%s\"", TmpBuf);
  if (mysql_query(&mys->mysql_conn,mys->SqlBuf)) {
    printf("sql error[3]: %s\n", mysql_error(&mys->mysql_conn));
    return(-1);
  } 
  mys->mysql_res = mysql_store_result(&mys->mysql_conn);
  mysql_free_result(mys->mysql_res);
  return(1);
}

int split_list( mysql_struct *mys, char *table, char *filebase, 
                char *query, int splits )
{
 int i,j;
 int splitsize;
 FILE *fs;
 char tmpbuf[200];

  mys_open(mys);

  if ( query == NULL || strlen(query) == 0 ) {
    snprintf( mys->SqlBuf, MAX_SQL_BUFF, "select count(email) from %s", table );
  } else {
    snprintf( mys->SqlBuf, MAX_SQL_BUFF, 
      "select count(email) from %s where %s", table, query );
  }
  if (mysql_query(&mys->mysql_conn,mys->SqlBuf)) {
    printf("mysql error 5: %s\n", mys->SqlBuf);
    return(-1);
  }
  mys->mysql_res = mysql_use_result(&mys->mysql_conn);

  i = 0;
  while ((mys->mysql_row = mysql_fetch_row(mys->mysql_res))) {
    i = atoi(mys->mysql_row[0]);
  }
  mysql_free_result(mys->mysql_res);

  splitsize = i / splits;

  if ( query == NULL || strlen(query) == 0 ) {
    snprintf( mys->SqlBuf, MAX_SQL_BUFF, "select email from %s", table );
  } else {
    snprintf( mys->SqlBuf, MAX_SQL_BUFF, 
      "select email from %s where %s", table, query );
  }
  if (mysql_query(&mys->mysql_conn,mys->SqlBuf)) {
    printf("mysql error 6: %s\n", mys->SqlBuf);
    return(-1);
  }
  mys->mysql_res = mysql_use_result(&mys->mysql_conn);

  fs = NULL;
  i = splitsize;
  j = 1;
  while ((mys->mysql_row = mysql_fetch_row(mys->mysql_res))) {

    if ( i >= splitsize ) {
      i = 0;
      if ( fs != NULL ) fclose(fs);

      snprintf(tmpbuf, 200, "%s.%d", filebase, j);
      ++j;
      if ( (fs = fopen(tmpbuf, "w+")) == NULL ) {
        mysql_free_result(mys->mysql_res);
        return(-1);
      }
    }
    fprintf(fs,"%s\n", mysql_row[0]);
    ++i;

  }
  if ( fs != NULL ) fclose(fs);

  mysql_free_result(mys->mysql_res);
  return(1);
}

int sub_email(mysql_struct *mys, char *email, char *list_id, int allow_dups)
{
 long long listidx;
 char tmpbuf[MAX_BUFF];

  if ( mys_open(mys) == -1 ) return(-1);
  listidx = (long long)1<<(long long)(atoll(list_id)-1);

  lowerit(email);
  if ( get_addr(email, NULL, tmpbuf, MAX_BUFF, 0) == -1 ) {
    return(-1);
  }

  /* insert the email */
  snprintf(mys->SqlBuf, MAX_SQL_BUFF, 
    "insert into emails ( email, list_sets, create_date ) \
values ('%s', %lld, now() )", 
    tmpbuf, listidx ); 

  /* insert the email, if this fails then the email was already in the database */
  if (mysql_query(&mys->mysql_conn,mys->SqlBuf)) {

    /* don't let an email be subscribed to more than one list */
    if ( allow_dups == 0 ) return(-1);


    /* update the record to also subscribe the email to this list */
    snprintf( mys->SqlBuf, MAX_SQL_BUFF,
      "update emails set list_sets=list_sets|%lld where email=\"%s\"", 
      listidx, tmpbuf);
    if (mysql_query(&mys->mysql_conn,mys->SqlBuf)) {
      printf("update emails sql error: %s: %s\n", 
        mys->SqlBuf, mysql_error(&mys->mysql_conn));
      return(-1);
    }
    mys->mysql_res = mysql_store_result(&mys->mysql_conn);
    mysql_free_result(mys->mysql_res);
    return(1);
  }
  mys->mysql_res = mysql_store_result(&mys->mysql_conn);
  mysql_free_result(mys->mysql_res);
  return(1);

}

int unsubscribe_mysql(mysql_struct *mys, char *email, char *list_id) 
{
 char TmpBuf[MAX_BUFF];
 long long listidx;

  if ( mys_open(mys) == -1 ) return(-1);

  listidx = (long long)1<<(long long)(atoll(list_id)-1);
 
  if ( get_addr(email, NULL, TmpBuf, MAX_BUFF, 0) == -1 ) {
    return(-1);
  } lowerit(TmpBuf);


  snprintf( mys->SqlBuf, MAX_SQL_BUFF,
    "update emails set list_sets=list_sets&~%lld where email=\"%s\"",
      listidx, TmpBuf );

  if (mysql_query(&mys->mysql_conn,mys->SqlBuf)) {
     return(0);
  }
  mys->mysql_res = mysql_store_result(&mys->mysql_conn);
  mysql_free_result(mys->mysql_res);

  snprintf(mys->SqlBuf, MAX_SQL_BUFF, 
    "select list_sets from emails where email=\"%s\"", TmpBuf);
  if (mysql_query(&mys->mysql_conn,mys->SqlBuf)) {
    printf("sql error[3]: %s %s\n", mys->SqlBuf, mysql_error(&mys->mysql_conn));
    return(-1);
  }

  if (!(mys->mysql_res = mysql_store_result(&mys->mysql_conn))) {
    printf("sql error[4]: %s %s\n", mys->SqlBuf, mysql_error(&mys->mysql_conn));
    return(-1);
  }

  if ( mysql_num_rows(mys->mysql_res) == 0 ) {
    mysql_free_result(mys->mysql_res);
    return(1);
  }

  while ((mys->mysql_row = mysql_fetch_row(mys->mysql_res))) {
    listidx = atoll(mys->mysql_row[0]);
  }
  mysql_free_result(mys->mysql_res);

  if ( listidx == 0 ) {
    snprintf( mys->SqlBuf, MAX_SQL_BUFF, "insert into unsubscribed \
( email, attempted, delivered, undeliverable, viewed, clicked, \
 list_sets, unsub_time ) select email, attempted, delivered, undeliverable, \
viewed, clicked, list_sets, now() from emails where email=\"%s\"", TmpBuf );
    if ( mysql_query(&mys->mysql_conn,mys->SqlBuf) ) {
      printf("s2: %s : %s\n", mys->SqlBuf, mysql_error(&mys->mysql_conn));
      return(-1);
    }
    mys->mysql_res = mysql_store_result(&mys->mysql_conn);
    mysql_free_result(mys->mysql_res);

    snprintf( mys->SqlBuf, MAX_SQL_BUFF, 
      "delete from emails where email=\"%s\"", TmpBuf);
    if ( mysql_query(&mys->mysql_conn,mys->SqlBuf) ) {
      printf("s3: %s : %s\n", mys->SqlBuf, mysql_error(&mys->mysql_conn));
      return(-1);
    }
    mys->mysql_res = mysql_store_result(&mys->mysql_conn);
    mysql_free_result(mys->mysql_res);

    snprintf( mys->SqlBuf, MAX_SQL_BUFF,  
      "delete from email_clickview where email = \"%s\"", TmpBuf );
    mysql_query(&mys->mysql_conn,mys->SqlBuf);
    mys->mysql_res = mysql_store_result(&mys->mysql_conn);
    mysql_free_result(mys->mysql_res);

    snprintf( mys->SqlBuf, MAX_SQL_BUFF,  
      "delete from email_ip where email = \"%s\"", TmpBuf );
    mysql_query(&mys->mysql_conn,mys->SqlBuf);
    mys->mysql_res = mysql_store_result(&mys->mysql_conn);
    mysql_free_result(mys->mysql_res);

    snprintf( mys->SqlBuf, MAX_SQL_BUFF,  
      "delete from vars_value where email = \"%s\"", TmpBuf );
    mysql_query(&mys->mysql_conn,mys->SqlBuf);
    mys->mysql_res = mysql_store_result(&mys->mysql_conn);
    mysql_free_result(mys->mysql_res);
  }

  return(1);
}

void fail_email(mysql_struct *mys, char *email_addr )
{

  if ( mys_open(mys) == -1 ) return;

  if ( email_addr != NULL && strlen(email_addr) >= 0 ) {
    snprintf( mys->SqlBuf, MAX_SQL_BUFF, "insert into failure \
( email, attempted, delivered, undeliverable, viewed, clicked, \
 list_sets ) select email, attempted, delivered, undeliverable, \
viewed, clicked, list_sets from emails where email=\"%s\"", email_addr );
  }
  if ( mysql_query(&mys->mysql_conn,mys->SqlBuf) ) {
    printf("s2: %s : %s\n", mys->SqlBuf, mysql_error(&mys->mysql_conn));
    return;
  }
  mys->mysql_res = mysql_store_result(&mys->mysql_conn);
  mysql_free_result(mys->mysql_res);

  if ( email_addr !=NULL && strlen(email_addr)>0 ) {
    snprintf( mys->SqlBuf, MAX_SQL_BUFF, 
      "delete from emails where email=\"%s\"", email_addr);
  }
  if ( mysql_query(&mys->mysql_conn,mys->SqlBuf) ) {
    printf("s3: %s : %s\n", mys->SqlBuf, mysql_error(&mys->mysql_conn));
    return;
  }
  mys->mysql_res = mysql_store_result(&mys->mysql_conn);
  mysql_free_result(mys->mysql_res);
}

int admin_unsubscribe_mysql(mysql_struct *mys, char *email, char *list_id) 
{
 char TmpBuf[MAX_BUFF];
 long long listidx;

  if ( mys_open(mys) == -1 ) return(-1);

  listidx = (long long)1<<(long long)(atoll(list_id)-1);
 
  if ( get_addr(email, NULL, TmpBuf, MAX_BUFF, 0) == -1 ) {
    return(-1);
  } lowerit(TmpBuf);


  snprintf( mys->SqlBuf, MAX_SQL_BUFF,
    "update emails set list_sets=list_sets&~%lld where email=\"%s\"",
      listidx, TmpBuf );

  if (mysql_query(&mys->mysql_conn,mys->SqlBuf)) {
     return(0);
  }
  mys->mysql_res = mysql_store_result(&mys->mysql_conn);
  mysql_free_result(mys->mysql_res);

  snprintf(mys->SqlBuf, MAX_SQL_BUFF, 
    "select list_sets from emails where email=\"%s\"", TmpBuf);
  if (mysql_query(&mys->mysql_conn,mys->SqlBuf)) {
    printf("sql error[3]: %s %s\n", mys->SqlBuf, mysql_error(&mys->mysql_conn));
    return(-1);
  }

  if (!(mys->mysql_res = mysql_store_result(&mys->mysql_conn))) {
    printf("sql error[4]: %s %s\n", mys->SqlBuf, mysql_error(&mys->mysql_conn));
    return(-1);
  }

  if ( mysql_num_rows(mys->mysql_res) == 0 ) {
    mysql_free_result(mys->mysql_res);
    return(1);
  }

  while ((mys->mysql_row = mysql_fetch_row(mys->mysql_res))) {
    listidx = atoll(mys->mysql_row[0]);
  }
  mysql_free_result(mys->mysql_res);

  if ( listidx == 0 ) {
    snprintf( mys->SqlBuf, MAX_SQL_BUFF, "insert into admin_unsubscribed \
( email, attempted, delivered, undeliverable, viewed, clicked, \
 list_sets, unsub_time ) select email, attempted, delivered, undeliverable, \
viewed, clicked, list_sets, now() from emails where email=\"%s\"", TmpBuf );
    if ( mysql_query(&mys->mysql_conn,mys->SqlBuf) ) {
      printf("s2: %s : %s\n", mys->SqlBuf, mysql_error(&mys->mysql_conn));
      return(-1);
    }
    mys->mysql_res = mysql_store_result(&mys->mysql_conn);
    mysql_free_result(mys->mysql_res);

    snprintf( mys->SqlBuf, MAX_SQL_BUFF, 
      "delete from emails where email=\"%s\"", TmpBuf);
    if ( mysql_query(&mys->mysql_conn,mys->SqlBuf) ) {
      printf("s3: %s : %s\n", mys->SqlBuf, mysql_error(&mys->mysql_conn));
      return(-1);
    }
    mys->mysql_res = mysql_store_result(&mys->mysql_conn);
    mysql_free_result(mys->mysql_res);
  }

  return(1);
}

void deferr_email(mysql_struct *mys, char *email_addr )
{

  if ( mys_open(mys) == -1 ) return;

  if ( email_addr != NULL && strlen(email_addr) >= 0 ) {
    snprintf( mys->SqlBuf, MAX_SQL_BUFF, "insert into deferred \
( email, attempted, delivered, undeliverable, viewed, clicked, \
 list_sets ) select email, attempted, delivered, undeliverable, \
viewed, clicked, list_sets from emails where email=\"%s\"", email_addr );
  }
  if ( mysql_query(&mys->mysql_conn,mys->SqlBuf) ) {
    printf("s2: %s : %s\n", mys->SqlBuf, mysql_error(&mys->mysql_conn));
    return;
  }
  mys->mysql_res = mysql_store_result(&mys->mysql_conn);
  mysql_free_result(mys->mysql_res);

  if ( email_addr !=NULL && strlen(email_addr)>0 ) {
    snprintf( mys->SqlBuf, MAX_SQL_BUFF, 
      "delete from emails where email=\"%s\"", email_addr);
  }
  if ( mysql_query(&mys->mysql_conn,mys->SqlBuf) ) {
    printf("s3: %s : %s\n", mys->SqlBuf, mysql_error(&mys->mysql_conn));
    return;
  }
  mys->mysql_res = mysql_store_result(&mys->mysql_conn);
  mysql_free_result(mys->mysql_res);
}


void unsub_email(mysql_struct *mys, char *email_addr, char *mail_id )
{
 char list_id[20];

  if ( mys_open(mys) == -1 ) { printf("1<BR>\n");return;}

  /* delete from all lists */
  if ( UnsubDeleteAll == 1 ) {
    if ( email_addr != NULL && strlen(email_addr) >= 0 ) {
      snprintf( mys->SqlBuf, MAX_SQL_BUFF, "insert into unsubscribed \
( email, attempted, delivered, undeliverable, viewed, clicked, \
 list_sets, unsub_time ) select email, attempted, delivered, undeliverable, \
viewed, clicked, list_sets, now() from emails where email=\"%s\"", email_addr );
      if ( mysql_query(&mys->mysql_conn,mys->SqlBuf) ) {
        printf("s2: %s : %s\n", mys->SqlBuf, mysql_error(&mys->mysql_conn));
        return;
      }
      mys->mysql_res = mysql_store_result(&mys->mysql_conn);
      mysql_free_result(mys->mysql_res);
  
      snprintf( mys->SqlBuf, MAX_SQL_BUFF, 
        "delete from emails where email=\"%s\"", email_addr);
      if ( mysql_query(&mys->mysql_conn,mys->SqlBuf) ) {
        printf("s3: %s : %s\n", mys->SqlBuf, mysql_error(&mys->mysql_conn));
        return;
      }
      mys->mysql_res = mysql_store_result(&mys->mysql_conn);
      mysql_free_result(mys->mysql_res);
    }

  /* delete just from this one list */
  } else if ( mail_id!=NULL && strlen(mail_id) > 0 ) {
    snprintf( mys->SqlBuf, MAX_SQL_BUFF,
      "select list_id from mailing where mail_id = %s", mail_id);
    if ( mysql_query(&mys->mysql_conn,mys->SqlBuf) ) {
      printf("s2: %s : %s\n", mys->SqlBuf, mysql_error(&mys->mysql_conn));
      return;
    }
    mys->mysql_res = mysql_store_result(&mys->mysql_conn);

    memset(list_id, 0, 20);
    while ((mys->mysql_row = mysql_fetch_row(mys->mysql_res))) {
      strcpy(list_id, mys->mysql_row[0]);
    }
    mysql_free_result(mys->mysql_res);

    if ( list_id[0] != 0 ) unsubscribe_mysql(mys, email_addr, list_id);
  }
}
