#include <stdio.h>
#include <stdlib.h>
#include <errno.h>
#include <string.h>
#include <pthread.h>

pthread_mutex_t mutex1 = PTHREAD_MUTEX_INITIALIZER;

void *print_message_function();

void main ()
{
	pthread_t threads[1000];
	pthread_attr_t tattr;
	size_t n;
	int i, ret;

	pthread_mutex_lock( &mutex1 );
	
	for (i=0;i<=320;i++)
	{
		ret = pthread_attr_init(&tattr);
	
		if (ret != 0)
			printf("attr init error\n");
	
		ret = pthread_attr_getstacksize(&tattr, &n);
	
		if (ret != 0)
			printf("set detach error\n");

		printf("size: %d\n", n);

		ret = pthread_attr_setstacksize(&tattr, 1048576);

		if(pthread_create( &threads[i], &tattr, print_message_function, NULL))
		{
			printf("pthread_create A failed: err=%d (%s) thread num=%d\n", errno, strerror(errno), i);
		}
		else
		{
			printf("good on %i\n", i);
		}
	}
}

void *print_message_function()
{
	pthread_mutex_lock( &mutex1 );
}

