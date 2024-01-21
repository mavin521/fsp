<!DOCTYPE html>
<html>
<head>
    <title>电视频道直播</title>
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
        <label for="channels">电视频道（用英文逗号分隔）：</label>
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
    $famous_channels = [
        'CCTV1', 'CCTV2', 'CCTV3', 'CCTV4', 'CCTV5', 'CCTV6', 'CCTV7', 'CCTV8', 'CCTV9', 'CCTV10',
        '湖南卫视', '江苏卫视', '浙江卫视', '北京卫视', '东方卫视', '安徽卫视', '黑龙江卫视', '吉林卫视', '辽宁卫视', '山东卫视',
        '香港电视台', '澳门电视台', '凤凰卫视', '星空卫视', '翡翠台', 'TVB经典', 'ViuTV', 'NOW直播', '濠視', 'J2',
        '中天电视', '华视', '台视', '东森电影', '中视', '华视', '民视', '大愛', '寰宇新闻', '公视HD',
        'BBC News', 'CNN', 'Fox News', 'ABC News', 'CNBC', 'Bloomberg', 'Al Jazeera English', 'DW News', 'France 24', 'Russia Today',
        'Discovery Channel', 'National Geographic', 'History Channel', 'Animal Planet', 'Travel Channel', 'Food Network', 'HGTV', 'BBC Earth', 'MTV', 'VH1',
        'ESPN', 'Fox Sports', 'NBA TV', 'NFL Network', 'Golf Channel', 'Tennis Channel', 'MLB Network', 'Sky Sports News', 'Eurosport', 'CBS Sports',
        'CCTV5+', 'Guangdong Sports', 'LeSports', 'WWE Network', 'Star Sports', 'beIN Sports', 'Sky Sports F1', 'NFL RedZone', 'NHL Network', 'MUTV',
        'Cartoon Network', 'Disney Channel', 'Nickelodeon', 'Boomerang', 'CBeebies', 'Nick Jr.', 'Disney Junior', 'PBS Kids', 'BabyTV', 'KidsCo',
        'MTV Asia', 'Channel V', 'Zing', 'B4U Music', '9XM', 'MCM', 'VH1 India', 'Radio City', 'Mastiii', 'Sun Music',
    ];

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
                }
            };
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send("channels=" + encodeURIComponent(channel) + "&action=play_directly");
        }
    </script>
</body>
</html>
