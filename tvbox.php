<?php
// 缓存文件路径
$cacheFile = 'cached_channels.txt'; // 请确保这个路径是可写的

// 缓存时间设置为24小时 (24 * 60 * 60秒)
$cacheTime = 24 * 60 * 60;

$urls = [
    'https://raw.githubusercontent.com/fenxp/iptv/main/live/tvlive.txt',
    'https://fongmi.cachefly.net/YuanHsing/YouTube_to_m3u/main/youtube.m3u'
];

$allContent = "";

foreach ($urls as $url) {
    // 检查缓存文件是否存在及是否过期
    $individualCacheFile = md5($url) . '.txt'; // 为每个URL创建一个缓存文件
    if (!file_exists($individualCacheFile) || (time() - filemtime($individualCacheFile) > $cacheTime)) {
        // 下载内容
        $content = file_get_contents($url);
        // 保存到缓存文件
        file_put_contents($individualCacheFile, $content);
    } else {
        // 从缓存文件读取内容
        $content = file_get_contents($individualCacheFile);
    }
    $allContent .= $content . "\n";
}

// 保存所有内容到主缓存文件
file_put_contents($cacheFile, $allContent);

// 从缓存文件读取内容
$content = file_get_contents($cacheFile);

// 假设每行是一个频道或类别标记
$lines = explode("\n", $content);

// CSS样式
echo "<style>
.genre {
    margin-top: 20px;
}
.genre-title {
    font-size: 24px;
}
.channels {
    display: flex;
    flex-wrap: wrap;
}
.channel {
    margin: 5px;
    padding: 10px;
    background: #f4f4f4;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: calc(25% - 10px); /* Adjust the width for more or less columns */
    text-align: center;
}
.channel a {
    text-decoration: none;
    color: #333;
}
</style>";

$currentGenre = "";
foreach ($lines as $line) {
    if ($line == '' || strpos($line, '#') === 0) {
        // 这是注释行或空行，忽略
        continue;
    }
    
    if (strpos($line, ',#genre#') !== false) {
        // 这是一个新的类别
        if ($currentGenre != "") {
            echo "</div>"; // 结束上一个类别的channels div
        }
        $currentGenre = str_replace(',#genre#', '', $line);
        echo "<div class='genre'><h2 class='genre-title'>$currentGenre</h2>";
        echo "<div class='channels'>"; // 开始一个新的channels div
    } else {
        // 尝试根据不同格式进行解析
        if (strpos($line, ',') !== false) {
            // CSV格式：频道名,链接
            list($channelName, $channelLink) = explode(',', $line, 2);
        } else if (strpos($line, 'EXTINF') !== false) {
            // 扩展M3U格式
            preg_match('/,([^,]*)$/', $line, $matches);
            $channelName = trim($matches[1]);
            $line = next($lines); // 获取下一行作为链接
            $channelLink = trim($line);
        } else {
            // 默认为直接链接
            $channelLink = trim($line);
            $channelName = "频道"; // 默认频道名
        }

        // 显示频道名，点击后在新标签页中打开视频播放
        echo "<div class='channel'><a href='$channelLink' target='_blank'>$channelName</a></div>";
    }
}
echo "</div></div>"; // 结束最后一个类别的channels div和genre div
?>
