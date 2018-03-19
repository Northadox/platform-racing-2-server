<?php

require_once __DIR__ . '/../fns/all_fns.php';
require_once __DIR__ . '/../queries/levels/levels_search.php';

header("Content-type: text/plain");

$mode = default_post('mode', 'user');
$search_str = default_post('search_str', '');
$order = default_post('order', 'date');
$dir = default_post('dir', 'desc');
$page = (int) default_post('page', 1);
$ip = get_ip();

$page = min(25, $page);
$key = "search-$mode-$search_str-$order-$dir-$page";
$cache_expire = 600; //10 minutes

try {
    // check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method.");
    }

    $page_str = apcu_fetch($key);

    while ($page_str === 'WAIT') {
        sleep(1);
        $page_str = apcu_fetch($key);
    }

    if ($page_str === false) {
        rate_limit("$ip-search", 10, 5);
        apcu_add($key, 'WAIT', 5); // will not overwrite existing
        $pdo = pdo_connect();

        $start = ($page - 1) * 6;
        $count = 6;
        $levels = levels_search($pdo, $search_str, $mode, $start, $count, $order, $dir);
        $page_str = format_level_list($levels);

        apcu_store($key, $page_str, $cache_expire); // will overwrite existing
    }

    echo $page_str;
} catch (Exception $e) {
    $error = $e->getMessage();
    echo "error=$error";
}
