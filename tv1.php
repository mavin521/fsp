<?php
// 缓存文件路径
$cacheFile = 'cachedchannels.txt'; // 请确保这个路径是可写的

// 缓存时间设置为24小时 (24 * 60 * 60秒)
$cacheTime = 24 * 60 * 60;

// 检查缓存文件是否存在及是否过期
if (!file_exists($cacheFile) || (time() - filemtime($cacheFile) > $cacheTime)) {
    // 您的链接地址
    $url = 'https://raw.githubusercontent.com/fenxp/iptv/main/live/tvlive.txt';

    // 下载内容
    $content = file_get_contents($url);

    // 保存到缓存文件
    file_put_contents($cacheFile, $content);
} else {
    // 从缓存文件读取内容
    $content = file_get_contents($cacheFile);
}

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
    if (strpos($line, ',#genre#') !== false) {
        // 这是一个新的类别
        if ($currentGenre != "") {
            echo "</div>"; // 结束上一个类别的channels div
        }
        $currentGenre = str_replace(',#genre#', '', $line);
        echo "<div class='genre'><h2 class='genre-title'>$currentGenre</h2>";
        echo "<div class='channels'>"; // 开始一个新的channels div
    } else {
        // 分割频道名和链接
        list($channelName, $channelLink) = explode(',', $line, 2);

        // 显示频道名，点击后在新标签页中打开视频播放
        echo "<div class='channel'><a href='$channelLink' target='_blank'>$channelName</a></div>";
    }
}
echo "</div></div>"; // 结束最后一个类别的channels div和genre div
?>
