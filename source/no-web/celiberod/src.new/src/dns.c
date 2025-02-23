#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/time.h>
#include <sys/param.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <sys/select.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <arpa/nameser.h>
#include <resolv.h>
#include <errno.h>
#include "dns.h"
#include "celcommon.h"
#include "celutil.h"

struct dns_t *dns_init(char flags)
{
  struct dns_t *d = NULL;
 
  d = (struct dns_t *)malloc(sizeof(struct dns_t));
  if (d == NULL)
     return NULL;
  
  memset((struct dns_t *)d, 0, sizeof(struct dns_t));
  d->retries = 0;
  d->tries   = 0;
  d->flags   = flags;
  d->type    = T_NONE;
  d->s       = -1;

  return d;
}

int dns_reset(struct dns_t *dns)
{
/*
  if (dns->stream)
     fclose(dns->stream);

  if (dns->query)
     free(dns->query);

  if (dns->res)
     free(dns->res);

  if (dns->s != -1)
     close(dns->s);

  memset((struct dns_t *)dns, 0, sizeof(struct dns_t));
*/
  if (dns->s != -1)
     close(dns->s);
  dns->s = -1;
  dns->retries = 0;
  dns->tries   = 0;
  dns->flags   = F_NONE;
  dns->type    = T_NONE;

  return(0);
}

int dns_kill(struct dns_t *dns)
{
/*
  if (dns->answers)
     free(dns->answers);
*/

  if (dns->stream)
     fclose(dns->stream);

  if (dns->query)
     free(dns->query);

  if (dns->res)
     free(dns->res);

  if (dns->s != -1)
     close(dns->s);

  free(dns);
  return 1;
}

int dns_query(struct dns_t *dns, char type, char retries, char *data)
{
  HEADER *hdr = NULL;
  int ret = 0, len = 0;

  dns->type = type;
  dns->ans_cnt = 0;
  if (retries) dns->retries = retries;
  else dns->retries = DEFAULT_RETRIES;

  len = strlen(data);

  /*
   * Allocate space for the query
   */
  if ( dns->query == NULL ) {
      dns->query = (char *)malloc(MALLOC_QUERY_SIZE);
      if (dns->query == NULL) return 0;
  }

  memset((char *)dns->query, 0, MALLOC_QUERY_SIZE);

  /*
   * Form the query
   */
  ret = res_mkquery(QUERY, (const char *)data, C_IN, dns->type, NULL, 0,
                      NULL, dns->query, QUERY_SIZE);
  if (ret == -1) return 0;

  /*
   * Fetch some information from the newly created query string
   */
  hdr = (HEADER *)dns->query;
  
  dns->id = ntohs(hdr->id);

  ret = dns_loop(dns);
  return ret;
}

int dns_loop(struct dns_t *dns)
{
  int ret = 0;

  while(1) {
    ret = dns_send(dns);
    if (!ret)
       return 0;    

    ret = dns_listen(dns);
    if (ret) {
       ret = dns_parse(dns);
       if (ret == 1)
          break;

       if (ret == -1)
          return 0;
    }
  }

  return 1;
}

int dns_send(struct dns_t *dns)
{
  int ret = 0;

  /*
  if (dns->stream != NULL) rewind(dns->stream); 
  */

  if ( dns->tries == dns->retries ) {
    /*printf("max dns retries reached, give up\n");*/
    return(0);
  }

  if (dns->tries == 0) {
     if ( dns->s == -1 ) dns->s = socket(AF_INET, SOCK_DGRAM, 0);
     if (dns->s == -1) return 0;
  
     ret = dns_ns_lookup(dns);
     if (!ret) return 0;

     dns->tries = 0;
  }

  ret = sendto(dns->s, (char *)dns->query, (size_t)QUERY_SIZE, 0, (struct sockaddr *)&dns->addr, sizeof(struct sockaddr_in));
  if (ret < QUERY_SIZE) {
     /*printf("sendto returned less %d\n", ret);*/
     return 0;
  } if ( ret > QUERY_SIZE ) {
     /*printf("sendto returned greater %d\n", ret);*/
     return 0;
  }

  dns->tries++;
  return 1;
}

int dns_ns_lookup(struct dns_t *dns)
{
  dns->addr.sin_family      = AF_INET;
  dns->addr.sin_port        = htons(53);
  dns->addr.sin_addr.s_addr = inet_addr(CelDNS);

  return 1;
}

int dns_listen(struct dns_t *dns)
{  
 int ret = 0;

  if (dns->res == NULL) {
     dns->res = (char *)malloc(RES_SIZE);
     if (dns->res == NULL)
        return 0;
  }
  memset((char *)dns->res, 0, RES_SIZE);

  FD_ZERO(&dns->rfds);
  FD_SET(dns->s, &dns->rfds);

  dns->tv.tv_sec =  REPLY_TIMEOUT;
  dns->tv.tv_usec = 0;
    
  ret = select((dns->s + 1), &dns->rfds, NULL, NULL, &dns->tv);
  if (ret == -11) return 0;  

  if (FD_ISSET(dns->s,&dns->rfds)) {
    ret = read(dns->s, dns->res, RES_SIZE);
    if (ret <= 0) return 0;
    dns->res_size = ret;
    return 1;
  }

  return(0);
}

int dns_parse(struct dns_t *dns)
{
  short sdata = 0;
  HEADER *hdr = NULL;
  unsigned short type = 0, len = 0;
  int ans = 0, qs = 0, ret = 0, i = 0;
  unsigned char *tail = NULL, *p = NULL;
  
  if (dns->res == NULL)
     return 0;

  i = 0;
  hdr = (HEADER *)dns->res;
  tail = dns->res + dns->res_size;
  p    = (dns->res + HFIXEDSZ);
  memset(dns->b, 0, MALLOC_HOST_SIZE);

  /*
     Check for our DNS ID
  */
  if (dns->id != ntohs(hdr->id))
     return 0;

  /*
     Make sure the query was a success
  */
  if (ntohs(hdr->rcode) != NOERROR)
     return -1;

  /*
     Fetch number of answers, and a little
     sanity check.
  */
  ans = ntohs(hdr->ancount);
  if (!ans)
     return -1;
    
  /*
     Skip our original query question
  */
  qs = ntohs(hdr->qdcount);
  while(qs--)
    p += (dn_skipname(p, tail) + QFIXEDSZ);
  
  dns_malloc(dns);

  i = 0;
  while((ans-- > 0) && (p < tail)) {
    ret = dn_expand(dns->res, tail, p, dns->b, (HOST_SIZE - 1));

    p += ret;

    if (ret < 1) {
       if (dns->ans_cnt)
          break;

       return 0;
    }
 
    memcpy((char *)&type, (char *)p, sizeof(short));
    type = ntohs(type);

    p += sizeof(short);
    p += sizeof(short);
    p += sizeof(long);

    memcpy((char *)&len, (char *)p, sizeof(short));
    len = ntohs(len);

    p += sizeof(short);

    if (type == dns->type) {
       switch(type) {
	  case T_A:
             /*dns->answers[i] = (struct answer_t *)malloc(sizeof(struct answer_t));*/
             if (dns->answers[i]) {
                /*dns->answers[i]->answer = (struct in_addr *)malloc(sizeof(struct in_addr));*/
                if (dns->answers[i]->answer) {
                   memset((char *)dns->answers[i]->answer, 0, sizeof(struct in_addr));
                   memcpy((char *)dns->answers[i]->answer, (char *)p, sizeof(struct in_addr));

                   i++;
                   dns->ans_cnt++;
                }
		/*
                else
                   free(dns->answers[i]->answer);
		   */
             }
             
             p += len;
             break;

          case T_MX:
             memcpy((char *)&sdata, (char *)p, sizeof(short));
             p += sizeof(short);

             ret = dn_expand(dns->res, tail, p, dns->b, (HOST_SIZE - 1));
             if (ret < 0)
                break;

             p += ret;

	     /* KLJ HACK */
             *(dns->b + HOST_SIZE-1) = '\0';
  
             len = strlen(dns->b);

             /*dns->answers[i] = (struct answer_t *)malloc(sizeof(struct answer_t));*/
             if (dns->answers[i]) {
                /*dns->answers[i]->answer = (char *)malloc(len + 1);*/
                if (dns->answers[i]->answer) {
                   dns->answers[i]->data   = ntohs(sdata);

                   memset((char *)dns->answers[i]->answer, 0, (len + 1));
                   memcpy((char *)dns->answers[i]->answer, (char *)dns->b, len);

                   i++;
                   dns->ans_cnt++;
                }
		/*
                else
                   free(dns->answers[i]->answer);
		   */
             }

             break;    
            
          case T_CNAME:
             
             memcpy((char *)&sdata, (char *)p, sizeof(short));
             //p += sizeof(short);

             ret = dn_expand(dns->res, tail, p, dns->b, (HOST_SIZE - 1));
             if (ret < 0) {
                break;
             }
             p += ret;

	     /* KLJ HACK */
             *(dns->b + HOST_SIZE-1) = '\0';
  
             len = strlen(dns->b);

             /*dns->answers[i] = (struct answer_t *)malloc(sizeof(struct answer_t));*/
             if (dns->answers[i]) {
                /*dns->answers[i]->answer = (char *)malloc(len + 1);*/
                if (dns->answers[i]->answer) {
                   dns->answers[i]->data   = ntohs(sdata);

                   memset((char *)dns->answers[i]->answer, 0, (len + 1));
                   memcpy((char *)dns->answers[i]->answer, (char *)dns->b, len);

                   i++;
                   dns->ans_cnt++;
                }
		/*
                else
                   free(dns->answers[i]->answer);
		   */
             }

             break;

          default:
             break;
       };
    }
  }

  if (dns->ans_cnt) {
     /*dns->answers[i] = NULL;*/
     return 1;
  }

  return 0;
}

void dns_malloc(struct dns_t *dns)
{
 int i;

  /*
     Allocate array for answers
  */  
  if ( dns->answers == NULL ) {
      dns->answers = (struct answer_t **)malloc((sizeof(void *) * 100));
      if (dns->answers == NULL) return;  
      for(i=0;i<50;++i){
         dns->answers[i] = (struct answer_t *)malloc(sizeof(struct answer_t));
         dns->answers[i]->answer = (struct in_addr *)malloc(1000);
      }
  }
  for(i=0;i<50;++i){
      memset(dns->answers[i]->answer, 0, 1000);
  }
}
