<?php
// 缓存文件路径
$cacheFile = 'cached_channels.txt'; // 请确保这个路径是可写的

// 缓存时间设置为24小时 (24 * 60 * 60秒)
$cacheTime = 24 * 60 * 60;

// 检查缓存文件是否存在及是否过期
if (!file_exists($cacheFile) || (time() - filemtime($cacheFile) > $cacheTime)) {
    // 您的链接地址
    $url = 'https://agit.ai/Yoursmile7/TVBox/raw/branch/master/live.txt';

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

$currentGenre = "";
echo "<div style='margin-bottom: 20px;'>";
foreach ($lines as $line) {
    if (strpos($line, ',#genre#') !== false) {
        // 这是一个新的类别
        if ($currentGenre != "") {
            echo "</table>";
        }
        $currentGenre = str_replace(',#genre#', '', $line);
        echo "<h2>$currentGenre</h2>";
        echo "<table>"; // 开始一个新的表格
    } else {
        // 分割频道名和链接
        list($channelName, $channelLink) = explode(',', $line, 2);

        // 显示频道名，点击后通过video标签播放
        // 用表格的行和单元格来组织内容
        echo "<tr><td><a href=\"#\" onclick=\"playVideo('$channelLink'); return false;\">$channelName</a></td></tr>";
    }
}
echo "</table></div>";

// 一个video标签用于播放视频
echo "<video id='player' width='640' height='480' controls style='display:none;'></video>";

echo "<script>
function playVideo(src) {
    var player = document.getElementById('player');
    player.style.display = 'block';
    player.src = src;
    player.play();
    player.onerror = function() {
        alert('无法播放: ' + src);
    };
}
</script>";
?>
