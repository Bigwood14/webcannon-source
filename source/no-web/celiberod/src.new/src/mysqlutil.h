#ifndef MYSQLUTIL_H
#define MYSQLUTIL_H 1

#include "celcommon.h"
#include <mysql.h>

extern int TotalLists;
extern char **Lists;

#define MAX_SQL_BUFF 1000
#define MAX_SQL_TMP  200

typedef struct mysql_struct {
  MYSQL mysql_conn;
  MYSQL_RES *mysql_res;
  MYSQL_ROW mysql_row;
  int mysql_connected;
  char SqlBuf[MAX_SQL_BUFF];
  char Tmp1[MAX_SQL_TMP];
  char Tmp2[MAX_SQL_TMP];
  char Tmp3[MAX_SQL_TMP];
  char Tmp4[MAX_SQL_TMP];
} mysql_struct;

extern MYSQL mysql_conn;
extern MYSQL_RES *mysql_res;
extern MYSQL_ROW mysql_row;
extern int mysql_connected;

extern MYSQL mysql_conn1;
extern MYSQL_RES *mysql_res1;
extern MYSQL_ROW mysql_row1;
extern int mysql_connected1;

extern MYSQL mysql_conn2;
extern MYSQL_RES *mysql_res2;
extern MYSQL_ROW mysql_row2;
extern int mysql_connected2;

extern MYSQL mysql_conn3;
extern MYSQL_RES *mysql_res3;
extern MYSQL_ROW mysql_row3;
extern int mysql_connected3;

extern MYSQL mysql_conn4;
extern MYSQL_RES *mysql_res4;
extern MYSQL_ROW mysql_row4;
extern int mysql_connected4;

/*
extern char SqlBuf[MAX_BUFF];
*/

void unsub_email(mysql_struct *mys, char *email_addr, char *mail_id );
int sub_email(mysql_struct *mys, char *email_addr, char *list, int allow_dups );

void fail_email(mysql_struct *mys, char *email_addr );
void defer_email(mysql_struct *mys, char *email_addr );

void mys_init(mysql_struct *mys);
int mys_open(mysql_struct *mys);
int mys_close(mysql_struct *mys);

/*
int conn_mysql();
int conn_mysql1();
int conn_mysql2();
int conn_mysql3();
int conn_mysql4();
*/
int conn_mysql_lists();
void update_emails(mysql_struct *mys, char *email, char estatus, char *list, int noattempt);
int del_email(mysql_struct *mys, char *email);
int insert_mysql(mysql_struct *mys, char *email, char *list_id);
int unsubscribe_mysql(mysql_struct *mys, char *email, char *list_id);
int admin_unsubscribe_mysql(mysql_struct *mys, char *email, char *list_id);
int parse_result_file(mysql_struct *mys,  char *filename );
/*
int close_mysql();
int close_mysql1();
int close_mysql2();
int close_mysql3();
int close_mysql4();
*/
int split_list( mysql_struct *mys, char *table, 
                char *filebase, char *query, int splits );
#endif
