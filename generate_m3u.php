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

function create_m3u_file($tv_channels, $filename = 'tv_channels.m3u') {
    $m3uContent = "#EXTM3U\n";

    foreach ($tv_channels as $channel) {
        $channel = trim($channel);
        $response = file_get_contents("http://tonkiang.us/?s=" . urlencode($channel));
        preg_match_all('/copyto\("([^"]+)"\)/', $response, $matches);
        $links = array_slice($matches[1], 0, 3);

        $valid_link_found = false;
        foreach ($links as $link) {
            if (check_link($link)) {
                $m3uContent .= "#EXTINF:-1, {$channel}\n{$link}\n";
                $valid_link_found = true;
                break;
            }
        }

        if (!$valid_link_found && count($links) > 0) {
            $m3uContent .= "#EXTINF:-1, {$channel}\n{$links[0]}\n";
        }
    }

    file_put_contents($filename, $m3uContent);
    return $filename;
}

function fetch_links($channel) {
    // ...（之前的代码）
}

// 获取最近搜索的频道名称列表
function get_recent_searches($file_path) {
    // ...（之前的代码）
}

// 保存最近搜索的频道名称
function save_recent_search($channel, $file_path) {
    // ...（之前的代码）
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
