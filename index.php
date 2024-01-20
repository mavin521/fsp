<!DOCTYPE html>
<html>
<head>
    <title>电视频道 M3U 文件生成器</title>
    <style>
        body {
            font-size: 2vw; /* 使用相对单位 vw（视口宽度的百分比） */
            text-align: center;
        }

        label {
            display: block;
            margin: 10px 0;
        }

        select, input[type="submit"] {
            padding: 1.5vw; /* 使用相对单位 vw */
            margin: 1vw 0;
        }
    </style>
</head>
<body>
    <form method="post" action="generate_m3u.php">
        <label for="channels">电视频道（用英文模式下的逗号分隔）:</label>
        <input type="text" id="channels" name="channels" required>

        <label for="action">选择操作：</label>
        <select id="action" name="action">
            <option value="generate_m3u">生成M3U文件</option>
            <option value="play_directly">直接播放</option>
        </select>

        <br>

        <input type="submit" value="执行操作">
    </form>
</body>
</html>
