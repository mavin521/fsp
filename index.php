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
        }

        .tv-channel {
            cursor: pointer;
            text-decoration: underline;
            color: blue;
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

    // 预设的著名电视台名称数组
    $famous_channels = [
        'CCTV1', 'CCTV2', 'CCTV3', 'CCTV4', 'CCTV5', 'CCTV6', 'CCTV7', 'CCTV8', 'CCTV9', 'CCTV10',
        '北京卫视', '上海卫视', '广东卫视', '江苏卫视', '浙江卫视', '湖南卫视', '安徽卫视', '东方卫视', '黑龙江卫视', '辽宁卫视',
        '山东卫视', '河南卫视', '湖北卫视', '重庆卫视', '四川卫视', '云南卫视', '陕西卫视', '甘肃卫视', '青海卫视', '宁夏卫视',
        '新疆卫视', '内蒙古卫视', '西藏卫视', '南方卫视', '南海卫视', '凤凰卫视', '卫视影院', '卫视中文台', '卫视欢腾HD', '卫视高清台',
        '香港电视台', '澳门电视台', '台湾中视', '台湾民视', '台湾公视', '东森电视', '华视', '中天新闻', '中天娱乐', '中天综合',
        'BBC News', 'CNN', 'ESPN', 'National Geographic', 'Discovery Channel', 'HBO', 'FOX', 'ABC', 'CBS', 'NBC',
        'MTV', 'Disney Channel', 'CNBC', 'Bloomberg', 'Al Jazeera English', 'Eurosport', 'France 24', 'DW', 'NHK World', 'KBS World',
        '中央台', '卫视台', '港澳台', '欧美电视台', '大陆港澳台中文',
        // 添加更多电视台名称
    ];

    echo '<h2>著名电视台：</h2>';
    echo '<p>点击电视频道名称查看直播源：</p>';
    
    foreach ($famous_channels as $famous_channel) {
        echo "<p class='tv-channel' onclick='fetchAndDisplay(\"$famous_channel\")'>$famous_channel</p>";
    }
    ?>

    <script>
        function fetchAndDisplay(channel) {
            // 发送异步请求，获取直播源链接并显示
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.body.innerHTML += xhr.responseText;
                }
            };
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send("channels=" + encodeURIComponent(channel) + "&action=play_directly");
        }
    </script>
</body>
</html>
