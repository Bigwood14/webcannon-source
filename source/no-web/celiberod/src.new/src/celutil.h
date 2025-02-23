#ifndef CELUTIL_H
#define CELUTIL_H 1

#include "celcommon.h"

#define PS_COMMAND "ps axww"

void ignore_signals();
int open_unix_server(char *sock_path);
int open_inet_server(char *ip, int port);
int open_unix_client(char *sock_path);
int open_inet_client(char *ip, int port);

int cel_init();
int check_cpuid();
int check_dead_time();
inline char *get_cpuid();
int get_addr(char *instr, char *startstr, char *outstr, int outlen, int rot );
int fix_crlf(char *filename);
int strip_headers(char *filename, char *from_address, char *reply_to);
void read_config();
char *comma_string(char *instr);
void lowerit(char *instr);
char rot13(char c);
int cel_open(char *ip);
int cel_open_smtp();
int cel_close();
int cel_close_smtp();
char *cel_start_mailing( int sock, char *list, char *body, int retry );
int get_email_addrs( char *filename );
int signal_process( char *name, int sig_num);
void set_perms();
inline char *get_mac();
int is_domain_valid( char *domain );
int is_username_valid( char *user );
inline int parse_email( char *email, char *user, char *domain, int buff_size );

#define CONF_BUFF 100

extern char *MaxThreads;
extern char *SockFile;
extern char *SmtpSockFile;
extern char *HeloHost;
extern char *RWTimeout;
extern char *ConnectTimeout;
extern char *MysqlUser;
extern char *MysqlPasswd;
extern char *MysqlServer;
extern char *MysqlDatabase;
extern char *CloneIp;
extern char *CelDNS;
extern int IMaxThreads;
extern int IRWTimeout;
extern int IConnectTimeout;
extern int MailingWait;
extern int MailingWaitMicro;
extern int ServerID;
extern int SmtpThreads;
extern int CacheThreads;
extern int FtpConn;
extern int InetSocket;
extern int UnsubDeleteAll;
extern int DeleteOnBounce;
extern int MaxSendsPerConn;

extern char *DayStr[7];
extern char * MonStr[12];

typedef struct rip_host
{
	char 	ip[200];
	char 	host[200];
	int 	yahoo_cons;
	int 	removed;
} rip_host;

typedef struct from_lines {
	char local[200];
	char domain[200];
	char name[200];
}from_lines;


extern int MaxIPS;
extern rip_host **IPS;

extern int MaxFroms;
extern from_lines **FROMS;

#endif
