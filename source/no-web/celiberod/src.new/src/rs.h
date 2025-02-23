int rs_init();
int rs_close();
int rs_add(char *tofile, char *bodyfile, time_t start_time);
int rs_delete( char *tofile, char *bodyfile, time_t start_time );
int rs_show(char *buff, int buff_len );
int rs_check();
void rs_thread();
