<?php
include dirname(dirname(dirname(__FILE__))) . '/engine/vendor/autoload.php';
header('HTTP/1.1 200 OK');

$bucket = bucket(substr($_SERVER{'REQUEST_URI'}, strlen('/comments/')));
$comments = r()->lrange("comments:$bucket", 0, 99);
$prefix = comment_path();
?>
<title>comments</title>
<style>
a           { color: #710f26 }
tag,date,sy { color: #b1a3a3 }
co          { color: #557ec1 }
*           { color: #433838; font: 1em/3.2ex monospace; padding: 0 }
body        { margin: 3ex 3ex 7ex; width: 77ex }
em,author   { font-weight: bold }
author      { display: block; margin: 0 0 1ex }
[comment]   { margin: 3ex 0 0 }
[comment]:last-child { margin-bottom: 5ex }
</style>
<h1><em>Comments</em> <sy><?=$bucket?></sy></h1>
<div>
<?php
if (!$comments) {
    echo "<p><co>/* listing nil comments. the nothingness has overtaken us. where's atreyu when ya need 'im */</co></p>";
}
foreach ($comments as &$c) {
    $c = json_decode($c, 1);
    if (strpos($c['website'], '//') === false) {
        $c['website'] = 'http://' . $c['website'];
    }
    //XXX: need to filter website plus any href values
    $c['website'] = strtr($c['website'], '"', '');
    $c['name']    = htmlspecialchars($c['name']);
    $c['comment'] = preg_replace('#<(/)?(a( href="[^>\]]+")?|em|strong|code)?>#', '\\[$1$2\\]', $c['comment']);
    $c['comment'] = nl2br(htmlspecialchars($c['comment']));
    $c['comment'] = preg_replace('#a href=([^\]]+)#', 'a rel="noopener noreferrer" target=_blank href=$1', $c['comment']);
    $c['comment'] = strtr($c['comment'], ['\[' => '<', '\]' => '>']) ;
    //XXX: actually filter [ inputs
    $auth = $c['website']
        ? "$c[name] (<a rel=\"noopener noreferrer\" target=_blank href=\"$c[website]\">site</a>)"
        : "$c[name]";
    echo "<div comment><author>$auth <date>$c[date]</date></author> \n$c[comment]</div>";
}
?>
</div>
<a href=/<?=$prefix?>/?p=<?=$bucket?>>Insert comment</a>
