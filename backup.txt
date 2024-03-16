<?php

function checkLink($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_exec($ch);
    $retCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $retCode == 200;
}

function fetchLinks($channel) {
    $file_path = 'cached_links.json';
    $cached_links = file_exists($file_path) ? json_decode(file_get_contents($file_path), true) : [];
    
    if (!empty($cached_links[$channel]) && time() < $cached_links[$channel]['expires']) {
        return $cached_links[$channel]['links'];
    }

    $response = file_get_contents("http://tonkiang.us/?s=" . urlencode($channel));
    preg_match_all('/copyto\("([^"]+)"\)/', $response, $matches);
    $links = array_filter(array_slice($matches[1], 0, 6), 'checkLink');

    $cached_links[$channel] = ['links' => $links, 'expires' => strtotime('tomorrow 03:00:00')];
    file_put_contents($file_path, json_encode($cached_links));

    return $links;
}

function downloadM3u($links, $channelName) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $channelName . '.m3u"');
    echo "#EXTM3U\n";
    foreach ($links as $link) {
        echo "#EXTINF:-1, $channelName\n$link\n";
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $channels = explode(',', $_POST['channels']);
    $action = $_POST['action'];
    $channel = trim($channels[0]); // Assumes the first channel for simplicity
    $links = fetchLinks($channel);

    if ($action === 'download_m3u' && !empty($links)) {
        downloadM3u($links, $channel);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>电视直播源搜索与下载</title>
    <style>
        body { text-align: center; font-size: 16px; }
        form, .content { margin: 20px auto; width: 80%; }
        .link-button { display: inline-block; margin: 10px; padding: 10px; background: #f0f0f0; border: 1px solid #ddd; text-decoration: none; color: black; }
    </style>
</head>
<body>
    <form method="post" action="">
        <label for="channels">输入电视频道名称：</label>
        <input type="text" id="channels" name="channels" required>
        <input type="submit" name="action" value="search" onclick="this.form.action='';">搜索并播放
        <input type="submit" name="action" value="download_m3u" onclick="this.form.action='';">下载M3U文件
    </form>

    <div class="content">
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && $action === 'search'): ?>
            <h2>直播源列表：<?= htmlspecialchars($channel) ?></h2>
            <?php if (!empty($links)): ?>
                <?php foreach ($links as $index => $link): ?>
                    <a href="<?= htmlspecialchars($link) ?>" target="_blank" class="link-button">直播源 <?= $index + 1 ?></a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>未找到频道的直播源。</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
