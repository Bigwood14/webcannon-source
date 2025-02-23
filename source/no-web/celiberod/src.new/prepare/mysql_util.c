#include <stdio.h>
#include <mysql.h>

#include "mysql_util.h"

//struct mysql_struct mys1;

void mys_init (struct mysql_struct *mys)
{
	mys->mysql_connected = 0;
}

int mys_open (struct mysql_struct *mys)
{
	if ( mys->mysql_connected == 1 )
		return(0);

	mysql_init(&mys->mysql_conn);
	if (!(mysql_real_connect(&mys->mysql_conn, "127.0.0.1", "root", "cheese", "celibero", 0, NULL, 0)))
	{
		printf("could not connect %s\n", mysql_error(&mys->mysql_conn));
		return(-1);
    }
	
	mys->mysql_connected = 1;

    return(0);
}

int mys_close (struct mysql_struct *mys)
{
	if ( mys->mysql_connected == 0 )
		return(0);

	mysql_close(&mys->mysql_conn);
	mys->mysql_connected = 0;

	return(0);
}
