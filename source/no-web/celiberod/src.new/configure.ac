dnl Process this file with autoconf to produce a configure script.
AC_INIT(celibero,3)
AM_INIT_AUTOMAKE(celibero,3)
AM_CONFIG_HEADER(config.h)
AC_CANONICAL_HOST

dnl Checks for programs.
AC_PROG_CC

if test "$GCC"="yes"
then
         CXXFLAGS=""
         CFLAGS=""
fi

incdir=""
for f in /usr/local/include/mysql /usr/include/mysql
do
    if test -d $f
    then
        incdir=$f
    fi
done

AC_ARG_ENABLE(incdir, [  --enable-incdir="" Directory where auth include files are.],
    incdir="$enableval",
    [])

libdir=""
for f in /usr/local/lib/mysql /usr/lib/mysql
do
    if test -d $f
    then
        libdir=$f
    fi
done

AC_ARG_ENABLE(libdir, [  --enable-libdir=path-to-auth-libs Directory where auth libs are.],
    libdir="$enableval",
        [])


use_mysql="yes"
auth_inc="-I$incdir"
AC_SUBST(auth_inc)

auth_libs="-L$libdir -lmysqlclient"
AC_SUBST(auth_libs)

AC_SUBST(use_mysql)

debugmode=""
auth_flags=""
AC_ARG_ENABLE(debug, [  --enable-debug="yes|no" Build with debug flag.],debugmode="y",debugmode="n")

if test "$debugmode" = "y"
then
	auth_flags="-g"
	CFLAGS="-g"
fi
AC_SUBST(auth_flags)

AC_TRY_RUN( [

#include <stdio.h>
#include <pwd.h>
#include <stdlib.h>

int main()
{
 struct passwd *pw;
 char tmpbuf[100];
 FILE *f;

  unlink("celibero.dir");
  unlink("celibero.uid");
  unlink("celibero.gid");

  if (( pw=getpwnam("celibero")) != 0 ) {
    f=fopen("celibero.dir","a+");
    fprintf(f,"%s\n", pw->pw_dir);
    fclose(f);

    f=fopen("celibero.uid", "a+");
    fprintf(f,"%d\n", pw->pw_uid);
    fclose(f);

    f=fopen("celibero.gid", "a+");
    fprintf(f,"%d\n", pw->pw_gid);
    fclose(f);
  } else {
    printf("No celibero user found.\n");
    printf("Please add the celibero user+group and run configure again\n");
  }
  return(0);
}
],
    AC_MSG_RESULT(yes),
    AC_MSG_RESULT(no)
    AC_MSG_ERROR(Could not compile and run even a trivial ANSI C program - check CC.),
    AC_MSG_ERROR(Could not compile and run even a trivial ANSI C program - check CC.))

if test ! -f celibero.uid
then
        AC_MSG_ERROR(No celibero user.)
fi

if test ! -f celibero.gid
then
        AC_MSG_ERROR(No celibero group.)
fi

if test ! -f celibero.dir
then
        AC_MSG_ERROR(No celibero home directory user.)
fi

celiberodir=`cat celibero.dir`
AC_SUBST(celiberodir)
AC_DEFINE_UNQUOTED(CELIBERODIR,"$celiberodir", [Stuff])
rm -f celibero.dir

celiberouid=`cat celibero.uid`
AC_SUBST(celiberouid)
AC_DEFINE_UNQUOTED(CELIBEROUID,$celiberouid, [Stuff])
rm -f celibero.uid

celiberogid=`cat celibero.gid`
AC_SUBST(celiberogid)
AC_DEFINE_UNQUOTED(CELIBEROGID,$celiberogid, [Stuff])
rm -f celibero.gid

sockfile=$celiberodir"/etc/celibero.sock"
AC_ARG_ENABLE(sockfile, [  --enable-sockfile=/path   Full path to the unix socket file.],
    sockfile="$enableval",
)
AC_DEFINE_UNQUOTED(SOCK_FILE,"$sockfile", [Stuff])

cpu="1"
AC_ARG_ENABLE(cpu, [  --enable-cpu=this   The CPU code.],
    cpu="$enableval",
)
AC_DEFINE_UNQUOTED(CPUID,"$cpu", [Stuff])

mac="1"
AC_ARG_ENABLE(mac, [  --enable-mac=this   The mac code.],
    mac="$enableval",
)
AC_DEFINE_UNQUOTED(MACID,"$mac", [Stuff])



dnl Checks for typedefs, structures, and compiler characteristics.
dnl AC_C_CONST

dnl Checks for libraries
AC_CHECK_LIB(nsl, inet_addr)
AC_CHECK_LIB(socket, connect)

AC_OUTPUT([
Makefile
src/Makefile
])

echo ""
echo "            Current settings"
echo "---------------------------------------"
echo "      celibero dir = $celiberodir"
echo "      celibero uid = $celiberouid"
echo "      celibero gid = $celiberogid"
echo "      celibero mac = $mac"
echo "      celibero cpu = $cpu"
