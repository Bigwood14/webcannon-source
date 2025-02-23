#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include <signal.h>
#include <errno.h>
#include <pthread.h>
#include <resolv.h>
#include <math.h>

#include <sys/types.h>
#include <sys/socket.h>
#include <sys/time.h>
#include <sys/un.h>
#include <sys/stat.h>

#include <arpa/inet.h>

#include <netinet/in.h>
#include <netinet/in_systm.h>
#include <netinet/ip.h>
#include <netinet/tcp.h>

#include "config.h"
#include "celcommon.h"
#include "celutil.h"
#include "mqa.h"
#include "rba.h"
#include "vqr.h"
#include "celiberod.h"
#include "uqa.h"
#include "rs.h"
#include "dc.h"
#include "rp.h"
#include "celacl.h"
#include "custom.h"
#include "mysqlutil.h"

struct mysql_struct mys1;
struct mysql_struct mys2;
struct mysql_struct mys3;
void mysql_init_sched();

#define DEC		( void *(*)(void*) )
#define TOKENS " ,:\n\t\r"

pthread_t vqt[MAX_THREADS];
vqr_struct *vqr_global[MAX_THREADS];
rba_struct *rba;

int BpMailId = 1;

/* TODO: moveto config */
#define MAX_UNIX_CLIENTS 10
uqa_struct Uqa[MAX_UNIX_CLIENTS];

int init_rba();
int init_vqr();
void rba_clear_log();
void rba_open_log();
void rba_close_log();
void rba_read_config();
void unix_client(uqa_struct *uqa);

int main( int argc, char *argv[] ) 
{
	// reads config file sets up intial variables
	cel_init();
	ignore_signals();
	init_rba();

	rba->id_only 	= atoi(argv[1]);	
	rba->threads 	= atoi(argv[2]);	
	rba->sleep 		= atoi(argv[3]);	
	//get_options(argc,argv);

	rba_read_config();

	// domain control - reads in the IPs/Domains
	dc_init();
	mys_init(&mys1);
	rp_init(&mys1, 0, rba);
	rba->MaxThreads = MaxIPS;
	// access control lists - is this needed?!
	init_acl_host();
	// opens unix server
	//init_startup();
	// makes the mailer threads
	init_vqr();
	// scheduler create the thread that checks for new mailings
	rs_init();
	
	printf("starting\n");
	
	// not needed? - reads a state dir that we do not have
	mqa_startup();

	fflush(stdout);

	// reads the schedule table resumes any previous mailing
	mysql_init_sched();
	// handles unix client connections - that dont do much
	handle_inet_client(rba->TheUnixSocket);

	return(0);
}

void get_options(int argc, char **argv)
{
	int	c;
	int	errflag;
	extern	char	*optarg;
	extern	int	optind;

	snprintf(rba->ConfigFile, MAX_BUFF, "%s/etc/celibero.conf", CELIBERODIR);
	strncpy(rba->SockFile, SOCK_FILE, MAX_BUFF);
	rba->MaxThreads = MAX_THREADS;

	errflag = 0;
	while( !errflag && (c=getopt(argc,argv,"V:vc:")) != -1 )
	{
		switch(c)
		{
			case 'v':
				printf("version: %s\n", VERSION);
				exit(EXIT_SUCCESS);
				break;
			case 'c':
				strncpy(rba->ConfigFile, optarg, MAX_BUFF);
				break;
			default:
				errflag = 1;
				break;
		}
	}

	if ( errflag == 1 ) {
		usage();
		exit(-1);
	}
}

void usage()
{
	printf("usage: -v for version\n");
}

void rba_read_config()
{
	strncpy( rba->hostname, HeloHost, MAX_BUFF);
	snprintf( rba->helo_string, MAX_BUFF, "HELO %s\r\n", rba->hostname); 
	rba->MaxThreads = IMaxThreads;
	strncpy(rba->SockFile,SockFile, MAX_BUFF);
}

void init_startup()
{
	if ( InetSocket == 1 ) {
		if ( (rba->TheUnixSocket = 
						open_inet_server("127.0.0.1", CELMAILD_PORT)) == -1 ) {
			printf("unix socket error\n");
		}
	} else {
		if ( (rba->TheUnixSocket = open_unix_server(CELMAILD_PATH)) == -1 ) {
			printf("unix socket error\n");
		}
	}
	res_init();
}

void handle_inet_client(int s)
{
 int i;
 int cs;
 static struct sockaddr_in remote;
 static char tmpbuf[30];
 /*static struct sockaddr_un remote;*/
 static socklen_t t;
 pthread_t ut;
 int err;

	ignore_signals();

	/* wait forever for accepts */
	while(1) {
		sleep(10);
		check_dead_time();
		/* accept, on error report the error and try again */
		if ((cs = accept(s, (struct sockaddr *)&remote, &t)) == -1 ) {
			//printf("unix client accept error: err=%s\n", strerror(errno));
			//printf("celiberod might all ready be running\n");
			sleep(10);
			continue;

		/* got a client connection */
		} else {

			strncpy(tmpbuf, inet_ntoa(remote.sin_addr), 30);
			if ( check_acl_host( tmpbuf ) == -1 ) {
				close(cs);
				printf("client acl reject: unauthorized client connection from %s\n", 
					tmpbuf);
				continue;
			}

			/* look for a unix client structure that's open */
			for(i=0;i<MAX_UNIX_CLIENTS;++i) {

				/* found one not in use */
				if ( Uqa[i].in_use == 0 ) {
					pthread_mutex_lock(&Uqa[i].UqaMutex);
					Uqa[i].in_use = 1;
					Uqa[i].thesock = cs;
					pthread_cond_signal(&Uqa[i].UqaCond);
					pthread_mutex_unlock(&Uqa[i].UqaMutex);
					break;
				}
			}

			if (i==MAX_UNIX_CLIENTS) {
				close(cs);
			}
		}
	}
}

/*
 * This routine handles talking to clients via the unix file socket
 */
void unix_client( uqa_struct *uqa )
{


	/* read a request */
	while( 1 ) {
		pthread_mutex_lock(&uqa->UqaMutex);
		uqa->in_use = 0;
		pthread_cond_wait(&uqa->UqaCond,&uqa->UqaMutex);
		pthread_mutex_unlock(&uqa->UqaMutex);

		uqa->keep_going = 1;
		while(uqa->keep_going == 1 ) {

			/* make sure we have a clean buffer */
			memset(uqa->readbuf, 0, TMPBUF_SIZE );

			/* read the request */
			uqa->i=read(uqa->thesock, uqa->readbuf, TMPBUF_SIZE-1);
			if ( uqa->i <= 0 ) {
				close(uqa->thesock);
	uqa->keep_going = 0;
				continue;
			}

			/* read the request */
			/* tokenize */
			if ( (uqa->tmpstr=strtok_r(uqa->readbuf,TOKENS,
															 (char **)&uqa->tokbuf))==NULL) { 
				/* no token, send back bad */
				if ( write( uqa->thesock, "bad	1\n", 7) == -1 ) {
					close(uqa->thesock);
		uqa->keep_going = 0;
		continue;
				}
				continue;
			}

			/* admin command coming in */
			if ( strcmp( uqa->tmpstr, "admin" ) == 0 ) {

				/* bad request */
				if ( (uqa->tmpstr = strtok_r(NULL, TOKENS, 
																		 (char **)&uqa->tokbuf)) == NULL ) { 
					/* no token, send back bad */
					if ( write( uqa->thesock, "bad	2\n", 7) == -1 ) {
						close(uqa->thesock);
			uqa->keep_going = 0;
			continue;
					}
					continue;
				}

				/* they want to shutdown */
				if ( strcmp( uqa->tmpstr, "shutdown" ) == 0 ) {
					printf("ending: admin shutdown\n");
					fflush(stdout);
					fflush(stderr);
					mqa_shutdown(rba);
					exit(0);


				} else if ( strcmp( uqa->tmpstr, "history" ) == 0 ) {

					if ( (uqa->tmpstr = strtok_r(NULL, TOKENS, 
																		 (char **)uqa->tokbuf)) == NULL ) {
						if ( write( uqa->thesock, "bad	3\n", 7) == -1 ) {
							close(uqa->thesock);
				uqa->keep_going = 0;
				continue;
						}
			continue;
					}

					if ( strcmp( uqa->tmpstr, "clear" ) == 0 ) {
						rba_clear_log(rba);
						write( uqa->thesock, "history log cleared\n", 20);
					}

				} else if ( strcmp( uqa->tmpstr, "read" ) == 0 ) {

					if ( (uqa->tmpstr = strtok_r(NULL, TOKENS, 
																			 (char **)&uqa->tokbuf)) == NULL ) {
						if ( write( uqa->thesock, "bad	3\n", 7) == -1 ) {
							close(uqa->thesock);
				uqa->keep_going = 0;
				continue;
						}
			continue;
					}

					if ( strcmp( uqa->tmpstr, "config" ) == 0 ) {
						read_config(rba);
						if ( write( uqa->thesock, "bad	3\n", 7) == -1 ) {
							close(uqa->thesock);
				uqa->keep_going = 0;
				continue;
						}
					}
				}

			} else	if ( strcmp( uqa->tmpstr, "mailing" ) == 0 ) {
				if ( (uqa->tmpstr = strtok_r(NULL, TOKENS, 
																		 (char **)&uqa->tokbuf)) == NULL ) {
					if ( write( uqa->thesock, "bad	5\n", 7) == -1 ) {
						close(uqa->thesock);
			uqa->keep_going = 0;
			continue;
					}
					continue;
				} 

				if ( strcmp( uqa->tmpstr, "start" ) == 0 ) {
					if ( (uqa->tmpstr = strtok_r(NULL, TOKENS, 
																			 (char **)&uqa->tokbuf)) == NULL ) {

						/* no token, send back bad */
						if ( write( uqa->thesock, "bad	6\n", 7) == -1 ) {
							close(uqa->thesock);
				uqa->keep_going = 0;
				continue;
						}
						continue;

					}
					uqa->tofile = uqa->tmpstr;

					if ( (uqa->tmpstr = strtok_r(NULL, TOKENS, 
																			 (char **)&uqa->tokbuf)) == NULL ) {
						/* no token, send back bad */
						if ( write( uqa->thesock, "bad	6\n", 7) == -1 ) {
							close(uqa->thesock);
				uqa->keep_going = 0;
				continue;
						}
						continue;
					} 
					uqa->bodyfile = uqa->tmpstr;

					if ( (uqa->tmpstr = strtok_r(NULL, TOKENS, 
																			(char **)&uqa->tokbuf)) == NULL ) {
						/* no token, send back bad */
						if ( write( uqa->thesock, "bad	6\n", 7) == -1 ) {
							close(uqa->thesock);
				uqa->keep_going = 0;
				continue;
						}
						continue;
					} 
					uqa->ret = atoi(uqa->tmpstr);
	
					pthread_mutex_lock(&rba->TheMqaMutex);
					uqa->ret = rba_start_mailing( uqa->tofile, uqa->bodyfile, uqa->ret, 
																			0, 0, 0, 0, BpMailId, 0, 0, 1, "", 0, 0 );
					pthread_mutex_unlock(&rba->TheMqaMutex);
		++BpMailId;
					switch(uqa->ret) {
						case MQA_NEW_SUCCESS:
							snprintf(uqa->status, TMPBUF_SIZE, "mailing started %s %s\n",
								uqa->tofile, uqa->bodyfile);
							break;
						case MQA_NEW_MAX_MAILINGS:
							snprintf(uqa->status, TMPBUF_SIZE, "error: max mailings reached\n");
							break;
						case MQA_NEW_BAD_ADDR_FILE:
							snprintf(uqa->status, TMPBUF_SIZE, 
								"error: could not open list file %s\n", uqa->tofile);
							break;
						case MQA_NEW_BAD_BODY_FILE:
							snprintf(uqa->status, TMPBUF_SIZE, 
								"error: could not open body file %s\n", uqa->bodyfile);
							break;
						case MQA_NEW_BAD_STAT_FILE:
							snprintf(uqa->status, TMPBUF_SIZE, 
								"error: could not open stat files\n");
							break;
						default:
							snprintf(uqa->status, TMPBUF_SIZE, 
								"error: rba_start_mailing returns unknown vaule %d\n", uqa->ret);
							break;
					}
					if ( write( uqa->thesock, uqa->status, strlen(uqa->status))==-1) {
						close(uqa->thesock);
			uqa->keep_going = 0;
			continue;
					}


				} else if ( strcmp( uqa->tmpstr, "stat" ) == 0 ) {
						strncpy(uqa->status,
"Mid Status Sent Total Success Failure Deferral List Body RetryCount StartTime\n",
							TMPBUF_SIZE);

						memset(uqa->tmpbuf, 0, MAX_BUFF);
						pthread_mutex_lock(&rba->TheMqaMutex);
						for(uqa->i=0;uqa->i<MAX_MAILINGS;++uqa->i) {
							switch( rba->mqa[uqa->i]->run_state) {
								case MQA_READY:
									uqa->value = "ready";
									break;
								case MQA_RUNNING:
									uqa->value = "running";
									break;
								case MQA_PAUSED:
									uqa->value = "paused";
									break;
								case MQA_VQR_DONE:
									uqa->value = "finishing";
									break;
								case MQA_CANCEL:
									uqa->value = "cancel";
									break;
								default:
									uqa->value = "run_state error";
									break;
							}
							memset(uqa->tmpbuf, 0, TMPBUF_SIZE);
							snprintf(uqa->tmpbuf, TMPBUF_SIZE, 
								"%d %s %d %d %d %d %d %s %s %d %lu\n",
								uqa->i,	uqa->value,
								rba->mqa[uqa->i]->total_success +
									rba->mqa[uqa->i]->total_failure +
									rba->mqa[uqa->i]->total_deferral,
								rba->mqa[uqa->i]->total_addrs,
								rba->mqa[uqa->i]->total_success,
								rba->mqa[uqa->i]->total_failure,
								rba->mqa[uqa->i]->total_deferral,
								rba->mqa[uqa->i]->AddrFile,
								rba->mqa[uqa->i]->BodyFile,
								rba->mqa[uqa->i]->retry_count,
								rba->mqa[uqa->i]->start_time);
							strncat(uqa->status, uqa->tmpbuf, TMPBUF_SIZE);
					}
					pthread_mutex_unlock(&rba->TheMqaMutex);
					if ( write( uqa->thesock, uqa->status, strlen(uqa->status))==-1) break;
				} else if ( strcmp( uqa->tmpstr, "pause" ) == 0 ) {
	
					if ( (uqa->tmpstr = strtok_r(NULL, TOKENS, 
																			 (char **)&uqa->tokbuf)) == NULL ) {
						/* no token, send back bad */
						if ( write( uqa->thesock, "bad 17\n", 7) == -1 ) {
							close(uqa->thesock);
				uqa->keep_going = 0;
				continue;
						}
						continue;
					}
					uqa->i = atoi(uqa->tmpstr);
	
					if ( (uqa->ret=mqa_pause( uqa->i )) != 0 ) {
						snprintf(uqa->status, UNIX_STATUS_SIZE, 
							"error %d: mailing %d not paused\n", 
							uqa->ret, uqa->i);
					} else {
						snprintf(uqa->status, UNIX_STATUS_SIZE,
							"mailing %d paused\n", uqa->i);
					}
					if ( write( uqa->thesock, uqa->status, strlen(uqa->status))==-1) { 
						close(uqa->thesock);
			uqa->keep_going = 0;
			continue;
					}
	
				} else if ( strcmp( uqa->tmpstr, "cancel" ) == 0 ) {
	
					if ( (uqa->tmpstr = strtok_r(NULL, TOKENS, 
																			 (char **)&uqa->tokbuf)) == NULL ) {
						/* no token, send back bad */
						if ( write( uqa->thesock, "bad	6\n", 7) == -1 ) {
							close(uqa->thesock);
				uqa->keep_going = 0;
				continue;
						}
						continue;
					}
	
					uqa->i = atoi(uqa->tmpstr);
					if ( (uqa->ret=mqa_cancel( uqa->i )) != 0 ) {
						snprintf(uqa->tmpbuf, TMPBUF_SIZE, 
							"error %d: mailing %d not cancelled\n", 
							uqa->ret, uqa->i);
					} else {
						snprintf(uqa->tmpbuf, TMPBUF_SIZE,
							"mailing %d cancelled\n", uqa->i);
					}
					if ( write( uqa->thesock, uqa->tmpbuf, strlen(uqa->tmpbuf))==-1) { 
						close(uqa->thesock);
			uqa->keep_going = 0;
			continue;
					}
	
				} else if ( strcmp( uqa->tmpstr, "resume" ) == 0 ) {

					if ( (uqa->tmpstr = strtok_r(NULL, TOKENS, 
																			 (char **)&uqa->tokbuf)) == NULL ) {
						/* no token, send back bad */
						if ( write( uqa->thesock, "bad	6\n", 7) == -1 ) {
							close(uqa->thesock);
				uqa->keep_going = 0;
				continue;
						}
						continue;
					}
					uqa->i = atoi(uqa->tmpstr);
					if ( (uqa->ret=mqa_resume( uqa->i )) != 0 ) {
						snprintf(uqa->tmpbuf, TMPBUF_SIZE,
							"error %d: mailing %d not resumed\n", 
							uqa->ret, uqa->i);
					} else {
						snprintf(uqa->tmpbuf, TMPBUF_SIZE,
							"mailing %d resumed\n", uqa->i);
					}
					if ( write( uqa->thesock, uqa->tmpbuf, strlen(uqa->tmpbuf))==-1) { 
						close(uqa->thesock);
			uqa->keep_going = 0;
			continue;
					}
	
				} else {
					/* no token, send back bad */
					if ( write( uqa->thesock, "bad 20\n", 7) == -1 ) {
						close(uqa->thesock);
			uqa->keep_going = 0;
			continue;
					}
				}
			} else	if ( strcmp( uqa->tmpstr, "quit" ) == 0 ) {
				close(uqa->thesock);
	uqa->keep_going = 0;
	continue;
			} else {
				if ( write( uqa->thesock, "unknown command\n", 17) == -1 ) {
					close(uqa->thesock);
		uqa->keep_going = 0;
		continue;
				}
			}
		}
	}
	pthread_exit(0);
}

int rba_start_mailing( char *tofile, char *bodyfile, int retry_count, 
	int success, int failure, int deferral, int pause,
	int mail_id, int msg_id, int max_threads, int first_time, char *yahoo_date, int aol_rotate, int max_per_ip )
{
	int 	ret;
	int 	i;
	int 	found;
	struct 	timeval tv;

	// check to see if threads are initalized first
	for (i=0;i<MaxIPS;++i)
	{
		if (vqr_global[i] == NULL)
		{
			printf("thread %d not ready going to sleep for 10\n", i);

			tv.tv_usec 	= 0;
			tv.tv_sec 	= 10;
			select(0, (fd_set *)0,(fd_set *)0, (fd_set *) 0, &tv);

			rba_start_mailing(tofile, bodyfile, retry_count, success, failure, deferral, pause, mail_id, msg_id, max_threads, first_time, yahoo_date, aol_rotate, max_per_ip);
			return;
			break;
		}
	}

	if ( (ret=mqa_new_mailing( tofile, bodyfile, retry_count,
							success, failure, deferral, pause, mail_id, msg_id, max_threads, first_time, yahoo_date, aol_rotate, max_per_ip)) != 0 )
	{
		printf("error: start_mailing: mqa_add reported error\n");
		return(ret);
	}

	for(found=0,i=0;i<MaxIPS&&found==0;++i)
	{
		if (vqr_global[i]->have_status == MQA_RUNNING ) found = 1;
	}
	if ( found == 0 ) pthread_mutex_unlock(&rba->TheMutex);

	return(0);
}

int init_rba()
{
 int i;

	rba = malloc(sizeof(rba_struct));

	if (rba == NULL)
	{
		printf("could not get memory for RBA malloc\n");
		exit(-1);
	}

	memset(rba, 0, sizeof(rba_struct));
	for(i=0;i<MAX_MAILINGS;++i){
		rba->mqa[i] = malloc(sizeof(mqa_struct));

		if (rba->mqa[i] == NULL)
		{
			printf("could not get memory for mqa[%d]\n", i);
			exit(-1);
		}

		memset(rba->mqa[i], 0, sizeof(mqa_struct));
		rba->mqa[i]->run_state 	= RBS_NO_STATUS;
		rba->mqa[i]->mqaidx 	= i;
		mys_init(&rba->mqa[i]->mys);
	}
	rba->mypid = getpid();
	rba->next_mqa = 0;
	gethostname(rba->hostname, MAX_BUFF);
	rba->CurMessId = 1;

	mys_init(&mys1);
	mys_init(&mys2);
	mys_init(&mys3);

	pthread_mutex_init(&rba->TheMqaMutex, NULL );
	return(0);

}


int init_vqr()
{
	int i, ret, ip;

	pthread_attr_t tattr;

	pthread_mutex_init(&rba->TheMutex, NULL );
	pthread_mutex_lock(&rba->TheMutex);

	ip = 0;	
	rba->MaxThreads = 0;
	for(i=0;i<(MaxIPS*rba->threads);++i)
	{
		ip++;
		if (ip >= MaxIPS)
			ip = 0;

		ret = pthread_attr_init(&tattr);
	
		if (ret != 0)
			printf("attr init error\n");
	
		//ret = pthread_attr_setdetachstate(&tattr,PTHREAD_CREATE_DETACHED);
	
		if (ret != 0)
			printf("set detach error\n");

		ret = pthread_attr_setstacksize(&tattr, 6145728);

		if (ret != 0)
			printf("set stack size error\n");

		vqr_global[i] = malloc(sizeof(vqr_struct));

		if ( vqr_global[i] == NULL )
		{
			printf("vqr %d malloc vqr_struct failed\n", i);
			exit(-1);
		}

		memset(vqr_global[i],0,sizeof(vqr_struct));

		vqr_global[i]->rba 		= rba;
		vqr_global[i]->mqa 		= NULL;
		vqr_global[i]->vqridx 	= i;
		vqr_global[i]->OurIP 	= ip;
		vqr_global[i]->PrevIP 	= -1;
		vqr_global[i]->sends 	= 0;

		rba->MaxThreads++;
		usleep(1);
		if(pthread_create(&vqt[i], &tattr, DEC vqr_loop, (void *)vqr_global[i]))
		{
			printf("pthread_create A failed: err=%d (%s) thread num=%d of=%d\n", errno, strerror(errno), i, rba->MaxThreads);
		}
		else
		{
			printf("create succeeded on %d of %d\n", i, rba->MaxThreads);
		}
	}

	return(0);
}

int rba_all_done( rba_struct *rba )
{
 int i;
 int found;

	/* check if all the mailings are done */
	for(i=0,found=0;found==0 && i<MAX_MAILINGS;++i) {
		if ( rba->mqa[i]->run_state == MQA_RUNNING ) {
			found = 1;
		}
	}
	if ( found == 1 ) return(0);

	for(i=0,found=0;found==0 && i<rba->MaxThreads;++i) {
		if ( vqr_global[i]->have_status != RBS_NO_STATUS ) {
			found = 1;
		}
	}
	if ( found == 1 ) return(0);

	return(1);

}

void rba_open_log()
{
 char tmpbuf[156];

	if ( rba->fs_log != NULL ) return;
	snprintf(tmpbuf, 156, "%s/history/log", CELIBERODIR);
	rba->fs_log = fopen(tmpbuf, "a+");
	if ( rba->fs_log == NULL ) {
		printf("could not open history log %s\n", tmpbuf);
	}

#ifdef NET_REPORT
	if ( rba->fs_report != NULL ) return;
	snprintf(tmpbuf, 156, "%s/history/report", CELIBERODIR);
	rba->fs_report = fopen(tmpbuf, "a+");
	if ( rba->fs_report == NULL ) {
		printf("could not open report log %s\n", tmpbuf);
	}
#endif

}

void rba_clear_log()
{
 char tmpbuf[156];

	rba_close_log();
	snprintf(tmpbuf, 156, "%s/history/log", CELIBERODIR);
	unlink(tmpbuf);
}

void rba_close_log()
{
	if ( rba->fs_log != NULL ) fclose(rba->fs_log);
	rba->fs_log = NULL;

#ifdef NET_REPORT
	if ( rba->fs_report != NULL ) fclose(rba->fs_report);
	rba->fs_log = NULL;
#endif
}

void uqa_init()
{/*
 int i;
 int err;
 pthread_t ut;


	memset( Uqa,0,sizeof(uqa_struct) * MAX_UNIX_CLIENTS);

	for(i=0;i<MAX_UNIX_CLIENTS;++i){
		pthread_mutex_init( &Uqa[i].UqaMutex, NULL );
		pthread_cond_init( &Uqa[i].UqaCond, NULL );
	}

	for(i=0;i<MAX_UNIX_CLIENTS;++i){
		if((err=pthread_create(&ut, NULL,DEC unix_client,(void *)&Uqa[i]))!=0){
			printf("pthread_create B failed: err=%d\n", err);
		}
	}*/

}

void mysql_update_schedule (int mail_id, int state)
{
	mys_open(&mys1);

	/* check for new starts */ 
	snprintf(mys1.SqlBuf, MAX_SQL_BUFF, "UPDATE `schedule` SET `state` = %d WHERE id = %d;", state, mail_id);
	
	if (mysql_query(&mys1.mysql_conn,mys1.SqlBuf))
	{
		printf("mysql_update_schedule:mysql: %s %s\n", mysql_error(&mys1.mysql_conn), mys1.SqlBuf);
		mys_close(&mys1);
		return;
	}

	mys_close(&mys1);
	return;
}

void mysql_check_sched()
{
	int i;
	int mail_id;
	int msg_id;
	int retry;
	int ret;
	int max_threads, CT, RT, MSC, MW_SR, MW_MR, aol_rotate, max_per_ip;
	double MW, MW_S, MW_M;

	mys_open(&mys1);

	/* check for new starts */ 
	snprintf(mys1.SqlBuf, MAX_SQL_BUFF, "SELECT `s`.`id`, `s`.`msg_id`, `s`.`max_threads`, `s`.`retries`, `m`.`yahoo_date`, `m`.`aol_rotate`, `m`.`max_per_ip` \
										FROM `schedule` `s`, `msg` `m` WHERE `m`.`id` = `s`.`msg_id` AND `scheduled_time` < NOW() AND `s`.`state` = '3' AND s.`id` = %d", rba->id_only);
	if (mysql_query(&mys1.mysql_conn,mys1.SqlBuf)) {
		printf("celiberod mysql error 0: %s %s\n", 
			mysql_error(&mys1.mysql_conn), mys1.SqlBuf);
		mys_close(&mys1);
		return;
	}
	mys1.mysql_res = mysql_store_result(&mys1.mysql_conn);

	while ((mys1.mysql_row = mysql_fetch_row(mys1.mysql_res)))
	{
		/*retry = atoi(mys1.mysql_row[2]);*/
		retry			= atoi(mys1.mysql_row[3]);
		mail_id			= atoi(mys1.mysql_row[0]);
		msg_id			= atoi(mys1.mysql_row[1]);
		max_threads		= atoi(mys1.mysql_row[2]);
		aol_rotate 		= atoi(mys1.mysql_row[5]);
		max_per_ip 		= atoi(mys1.mysql_row[6]);

		printf("start mailing\n"); fflush(stdout);
		pthread_mutex_lock(&rba->TheMqaMutex);
		ret = rba_start_mailing( mys1.mysql_row[0], mys1.mysql_row[0], retry, 0, 0, 0, 0, mail_id, msg_id, max_threads, 1, mys1.mysql_row[4], aol_rotate, max_per_ip);
			
	 printf("Updating the start time on table\n");
	 if(ret < 0)
	 {
				printf("Mailing failed to start\n");
	 }
	 else
	 {
			 snprintf(mys1.SqlBuf, MAX_SQL_BUFF, "UPDATE schedule SET `start_time` = NOW(), state = '4' WHERE id = '%i'",mail_id);
			 if (mysql_query(&mys1.mysql_conn,mys1.SqlBuf))
			 {
				 printf("celiberod mysql error updating start time: %s %s\n", 
				 mysql_error(&mys1.mysql_conn), mys1.SqlBuf);
				 mys_close(&mys1);
				 return;
			 }
	}
				 
			
		pthread_mutex_unlock(&rba->TheMqaMutex);
	}
	mysql_free_result(mys1.mysql_res);

	/* check cancel lines */
	snprintf(mys1.SqlBuf, MAX_SQL_BUFF, "SELECT id FROM schedule WHERE state = '8' ");
	if (mysql_query(&mys1.mysql_conn,mys1.SqlBuf)) {
		printf("celiberod mysql error 1: %s %s\n", 
			mysql_error(&mys1.mysql_conn), mys1.SqlBuf);
		mys_close(&mys1);
		return;
	}
	mys1.mysql_res = mysql_store_result(&mys1.mysql_conn);

	while ((mys1.mysql_row = mysql_fetch_row(mys1.mysql_res))) {
		mail_id = atoi(mys1.mysql_row[0]);

		snprintf(mys2.SqlBuf, MAX_SQL_BUFF, 
			"UPDATE schedule set state='9' WHERE id=%d ",mail_id);

		mys_open(&mys2);
		if (mysql_query(&mys2.mysql_conn,mys2.SqlBuf))
		{
			printf("celiberod mysql error 2: %s %s\n", 
			mysql_error(&mys2.mysql_conn), mys2.SqlBuf);
			mys_close(&mys2);
			continue;
		}
		mys2.mysql_res = mysql_store_result(&mys2.mysql_conn);
		mysql_free_result(mys2.mysql_res);

		for(i=0;i<MAX_MAILINGS;++i){
			if ( rba->mqa[i]->mail_id == mail_id ) {
				mqa_cancel( i );
			}
		}
	}
	mysql_free_result(mys1.mysql_res);

	/* check settings */
	snprintf(mys1.SqlBuf, MAX_SQL_BUFF, "SELECT `KEY`, `value` FROM config WHERE `KEY` = 'ENGINE_CT' OR `KEY` = 'ENGINE_RT' OR `KEY` = 'ENGINE_DNS' OR `KEY` = 'ENGINE_MSC' OR `KEY` = 'ENGINE_MAILING_WAIT'");
	if (mysql_query(&mys1.mysql_conn,mys1.SqlBuf)) {
		printf("celiberod mysql error 3: %s %s\n", 
			mysql_error(&mys1.mysql_conn), mys1.SqlBuf);
		mys_close(&mys1);
		return;
	}
	mys1.mysql_res = mysql_store_result(&mys1.mysql_conn);
	
	while ((mys1.mysql_row = mysql_fetch_row(mys1.mysql_res)))
	{
		if(strcmp(mys1.mysql_row[0], "ENGINE_CT") == 0) {
				CT = atoi(mys1.mysql_row[1]);
				if(CT > 0 && CT != IConnectTimeout) {
					IConnectTimeout = CT;
					printf("engine: new setting [ConnectTimeout] = [%d]\n", IConnectTimeout);
				}
		} else if(strcmp(mys1.mysql_row[0], "ENGINE_RT") == 0) {
				RT = atoi(mys1.mysql_row[1]);
				if(RT > 0 && RT != IRWTimeout) {
					IRWTimeout = RT;
					printf("engine: new setting [ReadTimeout] = [%d]\n", IRWTimeout);
				}
		} else if(strcmp(mys1.mysql_row[0], "ENGINE_DNS") == 0) {
				if(strcmp(CelDNS, mys1.mysql_row[1]) != 0) {
						strncpy(CelDNS, mys1.mysql_row[1], CONF_BUFF);
						printf("engine: new setting [DNS] = [%s]\n", CelDNS);
				}
		} else if(strcmp(mys1.mysql_row[0], "ENGINE_MSC") == 0) {
				MSC = atoi(mys1.mysql_row[1]);
				if(MSC > 0 && MSC != MaxSendsPerConn) {
					//MaxSendsPerConn = MSC;
					printf("engine: new setting [MaxSendsPerConn] = [%d]\n", MaxSendsPerConn);
				}
		}
		else if (strcmp(mys1.mysql_row[0], "ENGINE_MAILING_WAIT") == 0)
		{
			MW 		= atof(mys1.mysql_row[1]);
			MW_M 	= modf(MW, &MW_S);
			MW_SR 	= MW_S;
			MW_MR 	= MW_M*1000000;
			if (MW_SR != MailingWait)
			{
				MailingWait = MW_SR;
				printf("engine: new setting [MailingWait] = [%d]\n", MailingWait);
			}

			if (MW_MR != MailingWaitMicro)
			{
				MailingWaitMicro = MW_MR;
				printf("engine: new setting [MailingWaitMicro] = [%d]\n", MailingWaitMicro);
			}
		}
	}
	mysql_free_result(mys1.mysql_res);
}

void mysql_init_sched()
{
 int mail_id;
 int retry, retry_level, retries, aol_rotate, max_per_ip;
 int success;
 int failure;
 int deferral;
 int msg_id;
 int max_threads;

	 mys_open(&mys3);

	 snprintf(mys3.SqlBuf, MAX_SQL_BUFF, "SELECT `s`.`success`, `s`.`failure`, `s`.`deferral`, `s`.`id`, `s`.`msg_id`, \
											`s`.`max_threads`, `s`.`retry_level`, `s`.`retries`, `m`.`yahoo_date`, `m`.`aol_rotate`, `m`.`max_per_ip` \
										  FROM `schedule` `s`, `msg` `m` WHERE `s`.`msg_id` = `m`.`id` AND `scheduled_time` < NOW() AND `s`.`state` = '4' AND s.`id` = %d", rba->id_only);

	 /*printf("SqlBuf=%s\n", SqlBuf);*/

	 if (mysql_query(&mys3.mysql_conn,mys3.SqlBuf)) {
			 printf("mysql error 3: %s %s\n", 
			 mys3.SqlBuf, mysql_error(&mys3.mysql_conn));
			 mys_close(&mys3);
			 return;
	}
	
	mys3.mysql_res = mysql_use_result(&mys3.mysql_conn);

	while ((mys3.mysql_row = mysql_fetch_row(mys3.mysql_res)))
	{
		printf("init sched start %s %s %s %s\n",
			mys3.mysql_row[0], mys3.mysql_row[1], mys3.mysql_row[2], mys3.mysql_row[3]);

		success 		= atoi(mys3.mysql_row[0]);
		failure		 	= atoi(mys3.mysql_row[1]);
		deferral		= atoi(mys3.mysql_row[2]);
		msg_id			= atoi(mys3.mysql_row[4]);
		max_threads 	= atoi(mys3.mysql_row[5]);
		retry_level 	= atoi(mys3.mysql_row[6]);
		retries		 	= atoi(mys3.mysql_row[7]);
		aol_rotate		= atoi(mys3.mysql_row[9]);
		max_per_ip		= atoi(mys3.mysql_row[10]);
		retry 			= retry_level;
		
		mail_id	= atoi(mys3.mysql_row[3]);
		pthread_mutex_lock(&rba->TheMqaMutex);
	
		rba_start_mailing( mys3.mysql_row[3], mys3.mysql_row[3], retry, 
			success, failure, deferral, 0, mail_id, msg_id, max_threads, 1, mys3.mysql_row[8], aol_rotate, max_per_ip);

		pthread_mutex_unlock(&rba->TheMqaMutex);
		sleep(10);
	}

	mysql_free_result(mys3.mysql_res);

	snprintf(mys3.SqlBuf, MAX_SQL_BUFF, "UPDATE schedule SET state = '9' WHERE `state` = '8'");

	if (mysql_query(&mys3.mysql_conn,mys3.SqlBuf)) {
			printf("mysql error 3: %s %s\n", mys3.SqlBuf, mysql_error(&mys3.mysql_conn));
			mys_close(&mys3);
			return;
	}
	
	snprintf(mys3.SqlBuf, MAX_SQL_BUFF, "UPDATE schedule SET state = '7' WHERE `state` = '5'");

	if (mysql_query(&mys3.mysql_conn,mys3.SqlBuf)) {
			printf("mysql error 3: %s %s\n", mys3.SqlBuf, mysql_error(&mys3.mysql_conn));
			mys_close(&mys3);
			return;
	}

	mys_close(&mys3);

}
