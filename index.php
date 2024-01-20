<!DOCTYPE html>
<html>
<head>
    <title>电视频道 M3U 文件生成器</title>
</head>
<body>
    <form method="post" action="generate_m3u.php">
        <label for="channels">电视频道（用英文模式下的逗号分隔）:</label>
        <input type="text" id="channels" name="channels">
        <input type="hidden" name="action" value="play_directly">
        <input type="submit" value="直接播放">
    </form>
    
    <form method="post" action="generate_m3u.php">
        <label for="play_channels">生成 M3U 文件:</label>
        <select id="play_channels" name="channels">
            <?php
                $recent_searches = json_decode(file_get_contents('recent_searches.txt'), true);
                foreach ($recent_searches as $search) {
                    echo "<option value='{$search}'>{$search}</option>";
                }
            ?>
        </select>
        <input type="hidden" name="action" value="generate_m3u">
        <input type="submit" value="生成M3U文件">
    </form>

    <h2>其他用户最近搜索的频道：</h2>
    <ul>
        <?php
            $other_users_recent_searches = json_decode(file_get_contents('other_users_recent_searches.txt'), true);
            foreach ($other_users_recent_searches as $search) {
                echo "<li>{$search}</li>";
            }
        ?>
    </ul>
</body>
</html>
