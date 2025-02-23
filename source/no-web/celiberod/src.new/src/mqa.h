#include "mysqlutil.h"

#define MQA_READY    0
#define MQA_RUNNING  1
#define MQA_PAUSED   2
#define MQA_VQR_DONE 3
#define MQA_CANCEL   4
#define MQA_DONE     5
#define MQA_HOLD     6

#define MQA_NEW_SUCCESS        0
#define MQA_NEW_MAX_MAILINGS  -1 
#define MQA_NEW_BAD_ADDR_FILE -2 
#define MQA_NEW_BAD_BODY_FILE -3 
#define MQA_NEW_BAD_STAT_FILE -4 

typedef struct mqa_rotated_content
{
	char 	name[255];
	int 	count;
	int 	position;
	int 	rotated_id;
	char 	data[10][1000];
} mqa_rotated_content;

typedef struct mqa_struct {
	int		run_state;
	int 	mqaidx;
	char 	DeferalRetryFile[MAX_BUFF];
	char 	BodyFile[MAX_BUFF];
	char 	BodyFileAOL[MAX_BUFF];
	char 	BodyFileYahoo[MAX_BUFF];
	char 	DK_KeyFile[MAX_BUFF];
	char 	AddrFile[MAX_BUFF];
	char 	tofile[MAX_BUFF];
	char 	bodyfile[MAX_BUFF];
	char 	tmpbuf[MAX_BUFF];
	char 	From[MAX_BUFF];
	char 	yahoo_date[MAX_BUFF];
	char 	dk_headers[MAX_BUFF];
	int		mail_id;
	int		msg_id;
	FILE 	*TheAddr;
	FILE 	*TheSuccess;
	FILE 	*TheFailure;
	FILE 	*TheDeferral;
	FILE 	*DeferralRetry;
	FILE 	*DK_Key;
	FILE 	*TheLookup;
	int	 	total_addrs;
	int	 	total_success;
	int	 	last_success;
	int	 	total_failure;
	int	 	total_deferral;
	int	 	retry_count;
	int	 	deferral_retry;
	int	 	bodymem_size;
	int	 	bodymem_size_aol;
	int	 	bodymem_size_yahoo;
	int	 	dk_keymem_size;
	int	 	bodymalloc_size;
	int	 	bodymalloc_size_aol;
	int	 	bodymalloc_size_yahoo;
	int	 	dk_keymalloc_size;
	int	 	max_vars;
	int	 	max_threads;
	int	 	first_time;
	int 	rotated_content_count;
	int 	aol_rotate;
	int 	aol_rotate_count;
	int 	aol_current_ip;
	int 	max_per_ip;
	int 	current_ip_sends;
	time_t 	start_time;
	char 	*bodymem;
	char 	*bodymem_aol;
	char 	*bodymem_yahoo;
	char 	*dk_keymem;
	struct 	mysql_struct mys;
	struct	timeval	tv;
	struct 	mqa_rotated_content rotated_content[5];
} mqa_struct;

int mqa_new_mailing( char *tofile, char *bodyfile, 
		     int retry_count, int success, int failure, int deferral, 
		     int pause, int mail_id, int msg_id, int max_threads,  int first_time, char *yahoo_date, int aol_rotate, int max_per_ip );

int mqa_next_mailing( void *vqr, void *mqa );
int mqa_close(void *mqa, int status );
int mqa_end(void *mqa);
int mqa_check_end();
int mqa_cancel( int mqaidx );
int mqa_pause( int mqaidx );
int mqa_resume( int mqaidx );
void mqa_shutdown();
void mqa_startup();
int mqa_check_one( mqa_struct *mqa );

