@extends('layout/template_tailwind')

@section('content')
    <button class="test">test</button>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>

    <script>
        const socket = io("https://amecwebtest.mitsubishielevatorasia.co.th/lockpis", { query: { empno: "24012" } });
        // const btn = $(".test");
        socket.on("connect", () => {
            console.log("Socket connected", socket.id);

            // socket.emit("lock_item", { itemId : "13188" , order : "ETA039950" });
        });

        socket.on("disconnect", () => {
            console.log("Socket disconnected");
        });

        socket.on("lock_status", (data) => {
            // console.log("Received message from server:", data.content.text);
            console.log('lock item');
            console.log(data);
        });

        socket.on("connect_error", (err) => {
            console.error("Connection error:", err.message);
        });
    </script>

    <button id="openCamera" class="btn">เปิดกล้อง</button>
    <button id="snap" class="btn" style="display:none;">ถ่ายรูป</button>
    <button id="closeCamera" class="btn" style="display:none;">ปิดกล้อง</button>
    <br>
    <video id="video" width="320" height="240" autoplay style="display:none;"></video>
    <canvas id="canvas" width="320" height="240"></canvas>

    <script>
        let stream = null;

        document.getElementById('openCamera').onclick = async function () {
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
            const video = document.getElementById('video');
            video.srcObject = stream;
            video.style.display = 'block';
            document.getElementById('snap').style.display = 'inline';
            document.getElementById('closeCamera').style.display = 'inline';
            this.style.display = 'none';
        };

        document.getElementById('snap').onclick = function () {
            const canvas = document.getElementById('canvas');
            const video = document.getElementById('video');
            canvas.getContext('2d').drawImage(video, 0, 0, 320, 240);
            document.getElementById('closeCamera').click();
        };

        document.getElementById('closeCamera').onclick = function () {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            document.getElementById('video').style.display = 'none';
            document.getElementById('snap').style.display = 'none';
            document.getElementById('closeCamera').style.display = 'none';
            document.getElementById('openCamera').style.display = 'inline';
        };
    </script>


@endsection