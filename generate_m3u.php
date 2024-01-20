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
    $file = create_m3u_file($tv_channels);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    readfile($file);
    exit;
}
?>
