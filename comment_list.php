<?php
include dirname(dirname(dirname(__FILE__))) . '/engine/vendor/autoload.php';
header('HTTP/1.1 200 OK');

$bucket = bucket(substr($_SERVER{'REQUEST_URI'}, strlen('/comments/')));
$comments = r()->lrange("comments:$bucket", 0, 99);
$prefix = comment_path();
?>
<link rel=stylesheet href=/cee_ess_ess>
<title>comments</title> <sy><?=$bucket?></sy>
<div wrap>
<?php
if (!$comments) {
    echo "<p><co>/* listing nil comments. the nothingness has overtaken us. where's atreyu when ya need 'im */</co></p>";
}
foreach ($comments as &$c) {
    $c = json_decode($c, 1);
    if ($c['website'] && strpos($c['website'], '//') === false) {
        $c['website'] = 'http://' . $c['website'];
    }
    //XXX: need to filter website plus any href values
    $c['website'] = $c['website'] ? strtr($c['website'], '"', '') : null;
    $c['name']    = htmlspecialchars($c['name']);
    $c['comment'] = nl2br(htmlspecialchars($c['comment']));
    /*
    //XXX: actually filter [ inputs
    $c['comment'] = preg_replace('#<(/)?(a( href="[^>\]]+")?|em|strong|code)?>#', '\\[$1$2\\]', $c['comment']);
    $c['comment'] = preg_replace('#a href=([^\]]+)#', 'a rel="noopener noreferrer" target=_blank href=$1', $c['comment']);
    $c['comment'] = strtr($c['comment'], ['\[' => '<', '\]' => '>']) ;
     */
    $auth = $c['website']
        ? "<a rel=\"noopener noreferrer\" target=_blank href=\"$c[website]\" title=website>@</a> $c[name]"
        : "$c[name]";
    echo "<div comment><author>$auth <time>$c[date]</time></author> \n$c[comment]</div>";
}
?>
</div>
<a href=/<?=$prefix?>/?p=<?=$bucket?>>Insert comment</a>
