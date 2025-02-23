#include <mysql.h>

#define MAX_SQL_BUFF 1000
#define MAX_SQL_TMP  200

struct mysql_struct
{
	MYSQL mysql_conn;
	MYSQL_RES *mysql_res;
	MYSQL_RES *mysql_res_2;
	MYSQL_ROW mysql_row;
	MYSQL_ROW mysql_row_2;
	int 	mysql_connected;
	char 	sql_buffer[MAX_SQL_BUFF];
	char 	sql_buffer_2[MAX_SQL_BUFF];
	char 	tmp1[MAX_SQL_TMP];
	char 	tmp2[MAX_SQL_TMP];
	char 	tmp3[MAX_SQL_TMP];
	char 	tmp4[MAX_SQL_TMP];
};

void 	mys_init (struct mysql_struct *mys);
int 	mys_open (struct mysql_struct *mys);
int 	mys_close (struct mysql_struct *mys);
