<!DOCTYPE html>
<html>
<head>
    <title>电视频道直播源生成器</title>
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

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <form method="post" action="generate_live.php">
        <label for="channels">电视频道（用英文模式下的逗号分隔）:</label>
        <input type="text" id="channels" name="channels">
        <input type="hidden" name="action" value="play_directly">
        <input type="submit" value="直接播放">
    </form>
    
    <form method="post" action="generate_live.php">
        <label for="play_channels">生成 M3U 文件:</label>
        <select id="play_channels" name="channels">
            <?php
                // 这里可以根据需要显示推荐的频道
            ?>
        </select>
        <input type="hidden" name="action" value="generate_m3u">
        <input type="submit" value="生成M3U文件">
    </form>

    <h2>最近热搜频道：</h2>
    <ul>
        <?php
            // 这里可以显示其他用户最近搜索的频道
        ?>
    </ul>
</body>
</html>
