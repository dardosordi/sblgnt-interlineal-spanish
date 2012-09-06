BOOKS=../adaptations/Adaptations/*.xml
MORPH=morph/*.php
LMORPH=lmorph/*.php

concordance: concordance.php lconcordance.php

concordance.php: ${BOOKS} ${MORPH}
	php make_concordance.php > concordance.php

lconcordance.php: ${BOOKS} ${LMORPH}
	php make_lconcordance.php > lconcordance.php


concordance.txt: ${BOOKS} ${MORPH}
	php make_parsed.php > concordance.txt

cambios: concordance.php
	git diff concordance.php | grep '^- ' -A3 -B1 | grep -v morph 
