<!DOCTYPE html>
<html>
<head>
    <title>管理员 - 添加预设电视台</title>
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
    </style>
</head>
<body>
    <h2>添加预设电视台</h2>
    <form method="post" action="">
        <label for="newChannel">新电视频道名称：</label>
        <input type="text" id="newChannel" name="newChannel" required>
        <input type="submit" value="添加">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $newChannel = trim($_POST['newChannel']);
        if (!empty($newChannel)) {
            $famous_channels = include('famous_channels.php');
            $famous_channels[] = $newChannel;
            $famous_channels = array_unique($famous_channels);
            file_put_contents('famous_channels.php', '<?php return ' . var_export($famous_channels, true) . ';');
            echo "<p>已成功添加预设电视频道：{$newChannel}</p>";
        } else {
            echo "<p>电视频道名称不能为空</p>";
        }
    }
    ?>

    <p><a href="index.php">返回首页</a></p>
</body>
</html>
