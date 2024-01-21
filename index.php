<!DOCTYPE html>
<html>
<head>
    <title>电视频道 M3U 文件生成器</title>
    <style>
        body {
            text-align: center;
            font-size: 20px; /* 修改字体大小 */
        }

        form {
            margin: 20px auto; /* 居中显示表单 */
        }

        h2 {
            margin-top: 20px; /* 调整标题上边距 */
        }
    </style>
</head>
<body>
    <form method="post" action="generate_m3u.php">
        <label for="channels">电视频道（用英文模式下的逗号分隔）:</label>
        <input type="text" id="channels" name="channels">
        <input type="hidden" name="action" value="play_directly">
        <input type="submit" value="直接播放">
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

