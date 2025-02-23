@SET_MAKE@
CFLAGS = -D_FILE_OFFSET_BITS=64
celiberobindir=@celiberodir@/bin
celiberobin_PROGRAMS = celiberod bouncer celiberosmtpd cpuid

CELIBEROD_SOURCES		= dns.c vqr.c celutil.c mqa.c rs.c dc.c celacl.c rp.c
CELIBEROSMTPD_SOURCES	= celiberosmtpd.c celutil.c mysqlutil.c 
BOUNCER_SOURCES			= celutil.c
CPUID_SOURCES 			= celutil.c

celiberod_SOURCES  		= mysqlutil.c celiberod.c $(CELIBEROD_SOURCES)
celiberod_LDADD    		= @auth_libs@ -lpthread -lresolv -ldomainkeys -lcrypto
celiberod_LDFLAGS  		= @auth_flags@

bouncer_SOURCES 		= mysqlutil.c bouncer.c $(BOUNCER_SOURCES)
bouncer_LDADD			= @auth_libs@
bouncer_LDFLAGS			= 

cpuid_SOURCES 		= mysqlutil.c cpuid.c $(CPUID_SOURCES)
cpuid_LDADD			= @auth_libs@
cpuid_LDFLAGS		= 

celiberosmtpd_SOURCES 	= $(CELIBEROSMTPD_SOURCES)
celiberosmtpd_LDADD   	= @auth_libs@ -lpthread -lresolv
celiberosmtpd_LDFLAGS 	= 



dns.o: dns.h celcommon.h
mqa.o: celcommon.h celutil.h ../config.h mqa.h rba.h vqr.h
celiberod.o: ../config.h celcommon.h mqa.h rba.h vqr.h celiberod.h custom.h
celutil.o: ../config.h celcommon.h celutil.h custom.h
rs.o: rs.h ../config.h celcommon.h mqa.h
vqr.o: dns.h celcommon.h mqa.h rba.h vqr.h celutil.h custom.h ../config.h
celacl.o: celacl.h ../config.h celcommon.h
celiberosmtpd.o: ../config.h celutil.h
bouncer.o: ../config.h custom.h celutil.h celcommon.h mysqlutil.h
cpuid.o: ../config.h celutil.h

DEFS=@auth_inc@

install-exec-local:
	$(mkinstalldirs)  @celiberodir@/bin
	$(mkinstalldirs)  @celiberodir@/etc

install-data-local:
	
	chmod oug+rx  @celiberodir@
	chmod oug+rx  @celiberodir@/etc

AUTOMAKE_OPTIONS = foreign no-dependencies 

dc.o: ../config.h custom.h
dns.o: ../config.h custom.h
global.o: ../config.h custom.h
misc.o: ../config.h custom.h
mqa.o: ../config.h custom.h
mysqlutil.o: ../config.h custom.h
celacl.o: ../config.h custom.h
celiberod.o: ../config.h custom.h
celutil.o: ../config.h custom.h
rp.o: ../config.h custom.h
rs.o: ../config.h custom.h
vqr.o: ../config.h custom.h
celiberosmtpd.o: ../config.h custom.h
