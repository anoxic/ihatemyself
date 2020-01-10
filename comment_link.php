<?php
include dirname(__FILE__) . '/vendor/autoload.php';
$bucket = bucket($_SERVER{'REQUEST_URI'});
$comments = num_comments($bucket);
?>
<a comments href=/comments/<?=$bucket?>><?=$comments?> comments</a>
<a href=.. title="parent directory">..</a>
