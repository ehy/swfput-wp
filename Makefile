#! /usr/bin/make -f

PRJNAME = swfput-1

SRCS = ${PRJNAME}.php \
	Options_0_0_2.inc.php \
	OptField_0_0_2.inc.php \
	OptSection_0_0_2.inc.php \
	OptPage_0_0_2.inc.php

SDIRI = mingtest
SDIRO = mingput
SSRCS = $(SDIRI)/mingput.php $(SDIRI)/mainact.inc.php
SBINS = $(SDIRI)/mingput.swf

ALSO = Makefile README
ZALL = ${SRCS} ${ALSO}
ZSALL = ${SSRCS} ${SBINS}
PRJDIR = ${PRJNAME}
PRJSDIR = ${PRJNAME}/${SDIRO}
PRJZIP = ${PRJNAME}.zip

ZIP = zip -r -9 -v -T -X
PHPCLI = php

all: ${PRJZIP}

${PRJZIP}: ${SBINS} ${ZALL}
	test -e ttd && rm -rf ttd; test -e ${PRJDIR} && mv ${PRJDIR} ttd; \
	mkdir ${PRJDIR} ${PRJSDIR} && cp -r -p ${ZALL} ${PRJDIR} && \
	cp -r -p ${ZSALL} ${PRJSDIR} && rm -f ${PRJZIP} && \
	$(ZIP) ${PRJZIP} ${PRJDIR} && rm -rf ${PRJDIR} && \
	(test -e ttd && mv ttd ${PRJDIR}; ls -l ${PRJZIP})

$(SDIRI)/mingput.swf: $(SDIRI)/mingput.php $(SDIRI)/mainact.inc.php
	$(PHPCLI) $(SDIRI)/mingput.php > $(SDIRI)/mingput.swf

clean:
	rm -f ${PRJZIP}
