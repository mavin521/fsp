<!DOCTYPE html>
<html>
<head>
    <title>电视直播</title>
    <style>
        body {
            text-align: center;
            font-size: 20px;
        }

        form {
            margin: 20px auto;
        }

        h2 {
            margin-top: 20px;
        }

        p {
            margin: 10px 0;
            display: inline-block;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <form method="post" action="">
        <label for="channels">输入电视频道名称：</label>
        <input type="text" id="channels" name="channels">
        <input type="hidden" name="action" value="play_directly">
        <input type="submit" value="直接播放">
    </form>

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
        $file_path = 'cached_links.txt';
        $cached_links = [];

        if (file_exists($file_path)) {
            $cached_links = json_decode(file_get_contents($file_path), true);
        }

        if (isset($cached_links[$channel]) && time() < $cached_links[$channel]['expires']) {
            return $cached_links[$channel]['link'];
        }

        $new_source_response = file_get_contents("http://tonkiang.us/?s=" . urlencode($channel));
        preg_match_all('/copyto\("([^"]+)"\)/', $new_source_response, $new_source_matches);
        $new_source_links = array_slice($new_source_matches[1], 0, 5);
        $links = [];

        foreach ($new_source_links as $link) {
            $link = trim($link);
            $links[] = $link;
        }

        $cached_links[$channel] = [
            'link' => $links,
            'expires' => time() + 3 * 24 * 60 * 60,
        ];
        file_put_contents($file_path, json_encode($cached_links));

        return $links;
    }

    function get_recent_searches($file_path) {
        if (file_exists($file_path)) {
            return json_decode(file_get_contents($file_path), true);
        }
        return [];
    }

    function save_recent_search($channel, $file_path) {
        $recent_searches = get_recent_searches($file_path);
        array_unshift($recent_searches, $channel);
        $recent_searches = array_slice($recent_searches, 0, 30);
        file_put_contents($file_path, json_encode($recent_searches));
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $tv_channels = explode(',', $_POST['channels']);
        $action = $_POST['action'];

        foreach ($tv_channels as $channel) {
            $channel = trim($channel);
            save_recent_search($channel, 'recent_searches.txt');
        }

        if ($action == 'play_directly') {
            echo '<h2>直播源列表：</h2>';
            foreach ($tv_channels as $channel) {
                $channel = trim($channel);
                $links = fetch_links($channel);
                if (!empty($links)) {
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

    // 预设电视台名称
    $famous_channels = include('famous_channels.php');

    $unique_channels = array_unique($famous_channels);
    echo '<h2>预设电视台：</h2>';
    
    foreach ($unique_channels as $famous_channel) {
        echo "<p class='tv-channel' onclick='fetchAndDisplay(\"$famous_channel\")'>$famous_channel</p>";
    }
    ?>

    <script>
        function fetchAndDisplay(channel) {
            // 发送异步请求，获取直播源链接并直接输出到页面
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.write(xhr.responseText);
                    
                    // 修改页面 URL，记录当前状态
                    var newUrl = window.location.href + '?channel=' + encodeURIComponent(channel);
                    window.history.pushState({ path: newUrl }, '', newUrl);
                }
            };
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send("channels=" + encodeURIComponent(channel) + "&action=play_directly");
        }
    </script>
</body>
</html>
