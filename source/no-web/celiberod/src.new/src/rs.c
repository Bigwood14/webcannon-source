#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <time.h>
#include <sys/types.h>
#include <dirent.h>
#include <memory.h>
#include <errno.h>
#include <pthread.h>
#include "config.h"
#include "celcommon.h"
#include "celutil.h"
#include "mqa.h"
#include "rba.h"
#include "rs.h"


#define DEC    ( void *(*)(void*) )
#define RS_UNUSED  0
#define RS_FILLED  1
#define RS_CHECKED 2
#define MAX_RS 10

typedef struct rs_struct {
  char tofile[MAX_BUFF];
  char bodyfile[MAX_BUFF];
  time_t start_time;
  int  state;
} rs_struct;

mysql_struct mys;

/* local globals */
static char TmpBufS[MAX_BUFF];
static rs_struct RS[MAX_RS];

/* local functions */
int rs_write();
int rs_save();
void rs_send_report();

void mysql_check_sched();
void mqa_update_all_db();
void mqa_update_db(mqa_struct *mqa, mysql_struct *mys );

extern rba_struct *rba;
int dc_init();

void rs_thread()
{
 int    i;
 time_t mytime;
 struct tm mytm;
 struct timeval tv;
 int    send_report;
 int    dc_init_flag;
 mysql_struct mys2;

  send_report = 0;
  dc_init_flag = 0;
  mys_init(&mys2);

  while(1) {
    tv.tv_usec = 0; 
    tv.tv_sec = TV_WAIT_TIME;
    select(0, (fd_set *)0,(fd_set *)0, (fd_set *) 0, &tv);

    rs_check();

    mytime = time(NULL);
    localtime_r(&mytime, &mytm);
    if ( mytm.tm_hour == 5 && mytm.tm_min > 10 && dc_init_flag == 0 ) {
      dc_init_flag = 1;
      dc_init();
    } else if ( mytm.tm_hour == 5 && mytm.tm_min > 15 ) {
      dc_init_flag = 0;
    }
    
    /* check database schedule */
    mysql_check_sched();
    for(i=0;i<MAX_MAILINGS;++i) {
      mqa_update_db(rba->mqa[i],&mys2);
    }
  }
  pthread_exit(0);
}

int rs_init()
{
 FILE *fs;
 int i;
 int ret;
 pthread_t rt;

  memset(RS, 0, sizeof(rs_struct)*MAX_RS);

  snprintf(TmpBufS, MAX_BUFF, "%s/etc/sched", CELIBERODIR);
  if ( (fs=fopen(TmpBufS, "r")) != NULL ) {
    for(i=0;i<MAX_RS&&fgets(TmpBufS,MAX_BUFF,fs)!=NULL;++i) {
      ret = sscanf( TmpBufS, "%s %s %ld\n", RS[i].tofile, 
                                            RS[i].bodyfile, 
                                           &RS[i].start_time);
      if ( ret != 3 ) {
        printf("rs: error loading schedule %s\n", TmpBufS);
        break;;
      }
      RS[i].state = RS_FILLED;
      ++i;
    }
    fclose(fs);
  }

  if(pthread_create(&rt, NULL, DEC rs_thread, (void *)0)) {
    printf("pthread_create rs failed: err=%d\n", errno);
  }

  return(0);
}

int rs_close()
{
  rs_save();
  return(0);
}

int rs_add(char *tofile, char *bodyfile, time_t start_time)
{
 int i;
 int saved;

  if ( tofile == NULL )   return(-1);
  if ( bodyfile == NULL ) return(-1);
  if ( start_time == 0 )  return(-1);

  saved = 0;
  for(i=0;i<MAX_RS&&saved==0;++i) {
    if ( RS[i].state == RS_UNUSED ) {
      strncpy( RS[i].tofile, tofile, MAX_BUFF);
      strncpy( RS[i].bodyfile, bodyfile, MAX_BUFF);
      RS[i].state = RS_FILLED;
      RS[i].start_time = start_time;
      saved = 1;
    }
  }

  if ( saved == 0 ) return(-1);
  rs_save();
  return(0);
}

int rs_delete( char *tofile, char *bodyfile, time_t start_time )
{
 int i;
 int deleted;

  if ( tofile == NULL )   return(-1);
  if ( bodyfile == NULL ) return(-1);
  if ( start_time == 0 )  return(-1);

  deleted = 0;
  for(i=0;i<MAX_RS;++i) {
    if ( RS[i].state == RS_FILLED && 
         strcmp( RS[i].tofile, tofile ) == 0 &&
         strcmp( RS[i].bodyfile, bodyfile ) == 0 &&
         RS[i].start_time == start_time ) {
      deleted = 1;
      memset(&RS[i], 0, sizeof(rs_struct));
    }
  }

  if ( deleted == 0 ) return(-1);
  rs_save();
  return(0);
}

int rs_show(char *buff, int buff_len )
{
 int i;
 int found;

  found = 0;
  memset(buff, 0, buff_len);
  for(i=0;i<MAX_RS;++i) {
    if ( RS[i].state == RS_FILLED ) {
      snprintf(TmpBufS, MAX_BUFF, "%s %s %ld\n", RS[i].tofile, 
                                     RS[i].bodyfile, 
                                     RS[i].start_time);
      strncat( buff, TmpBufS, buff_len);
      ++found;
    }
  }
  if ( found == 0 ) strncat(buff, "\n", 1);
  return(0);
}

int rs_check()
{
 int i;
 time_t mytime;
 int modified;

  modified = 0;
  mytime = time(NULL);
  for(i=0;i<MAX_RS;++i) {
    if ( RS[i].state == RS_FILLED ) {
      if ( mytime >= RS[i].start_time ) {
        modified = 1; 
        RS[i].state = RS_CHECKED;
       // rba_start_mailing( RS[i].tofile, RS[i].bodyfile, 1, 0, 0, 0, 0, 0, 0, 0, 1 );
        memset(&RS[i], 0, sizeof(rs_struct));
      }
    }
  }
  if ( modified == 1 ) rs_save();
  return(0);
}


int rs_save()
{
 int i;
 FILE *fs;
 int count;

  snprintf(TmpBufS, MAX_BUFF, "%s/etc/sched", CELIBERODIR);

  for(i=0,count=0;i<MAX_RS;++i) if ( RS[i].state == RS_FILLED ) ++count;
  if ( count == 0 ) {
    unlink(TmpBufS);
    return(0);
  }


  snprintf(TmpBufS, MAX_BUFF, "%s/etc/sched", CELIBERODIR);
  if ( (fs=fopen(TmpBufS, "w+")) == NULL ) {
    printf("rs_save: could not open sched file %s\n", TmpBufS);
    return(-1);
  }

  for(i=0;i<MAX_RS;++i){
    if ( RS[i].state == RS_FILLED ) {
     fprintf( fs, "%s %s %ld\n", RS[i].tofile, 
                               RS[i].bodyfile, 
                               RS[i].start_time);
    }
  }
  fclose(fs);
  return(0);

}

void rs_send_report()
{
 FILE *fs;
 char tmpbuf[156];

  
  /* open report log */
  snprintf(tmpbuf, 156, "%s/history/report", CELIBERODIR);
  fs = fopen(tmpbuf, "a+");
  if ( fs == NULL ) {
    printf("rs: could not open report log %s\n", tmpbuf);
  }

  /* close log */
  fclose(fs);

  /* erase log */
  snprintf(tmpbuf, 156, "%s/history/report", CELIBERODIR);
  unlink(tmpbuf);
}
