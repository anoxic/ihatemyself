<?php
include dirname(dirname(dirname(__FILE__))) . '/engine/vendor/autoload.php';
$bucket = bucket($_REQUEST['p']);

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $d = json_decode(r()->get("xsrf:$_REQUEST[xsrf]") ?: 'false', 1);
    $b = r()->get("xsrf_block:$_REQUEST[xsrf]");

    if (!$d || $b) {
        $err = "session conflict (xsrf failed or blocked due to early submit)";
        $code = "409 Conflict";
        goto err;
    }

    $data = [
        "name"    => $_REQUEST[$d['name']],
        "email"   => $_REQUEST[$d['email']],
        "comment" => $_REQUEST[$d['comment']],
        "website" => $_REQUEST[$d['website']],
        "date"    => date(DATE_ATOM),
    ];

    foreach (['name','email','comment'] as $f) {
        if (empty(trim($data[$f]))) {
            $err = "required field `$f' is missing";
            goto err;
        }
    }

    goto done;

    err:
        header('HTTP/1.1 ' . ($code ?: '400 Bad Request'));
        header('Content-Type: text/plain');
        echo $err;
        r()->rpush("failed_comments", json_encode([
            'time'             => date(DATE_ATOM),
            'err'              => $err,
            'request'          => $_REQUEST,
            'cf_ipcountry'     => $_SERVER['HTTP_CF_IPCOUNTRY'],
            'cf_connecting_ip' => $_SERVER['HTTP_CF_CONNECTING_IP'],
            'user_agent'       => $_SERVER['HTTP_USER_AGENT'],
            'remote_ip'        => $_SERVER['REMOTE_ADDR'],
        ]));
        exit;

    done:
        r()->rpush("comments:$bucket", json_encode($data));
        header("Location: /comments/$bucket/");
        exit;
}

header('HTTP/1.1 404 Not Found');
$data = [
    'xsrf'    => "x".bin2hex(openssl_random_pseudo_bytes(16)),
    'name'    => "x".bin2hex(openssl_random_pseudo_bytes(16)),
    'website' => "x".bin2hex(openssl_random_pseudo_bytes(16)),
    'email'   => "x".bin2hex(openssl_random_pseudo_bytes(16)),
    'comment' => "x".bin2hex(openssl_random_pseudo_bytes(16)),
];
r()->setex("xsrf:$data[xsrf]", 60*60*2, json_encode($data));
r()->setex("xsrf_block:$data[xsrf]", 15, 1);
?>
<link rel=stylesheet href=/cee_ess_ess>
<style>
input,textarea { width: 100% }
[type=submit]  { margin: 3ex 0 5ex }
textarea       { min-height: 22ex }
p              { margin: 0 0 3ex }
</style>
<title>insert comment</title> <?=htmlspecialchars($bucket)?>

<div wrap commentf>
<sy>
<p>if ya wanna make a comment, ya gotta follow the rules:</p>
<p>email is required, but only so i can hunt ya down if ya say something i don' like. i won' publish it</p>
<p>ya gotta spend more'n 30 seconds writing yer comment, if ya spend more'n an hour on it n' summit tha form, yer comment is gone ferever, sorry</p>
<p>probably, ya c'n use these htmls: <em>a</em>,<em>em</em>,<em>strong</em>,<em>code</em></p>
</sy>

<form method=post>
<input name=xsrf value=<?=$data['xsrf']?> type=hidden>
<label>name <input name=<?=$data['name']?>></label>
<label>website (fer linkin') <input name=<?=$data['website']?>></label>
<label>webmail address <input name=<?=$data['email']?>></label>
<label>comment <textarea name=<?=$data['comment']?>></textarea></label>
<input type=submit>
<input type=hidden name=viewport>
</form>

<p>by submitting this form, you consent to collection of personal data you sent along. in addition, to help filter for spam, i might also collect this information:</p>
<p>ip address, operating system and browser information (user_agent), window/screen size</p>
<!--<p>sometimes this data will be sent to a third party to help gauge a spam score</p>-->

<script>
var w = window.innerWidth || 0
var h = window.innerHeight || 0
document.querySelector('[name="viewport"]').value = w + "," + h
</script>

</div>
