BOOKS=../adaptations/Adaptations/*.xml
MORPH=morph/*.php
LMORPH=lmorph/*.php

all: concordance index

concordance: lconcordance.php concordance.php

index: lindex.php rindex.php

concordance.php: ${BOOKS} ${MORPH} make_concordance.php
	php make_concordance.php

lconcordance.php: ${BOOKS} ${LMORPH} make_lconcordance.php
	php make_lconcordance.php


rindex.php: ${BOOKS} ${LMORPH} make_rindex.php
	php make_rindex.php

lindex.php: ${BOOKS} ${LMORPH} make_lindex.php
	php make_lindex.php


concordance.txt: ${BOOKS} ${MORPH}
	php make_parsed.php > concordance.txt

lconcordance.txt: ${BOOKS} ${MORPH}
	php make_lparsed.php > lconcordance.txt

cambios: concordance.php
	git diff concordance.php | grep '^- ' -A3 -B1 | grep -v morph 
