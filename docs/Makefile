PKG   = SWFPut

ROFF  = groff
TROFF = troff
GROTT = grotty -c
TMAC  = -ms
ROFFMAC = $(ROFF)  $(TMAC)
ROFFTXT = $(TROFF) $(TMAC)
PS2PDF  = ps2pdf
#PS2PDF  = pstopdf
TTT   = ascii
#TTT = utf8

SRC  = readme.roff

NAME = README
OPS  = $(NAME).ps
OPDF = $(NAME).pdf
OTXT = $(NAME).txt
OTTY = $(NAME).tty
UTF8 = $(NAME).ut8
UTTY = $(NAME).tt8
HTML = $(NAME).html

TDEF = $(OTXT)

#COL  = col -x
COL  = cat
SED  = sed

ASRC = asc2ans

all:  $(TDEF)

txt:  $(OTXT)

tty:  $(OTTY)

tt8:  $(UTTY)

ut8:  $(UTF8)

ps:   $(OPS)

pdf:  $(OPDF)

html: $(HTML)


$(HTML) : $(SRC)
	$(SED) -e 's;^<<CMAC>>;\.1C;' < \
	$(SRC) | $(ROFFMAC) -Thtml  > $@

$(OTTY) : $(SRC) $(ASRC)
	$(SED) -e 's;^<<CMAC>>;\.1C;' < \
	$(SRC) | $(ROFFMAC) -T$(TTT) | $(COL) | ./$(ASRC) | \
	$(SED) -e 's;^\(.\{1,\}\);    \1;' > $@

$(UTTY) : $(SRC) $(ASRC)
	$(SED) -e 's;^<<CMAC>>;\.1C;' < \
	$(SRC) | $(ROFFMAC) -Tutf8 | $(COL) | ./$(ASRC) | \
	$(SED) -e 's;^\(.\{1,\}\);    \1;' > $@

$(UTF8) : $(SRC)
	$(SED) -e 's;^<<CMAC>>;\.1C;' < \
	$(SRC) | $(ROFFTXT) -Tutf8 | $(GROTT) > $@

$(OTXT) : $(SRC) $(ASRC)
	$(SED) -e 's;^<<CMAC>>;\.1C;' < \
	$(SRC) | $(ROFFTXT) -T$(TTT) | $(GROTT) | ./$(ASRC) -t | \
	$(SED) -e 's;^\(.\{1,\}\);    \1;' > $@

$(OPS) : $(SRC)
	$(SED) -e 's;^<<CMAC>>;\.2C;' < \
	$(SRC) | $(ROFFMAC) -Tps > $@

$(OPDF) : $(OPS)
	$(PS2PDF)  $(OPS) $(OPDF)

$(ASRC) : $(ASRC).c
	$(CC) -o $@ $<

install: all
	mv -f $(TDEF) ..

clean:
	-rm -f $(OTTY) $(HTML) $(UTTY) $(UTF8) \
		$(OTXT) $(OPS) $(OPDF) $(ASRC)

