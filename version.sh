#! /bin/sh

VMAJOR=2
VMINOR=9
RMAJOR=0
RMINOR=0

V="$VMAJOR.$VMINOR.$RMAJOR"
test 0 -lt $RMINOR && V="$V.$RMINOR"
FMT='%s\n'
test X"$1" = X-s && FMT='"%s"\n'
# 32 bit int: vmajor<<24|vminor<<16|rmajor<<8|rminor
test X"$1" = X-i && \
  V=$(( VMAJOR*(256*256*256) + VMINOR*(256*256) + RMAJOR*256 + RMINOR ))
printf "$FMT" "$V"
