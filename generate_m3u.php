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
    // 文件路径
    $file_path = 'cached_links.txt';

    // 尝试从文件中读取直播源链接
    $cached_links = [];
    if (file_exists($file_path)) {
        $cached_links = json_decode(file_get_contents($file_path), true);
    }

    // 检查是否有缓存
    if (isset($cached_links[$channel]) && time() < $cached_links[$channel]['expires']) {
        return $cached_links[$channel]['link'];
    }

    // 从新链接获取直播源链接
    $new_source_link = "https://ghproxy.net/https://raw.githubusercontent.com/YueChan/Live/main/IPTV.m3u";
    $new_source_response = file_get_contents($new_source_link);
    preg_match_all('/#EXTINF:-1,(.+)\n(.+)/', $new_source_response, $new_source_matches);
    $new_source_channels = $new_source_matches[1];
    $new_source_links = $new_source_matches[2];

    $index = array_search($channel, $new_source_channels);
    if ($index !== false) {
        $link = trim($new_source_links[$index]);
        if (check_link($link)) {
            // 将直播源链接写入文件
            $cached_links[$channel] = [
                'link' => $link,
                'expires' => time() + 3 * 24 * 60 * 60,
            ];
            file_put_contents($file_path, json_encode($cached_links));
            return $link;
        }
    }

    // 如果新链接里找不到，回退到之前的链接
    $old_source_response = file_get_contents("http://tonkiang.us/?s=" . urlencode($channel));
    preg_match_all('/copyto\("([^"]+)"\)/', $old_source_response, $old_source_matches);
    $old_source_links = array_slice($old_source_matches[1], 0, 2); // 最多检查两个链接

    foreach ($old_source_links as $link) {
        $link = trim($link);
        if (check_link($link)) {
            // 将直播源链接写入文件
            $cached_links[$channel] = [
                'link' => $link,
                'expires' => time() + 3 * 24 * 60 * 60,
            ];
            file_put_contents($file_path, json_encode($cached_links));
            return $link;
        }
    }

    return false;
}

// 生成 M3U 文件
function create_m3u_file($tv_channels) {
    $m3u_content = "#EXTM3U\n";

    foreach ($tv_channels as $channel) {
        $channel = trim($channel);
        $link = fetch_links($channel);

        if ($link !== false) {
            $m3u_content .= "#EXTINF:-1,{$channel}\n{$link}\n";
        }
    }

    $filename = 'generated_playlist.m3u';
    file_put_contents($filename, $m3u_content);

    return $filename;
}

// 获取最近搜索的频道名称列表
function get_recent_searches($file_path) {
    // 尝试从文件中读取最近搜索的频道名称
    if (file_exists($file_path)) {
        return json_decode(file_get_contents($file_path), true);
    }

    return [];
}

// 保存最近搜索的频道名称
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
            $link = fetch_links($channel);

            if ($link !== false) {
                // 居中显示播放器窗口
                echo "<div style='text-align: center;'><video controls width='800' height='600'><source src='{$link}' type='video/mp4'></video></div>";
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
