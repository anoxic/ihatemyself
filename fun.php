<?php
function bucket($n)
{
    //$n = preg_replace('#/([^/]+)/([^/]*)/?#', '$1.$2', $n);
    $n = preg_replace('/[^A-Z0-9]+/', '_', strtoupper($n));
    $n = trim($n, "_");
    return $n;
}

function r($i = 0)
{
    static $r = [];
    if (!$r[$i]) {
        $r[$i] = new \RedisClient\RedisClient();
        $r[$i]->select($i);
    }
    return $r[$i];
}

function num_comments($bucket)
{
    return r()->llen("comments:$bucket");
}

function comment_path()
{
    $paths = explode(" ", config("comment"));
    return $paths[rand(0,count($paths))];
}

function config($c)
{
    return trim(file_get_contents(dirname(dirname(__FILE__)) . "/conf/$c"));
}
