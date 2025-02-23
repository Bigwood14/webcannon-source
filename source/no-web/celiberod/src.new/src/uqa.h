#define UNIX_STATUS_SIZE 3000

typedef struct uqa_struct {
  pthread_mutex_t UqaMutex;
  pthread_cond_t   UqaCond;
  int    thesock;
  int    in_use;
  int    keep_going;
  char   readbuf[TMPBUF_SIZE];
  char   tokbuf[TMPBUF_SIZE];
  char   status[UNIX_STATUS_SIZE];
  char   tmpbuf[TMPBUF_SIZE];
  int    i;
  int    ret;
  time_t mytime;
  char  *tmpstr;
  char  *tofile;
  char  *bodyfile;
  char  *value;
} uqa_struct;

void uqa_init();

