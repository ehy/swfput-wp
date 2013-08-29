#! /bin/sh
#
# script to make 'translation' for en_US from *.pot --
# not a real translation; just copy msgid to msgstr, adding
# typographical quotes, etc. -- the output is UTF-8, and
# this script contains UTF-8 in sed args
#
# this is not useful for real (non-English) translations
#
# Note that date "+%Y-%m-%d %H:%M %Z", as used below, is close
# to but not exactly the format requested in the xgettext
# generated .pot file.  The numeric time-zone offset would
# require `date "+%Y-%m-%d %H:%M%z"`, but posix lacks the
# (lowercase) '%z' spec.. See:
#
#	http://pubs.opengroup.org/onlinepubs/009695399/
#

PROG=${0##*/}

# NOTE: the odd forms of the output names are determined
# by the WordPress weblog software, as this version of this
# script is distributed with a WordPress plugin.
: ${POTNAME:=default}
: ${PONAME:="${POTNAME}-en_US.po"}
: ${MONAME:="${POTNAME}-en_US.mo"}

e2 () { echo ${PROG}: ${1+"$@"} 1>&2; }
fail () { e2 ${1+"$@"}; exit 1; }

mkdate () { date "+%Y-%m-%d %H:%M %Z"; }

TF=$(mktemp "${PROG}.XXXXXXXX") || fail cannot make temp file
TP=$(mktemp "${PROG}.XXXXXXXX") || fail cannot make temp file
cleanup () {
	test -f "$TF" && rm "$TF"
	test -f "$TP" && rm "$TP"
}
trap cleanup 0

IF=${1:-"${POTNAME}.pot"}
OF=${2:-"${PONAME}"}

# not making dir in this version of this script
#test -d en_US || mkdir en_US || fail cannot mkdir en_US

# Old sed args (from epspline:
#		-e 's/^msgid/msgstr/' \
#		-e 's/\([a-zA-Z0-9]\)'\''\([a-zA-Z0-9]\)/\1’\2/g' \
#		-e 's/\([a-zA-Z0-9\.]\)\\"/\1”/g' \
#		-e 's/'\''\\n/’\\n/g' \
#		-e 's/\\"\\n/”\\n/g' \
#		-e 's/\([ 	]\)\\"\("[ 	]*\)$/\1“\2/g' \
#		-e 's/\([a-zA-Z0-9\.]\)'\''/\1’/g' \
#		-e 's/\\"\([a-zA-Z0-9\%]\)/“\1/g' \
#		-e 's/^\(msg[^ 	]\{1,\}[ 	]\{1,\}"\)\\"\([ 	]\)/\1”\2/g' \
#		-e 's/^"\\"\([ 	]\)/"”\1/g' \
#		-e 's/'\''\([a-zA-Z0-9]\)/‘\1/g' \
#		-e 's/\([^!]\)--\([^>]\)/\1—\2/g' \
#		-e 's/^--\([^>]\)/—\1/g' \
#		-e 's/\([^!]\)--$/\1—/g' \
#		-e 's/\([Bb]\)ezier/\1ézier/g' \
#

:>"$TF"; :>"$TP"; while read -r L; do
	case "$L" in
	"msgstr \""* )
		sed \
		-e 's/^msgid/msgstr/' \
		-e 's/multi-widget/widget/' \
		< "$TF"
		:>"$TF"
		continue
		;;
	"msgstr[0] \""* )
		sed \
		-e 's/^msgid/msgstr\[0\]/' \
		-e 's/multi-widget/widget/' \
		< "$TF"
		cat < "$TP" > "$TF"
		:>"$TP"
		continue
		;;
	"msgstr[1] \""* )
		sed \
		-e 's/^msgid_plural/msgstr\[1\]/' \
		-e 's/multi-widget/widget/' \
		< "$TF"
		:>"$TF"
		continue
		;;
	esac
	if test -s "$TP" ; then
		printf '%s\n' "$L" >> "$TP"
	elif test -s "$TF" ; then
		test X"$L" = X'msgid_plural ""' || printf '%s\n' "$L" >> "$TF"
	fi
	printf '%s\n' "$L"
	case "$L" in
	"msgid \""* )
		printf '%s\n' "$L" > "$TF"
		;;
	"msgid_plural \""* )
		printf '%s\n' "$L" > "$TP"
		;;
	esac
done < "$IF" | sed \
	-e 's/^\([ 	]*"PO-Revision-Date: \).*\\n"[ 	]*$/\1'"$(mkdate)"'\\n"/' \
	-e 's/^\([ 	]*"Language: \)\\n"[ 	]*$/\1en_US\\n"/' \
	-e 's/^\("Content-Type: text\/plain; charset=\)CHARSET\\n"$/\1UTF-8\\n"/' \
	> "$OF"

IF="$OF"
OF="${MONAME}"

msgfmt -o "$OF" -v "$IF" || fail FAILED making "\"$OF\""

