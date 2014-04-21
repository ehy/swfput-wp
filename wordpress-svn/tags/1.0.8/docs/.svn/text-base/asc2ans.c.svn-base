/* Use this at your own risk.
 * Use of this implies your acceptance of all
 * and any consequences of its use
 */

#include <stdio.h>
#include <string.h>

#define ESC  '\033'
#define BS   '\b'

#if _WIN32 || __NT__
#define SEP '\\'
#else
#define SEP '/'
#endif

#define C_CYAN         "36"
#define C_GREEN        "32"
#define C_BLUE         "34"
#define C_RED          "31"

const char b_on [] = "\033[1m";
const char m_on [] = "\033[4m";
const char c_on [] = "\033[" C_CYAN "m";
const char aoff [] = "\033[0m";
const char* u_on = m_on;

int lbold, lul;

int ftxt = 0;

void
checkbuffer(char* buf, int* idx, int max, FILE* f)
{
  int n = *idx;

  if ( n >= max ) {
    fwrite(buf, 1, n, f);
    *idx = 0;
  }
}

#define NCHKBUF(n)   checkbuffer(buf, &bufi, (n), f)
#define CHKBUF       NCHKBUF(max)

void
badeof()
{
   fputs("\nUnexpected EOF\n\n", stderr);
   exit(1);
}


#define A_OFF \
{NCHKBUF(max-sizeof(aoff)); \
for(len=0;len<(sizeof(aoff)-1);len++){buf[bufi++]=aoff[len];}}

int
put_attr_str(char* buf, int* idx, int max, int lastc, FILE* f)
{
  /* On entry we know that the current char is ^H so we don't need it
   * but we do need the previous char `lastc' to determine underline
   * or bold.
   * Now set attr and fill buffer until seeing that attr should be reset
   * then reset attr and return. Of course while filling buffer it is
   * checked and flushed
   */
  int len, isul, bufi = *idx;
  const char* aon;
  int c, oe = lastc;

  if ( (c = getchar()) == EOF ) {
    badeof();
  }

  if ( lastc == c ) {
    isul = 0;
    len = lbold;
    aon = b_on;
  } else if ( lastc == '_' ) {
    isul = 1;
    len = lul;
    aon = u_on;
  } else {
    lastc = c;
    goto goodbye;
  }

  NCHKBUF(max - len - 2);
  while ( len-- ) {
    buf[bufi++] = *aon++;
  }

  buf[bufi++] = c;
  CHKBUF;

  /* Here, we've put the attr. and the first target char */
  /* Now, loop until the attr. prefixed chars run out */
  while ( (lastc = getchar()) != EOF ) {
    if ( (c = getchar()) != BS ) {
      A_OFF;
      buf[bufi++] = lastc;
      lastc = c;
      break;
    }
    if ( (c = getchar()) == EOF ) {
      badeof();
    }
    if ( (isul && lastc == c)
		  || (!isul && lastc != c && lastc == '_') ) {
      ungetc(c, stdin);
      A_OFF;
      lastc = put_attr_str(buf, &bufi, max, lastc, f);
      goto goodbye;
    }
    buf[bufi++] = c;
    CHKBUF;
  }

  if ( lastc == EOF )
    A_OFF;

goodbye:
  *idx = bufi;
  return lastc;
}

void
usage(char** argv)
{
  char* prog = strrchr(*argv, SEP);
  prog = prog == NULL ? *argv : prog + 1;

  fprintf(stderr,
  "\nUsage:   %s [-t|c] < input\n"
  "\n  One of -t or -c may be specified: -t produces straight text,"
  "\n  -c produces ANSI color attribute codes, and no option"
  "\n  produces ANSI attribute codes for a monochrome monitor.\n"
  "\n  Output is to stdout, and input from stdin."
  "\n  Input must be like nroff output.\n\n"
  , prog);

  exit(1);
}

#undef  CHKBUF
#undef  NCHKBUF
#define NCHKBUF(n)   checkbuffer(buf, &bufi, (n), stdout)
#define CHKBUF       NCHKBUF(sizeof(buf))

int
main(int argc, char* argv[])
{
  int lastc, c;
  int bufi = 0;
  char buf[1024];

  if ( argc == 2 ) {
    if ( strcmp(argv[1], "-t") == 0 ) {
      ftxt = 1;
    } else if ( strcmp(argv[1], "-c") == 0 ) {
      u_on = c_on;
    } else {
      usage(argv);
    }
  } else if ( argc > 2 ) {
    usage(argv);
  }
  lbold = strlen(b_on);
  lul = strlen(u_on);

  lastc = getchar();

  if ( lastc == EOF )
    return 1;

  #if _WIN32 || __NT__
  if ( !ftxt )
    fputs(aoff, stdout);
  #endif
  buf[bufi++] = lastc;

  while ( (c = getchar()) != EOF ) {
    CHKBUF;
    if ( c == BS ) {
      --bufi;
      if ( ftxt ) {
        int cn;
        if ( (cn = getchar()) == EOF ) {
          badeof();
        }
        buf[bufi++] = cn;
        continue;
      } else {
        lastc = put_attr_str(buf, &bufi, sizeof(buf), lastc, stdout);
        if ( lastc == EOF ) {
          break;
        }
        NCHKBUF(sizeof(buf) - 3);
        buf[bufi++] = lastc;
        continue;
      }
      c = getchar();
      CHKBUF;
    }

    buf[bufi++] = lastc = c;
  }

  if ( bufi ) {
    fwrite(buf, 1, bufi, stdout);
  }

  return 0;
}

