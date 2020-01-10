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
<title>insert comment</title>
<style>
a              { color: #710f26 }
tag,date,sy    { color: #b1a3a3 }
co             { color: #557ec1 }
*              { color: #433838; font: 1em/3.2ex monospace; padding: 0 }
body           { margin: 3ex 3ex 7ex; width: 77ex }
em             { font-weight: bold }
input,textarea { display: block; width: 75ex }
textarea       { min-height: 22ex }
[type=submit]  { margin: 3ex 0 0 }
input:not([type=submit]), textarea { border: 1px solid #b1a3a3; box-sizing: border-box; height: 4.7ex; padding: 1.2ex 1.4ex; transition: border .1s, padding .1s; }
input:not([type=submit]):focus, textarea:focus { border-width: .6ex; border-color: #557ec1; outline: 0; padding: .9ex 1.1ex }
</style>

<h1><em>Insert Comment</em></h1>
<h2><co><?=htmlspecialchars($bucket)?></co></h2>

<p><co>if ya wanna make a comment, ya gotta follow the rules:</co></p>
<p><co>email is required, but only so i can hunt ya down if ya say something i don' like. i won' publish it</co></p>
<p><co>ya gotta spend more'n 30 seconds writing yer comment, if ya spend more'n an hour on it n' summit tha form, yer comment is gone ferever, sorry</co></p>
<p><co>probably, ya c'n use these htmls: <em>a</em>,<em>em</em>,<em>strong</em>,<em>code</em></co></p>

<form method=post>
<input name=xsrf value=<?=$data['xsrf']?> type=hidden>
name <input name=<?=$data['name']?>>
website (fer linkin') <input name=<?=$data['website']?>>
webmail address <input name=<?=$data['email']?>>
comment <textarea name=<?=$data['comment']?>></textarea>
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
