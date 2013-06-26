
ROFF  = groff
TROFF = troff
GROTT = grotty -c
TMAC  = -ms
ROFFMAC = $(ROFF)  $(TMAC)
ROFFTXT = $(TROFF) $(TMAC)
TTT   = ascii
#TTT = utf8

NAME = README
OPS  = $(NAME).ps
OTXT = $(NAME).txt
OTTY = $(NAME).tty

TDEF = $(OTXT)

SRC  = $(NAME).gro

#COL  = col -x
COL  = cat
SED  = sed

ASRC = asc2ans

.SUFFIXES: .gro .ps .txt .ansi

all:  $(TDEF)

txt:  $(OTXT)

tty:  $(OTTY)

ps: $(OPS)

$(OTTY) : $(SRC) $(ASRC)
	$(SED) -e 's;^<<CMAC>>;\.1C;' < \
	$(SRC) | $(ROFFMAC) -T$(TTT) | $(COL) | ./$(ASRC) | \
	$(SED) -e 's;^\(.\{1,\}\);    \1;' > $@

$(OTXT) : $(SRC) $(ASRC)
	$(SED) -e 's;^<<CMAC>>;\.1C;' < \
	$(SRC) | $(ROFFTXT) -T$(TTT) | $(GROTT) | ./$(ASRC) -t | \
	$(SED) -e 's;^\(.\{1,\}\);    \1;' > $@

$(OPS) : $(SRC)
	$(SED) -e 's;^<<CMAC>>;\.2C;' < \
	$(SRC) | $(ROFFMAC) -Tps > $@

$(ASRC) : $(ASRC).c
	$(CC) -o $@ $<

install: all
	mv -f $(TDEF) ..

clean:
	-rm -f $(OTTY) $(OTXT) $(OPS) $(ASRC)

