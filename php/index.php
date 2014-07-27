<?php

include 'books.php';

$xml_path = dirname(dirname(__FILE__)) . '/adaptations/Adaptations/';
$moprhdb_path = dirname(__FILE__) . '/morph/';
$concordance_path = dirname(__FILE__) . '/concordance/';


?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<title>SBL GNT Interlineal Español</title>
<link rel="stylesheet" type="text/css" href="/css/style.css"/>
<script type="text/javascript" src="/js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="/js/interlineal.js"></script>
</head>
<body>
<div id="content">

<h1>SBL GNT Interlineal Español</h1>
<div class="interlineal">

<ul class="books">
    <? foreach($books as $book => $book_data):
        $filename = $xml_path . $books[$book]['xml'];
        if (!file_exists($filename)) {
            continue;
        }
    ?>
    <li>
        <h3><?= $book_data['title'] ?></h3>
        <? for ($x = 1; $x <= $book_data['chapters']; $x++): ?>
        <a href="<?= $book_data['dir'] ?>/<?= $x ?>.html"><?= $x ?></a>
        <? endfor; ?>
    </li>
    <? endforeach; ?>
</ul>

</div>
</div>
</body>
</html>
