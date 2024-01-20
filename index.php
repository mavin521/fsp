<!DOCTYPE html>
<html>
<head>
    <title>电视频道 M3U 文件生成器</title>
</head>
<body>
    <form method="post" action="generate_m3u.php">
        <label for="channels">电视频道（用英文模式下的逗号分隔）:</label>
        <input type="text" id="channels" name="channels">
        
        <label for="action">选择操作：</label>
        <select id="action" name="action">
            <option value="generate_m3u">生成M3U文件</option>
            <option value="play_directly">直接播放</option>
        </select>
        
        <input type="submit" value="执行操作">
    </form>
</body>
</html>
