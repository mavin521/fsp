<?php
function check_link($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $retcode == 200;
}

function fetch_links($channel) {
    // 尝试从文件中读取直播源链接
    $file_path = 'cached_links.txt';
    $cached_links = [];
    if (file_exists($file_path)) {
        $cached_links = json_decode(file_get_contents($file_path), true);
    }
    // 检查是否有缓存
    if (isset($cached_links[$channel]) && time() < $cached_links[$channel]['expires']) {
        return $cached_links[$channel]['link'];
    }

    // 从新链接获取直播源链接
    $new_source_response = file_get_contents("http://tonkiang.us/?s=" . urlencode($channel));
    preg_match_all('/copyto\("([^"]+)"\)/', $new_source_response, $new_source_matches);
    $new_source_links = array_slice($new_source_matches[1], 0, 5); // 获取最新的5个链接
    $links = [];
    foreach ($new_source_links as $link) {
        $link = trim($link);
        $links[] = $link;
    }

    // 将直播源链接写入文件
    $cached_links[$channel] = [
        'link' => $links,
        'expires' => time() + 3 * 24 * 60 * 60,
    ];
    file_put_contents($file_path, json_encode($cached_links));

    return $links;
}

function get_recent_searches($file_path) {
    // 尝试从文件中读取最近搜索的频道名称
    if (file_exists($file_path)) {
        return json_decode(file_get_contents($file_path), true);
    }
    return [];
}

function save_recent_search($channel, $file_path) {
    // 读取最近搜索的频道名称
    $recent_searches = get_recent_searches($file_path);
    // 添加新的搜索记录
    array_unshift($recent_searches, $channel);
    // 最多保留30个搜索记录
    $recent_searches = array_slice($recent_searches, 0, 30);
    // 保存到文件
    file_put_contents($file_path, json_encode($recent_searches));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tv_channels = explode(',', $_POST['channels']);
    $action = $_POST['action'];
    // 保存最近搜索的频道名称
    foreach ($tv_channels as $channel) {
        $channel = trim($channel);
        save_recent_search($channel, 'recent_searches.txt');
    }
    if ($action == 'play_directly') {
        // 直接播放
        echo '<h2>直播源列表：</h2>';
        foreach ($tv_channels as $channel) {
            $channel = trim($channel);
            $links = fetch_links($channel);
            if (!empty($links)) {
                // 显示链接供用户选择
                echo '<p>选择播放直播源：</p>';
                foreach ($links as $index => $link) {
                    echo "<p><a href='{$link}' target='_blank'>直播源 " . ($index + 1) . "</a></p>";
                }
            } else {
                echo "<p>未找到频道 '{$channel}' 的直播源。</p>";
            }
        }
        exit;
    }
}

// 不再显示其他用户最近搜索的频道

?>
