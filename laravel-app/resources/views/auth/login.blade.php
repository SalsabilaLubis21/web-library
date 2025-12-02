<!DOCTYPE html>
<html>
<head>
<title>Login Face</title>
</head>
<body>

<h2>Face Login</h2>

<video id="camera" width="300" autoplay></video>
<br><br>

<button onclick="login()">Login with Face</button>

<pre id="output"></pre>



<script>
async function startCamera() {
    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    document.getElementById('camera').srcObject = stream;
}

async function login() {
    const video = document.getElementById('camera');

    const detection = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();

    if (!detection) {
        alert("Face not detected!");
        return;
    }

    const vector = detection.descriptor;

    fetch("/login-face", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
        body: JSON.stringify({
            embedding: JSON.stringify(vector)
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            alert("Welcome " + data.user);
        } else {
            alert("Face not recognized!");
        }
    });
}

startCamera();
</script>

</body>
</html>
