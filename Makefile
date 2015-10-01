#! /usr/bin/make -f
# License: GNU GPLv3 (see http://www.gnu.org/licenses/gpl-3.0.html)

PRJVERS = 3.0.5
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
	mce_ifm.php \
	wpabspath.php \
	index.php

INCLD = php-inc
INCS  = ${INCLD}/class-SWF-params-evh.php \
	${INCLD}/class-SWF-put-widget-evh.php \
	${INCLD}/xed_form.php \
	${INCLD}/xed_widget_form.php \
	${INCLD}/help_txt.php 

# The Opt*.php are support classes, not tied to this plugin,
# so they do not share the text-domain and are not args to xgettext
POTSRCS = ${PRJSTEM}.php ${INCS} mce_ifm.php

DOCSD = docs
JSDIR = js
JSBIN = $(JSDIR)/editor_plugin.min.js $(JSDIR)/editor_plugin42.min.js $(JSDIR)/editor_plugin3x.min.js $(JSDIR)/formxed.min.js $(JSDIR)/screens.min.js $(H5DIR)/front.min.js
JSSRC = $(JSDIR)/editor_plugin.js $(JSDIR)/editor_plugin42.js $(JSDIR)/editor_plugin3x.js $(JSDIR)/formxed.js $(JSDIR)/screens.js $(H5DIR)/front.js
H5DIR = evhh5v
H5BIN = $(H5DIR)/evhh5v.css $(H5DIR)/ctlbar.svg $(H5DIR)/ctlvol.svg $(H5DIR)/ctrbut.svg $(JSDIR)/front.min.js
LCDIR = locale
LCDOM = $(PRJSTEM)_l10n
LCPOT = $(LCDIR)/$(LCDOM).pot
LCFPO = $(LCDIR)/$(LCDOM)-en_US.mo
LC_SH = $(LCDIR)/pot2en_US.sh
LCSRC = $(LCPOT)
LCALL = $(LC_SH) $(LCFPO) $(LCSRC)
MNAME = evhflv
SDIRI = $(MNAME)
MINGS = mingput.php
MINGA = mainact.inc.php
MINGC = obj.css
SSRCS = $(SDIRI)/$(MINGS) $(SDIRI)/$(MINGA) $(SDIRI)/$(MINGC)
SDEFS = default.mp4
SBINS = $(SDIRI)/$(MNAME).swf

ALSO = Makefile COPYING version.sh .htaccess
#READS= README README.tty README.tt8 README.pdf README.html
READS= README README.pdf README.html
ZALL = ${SRCS} ${ALSO} ${READS} ${SDEFS} readme.txt
ZSALL = ${SSRCS} #${SBINS}
ZDIR = $(H5DIR) $(INCLD) $(SDIRI) $(JSDIR) $(LCDIR) $(DOCSD)
BINALL = ${SBINS} ${JSBIN}
PRJDIR = ${PRJNAME}
PRJZIP = ${PRJNAME}.zip

XGETTEXT = xgettext
ZIP = zip -r -9 -v -T -X
ZXL = -x \*/.git/\* \*/.git\*
PHPCLI = php -f

all: ${PRJZIP}

${PRJZIP}: ${SBINS} ${SDEFS} ${H5BIN} ${JSBIN} ${ZALL} ${INCS} ${LCFPO}
	test -e ttd && rm -rf ttd; test -e ${PRJDIR} && mv ${PRJDIR} ttd; \
	mkdir ${PRJDIR} && \
	cp -r -p ${ZALL} ${ZDIR} ${PRJDIR} && \
	( cd ${PRJDIR}/${DOCSD} && make clean; true ) && \
	rm -f ${PRJZIP} && \
	$(ZIP) ${PRJZIP} ${PRJDIR} ${ZXL} && rm -rf ${PRJDIR} && \
	(test -e ttd && mv ttd ${PRJDIR}; ls -l ${PRJZIP})

$(SDIRI)/$(MNAME).swf: $(SDIRI)/$(MINGS) $(SDIRI)/$(MINGA)
	$(PHPCLI) $(SDIRI)/$(MINGS) -- BH=100 > $@

${JSBIN}: ${JSSRC}
	O=$@; I=$${O%%.*}.js; \
	(P=`which perl` && $$P -e 'use JavaScript::Minifier::XS qw(minify); print minify(join("",<>))' < "$$I" > "$$O" 2>/dev/null ) \
	|| \
	(P=`which perl` && $$P -e 'use JavaScript::Minifier qw(minify);minify(input=>*STDIN,outfile=>*STDOUT)' < "$$I" > "$$O" 2>/dev/null) \
	|| { cp -f "$$I" "$$O" && echo UN-MINIFIED $$I to $$O; }

# NOTE: The non-trivial front.js is broken by perl 'JavaScript::Packer'
# this rule is saved for reference in case Packer warrants another
# try some day
#${JSBIN}: ${JSSRC}
#	O=$@; I=$${O%%.*}.js; echo $$I to $$O; \
#	(P=`which perl` && $$P -e \
#		'use JavaScript::Packer;$$p=JavaScript::Packer->init();$$o=join("",<STDIN>);$$p->minify(\$$o,{"compress"=>"clean"});print STDOUT $$o;' < "$$I" > "$$O") \
#	|| cp -f "$$I" "$$O"

${H5BIN} : ${H5SRC}
	exit 0

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
