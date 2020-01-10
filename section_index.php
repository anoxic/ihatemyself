<link rel=stylesheet href=/cee_ess_ess>
<title>posts from <?=$d=substr($_SERVER{'REQUEST_URI'},1,4)?></title>
<pre>
<?php
include dirname(dirname(__FILE__)) . '/vendor/autoload.php';
echo "<a href=..>..</a>\n";
foreach(json_decode(r()->get('post_index')?:'[]', 1) as $p) {
    foreach (glob("*/index.php") as $f) {
        if ($p['f'] == "$d/$f") {
            echo '<a href=' . dirname($f) . "/>$d/" . dirname($f) . "</a>\n";
        }
    }
}
