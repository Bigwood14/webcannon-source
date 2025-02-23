#define _GNU_SOURCE

#include <unistd.h>
#include <netinet/in.h>
#include <sys/socket.h>
#include <arpa/inet.h>
#include <sys/types.h>
#include <fcntl.h>
#include <signal.h>
#include <stdio.h>
#include <stdlib.h>
#include <stdbool.h>
#include <ctype.h>
#include <string.h>
#include <time.h>
#include <errno.h>
#include <ifaddrs.h>
#include <sys/wait.h>
#include <netinet/in.h>

int verbose 	= 0;
int print_log 	= 0;

void smtp_clean_buffer (char *buffer) 
{
	int i;

	for(i=0;i<4000;++i)
	{
		if (buffer[i] == '\r')
			buffer[i] = 0;

		if (buffer[i] == '\n')
			buffer[i] = 0;
	}
}

int smtp_parse_code (char *buffer, int from)
{
	int i, code;
	bool bad = false;

	
	//from++;
	i		= from;
	code 	= 0;

//printf("parse %s from %d char [%c%c%c]\n", buffer, from, buffer[from], buffer[from+1], buffer[from+2]);	
	
	while (buffer[i] != 0 )
	{
		for(i=i; i < 4000 && buffer[i]!=0 && isdigit(buffer[i]) != 0; ++i)
			code = code * 10 + (buffer[i] - '0');

		if (buffer[i] == '-' && print_log == true)
			bad = true;

		while(buffer[i] != 0 && buffer[i] != '\n' )
			++i;

		if (buffer[i] == '\n' && bad == true)
		{
			return smtp_parse_code(buffer, (i+2));
		}


		if (buffer[i] != 0)
		{
			++i;
			if (buffer[i] != 0 )
				code = 0;
		} 
	} 

	//smtp_clean_buffer(buffer);

	return code;
}

int socklib_set_non_block (int sock)
{
	int flags;

	flags = fcntl(sock, F_GETFL);

	if (flags < 0)
		return flags;

	flags |= O_NONBLOCK;

	if (fcntl(sock, F_SETFL, flags) < 0)
	{
		printf("Could not set non block\n");
		return -1;
	}

	return 0;
}

int socklib_setup_client (char *mx_ip, struct sockaddr_in *bind_to, int port)
{
	int 	sock, err;
	struct 	sockaddr_in conn;

	if ((sock = socket(AF_INET, SOCK_STREAM, 0)) == -1)
		return -1;

	// bind
	if ((err = bind(sock, (struct sockaddr_in *) bind_to, sizeof(struct sockaddr_in))) == -1)
	{
		printf("err %s\n", strerror(errno));
		return -2;
	}
	
	// set block
	if (socklib_set_non_block(sock) == -1)
		return -3;

	memset(&conn, 0, sizeof(struct sockaddr_in));  
	conn.sin_family = AF_INET;
	conn.sin_port 	= htons(port);
	
	conn.sin_addr.s_addr = inet_addr(mx_ip);

	if (connect(sock, (struct sockaddr *) &conn, sizeof(struct sockaddr_in)) == -1 )
	{
		if ( errno != EINPROGRESS && errno != EWOULDBLOCK )
		{
			return -4;
		}
	}

	return sock;
}

inline int wait_write (int sock, char *write_buf, int write_size ) 
{
	int 	ret;
	struct	timeval	tv;
	fd_set	wfds;

	tv.tv_sec 	= 60;
	tv.tv_usec 	= 0;

	FD_ZERO(&wfds);
	FD_SET(sock, &wfds);

	ret = 0;
	if (select(sock+1,(fd_set *)0, &wfds,(fd_set *)0,&tv) >= 1)
	{
		if (verbose == 1)
			printf("write: %s", write_buf);

		if (print_log == 1)
			printf("%s", write_buf);

		ret = write(sock, write_buf, write_size);
    	return ret;
  }

  return -1;
}

int wait_read (int sock, char *buffer, struct sockaddr_in *ip, int num)
{
	int 	ret, code;
	struct	timeval	tv;
	fd_set	wfds;


	tv.tv_sec 	= 60;
	tv.tv_usec 	= 0; 

	FD_ZERO(&wfds);
	FD_SET(sock,&wfds);

	ret 	= 0;
	
	memset(buffer, 0, 1000);

	if (select(sock+1, &wfds, (fd_set *) 0, (fd_set *)0, &tv) >= 1)
	{
		ret = read(sock, buffer, 4000);
		if (verbose == 1)
			printf("read: %s\n", buffer);

		if (print_log == 1)
			printf("%s", buffer);
//printf("ret: %d\n", ret);
		if (ret > 0)
		{
			code = smtp_parse_code(buffer, 0);
		//printf("code %d\n", code);	
			if (code == 0)
			{
				// more to read
				return wait_read(sock, buffer, ip, num);
			}
		
			if (code >= 400)
			{
				if (print_log == 0)
					printf("%s failed (%d) with %s\n", inet_ntoa(ip->sin_addr), num, buffer);

				snprintf(buffer, 1000, "QUIT\r\n");
				wait_write(sock, buffer, strlen(buffer));
				close(sock);
				return -1;
			}
		}
		else
		{
			printf("%s Error Read\n", inet_ntoa(ip->sin_addr));
			close(sock);
			return ret;
		}
	}
	else
	{
		printf("%s Timeout\n", inet_ntoa(ip->sin_addr));
		close(sock);
		return -1;
	}

	return code;
}

int do_ip (struct sockaddr_in *ip, char *mx_ip, char *domain, char *recipient)
{
	char 	buffer[4000];
	int 	sock = -1;

	if (verbose == 1)
		printf("IP: %s [%d]\n", inet_ntoa(ip->sin_addr), ip->sin_addr.s_addr);

	if (print_log == 1)
	{
		printf("telnet mailin-01.mx.aol.com 25\n");
		printf("Trying %s...\n", mx_ip);
		printf("Connected to mailin-01.mx.aol.com (%s).\n", mx_ip);
		printf("Escape character is '^]'.\n");
	}

	if ((sock = socklib_setup_client(mx_ip, ip, 25)) < 0)
	{
		printf("sock failed %d\n", sock);
		return -1;
	}
	printf("\n");
	if (wait_read(sock, buffer, ip, 1) < 1)
		return -1;

	snprintf(buffer, 4000, "HELO %s\r\n", domain);
	wait_write(sock, buffer, strlen(buffer));
	
	if (wait_read(sock, buffer, ip, 2) < 1)
		return -1;

	snprintf(buffer, 4000, "MAIL FROM: <user@%s>\r\n", domain);
	wait_write(sock, buffer, strlen(buffer));

	if (wait_read(sock, buffer, ip, 3) < 1)
		return -1;

	snprintf(buffer, 4000, "RCPT TO: <%s>\r\n", recipient);
	wait_write(sock, buffer, strlen(buffer));

	if (wait_read(sock, buffer, ip, 4) < 1)
		return -1;

	snprintf(buffer, 4000, "DATA\r\n");
	wait_write(sock, buffer, strlen(buffer));

	if (wait_read(sock, buffer, ip, 5) < 1)
		return -1;

	snprintf(buffer, 4000, "FROM: <you@%s>\r\n\r\nHello This is a telnet test.\r\n.\r\n", domain);
	wait_write(sock, buffer, strlen(buffer));

	if (wait_read(sock, buffer, ip, 6) < 1)
		return -1;
		
	printf("%s OK\n", inet_ntoa(ip->sin_addr));

	snprintf(buffer, 4000, "QUIT\r\n");
	wait_write(sock, buffer, strlen(buffer));

	if (wait_read(sock, buffer, ip, 7) < 1)
		return -1;

	close(sock);
	sock = -1;	
	
	return 0;
}

int main (int argc, char **argv)
{
	struct 	ifaddrs *ifaddr, *ifa;
	struct 	sockaddr_in *ip;
	char 	*use_domain = NULL, *use_ip = NULL, *use_mx = NULL, *use_recip = NULL, mx_ip[100], domain[200], recipient[200];
	int 	all_flag = 0, c;

	opterr = 0;
     
	while ((c = getopt (argc, argv, "ad:i:lm:r:v")) != -1)
	{
		switch (c)
		{
			case 'a':
				all_flag = 1;
				break;
			case 'd':
				use_domain = optarg;
				break;
			case 'i':
				use_ip = optarg;
				break;
			case 'l':
				print_log = 1;
				break;
			case 'm':
				use_mx = optarg;
				break;
			case 'r':
				use_recip = optarg;
				break;
			case 'v':
				verbose = 1;
				break;
		}
	}

	if (all_flag == 0 && use_ip == 0)
	{
		printf("Please supply the all ips flag -a or specify an ip address with -i\n");
		exit(EXIT_FAILURE);
	}

	// setup mx ip address
	memset(mx_ip, 0, 100);

	if (use_mx == NULL)
		snprintf(mx_ip, 100, "205.188.159.57");
	else
		snprintf(mx_ip, 100, "%s", use_mx);

	// setup domain
	memset(domain, 0, 200);

	if (use_domain == NULL)
		snprintf(domain, 200, "domain.com");
	else
		snprintf(domain, 200, "%s", use_domain);

	// setup recip
	memset(recipient, 0, 200);

	if (use_recip == NULL)
		snprintf(recipient, 200, "jonesmcjones09@aol.com");
	else
		snprintf(recipient, 200, "%s", use_recip);

	// switch between all IP on the interface all a specified one
	if (use_ip == NULL)
	{
		if (getifaddrs(&ifaddr) == -1)
		{
			perror("getifaddrs");
			exit(EXIT_FAILURE);
		}
	
		for (ifa = ifaddr; ifa != NULL; ifa = ifa->ifa_next)
		{
			if (ifa->ifa_addr->sa_family == AF_INET)
			{
				ip = (struct sockaddr_in *) ifa->ifa_addr;
				// we dont want localhost	
				if (ip->sin_addr.s_addr != 0x100007f)
				{
					do_ip(ip, mx_ip, domain, recipient); 
				}
			}
		}
	
		freeifaddrs(ifaddr);
	}
	else
	{
		ip 					= (struct sockaddr_in *) malloc(sizeof(struct sockaddr_in));
		memset(ip, 0, sizeof(struct sockaddr_in));  
		ip->sin_family 		= AF_INET;
		ip->sin_addr.s_addr = inet_addr(use_ip);

		do_ip(ip, mx_ip, domain, recipient);
	}

	exit(EXIT_SUCCESS);
}
