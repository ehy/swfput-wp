#! /bin/sh

V="1.0.4"
FMT='%s\n'
test X"$1" = X-s && FMT='"%s"\n'
# 32 bit int: vmajor<<24|vminor<<16|rmajor<<8|rminor
test X"$1" = X-i && V="16778240"
printf "$FMT" "$V"
