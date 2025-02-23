#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include "config.h"
#include "celcommon.h"
#include "celutil.h"
#include "mysqlutil.h"
#include "mqa.h"
#include "rba.h"
struct mysql_struct mys1;
static char TmpBuf[MAX_BUFF];

int MaxIPS;
int MaxFroms;

#define TOKENS " ,:\n\t\r"

rip_host **IPS;
from_lines **FROMS;

void rp_removed (mysql_struct *mys, int msg_id, int mail_id)
{
	int i;

	mys_open(mys);

	snprintf(mys->SqlBuf, MAX_SQL_BUFF, "SELECT `si`.`ip` \
		FROM `server_to_ip` `si`, `msg_to_ip` `mi`, `schedule` `s`\
		WHERE `mi`.`ip_id` = `si`.`ip_id` AND `mi`.`draft_id` = `s`.`msg_id` AND `s`.`id` = '%d' AND `mi`.`removed` = 1", mail_id);

	if (mysql_query(&mys->mysql_conn,mys->SqlBuf))
	{
		printf("fetch mailing ips error 2: %s %s\n", 
		mysql_error(&mys->mysql_conn), mys->SqlBuf);
		mys_close(mys);
		return;
	}

			
	mys->mysql_res = mysql_store_result(&mys->mysql_conn);

	while (mys->mysql_row = mysql_fetch_row(mys->mysql_res))
	{		
		for (i=0;i<MaxIPS;i++)
		{
			if (strncmp(mys->mysql_row[0], IPS[i]->ip, 255) == 0)
			{
				printf("Deactivated %s\n", IPS[i]->ip);
				IPS[i]->removed = 1;
				break;
			}
		}		
	}

	mysql_free_result(mys->mysql_res);	
	mys_close(mys);
}

int rp_init(mysql_struct *mys, int msg_id, rba_struct *rba)
{
 FILE *fs;
 char *ip;
 char *host;
 int	count;
 char *tmpptr;
 char domains[555][255];
 char real_domains[555][255];
 char real_ips[555][255];
 int i, j;
 
	mys_open(mys);

	snprintf(mys->SqlBuf, MAX_SQL_BUFF, "SELECT `si`.`ip`, `si`.`domain` \
		FROM `server_to_ip` `si`, `msg_to_ip` `mi`, `schedule` `s`\
		WHERE `mi`.`ip_id` = `si`.`ip_id` AND `mi`.`draft_id` = `s`.`msg_id` AND `s`.`id` = '%d' AND `mi`.`removed` = 0", rba->id_only);

	if (mysql_query(&mys->mysql_conn,mys->SqlBuf))
	{
		printf("fetch mailing ips error 2: %s %s\n", 
		mysql_error(&mys->mysql_conn), mys->SqlBuf);
		mys_close(mys);
		return;
	}

	count = 0;
			
	mys->mysql_res = mysql_store_result(&mys->mysql_conn);

	while (mys->mysql_row = mysql_fetch_row(mys->mysql_res))
	{		
		strncpy(real_domains[count],  mys->mysql_row[1], 225);
		strncpy(real_ips[count], mys->mysql_row[0], 225);
		count ++;
	}

	mysql_free_result(mys->mysql_res);	
	mys_close(mys);
	
	MaxIPS = count;

	/* report errors */
	if ( MaxIPS == 0 ) {
		printf("Could not find any ips\n");
		return(-1);
	}
	
	
	/* malloc space */
	if(IPS != NULL) {
			free(IPS);
	}
	IPS = (rip_host **)malloc(sizeof(rip_host *) * count);
	count = 0;
	for(count = 0; count < MaxIPS; count ++)
	{
		printf("Found IP [%s] [%s]\n", real_domains[count], real_ips[count]);
		IPS[count] = malloc(sizeof(rip_host));
		strncpy( IPS[count]->ip, real_ips[count], 200 );
		strncpy( IPS[count]->host, real_domains[count], 200 );
		IPS[count]->yahoo_cons 	= 0;
		IPS[count]->removed 	= 0;
	}

	/* return success */
	return(1);
}

int from_init(mysql_struct *mys, int msg_id)
{
	int  count;
	char from[255][255];
	char from_local[255][255];
	char from_domain[255][255];
	int j;
 
	mys_open(mys);

	/* look froms for this draft */ 
	snprintf(mys->SqlBuf, MAX_SQL_BUFF, "SELECT `from`, `from_local`, `from_domain` FROM msg_to_from WHERE `msg_id` = '%i'", msg_id);

	if (mysql_query(&mys->mysql_conn,mys->SqlBuf))
	{
		printf("fetch mailing froms error: %s %s\n", mysql_error(&mys->mysql_conn), mys->SqlBuf);
		mys_close(mys);
		return;
	}

	mys->mysql_res 	= mysql_store_result(&mys->mysql_conn);
	j 				= 0;

	while ((mys->mysql_row = mysql_fetch_row(mys->mysql_res)))
	{
			strncpy(from[j], mys->mysql_row[0], 255);
			strncpy(from_local[j], mys->mysql_row[1], 255);
			strncpy(from_domain[j], mys->mysql_row[2], 255);
			++ j;
	}

	mysql_free_result(mys->mysql_res);
	mys_close(mys);
	
	MaxFroms = j;

	/* report errors */
	if ( MaxFroms == 0 )
	{
		printf("Could not find any froms\n");
		return(-1);
	}
	
	/* malloc space */
	if(FROMS != NULL)
	{
		free(FROMS);
	}

	FROMS = (from_lines **)malloc(sizeof(from_lines *) * j);
	count = 0;

	for(count = 0; count < MaxFroms; count ++)
	{
		printf("Found From [%s] <%s@%s>\n", from[count], from_local[count], from_domain[count]);
		FROMS[count] = malloc(sizeof(from_lines));
		strncpy( FROMS[count]->name, from[count], 200 );
		strncpy( FROMS[count]->local, from_local[count], 200 );
		strncpy( FROMS[count]->domain, from_domain[count], 200 );
	}

	/* return success */
	return(1);
}
