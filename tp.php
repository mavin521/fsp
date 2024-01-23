<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>直播链接播放页面</title>
    <!-- 引入Chromecast SDK -->
    <script type="text/javascript" src="https://www.gstatic.com/cv/js/sender/v1/cast_sender.js"></script>
</head>
<body>
    <!-- 输入框 -->
    <label for="liveUrlInput">输入直播链接：</label>
    <input type="text" id="liveUrlInput" placeholder="输入m3u8格式的直播链接">

    <!-- 播放器和投屏按钮 -->
    <div>
        <video id="liveVideo" controls></video>
        <button id="castButton">投屏</button>
    </div>

    <!-- 播放和投屏代码 -->
    <script>
        document.getElementById('castButton').addEventListener('click', function() {
            var liveStreamUrl = document.getElementById('liveUrlInput').value;
            var videoElement = document.getElementById('liveVideo');

            // 设置直播链接
            videoElement.src = liveStreamUrl;

            // 使用 Cast API 连接到 Chromecast 设备
            cast.framework.CastContext.getInstance().requestSession().then(
                function(session) {
                    // 连接成功
                    console.log('Connected to casting device');

                    // 创建媒体元素
                    var mediaInfo = new cast.framework.messages.MediaInfo(videoElement.src);
                    var request = new cast.framework.messages.LoadRequest(mediaInfo);

                    // 将媒体加载到 Chromecast 设备
                    session.loadMedia(request).then(
                        function() {
                            console.log('Media loaded successfully to casting device');
                        },
                        function(errorCode) {
                            console.error('Error loading media to casting device: ' + errorCode);
                        }
                    );
                },
                function(errorCode) {
                    // 连接失败
                    console.error('Error connecting to casting device: ' + errorCode);
                }
            );
        });
    </script>
</body>
</html>
