#define RBS_NO_STATUS 0
#define RBS_SUCCESS   1
#define RBS_FAILURE   2
#define RBS_DEFERRAL  3
#define RBS_RUNNING   4

#define RB_NO_STATE           0
#define RB_RUNNING            1
#define RB_WAITING_TO_FINISH  2
#define RB_DONE               3

typedef struct rba_struct {
	int CurrentIP;
	int CurrentFrom;
	int id_only;
	int threads;
	int sleep;
	long unsigned CurMessId;
	int CurrentXMailer;
	char ConfigFile[MAX_BUFF];
	char SockFile[MAX_BUFF];
	int	MaxThreads;
	pthread_mutex_t TheMutex;
	int TheUnixSocket;
	mqa_struct *mqa[MAX_MAILINGS];
	char hostname[MAX_BUFF];
	char helo_string[MAX_BUFF];
	pid_t mypid;
	int	next_mqa;
	FILE *fs_log;
	FILE *fs_report;
	pthread_mutex_t TheMqaMutex;
} rba_struct;

int rba_all_done( rba_struct *rba );
int rba_start_mailing(char *tofile,char *bodyfile,int retry_count,
		      int success, int failure, int deferral, 
                      int pause, int mail_id, int msg_id, int max_threads, int first_time, char *yahoo_date, int aol_rotate, int max_per_ip);
