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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tv_channels = explode(',', $_POST['channels']);
    $action = $_POST['action'];

    if ($action == 'generate_m3u') {
        // 生成M3U文件
        $file = create_m3u_file($tv_channels);
        
        // 提示文件生成完成
        echo '<h2>文件生成完成，请下载：</h2>';
        echo "<a href='{$file}' download>下载文件</a>";
        exit;
    } elseif ($action == 'play_directly') {
        // 直接播放
        echo '<h2>直播源列表：</h2>';
        foreach ($tv_channels as $channel) {
            $channel = trim($channel);
            $response = file_get_contents("http://tonkiang.us/?s=" . urlencode($channel));
            preg_match_all('/copyto\("([^"]+)"\)/', $response, $matches);
            $links = array_slice($matches[1], 0, 3);

            $valid_link_found = false;
            foreach ($links as $link) {
                if (check_link($link)) {
                    // 输出直播源
                    echo "<video controls width='800' height='600'><source src='{$link}' type='video/mp4'></video>";
                    $valid_link_found = true;
                    break;
                }
            }

            if (!$valid_link_found && count($links) > 0) {
                // 输出第一个链接
                echo "<video controls width='800' height='600'><source src='{$links[0]}' type='video/mp4'></video>";
            }
        }
        exit;
    }
}
?>
