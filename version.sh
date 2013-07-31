#! /bin/sh

V="1.0.0"
FMT='%s\n'
test X"$1" = X-s && FMT='"%s"\n'
test X"$1" = X-i && V="16777216"
printf "$FMT" "$V"
