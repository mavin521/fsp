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
    // 示例获取旧链接的逻辑，您需要替换为实际逻辑
    // 这里简单返回一个示例旧链接数组
    $old_source_response = file_get_contents("http://tonkiang.us/?s=" . urlencode($channel));
    preg_match_all('/copyto\("([^"]+)"\)/', $old_source_response, $old_source_matches);
    $old_source_links = array_slice($old_source_matches[1], 0, 4); // 最多检查四个链接

    $valid_links = array_filter($old_source_links, 'check_link');
    $valid_links = array_slice($valid_links, 0, 2);

    return $valid_links;
}

function fetch_links($channel) {
    // 文件路径
    $file_path = 'cached_links.txt';

    // 尝试从文件中读取直播源链接
    $cached_links = [];
    if (file_exists($file_path)) {
        $cached_links = json_decode(file_get_contents($file_path), true);
    }

    // 检查是否有缓存
    if (isset($cached_links[$channel]) && time() < $cached_links[$channel]['expires']) {
        return $cached_links[$channel]['links'];
    }

    // 从新链接获取直播源链接
    $new_source_links = get_old_links_for_channel($channel);

    // 如果找到新链接，直接返回给用户
    if (!empty($new_source_links)) {
        return $new_source_links;
    }

    // 如果新链接找不到，回退到之前的链接
    if (isset($cached_links[$channel]) && count($cached_links[$channel]['links']) === 2) {
        return $cached_links[$channel]['links'];
    } else {
        $old_source_links = get_old_links_for_channel($channel);
        $valid_links = array_filter($old_source_links, 'check_link');
        $valid_links = array_slice($valid_links, 0, 2);
    }

    // 将直播源链接写入文件
    $cached_links[$channel] = [
        'links' => $valid_links,
        'expires' => time() + 3 * 24 * 60 * 60,
    ];
    file_put_contents($file_path, json_encode($cached_links));

    return $valid_links;
}

// 其他函数保持不变...

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tv_channels = explode(',', $_POST['channels']);
    $action = $_POST['action'];

    // 保存最近搜索的频道名称
    foreach ($tv_channels as $channel) {
        $channel = trim($channel);
        save_recent_search($channel, 'recent_searches.txt');
    }

    if ($action == 'generate_m3u') {
        // 生成 M3U 文件
        $filename = create_m3u_file($tv_channels);
        echo '<h2>文件生成完成，请下载：</h2>';
        echo "<a href='{$filename}' download>下载文件</a>";
        exit;
    } elseif ($action == 'play_directly') {
        // 直接播放
        echo '<h2>直播源列表：</h2>';
        foreach ($tv_channels as $channel) {
            $channel = trim($channel);
            $links = fetch_links($channel);

            if (!empty($links)) {
                // 居中显示播放器窗口
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

// 显示其他用户最近搜索的频道名称
$other_users_recent_searches = get_recent_searches('other_users_recent_searches.txt');
echo '<h2>其他用户最近搜索的频道：</h2>';
echo '<ul>';
foreach ($other_users_recent_searches as $search) {
    echo "<li>{$search}</li>";
}
echo '</ul>';
?>
