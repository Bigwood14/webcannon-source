#define _GNU_SOURCE
#include <string.h>
#include <sys/types.h>
#include <signal.h>
#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <openssl/md5.h>

#include "hashtable.h"
#include "prepare.h"
#include "mysql_util.h"

static 	struct mysql_struct mys1;
char 	domains[100][255];
int 	domains_count;

void prepare_table_list (struct prepare *prepare)
{
	int i;

	prepare->table_count = 0;

	snprintf(prepare->tables[prepare->table_count].name, 100, "email_0_9");
	snprintf(prepare->tables[prepare->table_count].code, 3, "ab");
	prepare->table_count++;

	snprintf(prepare->tables[prepare->table_count].name, 100, "email_misc");
	snprintf(prepare->tables[prepare->table_count].code, 3, "aa");
	prepare->table_count++;

	for (i=0;i<26;i++)
	{
		snprintf(prepare->tables[prepare->table_count].name, 100, "email_%c", (char)i+97);
		snprintf(prepare->tables[prepare->table_count].code, 3, "%cz", (char)i+97);
		prepare->table_count++;
	}
}

int prepare_get_domain_only (struct prepare *prepare)
{
	domains_count = 0;

	mys_init(&mys1);
	mys_open(&mys1);

	snprintf(mys1.sql_buffer, MAX_SQL_BUFF, "SELECT `domain` FROM `msg_to_domain_2` WHERE `msg_id` = %d AND `invert` = 0", prepare->msg_id);

	if (mysql_query(&mys1.mysql_conn, mys1.sql_buffer))
	{
		printf("mysql error: %s %s\n", mysql_error(&mys1.mysql_conn), mys1.sql_buffer);
		mys_close(&mys1);
		return -1;
	}

	mys1.mysql_res  = mysql_store_result(&mys1.mysql_conn);

	while ((mys1.mysql_row = mysql_fetch_row(mys1.mysql_res)))
	{
		strncpy(domains[domains_count], mys1.mysql_row[0], 255);
		domains_count++;
	}

	mysql_free_result(mys1.mysql_res);

	mys_close(&mys1);
	return 0;

}

int prepare_create_hashtable (struct prepare *prepare)
{
	char sql_w[1000], tmp[100];
	int count, i, sup_count;
	int sups[100];

	mys_init(&mys1);
	mys_open(&mys1);

	prepare->suppression_count = 0;

	snprintf(mys1.sql_buffer, MAX_SQL_BUFF, "SELECT `suppression_list_id`, `s`.`has_md5` FROM `msg_to_suppression` `ms`, `supression_lists` `s` WHERE `msg_id` = %d AND `s`.`sup_list_id` = `ms`.`suppression_list_id`", prepare->msg_id);

	if (mysql_query(&mys1.mysql_conn, mys1.sql_buffer))
	{
		printf("mysql error: %s %s\n", mysql_error(&mys1.mysql_conn), mys1.sql_buffer);
		mys_close(&mys1);
		return -1;
	}

	mys1.mysql_res	= mysql_store_result(&mys1.mysql_conn);
	count 			= mysql_num_rows(mys1.mysql_res);
	i 				= 0;
	sup_count 		= 0;

	memset(sql_w, '\0', 1000);

	while ((mys1.mysql_row = mysql_fetch_row(mys1.mysql_res)))
	{
		sups[sup_count++] = atoi(mys1.mysql_row[0]);

		i++;
		if (i == count)
			snprintf(tmp, 100, " sup_list_id=%d", atoi(mys1.mysql_row[0]));
		else
			snprintf(tmp, 100, " sup_list_id=%d OR", atoi(mys1.mysql_row[0]));
		strcat(sql_w, tmp);

		
		if (atoi(mys1.mysql_row[1]) > 0)
			prepare->has_md5 = 1;
	}

	mysql_free_result(mys1.mysql_res);

	if (count > 0)
	{
		snprintf(mys1.sql_buffer, MAX_SQL_BUFF, "SELECT COUNT(*) FROM `email_to_sup` WHERE %s", sql_w);

		if (mysql_query(&mys1.mysql_conn, mys1.sql_buffer))
		{
			printf("mysql error: %s %s\n", mysql_error(&mys1.mysql_conn), mys1.sql_buffer);
			mys_close(&mys1);
			return -1;
		}

		mys1.mysql_res  = mysql_store_result(&mys1.mysql_conn);
		mys1.mysql_row 	= mysql_fetch_row(mys1.mysql_res);
		count 			= atoi(mys1.mysql_row[0]);

		mysql_free_result(mys1.mysql_res);

		prepare->hash_table = create_hash_table((count+100));

		for (i=0;i<sup_count;i++)
		{
			snprintf(mys1.sql_buffer, MAX_SQL_BUFF, "SELECT `email` FROM `email_to_sup` WHERE sup_list_id = %i", sups[i]);

			if (mysql_query(&mys1.mysql_conn, mys1.sql_buffer))
			{
				printf("mysql error: %s %s\n", mysql_error(&mys1.mysql_conn), mys1.sql_buffer);
				mys_close(&mys1);
				return -1;
			}

			mys1.mysql_res  = mysql_store_result(&mys1.mysql_conn);

			while ((mys1.mysql_row = mysql_fetch_row(mys1.mysql_res)))
			{
				add_string(prepare->hash_table, mys1.mysql_row[0]);
				prepare->suppression_count++;
			}

			mysql_free_result(mys1.mysql_res);
		}
	}
	else
		prepare->hash_table = create_hash_table((100));

	// also do domain NOTS here
	snprintf(mys1.sql_buffer, MAX_SQL_BUFF, "SELECT `domain` FROM `msg_to_domain_2` WHERE `msg_id` = %d AND `invert` = 1", prepare->msg_id);

	if (mysql_query(&mys1.mysql_conn, mys1.sql_buffer))
	{
		printf("mysql error: %s %s\n", mysql_error(&mys1.mysql_conn), mys1.sql_buffer);
		mys_close(&mys1);
		return -1;
	}

	mys1.mysql_res  = mysql_store_result(&mys1.mysql_conn);

	while ((mys1.mysql_row = mysql_fetch_row(mys1.mysql_res)))
	{
		add_string(prepare->hash_table, mys1.mysql_row[0]);
		prepare->suppression_count++;
	}

	mysql_free_result(mys1.mysql_res);

	mys_close(&mys1);
	return 0;
}

int prepare_get_message (struct prepare *prepare)
{
	return 0;
}

void prepare_init (struct prepare *prepare)
{
	char tmp[1000];

	snprintf(tmp, 1000, "/www/celibero/no-web/celiberod/list/%d", prepare->id);

	if ((prepare->list = fopen(tmp, "w+")) == NULL)
	{
		printf("Could not open out file\n");
		exit(-1);
	}

	prepare_table_list(prepare);
}

int prepare_lists (struct prepare *prepare)
{
	mys_init(&mys1);
	mys_open(&mys1);

	snprintf(mys1.sql_buffer, MAX_SQL_BUFF, "SELECT `list`.`name`, `list`.`list_id`, `msg_to_list`.`skip`, `msg_to_list`.`max` FROM `msg_to_list`, `list` \
											 WHERE `msg_to_list`.`msg_id` = %d AND `list`.`list_id` = `msg_to_list`.`list_id`", prepare->msg_id);

	if (mysql_query(&mys1.mysql_conn, mys1.sql_buffer))
	{
		printf("mysql error: %s %s\n", mysql_error(&mys1.mysql_conn), mys1.sql_buffer);
		mys_close(&mys1);
		return -1;
	}

	prepare->list_count = 0;
	mys1.mysql_res	= mysql_store_result(&mys1.mysql_conn);

	while ((mys1.mysql_row = mysql_fetch_row(mys1.mysql_res)))
	{
		strncpy(prepare->lists[prepare->list_count].name, mys1.mysql_row[0], 100);
		prepare->lists[prepare->list_count].id 		= atoi(mys1.mysql_row[1]);
		prepare->lists[prepare->list_count].skip	= atoi(mys1.mysql_row[2]);
		prepare->lists[prepare->list_count].max		= atoi(mys1.mysql_row[3]);
		prepare->list_count++;
	}

	mysql_free_result(mys1.mysql_res);
	mys_close(&mys1);
	return 0;
}

int prepare_subjects (struct prepare *prepare)
{
	int len, i;

	mys_init(&mys1);
	mys_open(&mys1);

	snprintf(mys1.sql_buffer, MAX_SQL_BUFF, "SELECT `subject` FROM `msg_to_subject` WHERE `msg_id` = %d", prepare->msg_id);

	if (mysql_query(&mys1.mysql_conn, mys1.sql_buffer))
	{
		printf("mysql error: %s %s\n", mysql_error(&mys1.mysql_conn), mys1.sql_buffer);
		mys_close(&mys1);
		return -1;
	}

	prepare->subject_count 	= 0;
	prepare->subject_next 	= 0;

	mys1.mysql_res	= mysql_store_result(&mys1.mysql_conn);

	while ((mys1.mysql_row = mysql_fetch_row(mys1.mysql_res)))
	{
		strncpy(prepare->subjects[prepare->subject_count].subject, mys1.mysql_row[0], 120);

		len = strlen(prepare->subjects[prepare->subject_count].subject);
		prepare->subjects[prepare->subject_count].mm = 0;
		for (i=0;i<len;i++)
		{
			if (prepare->subjects[prepare->subject_count].subject[i] == '{')
			{
				prepare->subjects[prepare->subject_count].mm = 1;
				break;
			}
		}

		prepare->subject_count++;
	}

	mysql_free_result(mys1.mysql_res);	
	mys_close(&mys1);
	return 0;
}

void trim(char *s)
{
	// Trim spaces and tabs from beginning:
	int i=0,j;
	while((s[i]==' ')||(s[i]=='\t')) {
		i++;
	}
	if(i>0) {
		for(j=0;j<strlen(s);j++) {
			s[j]=s[j+i];
		}
	s[j]='\0';
	}

	// Trim spaces and tabs from end:
	i=strlen(s)-1;
	while((s[i]==' ')||(s[i]=='\t')) {
		i--;
	}
	if(i<(strlen(s)-1)) {
		s[i+1]='\0';
	}
}

void prepare_mm_get_default (char *field, struct prepare_extra *extra)
{
	snprintf(mys1.sql_buffer_2, MAX_SQL_BUFF, "SELECT `%s` FROM `mm_defaults`", field);
	if (mysql_query(&mys1.mysql_conn, mys1.sql_buffer_2))
		return;
	
	mys1.mysql_res_2 	= mysql_store_result(&mys1.mysql_conn);
	mys1.mysql_row_2 	= mysql_fetch_row(mys1.mysql_res_2);

	if (mys1.mysql_row_2 == NULL)
		strncpy(extra->mm_default, "", 300);
	else
		strncpy(extra->mm_default, mys1.mysql_row_2[0], 300);
	mysql_free_result(mys1.mysql_res_2);
}

int prepare_schedule (struct prepare *prepare)
{
	int len, i, j, k, num_part, m;
	char *token, tmp[320];

	mys_init(&mys1);
	mys_open(&mys1);

	snprintf(mys1.sql_buffer, MAX_SQL_BUFF, "SELECT `s`.`sql_extra`, `s`.`msg_id`, `m`.`seeds` FROM `schedule` `s`, `msg` `m` WHERE `s`.`msg_id` = `m`.`id` AND `s`.`id` = %d", prepare->id);
	if (mysql_query(&mys1.mysql_conn, mys1.sql_buffer))
	{
		printf("mysql error: %s %s\n", mysql_error(&mys1.mysql_conn), mys1.sql_buffer);
		mys_close(&mys1);
		return -1;
	}

	mys1.mysql_res	= mysql_store_result(&mys1.mysql_conn);

	if (mysql_num_rows(mys1.mysql_res) < 1)
	{
		mys_close(&mys1);
		return -1;
	}

	mys1.mysql_row = mysql_fetch_row(mys1.mysql_res);

	prepare->seed_count = 0;
	// seeds
	len = strlen(mys1.mysql_row[2]);
	k 	= 0;

	if (len > 2)
	{
		k 			= 0;
		j 			= 0;
		m 			= 0;
		num_part 	= 0;
		memset(tmp, 0, 320);
		for (i=0; i<len; i++)
		{
			if (mys1.mysql_row[2][i] == '\r')
				continue;

			if (mys1.mysql_row[2][i] == '\n' || mys1.mysql_row[2][i+1] == 0)
			{
				if (mys1.mysql_row[2][i+1] == 0)
				{
					if (num_part == 0 && mys1.mysql_row[2][i] != '\n')
						prepare->seeds[k].email[m++] = mys1.mysql_row[2][i];

					prepare->seeds[k].email[m] = '\0';

					if (num_part == 1)
						tmp[j++] = mys1.mysql_row[2][i];
				}

				if (num_part == 1)
					prepare->seeds[k].position = atoi(tmp);
				else
				{
					srand(time(NULL));
					prepare->seeds[k].position_random 	= rand() % 2000000; 
					prepare->seeds[k].position 			= -1;
				}

				k++;
				num_part 	= 0;
				m 			= 0;
				memset(tmp, 0, 320);
				continue;
			}
			if (mys1.mysql_row[2][i] == ':')
			{
				num_part 	= 1;
				j 			= 0;
				continue;
			}

			if (num_part == 0)
			{
				prepare->seeds[k].email[m++] = mys1.mysql_row[2][i];
			}
			else
			{
				tmp[j++] = mys1.mysql_row[2][i];
			}
		}
	}
	
	prepare->seed_count = k;

	for (i=0;i<prepare->seed_count;i++)
	{
		printf("Got seed, email [%s], position [%d]\n", prepare->seeds[i].email, prepare->seeds[i].position);
	}
	prepare->extra_count = 0;

	if (strlen(mys1.mysql_row[0]) > 2)
	{
		snprintf(prepare->sql_extra, 2000, ",%s", mys1.mysql_row[0]);

		len = strlen(prepare->sql_extra);

		for (i=0;;i++,mys1.mysql_row[0] = NULL)
		{
			token = strtok(mys1.mysql_row[0], ",");

			if (token == NULL)
				break;
			trim(token);
			strncpy(tmp, token, 320);
		
			snprintf(prepare->extra_fields[i].field, 320, "{%s}", tmp);			
			prepare_mm_get_default(tmp, &prepare->extra_fields[i]);	
			prepare->extra_fields[i].len = strlen(prepare->extra_fields[i].field);
			prepare->extra_count++;
		}
	}
	else
		prepare->sql_extra[0] 	= '\0';
	
	prepare->msg_id = atoi(mys1.mysql_row[1]);	
	mysql_free_result(mys1.mysql_res);
	mys_close(&mys1);
	return 0;
}

char *replace_str(char *str, char *orig, char *rep)
{
  static char buffer[4096];
  char *p;

  if(!(p = strstr(str, orig)))  // Is 'orig' even in 'str'?
    return str;

  strncpy(buffer, str, p-str); // Copy characters from 'str' start to 'orig' st$
  buffer[p-str] = '\0';

  sprintf(buffer+(p-str), "%s%s", rep, p+strlen(orig));

  return buffer;
}

int main (int argc, const char* argv[])
{
	struct 			prepare prepare;
	int 			i, j, k, l, sup_count, good_count, num_fields, extra, m, n, o, skip, max, md5_i;
	char 			tmp[3000], tmp_subject[320], *buffer, last_id[100], md5_sum[33], md5_s[3];
	unsigned char 	md5_temp[16];

	extra = 0;

	if (argc < 2 || argc > 2)
	{
		printf("Wrong number of arguments supplied\n");
		exit(-1);
	}

	prepare.id 		= atoi(argv[1]);
	prepare.has_md5 = 0;
	prepare_init(&prepare);

	if (prepare_schedule(&prepare) < 0)
	{
		printf("Prepare schedule failed\n");
		exit(-1);
	}

	if (prepare_lists(&prepare) < 0)
	{
		printf("Prepare lists failed\n");
		exit(-1);
	}

	if (prepare_subjects(&prepare) < 0)
	{
		printf("Prepare subjects failed\n");
		exit(-1);
	}

	if (prepare_get_domain_only(&prepare) < 0)
	{
		printf("Prepare domain only failed\n");
		exit(-1);
	}

	if (prepare_create_hashtable(&prepare) < 0)
	{
		printf("Failed to make hashtable\n");
		exit(-1);
	}
	
	mys_init(&mys1);
	mys_open(&mys1);

	j 			= 0;
	sup_count 	= 0;
	good_count 	= 0;

	for (k=0;k<prepare.list_count;k++)
	{
		skip 	= 0;
		max 	= 0;

		for (i=0;i<prepare.table_count;i++)
		{
			snprintf(mys1.sql_buffer, MAX_SQL_BUFF, "SELECT local, domain, CONCAT(local, '@', domain) as email, id %s FROM `celibero_list_%i`.`%s`;", prepare.sql_extra, prepare.lists[k].id, prepare.tables[i].name);

			if (mysql_query(&mys1.mysql_conn, mys1.sql_buffer) != 0)
			{
				printf("mysql error: %s %s\n", mysql_error(&mys1.mysql_conn), mys1.sql_buffer);
				mys_close(&mys1);
				return -1;
			}
	
			if ((mys1.mysql_res = mysql_store_result(&mys1.mysql_conn)) == NULL)
			{
				printf("mysql error 2: %s %s\n", mysql_error(&mys1.mysql_conn), mys1.sql_buffer);
				mys_close(&mys1);
				return -1;
			}

			// very first time around
			if (k == 0 && mys1.mysql_res)
			{
				num_fields 	= mysql_num_fields(mys1.mysql_res);
				extra 		= 1;
			}
			// in case first time had no rows
			else if (extra != 1 && mys1.mysql_res)
			{
				num_fields  = mysql_num_fields(mys1.mysql_res);
				extra 		= 1;
			}
	
			while ((mys1.mysql_row = mysql_fetch_row(mys1.mysql_res)))
			{
				skip++;

				if (skip < prepare.lists[k].skip)
					continue;

				max++;

				if (prepare.lists[k].max > 0)
				{
					if (max >= prepare.lists[k].max)
						continue;
				}

				j++;
	
				if (j == 100000)
				{
					printf("checkin good:%d sup:%d\n", good_count, sup_count);
					j = 0;
				}
	
				if (prepare.suppression_count > 0)
				{
					if (prepare.has_md5 > 0)
					{
						// we we incl md5
						memset(md5_temp, 0, 16);
						memset(md5_sum, 0, 33);
						memset(md5_s, 0, 3);

						MD5((unsigned char *)mys1.mysql_row[2], strlen(mys1.mysql_row[2]), md5_temp);
						

						for(md5_i=0; md5_i<16; md5_i++ )
						{
							sprintf(md5_s, "%02x", md5_temp[md5_i]);
							strcat(md5_sum, md5_s);
						}
		
						if (lookup_str(prepare.hash_table, mys1.mysql_row[1]) == 1 || lookup_str(prepare.hash_table, mys1.mysql_row[2]) == 1 || lookup_str(prepare.hash_table, md5_sum) == 1)
						{
							sup_count++;
							continue;
						}
					}
					else
					{
						if (lookup_str(prepare.hash_table, mys1.mysql_row[1]) == 1 || lookup_str(prepare.hash_table, mys1.mysql_row[2]) == 1)
						{
							sup_count++;
							continue;
						}
					}
				}

				if (domains_count > 0)
				{
					o = -1;
					for (n=0;n<domains_count;n++)
					{
						if (strcasestr(mys1.mysql_row[1], domains[n]) != 0)
						{
							o = 0;
							break;
						}
					}				
				}

				if (o == -1)
					continue;

				prepare.subject_next++;

				if (prepare.subject_next >= prepare.subject_count)
					prepare.subject_next = 0;

				memset(tmp_subject, '\0', 320);
				strcpy(tmp_subject, prepare.subjects[prepare.subject_next].subject);

				if (num_fields > 4)
				{
					memset(tmp, '\0', 3000);
					for (l=4;l<num_fields;l++)
					{
						strcat(tmp, "|");
						strcat(tmp, mys1.mysql_row[l]);
					}
				}

				if (prepare.subjects[prepare.subject_next].mm == 1)
				{
					for (l=0;l<prepare.extra_count;l++)
					{
						if (mys1.mysql_row[l+4][0] == '\0')
							buffer = replace_str(tmp_subject, prepare.extra_fields[l].field, prepare.extra_fields[l].mm_default);
						else
							buffer = replace_str(tmp_subject, prepare.extra_fields[l].field, mys1.mysql_row[l+4]);

						strncpy(tmp_subject, buffer, 320);
					}
				}
					
				good_count++;
				fprintf(prepare.list,"%s|%s%s|%i||%s%s\n", mys1.mysql_row[2], prepare.tables[i].code, mys1.mysql_row[3], prepare.lists[k].id, tmp_subject, tmp);
				strncpy(last_id, mys1.mysql_row[3], 100);				
				// do a seed check
				for (m=0;m<prepare.seed_count;m++)
				{
					if (prepare.seeds[m].position > 0)
					{
						if ((good_count >= prepare.seeds[m].position || good_count == 1) && ((good_count % prepare.seeds[m].position) == 0))
						{
							printf("Seeding %s on %d\n", prepare.seeds[m].email, good_count);
							fprintf(prepare.list,"%s|%s%s|%i||%s%s\n", prepare.seeds[m].email, prepare.tables[i].code, mys1.mysql_row[3], prepare.lists[k].id, tmp_subject, tmp);
						}
					}
					else if (prepare.seeds[m].position == -1)
					{
						if (prepare.seeds[m].position_random == good_count)
						{
							printf("Seeding %s on %d\n", prepare.seeds[m].email, good_count);
							fprintf(prepare.list,"%s|%s%s|%i||%s%s\n", prepare.seeds[m].email, prepare.tables[i].code, mys1.mysql_row[3], prepare.lists[k].id, tmp_subject, tmp);
							prepare.seeds[m].position = -2;
						}
					}
				}
			}
	
			mysql_free_result(mys1.mysql_res);
		}
	}

	// check that all seeds are done seed the remain
	for (m=0;m<prepare.seed_count;m++)
	{
		if (prepare.seeds[m].position == -1)
		{
			printf("Seeding %s on end\n", prepare.seeds[m].email);
			fprintf(prepare.list,"%s|%s%s|%i||%s%s\n", prepare.seeds[m].email, prepare.tables[1].code, last_id, prepare.lists[k].id, tmp_subject, tmp);
		}
	}	

	fclose(prepare.list);

	snprintf(mys1.sql_buffer, MAX_SQL_BUFF, "UPDATE `schedule` SET `total_emails` = %d, `state` = 3 WHERE `id` = %d;", good_count, prepare.id);

	if (mysql_query(&mys1.mysql_conn, mys1.sql_buffer))
	{
		printf("mysql error: %s %s\n", mysql_error(&mys1.mysql_conn), mys1.sql_buffer);
		mys_close(&mys1);
		return -1;
	}

	printf("done good: %d, sup: %d\n", good_count, sup_count);
	mys_close(&mys1);
	return 0;
}
