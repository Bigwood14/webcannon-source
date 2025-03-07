void get_options(int argc, char **argv);
void usage();
void handle_inet_client(int s);
void read_config();
void smtp_code(vqr_struct *vqr);
void get_date_header(vqr_struct *vqr);
int get_mail_ip(vqr_struct *vqr); 
void init_startup();
int start_mailing( char *tofile, char *bodyfile, int retry_count );
int parse_vars( vqr_struct *vqr );
int rba_all_done( rba_struct *rba );
void mysql_update_schedule (int mail_id, int state);
