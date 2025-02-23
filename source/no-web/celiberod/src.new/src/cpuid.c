#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <unistd.h>
#include "celutil.h"
#include "config.h"

void get_options(int argc,char **argv);

int ShowCPUID;
int ShowMACID;

int main( int argc, char **argv ) 
{
  get_options(argc,argv);
  if ( ShowCPUID == 1 ) printf("%s\n", get_cpuid());
  if ( ShowMACID == 1 ) printf("%s\n", get_mac());
  return(0);
}

void get_options(int argc,char **argv)
{
 int c;
 int errflag;
 extern char *optarg;
 extern int optind;
  ShowCPUID = 0;
  ShowMACID = 0;
  errflag = 0;
  while( !errflag && (c=getopt(argc,argv,"vcma")) != -1 ) {
    switch(c) {
      case 'v':
        printf("version: 1\n");
        break;
      case 'c':
	ShowCPUID = 1;
        break;
      case 'm':
	ShowMACID = 1;
        break;
      case 'a':
	ShowMACID = 1;
	ShowCPUID = 1;
        break;
      default:
        errflag = 1;
        break;
    }
  }

  if ( errflag > 0 ) {
    printf("cpuid: -v (print version)\n");
    printf("       -c (print cpuid)\n");
    printf("       -m (print macid)\n");
  }

}
