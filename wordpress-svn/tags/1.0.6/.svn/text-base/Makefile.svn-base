#! /usr/bin/make -f
# License: GNU GPLv3 (see http://www.gnu.org/licenses/gpl-3.0.html)

PRJVERS = 1.0.6
PRJSTEM = swfput
PRJNAME = $(PRJSTEM)-$(PRJVERS)

COPYRIGHT_HOLDER = Ed Hynan
COPYRIGHT_YEAR   = 2013
TRANS_BUGS_EMAIL = edhynan@gmail.com

SRCS = ${PRJSTEM}.php \
	Options_0_0_2b.inc.php \
	OptField_0_0_2b.inc.php \
	OptSection_0_0_2b.inc.php \
	OptPage_0_0_2b.inc.php \
	index.php

# The Opt*.php are support classes, not tied to this plugin,
# so they do not share the text-domain and are not args to xgettext
POTSRCS = ${PRJSTEM}.php

DOCSD = docs
JSDIR = js
JSBIN = $(JSDIR)/formxed.js $(JSDIR)/screens.js
JSSRC = $(JSDIR)/formxed.dev.js $(JSDIR)/screens.dev.js
LCDIR = locale
LCDOM = $(PRJSTEM)_l10n
LCPOT = $(LCDIR)/$(LCDOM).pot
LCFPO = $(LCDIR)/$(LCDOM)-en_US.mo
LC_SH = $(LCDIR)/pot2en_US.sh
LCSRC = $(LCPOT)
LCALL = $(LC_SH) $(LCFPO) $(LCSRC)
SDIRI = mingtest
SDIRO = mingput
SSRCS = $(SDIRI)/mingput.php $(SDIRI)/mainact.inc.php $(SDIRI)/obj.css
SBINS = $(SDIRI)/default.flv \
	$(SDIRI)/mingput.swf \
	$(SDIRI)/mingput44.swf \
	$(SDIRI)/mingput40.swf \
	$(SDIRI)/mingput36.swf \
	$(SDIRI)/mingput32.swf \
	$(SDIRI)/mingput28.swf \
	$(SDIRI)/mingput24.swf

ALSO = Makefile COPYING
#READS= README README.tty README.tt8 README.pdf README.html
READS= README README.pdf README.html
ZALL = ${SRCS} ${ALSO} ${READS} readme.txt
ZSALL = ${SSRCS} ${SBINS}
ZDIR = $(JSDIR) $(LCDIR) $(DOCSD)
BINALL = ${SBINS} ${JSBIN}
PRJDIR = ${PRJNAME}
PRJSDIR = ${PRJNAME}/${SDIRO}
PRJZIP = ${PRJNAME}.zip

XGETTEXT = xgettext
ZIP = zip -r -9 -v -T -X
PHPCLI = php -f

all: ${PRJZIP}

${PRJZIP}: ${SBINS} ${JSBIN} ${ZALL} ${LCFPO}
	test -e ttd && rm -rf ttd; test -e ${PRJDIR} && mv ${PRJDIR} ttd; \
	mkdir ${PRJDIR} ${PRJSDIR} && \
	cp -r -p ${ZALL} ${ZDIR} ${PRJDIR} && \
	( cd ${PRJDIR}/${DOCSD} && make clean; true ) && \
	cp -r -p ${ZSALL} ${PRJSDIR} && rm -f ${PRJZIP} && \
	$(ZIP) ${PRJZIP} ${PRJDIR} && rm -rf ${PRJDIR} && \
	(test -e ttd && mv ttd ${PRJDIR}; ls -l ${PRJZIP})

$(SDIRI)/default.flv: $(SDIRI)/droptest.flv
	ln $(SDIRI)/droptest.flv $(SDIRI)/default.flv

$(SDIRI)/mingput.swf: $(SDIRI)/mingput.php $(SDIRI)/mainact.inc.php
	$(PHPCLI) $(SDIRI)/mingput.php > $(SDIRI)/mingput.swf

$(SDIRI)/mingput44.swf: $(SDIRI)/mingput.php $(SDIRI)/mainact.inc.php
	$(PHPCLI) $(SDIRI)/mingput.php -- BH=44 > $(SDIRI)/mingput44.swf

$(SDIRI)/mingput40.swf: $(SDIRI)/mingput.php $(SDIRI)/mainact.inc.php
	$(PHPCLI) $(SDIRI)/mingput.php -- BH=40 > $(SDIRI)/mingput40.swf

$(SDIRI)/mingput36.swf: $(SDIRI)/mingput.php $(SDIRI)/mainact.inc.php
	$(PHPCLI) $(SDIRI)/mingput.php -- BH=36 > $(SDIRI)/mingput36.swf

$(SDIRI)/mingput32.swf: $(SDIRI)/mingput.php $(SDIRI)/mainact.inc.php
	$(PHPCLI) $(SDIRI)/mingput.php -- BH=32 > $(SDIRI)/mingput32.swf

$(SDIRI)/mingput28.swf: $(SDIRI)/mingput.php $(SDIRI)/mainact.inc.php
	$(PHPCLI) $(SDIRI)/mingput.php -- BH=28 > $(SDIRI)/mingput28.swf

$(SDIRI)/mingput24.swf: $(SDIRI)/mingput.php $(SDIRI)/mainact.inc.php
	$(PHPCLI) $(SDIRI)/mingput.php -- BH=24 > $(SDIRI)/mingput24.swf

${JSBIN}: ${JSSRC}
	O=$@; I=$${O%.*}.dev.js; \
	(P=`which perl` && $$P -e 'use JavaScript::Minifier qw(minify);minify(input=>*STDIN,outfile=>*STDOUT)' < "$$I" > "$$O" 2>/dev/null) \
	|| (P=`which perl` && $$P -e \
		'use JavaScript::Packer;$$p=JavaScript::Packer->init();$$o=join("",<STDIN>);$$p->minify(\$$o,{"compress"=>"clean"});print STDOUT $$o;' < "$$I" > "$$O") \
	|| cp -f "$$I" "$$O"

$(READS): docs/readme.roff
	(cd docs && make txt tty tt8 pdf html && \
	cp -f README.txt README.tty README.tt8 README.pdf README.html ..)
	rm -f README; mv README.txt README

en_US-mo $(LCFPO): $(LCPOT)
	@echo Making $(LCFPO).
	@F=$$(pwd)/$(LC_SH); test -f "$$F" && test -x "$$F" || \
		{ printf '"%s" not found or not executable: FAILED\n' "$$F"; \
		exit 0; }; \
	(cd $(LCDIR) && POTNAME=$(LCDOM) "$$F") || \
	{ echo FAILED to make the l10n binary $(LFPO); \
	echo If you care about translations then check that \
	GNU gettext package is installed; exit 0; }

TOOLONGSTR = This file is distributed under the same license as the PACKAGE package.
TOOLONGREP = This file is distributed under the same license as the $(PRJSTEM) package.

pot $(LCPOT): $(POTSRCS)
	@echo Invoking $(XGETTEXT) to make $(LCPOT).
	@$(XGETTEXT) --output=- --debug --add-comments \
	--keyword=__ --keyword=_e --keyword=_n:1,2 \
	--package-name=$(PRJSTEM) --package-version=$(PRJVERS) \
	--copyright-holder='$(COPYRIGHT_HOLDER)' \
	--msgid-bugs-address='$(TRANS_BUGS_EMAIL)' \
	--language=PHP --width=72 $(POTSRCS) | \
	sed -e 's/^# SOME DESCRIPTIVE TITLE./# $(PRJSTEM) $(PRJVERS) Pot Source/' \
		-e 's/^\(# Copyright (C) \)YEAR/\1$(COPYRIGHT_YEAR)/' \
		-e 's/# $(TOOLONGSTR)/# $(TOOLONGREP)/' > $(LCPOT) && \
	echo Succeeded with $@ || \
	{ echo FAILED to make the i18n template $(LCPOT); \
	echo If you care about translations then check that \
	GNU gettext package is installed; exit 0; }

clean-docs:
	cd docs && make clean

clean: clean-docs
	rm -f ${BINALL}

cleanzip:
	rm -f ${PRJZIP}

cleanall: clean cleanzip
	rm -f $(READS)
