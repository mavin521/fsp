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

function get_old_links_for_channel($channel) {
    $old_source_response = file_get_contents("http://tonkiang.us/?s=" . urlencode($channel));
    preg_match_all('/copyto\("([^"]+)"\)/', $old_source_response, $old_source_matches);
    $old_source_links = array_slice($old_source_matches[1], 0, 4);

    $valid_links = array_filter($old_source_links, 'check_link');
    $valid_links = array_slice($valid_links, 0, 2);

    return $valid_links;
}

function fetch_links($channel) {
    $file_path = 'cached_links.txt';

    $cached_links = [];
    if (file_exists($file_path)) {
        $cached_links = json_decode(file_get_contents($file_path), true);
    }

    if (isset($cached_links[$channel]) && time() < $cached_links[$channel]['expires']) {
        return $cached_links[$channel]['links'];
    }

    $new_source_links = get_old_links_for_channel($channel);

    if (!empty($new_source_links)) {
        return $new_source_links;
    }

    if (isset($cached_links[$channel]) && count($cached_links[$channel]['links']) === 2) {
        return $cached_links[$channel]['links'];
    } else {
        $old_source_links = get_old_links_for_channel($channel);
        $valid_links = array_filter($old_source_links, 'check_link');
        $valid_links = array_slice($valid_links, 0, 2);
    }

    $cached_links[$channel] = [
        'links' => $valid_links,
        'expires' => time() + 3 * 24 * 60 * 60,
    ];
    file_put_contents($file_path, json_encode($cached_links));

    return $valid_links;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tv_channels = explode(',', $_POST['channels']);
    $action = $_POST['action'];

    if ($action == 'generate_m3u') {
        echo '<h2>文件生成完成，请下载：</h2>';
        echo '<p>生成 M3U 文件的功能未完全定义。</p>';
        // 请在这里添加生成 M3U 文件的代码
        exit;
    } elseif ($action == 'play_directly') {
        echo '<h2>直播源列表：</h2>';
        foreach ($tv_channels as $channel) {
            $channel = trim($channel);
            $links = fetch_links($channel);

            if (!empty($links)) {
                echo "<div style='text-align: center;'>";
                echo "<p>频道：{$channel}</p>";
                echo "<p>链路1：<a href='{$links[0]}' target='_blank'>{$links[0]}</a></p>";
                echo "<p>链路2：<a href='{$links[1]}' target='_blank'>{$links[1]}</a></p>";
                echo "</div>";
            }
        }
        exit;
    }
}

// ...（其他函数代码）
?>
