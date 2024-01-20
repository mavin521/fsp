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
            return $link;
        }
    }

    return false;
}

function create_m3u_file($tv_channels, $filename = 'tv_channels.m3u') {
    $m3uContent = "#EXTM3U\n";

    foreach ($tv_channels as $channel) {
        $channel = trim($channel);
        $link = fetch_links($channel);

        if ($link !== false) {
            $m3uContent .= "#EXTINF:-1, {$channel}\n{$link}\n";
        }
    }

    file_put_contents($filename, $m3uContent);
    return $filename;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tv_channels = explode(',', $_POST['channels']);
    $action = $_POST['action'];

    if ($action == 'generate_m3u') {
        $file = create_m3u_file($tv_channels);
        echo '<h2>文件生成完成，请下载：</h2>';
        echo "<a href='{$file}' download>下载文件</a>";
        exit;
    } elseif ($action == 'play_directly') {
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
?>
