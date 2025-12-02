import base64
import numpy as np
import cv2
from flask import Flask, request, jsonify
import insightface

app = Flask(__name__)

# LOAD MODEL INSIGHTFACE
model = insightface.app.FaceAnalysis(name="buffalo_l")
model.prepare(ctx_id=0)       # CPU = 0

def read_image_from_base64(b64):
    img_bytes = base64.b64decode(b64)
    nparr = np.frombuffer(img_bytes, np.uint8)
    img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
    return img

@app.route("/detect_face", methods=["POST"])
def detect_face():
    try:
        data = request.get_json()
        image_b64 = data.get("image")

        img = read_image_from_base64(image_b64)

        faces = model.get(img)

        results = []
        for face in faces:
            results.append({
                "bbox": face.bbox.tolist(),
                "embedding": face.embedding.tolist()
            })

        return jsonify({
            "success": True,
            "faces": results
        })

    except Exception as e:
        return jsonify({"success": False, "error": str(e)})


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5001)
