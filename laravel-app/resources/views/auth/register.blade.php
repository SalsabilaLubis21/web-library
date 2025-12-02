<!DOCTYPE html>
<html>
<head>
<title>Register Face</title>
</head>
<body>

<h2>Register with Face</h2>

<input type="text" id="name" placeholder="Name"><br><br>
<input type="email" id="email" placeholder="Email"><br><br>

<video id="camera" width="300" autoplay></video>
<br><br>

<button onclick="capture()">Capture Face</button>

<script>
async function startCamera() {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    document.getElementById('camera').srcObject = stream;
}

function capture() {
    const video = document.getElementById('camera');

    // Buat canvas untuk menangkap frame kamera
    const canvas = document.createElement("canvas");
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    const ctx = canvas.getContext("2d");
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    // Convert ke Base64 PNG
    const base64Image = canvas.toDataURL("image/png");

    fetch("/register-face", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            image: base64Image
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            alert("Face Registered Successfully!");
        } else {
            alert("Error: " + data.message);
        }
    });
}

startCamera();
</script>

</body>
</html>
