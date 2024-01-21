<?php
// 您的链接地址
$url = 'https://agit.ai/Yoursmile7/TVBox/raw/branch/master/live.txt';

// 获取内容
$content = file_get_contents($url);

// 假设每行是一个频道或类别标记
$lines = explode("\n", $content);

$currentGenre = "";
echo "<div>";
foreach ($lines as $line) {
    if (strpos($line, ',#genre#') !== false) {
        // 这是一个新的类别
        $currentGenre = str_replace(',#genre#', '', $line);
        echo "<h2>$currentGenre</h2><ul>";
    } else {
        // 分割频道名和链接
        list($channelName, $channelLink) = explode(',', $line, 2);

        // 显示频道名，点击后通过iframe播放
        echo "<li><a href=\"#\" onclick=\"document.getElementById('player').src='$channelLink'; return false;\">$channelName</a></li>";
    }
}
echo "</ul></div>";

// 一个iframe用于播放视频
echo "<iframe id='player' width='640' height='480' src='' frameborder='0' allowfullscreen></iframe>";
?>
