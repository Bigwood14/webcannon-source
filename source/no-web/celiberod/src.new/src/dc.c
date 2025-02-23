#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>
#include <fcntl.h>
#include <signal.h>
#include <pthread.h>
#include <ctype.h>
#include <errno.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <sys/time.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <sys/un.h>
#include <sys/stat.h>
#include <unistd.h>
#include <netdb.h>
#include <netinet/in.h>
#include <resolv.h>
#include "dns.h"
#include "celcommon.h"
#include "mqa.h"
#include "rba.h"
#include "vqr.h"
#include "celutil.h"
#include "custom.h"
#include "config.h"
#include "dc.h"

#define MAX_DOMAIN 156
#define MAX_MX_IPS 50
typedef struct dc_domain {
  char domain[MAX_DOMAIN];
  char host[MAX_DOMAIN];
  int  dist;
  int  max_ips;
  int  next_ip;
  struct in_addr ips[MAX_MX_IPS];
} dc_domain;

#define MAX_MYDOMAINS 12
dc_domain mydomains[MAX_MYDOMAINS];

struct dns_t *dns1;
struct dns_t *dns2;

int dc_init()
{
 int i;

 dns1   = dns_init(F_NONE);
 dns2   = dns_init(F_NONE);

 strncpy( mydomains[0].domain, "hotmail.com", MAX_DOMAIN);
 strncpy( mydomains[1].domain, "yahoo.com", MAX_DOMAIN);
 strncpy( mydomains[2].domain, "aol.com", MAX_DOMAIN);
 strncpy( mydomains[3].domain, "msn.com", MAX_DOMAIN);
 strncpy( mydomains[4].domain, "rediffmail.com", MAX_DOMAIN);
 strncpy( mydomains[5].domain, "yahoo.co.uk", MAX_DOMAIN);
 strncpy( mydomains[6].domain, "netscape.net", MAX_DOMAIN);
 strncpy( mydomains[8].domain, "earthlink.com", MAX_DOMAIN);
 strncpy( mydomains[9].domain, "lycos.com", MAX_DOMAIN);
 strncpy( mydomains[10].domain, "juno.com", MAX_DOMAIN);
 strncpy( mydomains[11].domain, "email.com", MAX_DOMAIN);

 for(i=0;i<MAX_MYDOMAINS;++i) {
   dc_count(i);
   mydomains[i].next_ip = 0; 
   dc_get(i);
 }
 return(0);
}

int dc_get( int l ) 
{
 int ret;
 int i;
 int j;
 int m;

  ret = dns_query(dns1, T_MX, 0, mydomains[l].domain ); 
  dns_reset(dns1); 

  for(m=0,i=0;i<dns1->ans_cnt;++i){
    if ( dns1->answers[i]->data == mydomains[l].dist )  {
      strncpy(&mydomains[l].host[0], dns1->answers[i]->answer, MAX_DOMAIN);
      /*
      printf("domain: %s host: %s\n", mydomains[l].domain, 
		      mydomains[l].host);
       */
      ret = dns_query(dns2, T_A, 0, (char *)dns1->answers[i]->answer);
      for(j=0;j<dns2->ans_cnt;++j) {
        memcpy(&mydomains[l].ips[m], dns2->answers[j]->answer, 
          sizeof(struct in_addr));
        /*printf("	ip: %s\n", inet_ntoa(mydomains[l].ips[m]));*/
        ++m;
      }
      dns_reset(dns2);
    }
  }
  mydomains[l].max_ips = m;
  return(0);
}

int dc_count( int l ) 
{
 int ret;
 int i;
 int j;
 int count;
 int dist = 100;

  dns1   = dns_init(F_NONE);
  dns2   = dns_init(F_NONE);
  ret = dns_query(dns1, T_MX, 0, mydomains[l].domain ); 
  dns_reset(dns1); 
  count = 0;

  for(i=0;i<dns1->ans_cnt;++i){
    if ( dns1->answers[i]->data < dist )  {
      dist = dns1->answers[i]->data;
    }
  }
  mydomains[l].dist = dist;

  for(i=0;i<dns1->ans_cnt;++i){
    if ( dns1->answers[i]->data == mydomains[l].dist )  {
      ret = dns_query(dns2, T_A, 0, (char *)dns1->answers[i]->answer);
      for(j=0;j<=dns2->ans_cnt;++j) {
        ++count;
      }
    }
    dns_reset(dns2);
  }
  mydomains[l].max_ips = count;
  return(count);
}

struct in_addr *dc_check( vqr_struct *vqr, char *domain )
{
 int i;
 int j;

  for(i=0;i<MAX_MYDOMAINS;++i) {
    if ( strcmp( domain, mydomains[i].domain ) == 0 ) {
      j = mydomains[i].next_ip;
      ++mydomains[i].next_ip;
      if ( mydomains[i].next_ip >= mydomains[i].max_ips ) {
        mydomains[i].next_ip = 0;
      }
      strncpy(vqr->mail_host, mydomains[i].host, MALLOC_TMPBUF_SIZE);
      /*strncpy(vqr->mail_domain, mydomains[i].domain, MALLOC_TMPBUF_SIZE);*/
      return(&mydomains[i].ips[j]);
    }
  }
  return(NULL);
}
