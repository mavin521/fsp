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

                // 计算频道热度
                $channel_counts = array_count_values($recent_searches);
                arsort($channel_counts);

                // 显示推荐频道（最多30个）
                $counter = 0;
                foreach ($channel_counts as $search => $count) {
                    if ($counter >= 30) {
                        break;
                    }
                    echo "<option value='{$search}'>{$search}</option>";
                    $counter++;
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
            $unique_other_users_recent_searches = array_unique($other_users_recent_searches);

            // 显示其他用户最近搜索的频道（最多30个，不重复）
            $counter = 0;
            foreach ($unique_other_users_recent_searches as $search) {
                if ($counter >= 30) {
                    break;
                }
                echo "<li>{$search}</li>";
                $counter++;
            }
        ?>
    </ul>
</body>
</html>
