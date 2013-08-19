#! /usr/bin/make -f

PRJVERS = 1.0.3
PRJSTEM = swfput
PRJNAME = $(PRJSTEM)-$(PRJVERS)

SRCS = ${PRJSTEM}.php \
	Options_0_0_2.inc.php \
	OptField_0_0_2.inc.php \
	OptSection_0_0_2.inc.php \
	OptPage_0_0_2.inc.php

DOCSD = docs
JSDIR = js
JSBIN = $(JSDIR)/formxed.js
JSSRC = $(JSDIR)/formxed.dev.js
LCDIR = locale
LCDOM = default
LCPOT = $(LCDIR)/$(LCDOM).pot
LCFPO = $(LCDIR)/$(LCDOM)-en_US.mo
LC_SH = $(LCDIR)/pot2en_US.sh
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
	cp -r -p ${ZALL} ${JSDIR} ${LCDIR} ${DOCSD} ${PRJDIR} && \
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
	(P=`which perl` && $$P -e 'use JavaScript::Minifier qw(minify);minify(input=>*STDIN,outfile=>*STDOUT)' < ${JSSRC} > ${JSBIN} 2>/dev/null) \
	|| (P=`which perl` && $$P -e \
		'use JavaScript::Packer;$$p=JavaScript::Packer->init();$$o=join("",<STDIN>);$$p->minify(\$$o,{"compress"=>"clean"});print STDOUT $$o;' < ${JSSRC} > ${JSBIN}) \
	|| cp -f ${JSSRC} ${JSBIN}

$(READS): docs/readme.roff
	(cd docs && make txt tty tt8 pdf html && \
	cp -f README.txt README.tty README.tt8 README.pdf README.html ..)
	rm -f README; mv README.txt README

en_US-mo $(LCFPO): $(LCPOT)
	@echo Making $(LCFPO).
	@F=$$(pwd)/$(LC_SH); test -f "$$F" && test -x "$$F" || \
		{ printf '"%s" not found or not executable: FAILED\n' "$$F"; \
		exit 0; }; \
	(cd $(LCDIR) && "$$F") || \
	{ echo FAILED to make the l10n binary $(LFPO); \
	echo If you care about translations then check that \
	GNU gettext package is installed; exit 0; }

pot $(LCPOT): $(SRCS)
	@echo Invoking $(XGETTEXT) to make $(LCPOT).
	@$(XGETTEXT) --output=$(LCPOT) --debug --add-comments \
	--keyword=__ --keyword=_e --keyword=_n \
	--package-name=$(PRJSTEM) --package-version=$(PRJVERS) \
	--copyright-holder='Ed Hynan' \
	--language=PHP $(SRCS) && \
	echo Succeeded with $@ || \
	{ echo FAILED to make the i18n template $(LCPOT); \
	echo If you care about translations then check that \
	GNU gettext package is installed; exit 0; }

clean-docs:
	cd docs && make clean

clean: clean-docs
	rm -f ${PRJZIP} ${BINALL}

cleanall: clean
	rm -f $(READS)
