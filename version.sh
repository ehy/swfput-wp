#! /bin/sh

V="1.0.2"
FMT='%s\n'
test X"$1" = X-s && FMT='"%s"\n'
# 32 bit int: vmajor<<24|vminor<<16|rmajor<<8|rminor
test X"$1" = X-i && V="16777728"
printf "$FMT" "$V"
