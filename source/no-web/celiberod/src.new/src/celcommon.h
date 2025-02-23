#ifndef CELCOMMON_H
#define CELCOMMON_H 1

#define EMAIL_SIZE 40

#define TV_WAIT_TIME 60 


#define CELSMTPD_PATH   "/tmp/celsmtpd.sock"
#define CELCMD_PATH   "/tmp/celcmd.sock"
#define CELMAILD_PATH   "/tmp/celiberod.sock"
#define CELMAILD_PORT 6565

#define CEL_LOCAL "127.0.0.1"

#define MAX_THREADS 1000 
#define MAX_MAILINGS 5
#define SMTP_THREADS 50
#define CACHE_THREADS 50

#define MALLOC_TMPBUF_SIZE 2000
#define TMPBUF_SIZE MALLOC_TMPBUF_SIZE - 1 

#define USER_DOMAIN_SIZE 200

#define MAX_BUFF 1000
#define MAX_BIG_BUFF 4000

#define CONNECT_TIMEOUT 30
#define RW_TIMEOUT 30

#define RBA_FAILURE 'f'
#define RBA_SUCCESS 's'
#define CEL_UNSUB   'u'
#define RBA_NEW     'n'
#define RBA_DEFER   'd'

#endif
