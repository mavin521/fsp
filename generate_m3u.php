<?php
function fetch_links($channel) {
    $url = "http://tonkiang.us/?s=" . urlencode($channel);
    $html = file_get_contents($url);

    preg_match_all('/copyto\("([^"]+)"\)/', $html, $matches);
    $links = array_slice(array_filter($matches[1], 'check_link'), 0, 2);

    return $links;
}

function check_link($url) {
    $headers = get_headers($url, 1);
    return strpos($headers[0], '200') !== false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tv_channels = explode(',', $_POST['channels']);
    $action = $_POST['action'];

    foreach ($tv_channels as $channel) {
        $channel = trim($channel);

        if ($action == 'generate_m3u') {
            $filename = 'playlist.m3u';
            $m3u_content = "#EXTM3U\n";
            $links = fetch_links($channel);

            if (!empty($links)) {
                $m3u_content .= "#EXTINF:-1,{$channel}\n";
                $m3u_content .= "{$links[0]}\n";
                $m3u_content .= "{$links[1]}\n";
            }

            file_put_contents($filename, $m3u_content);
        } elseif ($action == 'play_directly') {
            echo '<h2>直播源列表：</h2>';
            $links = fetch_links($channel);

            if (!empty($links)) {
                echo "<div style='text-align: center;'>";
                echo "<p>频道：{$channel}</p>";
                echo "<p>链路1：<a href='{$links[0]}' target='_blank'>{$links[0]}</a></p>";
                echo "<p>链路2：<a href='{$links[1]}' target='_blank'>{$links[1]}</a></p>";
                echo "</div>";
            }
        }
    }
}

// 显示其他用户最近搜索的频道名称
$other_users_recent_searches = get_recent_searches('other_users_recent_searches.txt');
echo '<h2>其他用户最近搜索的频道：</h2>';
echo '<ul>';
foreach ($other_users_recent_searches as $search) {
    echo "<li>{$search}</li>";
}
echo '</ul>';
?>
