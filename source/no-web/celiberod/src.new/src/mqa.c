#include <stdio.h>
#include <stdlib.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <sys/time.h>
#include <dirent.h>
#include <unistd.h>
#include <string.h>
#include <errno.h>
#include <pthread.h>
#include <netinet/in.h>
#include "celcommon.h"
#include "celutil.h"
#include "config.h"
#include "mqa.h"
#include "rba.h"
#include "vqr.h"
#include "rp.h"
#include "mysqlutil.h"

extern rba_struct *rba;
extern vqr_struct *vqr_global[MAX_THREADS];
struct mysql_struct mys2;
struct mysql_struct mys3;
void mqa_update_log( mqa_struct *mqa );
void mqa_save_state( mqa_struct *mqa );
void mqa_erase_state( mqa_struct *mqa );

void mqa_update_db(mqa_struct *mqa, mysql_struct *mys );

extern void rba_open_log();
extern void rba_close_log();

void mqa_get_rotated_content (mqa_struct *mqa, mysql_struct *mys, int msg_id)
{
	unsigned int count, i, j;
	
	mys_open(mys);

	snprintf(mys->SqlBuf, MAX_SQL_BUFF, "SELECT `rotated_id`, `name` FROM `msg_to_rotated` WHERE `msg_id` = '%i'", msg_id);

	if (mysql_query(&mys->mysql_conn,mys->SqlBuf))
	{
		printf("fetch rotated error: %s %s\n", mysql_error(&mys->mysql_conn), mys->SqlBuf);
		mys_close(mys);
		return;
	}

	mys->mysql_res 	= mysql_store_result(&mys->mysql_conn);
	count			= (unsigned int) mysql_num_rows(mys->mysql_res);
	i = 0;
	while ((mys->mysql_row = mysql_fetch_row(mys->mysql_res)))
	{

		mqa->rotated_content[i].position 		= 0;
		mqa->rotated_content[i].rotated_id 	= atoi(mys->mysql_row[0]);

		strncpy(mqa->rotated_content[i].name, mys->mysql_row[1], 255);

		printf("Content set [%i] [%d] found %s - %s - %s\n", mqa->rotated_content[i].rotated_id, mqa->rotated_content[i].position, mqa->rotated_content[i].name, mys->SqlBuf, mys->mysql_row[1]);


		i++;
	}
	mqa->rotated_content_count = count;
	mysql_free_result(mys->mysql_res);

	for (i=0;i<count;i++)
	{
		snprintf(mys->SqlBuf, MAX_SQL_BUFF, "SELECT `data` FROM `extra_content_data` WHERE `content_id` = '%i'", mqa->rotated_content[i].rotated_id);		
		if (mysql_query(&mys->mysql_conn,mys->SqlBuf))
		{
			printf("fetch rotated error: %s %s\n", mysql_error(&mys->mysql_conn), mys->SqlBuf);
			mys_close(mys);
			return;
		}

		mys->mysql_res 	= mysql_store_result(&mys->mysql_conn);
		count			= (unsigned int) mysql_num_rows(mys->mysql_res);

		j = 0;
		while ((mys->mysql_row = mysql_fetch_row(mys->mysql_res)))
		{
			strncpy(mqa->rotated_content[i].data[j], mys->mysql_row[0], 1000);
			printf("Content: %s\n", mqa->rotated_content[i].data[j]);

			j++;
		}
		mqa->rotated_content[i].count = count;	
		mysql_free_result(mys->mysql_res);
	}	
	mys_close(mys);
} 

/*
 * Start a new mailing
 * Input
 *   tofile   = list of email addresses
 *   bodyfile = email body to send
 *   retry_count = 1 to 10 (1 is highest)
 *   success  = if a restart, this is last known success total
 *   failure  = if a restart, this is last known failure total
 *   deferral = if a restart, last known deferral total
 *   pause    = 0 then just run it
 *            = 1 mark as paused (do not run, await user input)
 */
int mqa_new_mailing(char *tofile, char *bodyfile, int retry_count,
                    int success, int failure, int deferral, int pause, 
                    int mail_id, int msg_id, int max_threads, int first_time, char *yahoo_date, int aol_rotate, int max_per_ip )
{
 int mqaidx;
 int i, j, in_header;
 struct stat bodystat;
 struct stat bodystat_aol;
 struct stat bodystat_yahoo;
 struct stat dk_keystat;
 FILE *fs;

  check_dead_time();
 
  /* look for an open slot and return error if none available */
  for(mqaidx=0;mqaidx<MAX_MAILINGS;++mqaidx) {
    if ( rba->mqa[mqaidx]->run_state == RB_NO_STATE ) break;
  }

  /* if no more slots return an error */
  if ( mqaidx >= MAX_MAILINGS ) {
    printf("no new mailings available\n");
    return(MQA_NEW_MAX_MAILINGS);
  }

  if ( pause == 1 ) {
    rba->mqa[mqaidx]->run_state = MQA_PAUSED;
  } else {
    rba->mqa[mqaidx]->run_state = MQA_HOLD;
  }

	rba->mqa[mqaidx]->max_threads 		= max_threads;
	rba->mqa[mqaidx]->aol_rotate 		= aol_rotate;
	rba->mqa[mqaidx]->max_per_ip 		= max_per_ip;
	rba->mqa[mqaidx]->aol_rotate_count 	= 0;
	rba->mqa[mqaidx]->current_ip_sends 	= 0;

  strncpy( rba->mqa[mqaidx]->tofile, tofile, MAX_BUFF);
  strncpy( rba->mqa[mqaidx]->bodyfile, bodyfile, MAX_BUFF);
  strncpy( rba->mqa[mqaidx]->yahoo_date, yahoo_date, MAX_BUFF);

  /* setup the body, aol body and address files with full paths */
  snprintf( rba->mqa[mqaidx]->BodyFile, MAX_BUFF,
    "%s/body/%s", CELIBERODIR, bodyfile);
  snprintf( rba->mqa[mqaidx]->BodyFileAOL, MAX_BUFF,
    "%s/body/%s_aol", CELIBERODIR, bodyfile);
  snprintf( rba->mqa[mqaidx]->BodyFileYahoo, MAX_BUFF,
    "%s/body/%s_yahoo", CELIBERODIR, bodyfile);
  snprintf( rba->mqa[mqaidx]->AddrFile, MAX_BUFF,
    "%s/list/%s",CELIBERODIR, tofile);
  snprintf( rba->mqa[mqaidx]->DK_KeyFile, MAX_BUFF,
    "%s/etc/%s",CELIBERODIR, "priv.key");

  /* open the address list file */
  if ( (rba->mqa[mqaidx]->TheAddr = fopen(rba->mqa[mqaidx]->AddrFile, "r")) 
        == NULL ) {
    printf("could not open list file %s\n", rba->mqa[mqaidx]->AddrFile); 
    rba->mqa[mqaidx]->run_state = MQA_READY;
    return(MQA_NEW_BAD_ADDR_FILE);
  }

  /* open the success status file */
  snprintf(rba->mqa[mqaidx]->tmpbuf, 156, "%s/results/%s.success", 
    CELIBERODIR, tofile); 
  if ( (rba->mqa[mqaidx]->TheSuccess = 
          fopen(rba->mqa[mqaidx]->tmpbuf, "a+")) == NULL ) {
    fclose(rba->mqa[mqaidx]->TheAddr);
    printf("could not open success file %s\n", rba->mqa[mqaidx]->tmpbuf);
    rba->mqa[mqaidx]->run_state = MQA_READY;
    return(MQA_NEW_BAD_STAT_FILE);
  }

  /* open the failure status file */
  snprintf(rba->mqa[mqaidx]->tmpbuf, 156, "%s/results/%s.failure", 
    CELIBERODIR, tofile ); 
  if ( (rba->mqa[mqaidx]->TheFailure = 
          fopen(rba->mqa[mqaidx]->tmpbuf, "a+")) == NULL ) {
    fclose(rba->mqa[mqaidx]->TheAddr);
    fclose(rba->mqa[mqaidx]->TheSuccess);
    printf("could not open failure file %s\n", rba->mqa[mqaidx]->tmpbuf);
    rba->mqa[mqaidx]->run_state = MQA_READY;
    return(MQA_NEW_BAD_STAT_FILE);
  }

  /* open the deferral status file */
  snprintf(rba->mqa[mqaidx]->tmpbuf, 156, "%s/results/%s.deferral", 
    CELIBERODIR, tofile); 
  if ( (rba->mqa[mqaidx]->TheDeferral = 
         fopen(rba->mqa[mqaidx]->tmpbuf, "a+"))== NULL ) {
    fclose(rba->mqa[mqaidx]->TheAddr);
    fclose(rba->mqa[mqaidx]->TheSuccess);
    fclose(rba->mqa[mqaidx]->TheFailure);
    printf("could not open deferral file %s\n", rba->mqa[mqaidx]->tmpbuf);
    rba->mqa[mqaidx]->run_state = MQA_READY;
    return(MQA_NEW_BAD_STAT_FILE);
  }

  /* open the lookup file */
  snprintf(rba->mqa[mqaidx]->tmpbuf, 156, "%s/results/%s.lookup", CELIBERODIR, tofile); 
  if ( (rba->mqa[mqaidx]->TheLookup = fopen(rba->mqa[mqaidx]->tmpbuf, "a+"))== NULL )
  {
    fclose(rba->mqa[mqaidx]->TheAddr);
    fclose(rba->mqa[mqaidx]->TheSuccess);
    fclose(rba->mqa[mqaidx]->TheFailure);
    fclose(rba->mqa[mqaidx]->TheDeferral);
    printf("could not open lookup file %s\n", rba->mqa[mqaidx]->tmpbuf);
    rba->mqa[mqaidx]->run_state = MQA_READY;
    return(MQA_NEW_BAD_STAT_FILE);
  }

  /* 
   * open the deferral retry file which contains the exact content
   * of the address list file for each email that was marked as deferred
   */
  rba->mqa[mqaidx]->DeferralRetry = NULL;
  if ( retry_count > 0 ) {
    snprintf(rba->mqa[mqaidx]->DeferalRetryFile, 156, 
      "%s/results/%s^%s^%d^.deferral.retry", 
      CELIBERODIR, bodyfile, tofile, retry_count ); 
    if ( (rba->mqa[mqaidx]->DeferralRetry = 
           fopen(rba->mqa[mqaidx]->DeferalRetryFile, "w+"))== NULL ) {
      fclose(rba->mqa[mqaidx]->TheAddr);
      fclose(rba->mqa[mqaidx]->TheSuccess);
      fclose(rba->mqa[mqaidx]->TheFailure);
      fclose(rba->mqa[mqaidx]->TheDeferral);
      fclose(rba->mqa[mqaidx]->TheLookup);
      printf("could not open deferral.retry file %s\n", 
        rba->mqa[mqaidx]->tmpbuf);
      rba->mqa[mqaidx]->run_state = MQA_READY;
      return(MQA_NEW_BAD_STAT_FILE);
    }
  }

  /* fix the formatting */
  fix_crlf(rba->mqa[mqaidx]->BodyFile );
  fix_crlf(rba->mqa[mqaidx]->BodyFileAOL );

  /* get the size of the body file */
  if ( stat( rba->mqa[mqaidx]->BodyFile, &bodystat) != 0 ) {
    printf("could not stat body file\n");
    fclose(rba->mqa[mqaidx]->TheAddr);
    fclose(rba->mqa[mqaidx]->TheSuccess);
    fclose(rba->mqa[mqaidx]->TheFailure);
    fclose(rba->mqa[mqaidx]->TheDeferral);
	fclose(rba->mqa[mqaidx]->TheLookup);
    rba->mqa[mqaidx]->run_state = MQA_READY;
    return(MQA_NEW_BAD_BODY_FILE); 
  }

  /* get the size of the aol body file */
  if ( stat( rba->mqa[mqaidx]->BodyFileAOL, &bodystat_aol) != 0 ) {
    printf("could not stat aol body file\n");
    fclose(rba->mqa[mqaidx]->TheAddr);
    fclose(rba->mqa[mqaidx]->TheSuccess);
    fclose(rba->mqa[mqaidx]->TheFailure);
    fclose(rba->mqa[mqaidx]->TheDeferral);
	fclose(rba->mqa[mqaidx]->TheLookup);
    rba->mqa[mqaidx]->run_state = MQA_READY;
    return(MQA_NEW_BAD_BODY_FILE); 
  }

  /* get the size of the yahoo body file */
  if ( stat( rba->mqa[mqaidx]->BodyFileYahoo, &bodystat_yahoo) != 0 ) {
    printf("could not stat yahoo body file\n");
    fclose(rba->mqa[mqaidx]->TheAddr);
    fclose(rba->mqa[mqaidx]->TheSuccess);
    fclose(rba->mqa[mqaidx]->TheFailure);
    fclose(rba->mqa[mqaidx]->TheDeferral);
	fclose(rba->mqa[mqaidx]->TheLookup);
    rba->mqa[mqaidx]->run_state = MQA_READY;
    return(MQA_NEW_BAD_BODY_FILE); 
  }
 
  
  /* get the size of the domain key file */
  if ( stat( rba->mqa[mqaidx]->DK_KeyFile, &dk_keystat) != 0 ) {
    printf("could not stat domain key file\n");
    fclose(rba->mqa[mqaidx]->TheAddr);
    fclose(rba->mqa[mqaidx]->TheSuccess);
    fclose(rba->mqa[mqaidx]->TheFailure);
    fclose(rba->mqa[mqaidx]->TheDeferral);
	fclose(rba->mqa[mqaidx]->TheLookup);
    rba->mqa[mqaidx]->run_state = MQA_READY;
    return(MQA_NEW_BAD_BODY_FILE); 
  }

  /* assign sizes */
  rba->mqa[mqaidx]->bodymem_size      = bodystat.st_size;
  rba->mqa[mqaidx]->bodymem_size_aol  = bodystat_aol.st_size;
  rba->mqa[mqaidx]->bodymem_size_yahoo  = bodystat_yahoo.st_size;
  rba->mqa[mqaidx]->dk_keymem_size    = dk_keystat.st_size;

  /* If we don't have memory yet, get some to hold the body file */
  if ( rba->mqa[mqaidx]->bodymem == NULL ) {
    rba->mqa[mqaidx]->bodymalloc_size = bodystat.st_size;
    rba->mqa[mqaidx]->bodymem = malloc( bodystat.st_size ); 

  /* if we already have memory, only get more if we need more */
  } else {
    if ( bodystat.st_size > rba->mqa[mqaidx]->bodymalloc_size ) {
      rba->mqa[mqaidx]->bodymalloc_size = bodystat.st_size;
      rba->mqa[mqaidx]->bodymem = realloc( rba->mqa[mqaidx]->bodymem,
        bodystat.st_size);
    }
  }
  
  /* If we don't have memory yet, get some to hold the aol body file */
  if ( rba->mqa[mqaidx]->bodymem_aol == NULL ) {
    rba->mqa[mqaidx]->bodymalloc_size_aol = bodystat_aol.st_size;
    rba->mqa[mqaidx]->bodymem_aol = malloc( bodystat_aol.st_size ); 

  /* if we already have memory, only get more if we need more */
  } else {
    if ( bodystat_aol.st_size > rba->mqa[mqaidx]->bodymalloc_size_aol ) {
      rba->mqa[mqaidx]->bodymalloc_size_aol = bodystat_aol.st_size;
      rba->mqa[mqaidx]->bodymem_aol = realloc( rba->mqa[mqaidx]->bodymem_aol,
        bodystat_aol.st_size);
    }
  }

  /* If we don't have memory yet, get some to hold the yahoo body file */
  if ( rba->mqa[mqaidx]->bodymem_yahoo == NULL ) {
    rba->mqa[mqaidx]->bodymalloc_size_yahoo = bodystat_yahoo.st_size;
    rba->mqa[mqaidx]->bodymem_yahoo = malloc( bodystat_yahoo.st_size ); 

  /* if we already have memory, only get more if we need more */
  } else {
    if ( bodystat_yahoo.st_size > rba->mqa[mqaidx]->bodymalloc_size_yahoo ) {
      rba->mqa[mqaidx]->bodymalloc_size_yahoo = bodystat_yahoo.st_size;
      rba->mqa[mqaidx]->bodymem_yahoo = realloc( rba->mqa[mqaidx]->bodymem_yahoo,
        bodystat_yahoo.st_size);
    }
  }
 
  
  /* If we don't have memory yet, get some to hold the domain key file */
  if ( rba->mqa[mqaidx]->dk_keymem == NULL ) {
    rba->mqa[mqaidx]->dk_keymalloc_size = dk_keystat.st_size;
    rba->mqa[mqaidx]->dk_keymem = malloc( dk_keystat.st_size ); 

  /* if we already have memory, only get more if we need more */
  } else {
    if ( dk_keystat.st_size > rba->mqa[mqaidx]->dk_keymalloc_size ) {
      rba->mqa[mqaidx]->dk_keymalloc_size = dk_keystat.st_size;
      rba->mqa[mqaidx]->dk_keymem = realloc( rba->mqa[mqaidx]->dk_keymem,
        dk_keystat.st_size);
    }
  }
  /* 
   * clean out the body memory and all status variables,
   * set the start time
   */
  memset(rba->mqa[mqaidx]->bodymem, 0, rba->mqa[mqaidx]->bodymem_size );
  memset(rba->mqa[mqaidx]->bodymem_aol, 0, rba->mqa[mqaidx]->bodymem_size_aol );
  memset(rba->mqa[mqaidx]->bodymem_yahoo, 0, rba->mqa[mqaidx]->bodymem_size_yahoo );
  memset(rba->mqa[mqaidx]->dk_keymem, 0, rba->mqa[mqaidx]->dk_keymem_size );

  rba->mqa[mqaidx]->total_success 	= success;
  rba->mqa[mqaidx]->total_failure 	= failure;
  rba->mqa[mqaidx]->total_deferral 	= deferral;
  rba->mqa[mqaidx]->deferral_retry 	= retry_count; 
  rba->mqa[mqaidx]->retry_count 	= retry_count;
  rba->mqa[mqaidx]->mail_id 		= mail_id;
  rba->mqa[mqaidx]->msg_id 			= msg_id;
  rba->mqa[mqaidx]->first_time 		= first_time;

  /* open the body file */
  if ( (fs = fopen(rba->mqa[mqaidx]->BodyFile, "r")) == NULL ) {
    printf("fopen body error %d", errno);
    fclose(rba->mqa[mqaidx]->TheAddr);
    fclose(rba->mqa[mqaidx]->TheSuccess);
    fclose(rba->mqa[mqaidx]->TheFailure);
    fclose(rba->mqa[mqaidx]->TheDeferral);
	fclose(rba->mqa[mqaidx]->TheLookup);
    rba->mqa[mqaidx]->run_state = MQA_READY;
    return(MQA_NEW_BAD_BODY_FILE);
  }
  /* read in the body file into memory */
  i = 0;
  while(i<rba->mqa[mqaidx]->bodymem_size) {
    i+=fread(&rba->mqa[mqaidx]->bodymem[i], sizeof(char),
          rba->mqa[mqaidx]->bodymem_size-i, fs );
  }
  /* we don't need the open body file any more */
  fclose(fs);
  /* open the aol body file */
  if ( (fs = fopen(rba->mqa[mqaidx]->BodyFileAOL, "r")) == NULL ) {
    printf("fopen aol body error %d", errno);
    fclose(rba->mqa[mqaidx]->TheAddr);
    fclose(rba->mqa[mqaidx]->TheSuccess);
    fclose(rba->mqa[mqaidx]->TheFailure);
    fclose(rba->mqa[mqaidx]->TheDeferral);
	fclose(rba->mqa[mqaidx]->TheLookup);
    rba->mqa[mqaidx]->run_state = MQA_READY;
    return(MQA_NEW_BAD_BODY_FILE);
  }
  /* read in the body file into memory */
  i = 0;
  while(i<rba->mqa[mqaidx]->bodymem_size_aol) {
    i+=fread(&rba->mqa[mqaidx]->bodymem_aol[i], sizeof(char),
          rba->mqa[mqaidx]->bodymem_size_aol-i, fs );
  }
  /* we don't need the open body file any more */
  fclose(fs);

  /* open the yahoo body file */
  if ( (fs = fopen(rba->mqa[mqaidx]->BodyFileYahoo, "r")) == NULL ) {
    printf("fopen yahoo body error %d", errno);
    fclose(rba->mqa[mqaidx]->TheAddr);
    fclose(rba->mqa[mqaidx]->TheSuccess);
    fclose(rba->mqa[mqaidx]->TheFailure);
    fclose(rba->mqa[mqaidx]->TheDeferral);
	fclose(rba->mqa[mqaidx]->TheLookup);
    rba->mqa[mqaidx]->run_state = MQA_READY;
    return(MQA_NEW_BAD_BODY_FILE);
  }
  /* read in the body file into memory */
  i = 0;
  while(i<rba->mqa[mqaidx]->bodymem_size_yahoo) {
    i+=fread(&rba->mqa[mqaidx]->bodymem_yahoo[i], sizeof(char),
          rba->mqa[mqaidx]->bodymem_size_yahoo-i, fs );
  }
  /* we don't need the open body file any more */
  fclose(fs);
  
  /* open the domain key file */
  if ( (fs = fopen(rba->mqa[mqaidx]->DK_KeyFile, "r")) == NULL ) {
    printf("fopen domain key error %d", errno);
    fclose(rba->mqa[mqaidx]->TheAddr);
    fclose(rba->mqa[mqaidx]->TheSuccess);
    fclose(rba->mqa[mqaidx]->TheFailure);
    fclose(rba->mqa[mqaidx]->TheDeferral);
	fclose(rba->mqa[mqaidx]->TheLookup);
    rba->mqa[mqaidx]->run_state = MQA_READY;
    return(MQA_NEW_BAD_BODY_FILE);
  }
  /* read in the body file into memory */
  i = 0;
  while(i<rba->mqa[mqaidx]->dk_keymem_size) {
    i+=fread(&rba->mqa[mqaidx]->dk_keymem[i], sizeof(char),
          rba->mqa[mqaidx]->dk_keymem_size-i, fs );
  }
  
  /* we don't need the open body file any more */
  fclose(fs);
  
  /* get the from address */
  get_addr( rba->mqa[mqaidx]->bodymem, NULL, rba->mqa[mqaidx]->From, MAX_BUFF, 0);

  /* count the total number of addresses */
  rba->mqa[mqaidx]->total_addrs = 0;
  while( fgets( rba->mqa[mqaidx]->tmpbuf, 156, rba->mqa[mqaidx]->TheAddr) 
            != NULL ) {
    rba->mqa[mqaidx]->run_state = MQA_READY;
    ++rba->mqa[mqaidx]->total_addrs;
  }
  /* go back to the begining of the file */
  rewind(rba->mqa[mqaidx]->TheAddr);

  /* walk forward to the current state if we are resuming a mailing */
  i = success + failure + deferral;
  while ( i > 0 ) {
    if ( fgets( rba->mqa[mqaidx]->tmpbuf, MAX_BUFF, 
                rba->mqa[mqaidx]->TheAddr) == NULL ) break;
    --i;
  }

  rba->mqa[mqaidx]->start_time = time(NULL);

  /* set the max vars count */
  rba->mqa[mqaidx]->max_vars = 0; 
  for(i=0;i<rba->mqa[mqaidx]->bodymem_size;++i) {
    if ( rba->mqa[mqaidx]->bodymem[i] == '{' &&
         rba->mqa[mqaidx]->bodymem[i+1] == '{'  ) {
      ++rba->mqa[mqaidx]->max_vars;
      ++i;
    }
  }
  
    /* find the ips for this mailing */
    /*rp_init(&mys2, msg_id);*/
	/* find the from lines for this mailing */
	from_init(&mys2, msg_id);  
	/* get the rotated content for this mailing */
	mqa_get_rotated_content(rba->mqa[mqaidx], &mys2, msg_id);

	/* build the header string for domain keys */
	memset(rba->mqa[mqaidx]->dk_headers, 0, MAX_BUFF);
	in_header 	= 0;
	j 			= 0;
	for (i=0; i<rba->mqa[mqaidx]->bodymem_size;i++)
	{
		// if its the end of headers (\r\n\r\n)
		if (rba->mqa[mqaidx]->bodymem[i] == '\r' && rba->mqa[mqaidx]->bodymem[i+1] == '\n' &&
			rba->mqa[mqaidx]->bodymem[i+2] == '\r' && rba->mqa[mqaidx]->bodymem[i+3] == '\n')
			break;

		// if its the end of the header reset in_header to 0
		if (rba->mqa[mqaidx]->bodymem[i] == '\r' && rba->mqa[mqaidx]->bodymem[i+1] == '\n' &&
			(rba->mqa[mqaidx]->bodymem[i+2] != '\t' && rba->mqa[mqaidx]->bodymem[i+2] != ' '))
		{
			i += 2;
			in_header = 0;	
		}

		// if its end of header 'label' (:) are in header mark end
		if (in_header == 0 && rba->mqa[mqaidx]->bodymem[i] == ':')
		{
			rba->mqa[mqaidx]->dk_headers[j++] = ':';
			in_header = 1;
		}

		// copy the tag
		if (in_header == 0)
			rba->mqa[mqaidx]->dk_headers[j++] = rba->mqa[mqaidx]->bodymem[i];
	}

	rba->mqa[mqaidx]->dk_headers[j-1] = 0;

	printf("header str is: %s\n", rba->mqa[mqaidx]->dk_headers);
	
  printf("start: %d : %s : %s : %d : total %d\n", 
    rba->mqa[mqaidx]->mail_id,
    rba->mqa[mqaidx]->tofile, 
    rba->mqa[mqaidx]->bodyfile, 
    rba->mqa[mqaidx]->retry_count, rba->mqa[mqaidx]->total_addrs );

  if ( pause == 1 ) {
    rba->mqa[mqaidx]->run_state = MQA_PAUSED;
  } else {
    rba->mqa[mqaidx]->run_state = MQA_RUNNING;
  }

  /* write this transaction to the log file */
  mqa_update_log( rba->mqa[mqaidx] );

  return(MQA_NEW_SUCCESS);
}

/* 
 * All emails have been schedule to be sent but have
 * not yet been delivered. Mark the mailing as VQR_DONE
 * and close the address file, we don't need it anymore
 */
int mqa_end(void *mqa_in)
{
 mqa_struct *mqa = mqa_in;
  
  mqa->run_state = MQA_VQR_DONE;
  if ( mqa->TheAddr != NULL ) {
    fclose(mqa->TheAddr);
    mqa->TheAddr = NULL;
  }
  return(0);
}

/*
 * All threads have completed sending this mailing, 
 * we can now process it as completeded, so we can
 * close out everything to do with it.
 */
int mqa_close(void *mqa_in, int status )
{
 mqa_struct *mqa = mqa_in;

 int send_deferals = 0;
 int retry_count = 0;
 char tmpbuf[156];
 char tmpbuf1[156];
 char tmpbuf2[MAX_BUFF];
 char tofile[156];
 char deferalfile[156];
 char bodyfile[156];

  /* don't be a joker! */
  if ( mqa==NULL ) return(-1);

  /* save the info for the next mailing */
  if ( mqa->retry_count > 0 && 
       mqa->total_deferral > 0 && 
       mqa->run_state != MQA_CANCEL ) {
    send_deferals = 1;
    retry_count = mqa->retry_count-1;
    strncpy(bodyfile, mqa->bodyfile, 156);
    strncpy(tofile, mqa->tofile, 156);
    strncpy(deferalfile, mqa->DeferalRetryFile, 156);
  }
  /* delete any state files for the mailing */
  mqa_erase_state( mqa );

  /* mark the mailing as done */
  mqa->run_state = status;

  /* close all the streams if still open */
  if ( mqa->TheAddr != NULL ) {
    fclose(mqa->TheAddr);
    mqa->TheAddr = NULL;
  }
  if ( mqa->TheSuccess != NULL ) {
    fclose(mqa->TheSuccess);
    mqa->TheSuccess = NULL;
  }
  if ( mqa->TheFailure != NULL ) {
    fclose(mqa->TheFailure);
    mqa->TheFailure = NULL;
  }
  if ( mqa->TheDeferral != NULL ) {
    fclose(mqa->TheDeferral);
    mqa->TheDeferral = NULL;
  }

 if ( mqa->TheLookup != NULL ) {
	fclose(mqa->TheLookup);
    mqa->TheLookup = NULL;
 }

  if ( mqa->DeferralRetry != NULL ) {
    fclose(mqa->DeferralRetry);
    mqa->DeferralRetry = NULL;
  }

  /*if ( strlen(mqa->BodyFile) > 0 ) unlink( mqa->BodyFile );*/
  /*if ( strlen(mqa->AddrFile) > 0 ) unlink( mqa->AddrFile );*/

  /* print the ending status message */
  printf(
    "end: %d : %s : %s : %d : total %d : success %d : failure %d : deferral %d : seconds %ld\n",
    mqa->mail_id,
    mqa->tofile, mqa->bodyfile, mqa->retry_count, mqa->total_addrs, 
    mqa->total_success, mqa->total_failure, mqa->total_deferral,
    time(NULL) - mqa->start_time);
  fflush(stdout);

  /* update the log with this new state */
  mqa_update_log( mqa );

  /* zero out all the state values */
  mqa->total_addrs = 0;
  mqa->total_success = 0;
  mqa->total_failure = 0;
  mqa->total_deferral = 0;
  mqa->start_time = 0;
  mqa->retry_count = 0;
  mqa->last_success = 0;

  /* Clean out the BodyFile name and AddrFile name */
  strncpy(tmpbuf2, mqa->yahoo_date ,MAX_BUFF); 

  memset( mqa->BodyFile, 0, MAX_BUFF);
  memset( mqa->AddrFile, 0, MAX_BUFF);
  memset( mqa->yahoo_date, 0, MAX_BUFF);

  /* 
   * mark the state as ready for more!
   * when this happens, this mailing slot will be
   * seen as ready to be used for another mailing
   * So it is the last thing we do.
   */
  mqa->run_state = MQA_READY;

  if ( send_deferals == 1 ) {
    /* move and rename the deferal retry file */
    snprintf(tmpbuf1, 156, "%s.%d", tofile, retry_count);
    snprintf(tmpbuf, 156, "%s/list/%s", CELIBERODIR, tmpbuf1); 
    rename( deferalfile, tmpbuf );
    rba_start_mailing( tmpbuf1, bodyfile, retry_count, 0, 0, 0, 0, mqa->mail_id, mqa->msg_id, 600, 0,  tmpbuf2, mqa->aol_rotate, mqa->max_per_ip);
  }

  return(0);
}

/*
 * Get the next email to send out. 
 * The vqr threads call this function each time they are
 * ready to send out more mail. So this function picks
 * between all the available mailings and sets up the vqr
 * structure so the vqr thread can send a new one.
 *
 * If there is no more mail to send then we do a little
 * dance between all the vqr threads so go into a 
 * "hibernation mode", awaiting a new mailing to send.
 */
int mqa_next_mailing( void *vqr_in, void *rba_in )
{
 vqr_struct *vqr = vqr_in;
 rba_struct *rba = rba_in;
 mqa_struct *mqa = vqr->mqa;
 int start_mqa;
 int found, i;

	vqr->mqa = NULL;
	mqa_check_end();

  /*
   * of all the available mail slots, look for one that is running
   * We use the rba->next_mqa global flag to keep track of the
   * last one we sent, so we can round robin through all the
   * available mailings.
   */
  found = 0;

	if (mqa != NULL)
	{
		if (mqa->aol_rotate > 0)
		{
			// if sends per ip is exceeded let the system think its time to rotate IPs because of blocks.
			if (vqr->OurIP == mqa->aol_current_ip && mqa->max_per_ip > 0)
			{
				mqa->current_ip_sends++;

				if (mqa->current_ip_sends >= mqa->max_per_ip)
				{
					printf("sent enough on this IP busting out\n");
					vqr->aol_reject = 1;
					mqa->aol_rotate_count = mqa->aol_rotate;
				}
			}	

			if (vqr->aol_reject == 1 && vqr->OurIP == mqa->aol_current_ip)
			{
				printf("AOL: score reject\n");
				mqa->aol_rotate_count++;
	
				if (mqa->aol_rotate_count >= mqa->aol_rotate)
				{
					printf("AOL: thats %d annnnd rotate\n", mqa->aol_rotate_count);
					mqa->aol_rotate_count = 0;
					mqa->current_ip_sends = 0;
					mqa->aol_current_ip++;

					for (i=mqa->aol_current_ip;i<MaxIPS;i++)
					{
						if (IPS[mqa->aol_current_ip]->removed == 1)
							mqa->aol_current_ip++;
						else
							break;
					}

					vqr->force_new_connect = 1;
					if (mqa->aol_current_ip >= MaxIPS)
					{
						printf("AOL: blown all IPs cancelling\n");
						// we have error'd out all IPS we need to cancel
						//mqa_cancel(mqa->mqaidx);
						mqa->run_state = MQA_CANCEL;
						mysql_update_schedule(mqa->mail_id, 8);
					}
				}
			}

			vqr->aol_reject = 0;
		}
	}

  start_mqa = rba->next_mqa;
  do {

    /* ah ha! we found a running mailing */
    /* && rba->mqa[rba->next_mqa]->max_threads >= vqr->vqridx */
    if ( rba->mqa[rba->next_mqa]->run_state == MQA_RUNNING 
    && rba->mqa[rba->next_mqa]->max_threads > vqr->vqridx) {
		
		/* speed throttling 
		if (MailingWait > 0 || MailingWaitMicro > 0)
		{
			rba->mqa[rba->next_mqa]->tv.tv_sec 	= MailingWait;
			rba->mqa[rba->next_mqa]->tv.tv_usec = MailingWaitMicro; 
			select(0, (fd_set *)0,(fd_set *)0, (fd_set *) 0, &rba->mqa[rba->next_mqa]->tv);
		}*/
	
		// setup the IP to use (if on aol rotation)		
		if (rba->mqa[rba->next_mqa]->aol_rotate > 0)
		{
			vqr->OurIP 	= rba->mqa[rba->next_mqa]->aol_current_ip;

			if (vqr->PrevIP != vqr->OurIP)
			{
				vqr->force_new_connect 	= 1;
				vqr->PrevIP 			= vqr->OurIP;
			}
		}

      /* tell the vqr thread about this mailing */ 
      vqr->mqa = rba->mqa[rba->next_mqa]; 
      found = 1;
    }
    
    /*if ( rba->mqa[rba->next_mqa]->run_state == MQA_RUNNING && found != 1) {
      printf("%i locked out\n", vqr->vqridx);
    }*/
    /* 
     * get ready for the next time we are called, step
     * to the next mailing.
     */
    ++rba->next_mqa;

    /* at the end? then start again at the begining */
    if ( rba->next_mqa == MAX_MAILINGS ) rba->next_mqa = 0;

    /* 
     * keep trying until we find a good one or we wrapped
     * around to the begining again.
     */
  } while ( found == 0 && rba->next_mqa != start_mqa );

  /* 
   * if we didn't find any more mail to send report it
   * to the vqr thread so we can start the "hibernation" dance
   */
  if ( found == 0 ) return(1);

  /* 
   * Otherwise, return success to the vqr thread
   * We got one!
   */
  return(0);
}

int mqa_check_end()
{
 int i;

  for(i=0;i<MAX_MAILINGS;++i) mqa_check_one(rba->mqa[i]);
  return(0);
}

int mqa_check_one( mqa_struct *mqa ) 
{
 int i;
 int found;
 int ret = 0;

  /* don't be a wise guy */
  if ( mqa == NULL ) return(ret);

  for(found=0,i=0;i<rba->MaxThreads&&found==0;++i) {
    if ( vqr_global[i]->mqa == mqa ) found = 1;
  }
  ret = 0;
  switch (mqa->run_state) {
    case MQA_READY:
    case MQA_RUNNING:
    case MQA_PAUSED:
    case MQA_HOLD:
      break;
    case MQA_CANCEL:
    case MQA_DONE:
      if ( found == 0 ) mqa_close(mqa, mqa->run_state);
      break;
    case MQA_VQR_DONE:
      /*
       * if ( found == 0 && mqa->total_addrs == mqa->total_success + 
       *   mqa->total_failure + mqa->total_deferral ) {
       */
      if ( found == 0 ) mqa_close(mqa, MQA_DONE);
      break;
    default:
      printf("mqa_check_one: run_state invalid: %d\n", mqa->run_state );
      ret = -1;
      break;
  }
  return(ret);
}

int mqa_cancel( int mqaidx )
{
 int i;
 int found;
 int ret;

  /* boundary check */
  if ( mqaidx < 0 || mqaidx >= MAX_MAILINGS ) return(-1);

  /* lock the mailings */
  pthread_mutex_lock(&rba->TheMutex);

  /* look for a vqr running this mqa */
  found = 0;
  for(i=0;found==0&&i<rba->MaxThreads;++i) {
    if ( vqr_global[i]->have_status != RBS_NO_STATUS && 
         vqr_global[i]->mqa == rba->mqa[mqaidx] ) found = 1;
  }

  ret = 0;
  switch( rba->mqa[mqaidx]->run_state ) {
    case MQA_RUNNING:
    case MQA_PAUSED:
    case MQA_CANCEL:
    case MQA_DONE:
    case MQA_VQR_DONE:
      /* if no vqr is running this mqa then close and prep the mqa for
       * a new mailing */
      if ( found == 0 ) {
        mqa_close(rba->mqa[mqaidx], MQA_CANCEL);
      } else {
        rba->mqa[mqaidx]->run_state = MQA_CANCEL;
        mqa_update_log( rba->mqa[mqaidx] );
        mqa_erase_state( rba->mqa[mqaidx] ); 
      }
      break;
    default: 
      printf("mqa_cancel: run_state invalid: %d\n", rba->mqa[mqaidx]->run_state );
      ret = -1;
  }
  pthread_mutex_unlock(&rba->TheMutex);
  return(ret);
}

int mqa_pause( int mqaidx )
{
 mqa_struct *mqa;

  if ( mqaidx < 0 || mqaidx >= MAX_MAILINGS ) return(-1);

  mqa = rba->mqa[mqaidx];
  if ( mqa->run_state == MQA_RUNNING ) {
    /* set the state to paused so no one else reads 
     * from the list file
     * write out a state file so we can read it next time */
    pthread_mutex_lock(&rba->TheMqaMutex);
    mqa->run_state = MQA_PAUSED;
    mqa_update_log( mqa );
    mqa_save_state( mqa );
    pthread_mutex_unlock(&rba->TheMqaMutex);
    return(0);
  }
  return(-1);

}

int mqa_resume( int mqaidx )
{
 int i;
 int found;

  if ( mqaidx < 0 || mqaidx >= MAX_MAILINGS ) return(-1);

  if ( rba->mqa[mqaidx]->run_state == MQA_PAUSED ) {
    pthread_mutex_lock(&rba->TheMqaMutex);
    rba->mqa[mqaidx]->run_state = MQA_RUNNING;
    mqa_update_log( rba->mqa[mqaidx] );
    mqa_erase_state( rba->mqa[mqaidx] ); 
    pthread_mutex_unlock(&rba->TheMqaMutex);
  }

  for(found=0,i=0;i<rba->MaxThreads&&found==0;++i) {
    if (  vqr_global[i]->have_status == MQA_RUNNING ) found = 1;
  }
  if ( found == 0 ) pthread_mutex_unlock(&rba->TheMutex);

  /* figure out how to write pause info */
  return(0);
}

void mqa_update_log( mqa_struct *mqa ) 
{
 char *status;
 time_t mytime;

  mytime = time(NULL);

  switch ( mqa->run_state ) {
    case MQA_READY:
      status = "ready";
      break;
    case MQA_RUNNING:
      status = "running";
      break;
    case MQA_PAUSED:
      status = "paused";
      break;
    case MQA_VQR_DONE:
      status = "vqr_done";
      break;
    case MQA_CANCEL:
      status = "cancel";
      break;
    case MQA_DONE:
      status = "done";
      break;
    default:
      status = "unknown";
      break;
  }

  rba_open_log();
  if ( rba->fs_log != NULL ) {
    fprintf(rba->fs_log, "%s %s %s %d %d %d %d %ld %ld\n",
      status, mqa->AddrFile, mqa->BodyFile, 
      mqa->total_addrs, mqa->total_success, mqa->total_failure, 
      mqa->total_deferral, mqa->start_time, mytime);
    fflush(rba->fs_log);
  }
  rba_close_log();

  mqa_update_db(mqa, &mqa->mys);
  mys_close(&mqa->mys);
}

void mqa_save_state( mqa_struct *mqa )
{
 FILE *fs;

  /* format the state file name */
  snprintf(mqa->tmpbuf, MAX_BUFF, "%s/state/%s^%s", 
    CELIBERODIR, mqa->tofile, mqa->bodyfile);

  if ( (fs = fopen(mqa->tmpbuf, "w+")) == NULL ) return;

  /* save the totals, succes, failure, deferral */ 
  fprintf(fs, "%d %d %d\n", 
    mqa->total_success, mqa->total_failure, mqa->total_deferral);

  fclose(fs);

}

void mqa_erase_state( mqa_struct *mqa )
{

  /* format the state file name */
  snprintf(mqa->tmpbuf, MAX_BUFF, "%s/state/%s^%s", 
    CELIBERODIR, mqa->tofile, mqa->bodyfile);

  /* remove the file */
  unlink(mqa->tmpbuf);
}

void mqa_shutdown()
{
 int i;

  /* pause every running mailing and save the state */
  for(i=0;i<MAX_MAILINGS;++i) mqa_pause(i);

}

void mqa_startup()
{
 FILE *fs;
 DIR *mydir;
 struct dirent *mydirent;
 static char tmpbuf[156];
 static char tmpbuf_r[156];
 char *tofile;
 char *bodyfile;
 int success;
 int failure;
 int deferral;

  snprintf(tmpbuf, 156, "%s/state", CELIBERODIR );

  if ( (mydir=opendir(tmpbuf)) == NULL ) return;

  while((mydirent=readdir(mydir))!=NULL) {
    if ( mydirent->d_name[0] == '.' ) continue;

    snprintf(tmpbuf, 156, "%s/state/%s", CELIBERODIR, mydirent->d_name);
    if ( (fs = fopen(tmpbuf, "r")) == NULL ) continue;
    if ( fgets( tmpbuf, 156, fs ) == NULL ) {
      fclose(fs);
      continue;
    }
    if ((tofile = strtok_r(tmpbuf, " \n\r",(char **)&tmpbuf_r)) == NULL ) continue;
    success = atol(tofile);
    if ((tofile = strtok_r(NULL, " \n\r",(char **)&tmpbuf_r)) == NULL ) continue;
    failure = atol(tofile);
    if ((tofile = strtok_r(NULL, " \n\r",(char **)&tmpbuf_r)) == NULL ) continue;
    deferral = atol(tofile);

    strncpy(tmpbuf, mydirent->d_name, 156);
    if ((tofile = strtok_r(tmpbuf, "^",(char **)&tmpbuf_r)) == NULL) continue;
    if ((bodyfile = strtok_r(NULL, "^",(char **)&tmpbuf_r)) == NULL ) continue;

    /* start it up but in paused state */
    printf("start mailing in  paused state\n");
    pthread_mutex_lock(&rba->TheMqaMutex);
    mqa_new_mailing(tofile, bodyfile, 1, success, failure, deferral, 1, 0, 0, 0, 1, "", 0, 0 );
    pthread_mutex_unlock(&rba->TheMqaMutex);
    printf("end  calling paused state\n");

  }
  closedir(mydir);
}

void mqa_update_db(mqa_struct *mqa, mysql_struct *mys ) 
{
 char *status;
 int state = 0, success = 0;

  mys_open(mys);

  switch ( mqa->run_state ) {
    case MQA_READY:
      return;
    case MQA_RUNNING:
      status = "running";
      state = 4;
      break;
    case MQA_PAUSED:
      status = "paused";
      state = 3;
      break;
    case MQA_VQR_DONE:
      status = "vqr_done";
      state = 7;
      break;
    case MQA_CANCEL:
      status = "cancel";
      state = 9;
      break;
    case MQA_DONE:
      status = "done";
      state = 7;
      break;
    default:
      return;
  }

  if(state == 7 && mqa->retry_count > 0 && mqa->total_deferral > 0 && mqa->run_state != MQA_CANCEL)
  {
      state = 5;
  }

  if(mqa->first_time < 1 && state == 4)
  {
    success = mqa->total_success - mqa->last_success;
    mqa->last_success = mqa->total_success;
    snprintf(mys->SqlBuf, MAX_SQL_BUFF, "UPDATE schedule SET end_time = NOW(), success=success+%d, deferral=deferral-%d, retry_level=%d, state=5 WHERE id=%d", 
             success, success, mqa->retry_count, mqa->mail_id);
  }
  else if(mqa->first_time < 1)
  {
    success = mqa->total_success - mqa->last_success;
    mqa->last_success = mqa->total_success;
    snprintf(mys->SqlBuf, MAX_SQL_BUFF, "UPDATE schedule SET state=%i, end_time = NOW(), success=success+%d, deferral=deferral-%d, retry_level=%d WHERE id=%d", 
             state, success, success, mqa->retry_count, mqa->mail_id);
  }
  else if(state == 4)
  {
    snprintf(mys->SqlBuf, MAX_SQL_BUFF, "UPDATE schedule SET end_time = NOW(), success=%d, failure='%d', deferral='%d', retry_level=%d WHERE id=%d", 
             mqa->total_success, mqa->total_failure, mqa->total_deferral, mqa->retry_count, mqa->mail_id);
  }
  else
  {
      snprintf(mys->SqlBuf, MAX_SQL_BUFF, "UPDATE schedule SET state=%i, end_time = NOW(), success=%d, failure='%d', deferral='%d', retry_level=%d  WHERE id=%d", 
             state, mqa->total_success, mqa->total_failure, mqa->total_deferral, mqa->retry_count, mqa->mail_id);
  }
  if (mysql_query(&mys->mysql_conn,mys->SqlBuf)) {
    printf("mysql error 8: %s: %s\n", 
      mysql_error(&mys->mysql_conn), mys->SqlBuf);
    mys_close(mys);
    return;
  }
  mys->mysql_res = mysql_store_result(&mys->mysql_conn);
  mysql_free_result(mys->mysql_res);


	rp_removed(mys, mqa->msg_id, mqa->mail_id);
}
