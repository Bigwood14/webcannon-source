#define MAX_VARS 30
#define MAX_VARS_LEN 100

#define MAX_VARS_LINE MAX_VARS*MAX_VARS_LEN

#include <domainkeys.h>

struct vqr_dk
{
	unsigned char 		dk_sig[2048];
	size_t 			dk_sig_len;	
	DK_LIB 			*dk_lib;
	DK				*dk_dk;
	DK_STAT 		dk_stat;
	DK_FLAGS	 	dk_flags; // intialize
};

enum vqr_domain_type
{
	NONE		= 0,
	RR 			= 1,
	HOTMAIL		= 2,
	YAHOO		= 3,
	ATT			= 4,
	AOL			= 5,
	NETZERO 	= 6,
	VERIZON		= 7,
	CHARTER		= 8,
	COMCAST		= 9,
	BIGFOOT 	= 10,
	GREYLIST	= 11
};

typedef	struct	vqr_struct	{
	struct vqr_dk dk;
	mqa_struct	*mqa;
	rba_struct	*rba;
	int is_grey;
	int	mqaidx;
	int	vqridx;
	int	mess_size;
	int	bytes_sent;
	int	malloc_mess_size;
	int	malloc_mess_max;
	int	bodymem_size;
	int is_yahoo;
	int is_aol;
	int mta;
	int sends;
	char	*bodymem;
	char	*mess;
	char	*mess_tmp;
	char	tmpbuf[MALLOC_TMPBUF_SIZE];
	char	smtpbuf[MALLOC_TMPBUF_SIZE];
	char	from_addr[MALLOC_TMPBUF_SIZE];
	char	from_addr_tmp[MALLOC_TMPBUF_SIZE];
	char	date_str[MALLOC_TMPBUF_SIZE];
	char	to_addr[MALLOC_TMPBUF_SIZE];
	char	user[USER_DOMAIN_SIZE];
	char	domain[USER_DOMAIN_SIZE];
	char	status_line[MALLOC_TMPBUF_SIZE];
	char	mailid_buf[MAX_VARS_LEN];
	char	From[MAX_BUFF];
	char	*tmpstr;
	char	low[2];
	/*char	*tmpstr1;*/
	char	*outbuf;
	char	mail_host[MALLOC_TMPBUF_SIZE];
	char	mail_domain[MALLOC_TMPBUF_SIZE];
	char	mail_domain_prior[MALLOC_TMPBUF_SIZE];
	enum	vqr_domain_type domain_type;
	int		sock;
	int	yahoo_tries;
	int	skip_2;
	struct	sockaddr_in	sin;
	struct	in_addr	*myin;
	struct	hostent	*hostEntry;
	int	return_code;
	int	hacked_return_code;
	fd_set	wfds;
	struct	timeval	tv;
	int	have_status;
	int	prior_status;
	int	real_status;
	int	tmp_i;
	int	tmp_j;
	int	tmp_k;
	int	write_size;
	int	write_ret;
	int	read_ret;
	struct	timeval	start_timeval;
	struct	timeval	end_timeval;
	time_t	start_time;
	time_t	end_time;
	struct	tm	mytm;
	struct	dns_t	*dns;
	struct	dns_t	*dns1;
	char	dnstmp[TMPBUF_SIZE];
	int	ret;
	int	OurIP;
	int	PrevIP;
	int	OurFrom;
	int	PrevFrom;
	int	OurXMailer;
	FILE	*pipe_inject;
	int		MaxVars;
	char	VarsLine[MAX_VARS_LINE];
	char	Vars[MAX_VARS][MAX_VARS_LEN];
	int		VarsLen[MAX_VARS];
	int	connecterr;
	int	cur_sends_per_conn;
	int our_rotation[5];
	int aol_reject;
	int force_new_connect;
} vqr_struct;

void vqr_loop(vqr_struct *vqr);
void vqr_clean_return(vqr_struct *vqr );
int wait_read(vqr_struct *vqr);
int wait_write( vqr_struct *vqr, char *write_buf, int write_size );
void vqr_clean_return(vqr_struct *vqr );
void vqr_cleanup(vqr_struct *vqr);
void smtp_code(vqr_struct *vqr);
int get_addr_vqr(vqr_struct *vqr, int check_from );
void get_date_header( vqr_struct *vqr );
int get_mail_ip( vqr_struct *vqr );
int parse_vars( vqr_struct *vqr );
void vqr_clean_return(vqr_struct *vqr );
int vqr_alloc_mem(vqr_struct *vqr);
int get_dc_ip( vqr_struct *vqr );
int vqr_connect_it( vqr_struct *vqr );
int fill_mail_ip( vqr_struct *vqr );
int get_host_ip( vqr_struct *vqr, char *answer );
int ken_inet_aton(char *cp, in_addr_t *addr);
void var_subs( vqr_struct *vqr, char *inmem, int insize, 
		                char *outmem, int outsize );
void print_status( vqr_struct *vqr, char *status, char *status_text );
inline void close_status(struct vqr_struct *vqr, char *status, char *mesg );
int in_mail_from_sites(struct vqr_struct *vqr);
void vars_do (vqr_struct *vqr, int first);
