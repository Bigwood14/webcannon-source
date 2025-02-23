#include <stdlib.h>
#include <stdio.h>
#include <unistd.h>
#include <string.h>
#include "config.h"
#include "celcommon.h"
#include "celacl.h"

static char TmpBuf[MAX_BUFF];
static char TmpBuf_r[MAX_BUFF];

#define TOKENS " ,\n\r\t"

/* 
 *   update MAX_IPS to be number of IPs in the acl_host string
 */
char  **AclIps;
int MaxACLIps;

int check_acl_host( char *host_ip )
{
 int i;

  if ( strncmp( host_ip, "127.", 4 ) == 0 ) {
    return(0);
  }
  else if ( strncmp( host_ip, "0.0.0.0", 7 ) == 0 ) {
    return(0);
  } else {
    for(i=0;i<MaxACLIps;++i) {
      if ( strncmp( host_ip, AclIps[i], strlen(AclIps[i]) ) == 0 ) {
        return(0);
      }
    }
  }
  return(-1);
}

int init_acl_host()
{
 FILE *fs;
 int count;
 char *tmpstr;

  snprintf(TmpBuf, MAX_BUFF, "%s/etc/acl", CELIBERODIR);
  if ( (fs = fopen(TmpBuf, "r")) == NULL ) return(-1);

  count=0;
  while( fgets(TmpBuf, MAX_BUFF, fs)!=NULL) {
    if ( strtok_r(TmpBuf, TOKENS, (char **)&TmpBuf_r) != NULL ) ++count;
  }
  rewind(fs);
  MaxACLIps = count;

  AclIps = malloc(sizeof(char *)*count);

  count = 0;
  while( fgets(TmpBuf, MAX_BUFF, fs)!=NULL) {
    if ( (tmpstr=strtok_r(TmpBuf, TOKENS, (char **)&TmpBuf_r)) != NULL ) {
      AclIps[count] = malloc(strlen(tmpstr));
      strncpy( AclIps[count], tmpstr, strlen(tmpstr));
      ++count;
    }
  }
  fclose(fs);
  return(0);
}
