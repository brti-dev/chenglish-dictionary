<?php

require '../vendor/autoload.php';

use Pced\Vocab;

require_once __DIR__."/../config/config_app.php";

include __DIR__."/../templates/page_header.php";

?>
<h2><span class="hz" lang="zh">欢迎光临!</span></h2>

<div style="line-height:1.5em">

<p><b>Chenglish Dictionary is a fast, simple, mobile-friendly Chinese-English Dictionary powered by <a href="http://cc-cedict.org/" target="_blank">CC-CEDICT</a> with the additional function of creating, saving, producing, and managing highly functional vocabulary lists for personal use and study.</b></p>
<p>Although there are <a href="http://mdbg.net/chindict/chindict.php" title="MDBG Chinese-English Dictionary">already</a> <a href="http://dianhuadictionary.com/" title="Dianhua Chinese-English Dictionary">several</a> <a href="http://nciku.com" title="Nciku Chinese-English Dictionary">great</a> <a href="https://www.pleco.com/">dictionaries</a> available for Chinese language learners, none of them give efficient and effective means to reproduce custom vocabulary lists.</p>

<p>To begin, search for a term in the form field above.</p>

<p><b>TIP</b>: Use the * character as a wildcard.</p>

</div>

<h3>Random Dictionary Entry</h3>
<?php
$sql = "SELECT * FROM zhongwen ORDER BY RAND() LIMIT 1";
$statement = $pdo->query($sql);
$vocab = new Vocab($statement->fetch(), $pdo, $logger);
$vocab->renderHTML();

include __DIR__."/../templates/page_footer.php";

?>