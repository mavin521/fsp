<?php

function fetch_live_links($channel) {
    // 示例逻辑：通过访问指定 URL 获取旧的直播源链接
    $old_source_url = "http://tonkiang.us/?s=" . urlencode($channel);
    $old_source_response = file_get_contents($old_source_url);

    // 使用正则表达式匹配直播源链接
    preg_match_all('/copyto\("([^"]+)"\)/', $old_source_response, $old_source_matches);
    $old_source_links = array_slice($old_source_matches[1], 0, 2); // 最多获取两个链接

    // 示例：过滤有效链接，检查是否可访问
    $valid_links = array_filter($old_source_links, 'check_link');
    
    return $valid_links;
}

function check_link($url) {
    // 示例逻辑：使用 cURL 检查链接是否可访问
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $retcode == 200;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tv_channels = explode(',', $_POST['channels']);
    $action = $_POST['action'];

    if ($action == 'generate_m3u') {
        $filename = 'playlist.m3u';

        // 生成 M3U 文件的内容
        $m3u_content = "#EXTM3U\n";
        foreach ($tv_channels as $channel) {
            $m3u_content .= "#EXTINF:-1,{$channel}\n";
            $links = fetch_live_links($channel);
            foreach ($links as $link) {
                $m3u_content .= "{$link}\n";
            }
        }

        // 将内容写入文件
        file_put_contents($filename, $m3u_content);

        echo '<h2>文件生成完成，请下载：</h2>';
        echo "<a href='{$filename}' download>下载文件</a>";
        exit;
    } elseif ($action == 'play_directly') {
        echo '<h2>直播源列表：</h2>';
        foreach ($tv_channels as $channel) {
            $channel = trim($channel);
            $links = fetch_live_links($channel);

            if (!empty($links)) {
                // 在线播放
                echo "<div style='text-align: center;'>";
                echo "<p>频道：{$channel}</p>";
                foreach ($links as $link) {
                    echo "<p><a href='{$link}' target='_blank'>{$link}</a></p>";
                }
                echo "</div>";
            }
        }
        exit;
    }
}

// 其他函数和界面保持不变...

?>
