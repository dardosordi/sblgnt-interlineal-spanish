concordance: concordance.php lconcordance.php

concordance.php: ../adaptations/Adaptations/61-Mt.xml morph/mt.php
	php make_concordance.php > concordance.php

lconcordance.php: ../adaptations/Adaptations/61-Mt.xml lmorph/mt.php
	php make_lconcordance.php > lconcordance.php


concordance.txt: ../adaptations/Adaptations/61-Mt.xml morph/mt.php
	php make_parsed.php > concordance.txt

cambios: concordance.php
	git diff concordance.php | grep '^- ' -A3 -B1 | grep -v morph 
