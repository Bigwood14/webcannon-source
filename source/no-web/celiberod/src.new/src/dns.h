#define DNS_VERSION "1.0b"

/*
   Time to wait for a response between
   query retries.
*/
#define REPLY_TIMEOUT 3

/*
   Maximum size for a query buffer
*/
#define QUERY_SIZE 512
#define MALLOC_QUERY_SIZE 1024
#define RES_SIZE 2048
#define MALLOC_HOST_SIZE 1024
#define HOST_SIZE 255

/*
   Query types
*/
#define T_NONE  0
#ifndef T_A
#define T_A     1
#endif

#ifndef T_MX
*#define T_MX   15
#endif

#ifndef T_CNAME
*#define T_CNAME   5
#endif

/*
   Flags
*/
#define F_NONE 0

/*
   Default retry attempts for DNS response
*/
#define DEFAULT_RETRIES 3

struct answer_t {
  void *answer;
  short data;
};

struct dns_t {
  struct timeval tv;
  fd_set rfds;

  char type,               /* Query type     */
       flags,              /* Query flags    */
       retries,            /* Retries        */
       tries,              /* Tries          */
       ns_idx,             /* NS index       */
       *query,             /* Query string   */
       *res;               /* Query response */
  unsigned char b[MALLOC_HOST_SIZE];

  struct answer_t **answers; /* Answer array   */
  
  int s,                   /* Socket         */
      id,                  /* DNS ID         */
      res_size,            /* Response size  */
      ans_cnt,             /* Answer count   */
      used;                /* did we use this one yet? */

  FILE *stream;            /* DNS NS fd      */
  struct sockaddr_in addr; /* NS Address     */
};

struct dns_t *dns_init(char);
int dns_kill(struct dns_t *);
int dns_reset(struct dns_t *);
int dns_query(struct dns_t *, char, char, char *);
int dns_loop(struct dns_t *);
int dns_send(struct dns_t *);
int dns_ns_lookup(struct dns_t *);
int dns_listen(struct dns_t *);
int dns_parse(struct dns_t *);
void dns_malloc(struct dns_t *dns);
