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

function comment_urls()
{
    return ['отзыв','소문','zauważyć','記す','付注'];
}

function config($c)
{
    return trim(file_get_contents(dirname(__FILE__) . "/conf/$c"));
}
