from flask import Flask, request, jsonify
import face_recognition
import cv2
import numpy as np
import os
import pickle

app = Flask(__name__)

database_file = "database_embeddings.pkl"

# Charger la base de données
with open(database_file, "rb") as f:
    database = pickle.load(f)  # {id: [encodings]}

@app.route("/recognize", methods=["POST"])
def recognize_face():
    if "image" not in request.files:
        return jsonify({"error": "Aucune image fournie"}), 400

    file = request.files["image"]
    file_path = f"temp_{file.filename}"
    file.save(file_path)

    image = cv2.imread(file_path)
    rgb_image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)

    face_locations = face_recognition.face_locations(rgb_image)
    face_encodings = face_recognition.face_encodings(rgb_image, face_locations)

    recognized_ids = set()

    # Reconnaissance
    for test_encoding in face_encodings:
        min_distance = 1.0
        recognized_id = None

        for student_id, encodings in database.items():
            distances = face_recognition.face_distance(encodings, test_encoding)
            if len(distances) > 0:
                best_distance = np.min(distances)
                if best_distance < 0.5 and best_distance < min_distance:
                    min_distance = best_distance
                    recognized_id = int(student_id)

        if recognized_id is not None:
            recognized_ids.add(recognized_id)

    os.remove(file_path)

    # 🔥 Réponse finale compatible Laravel
    results = []

    for student_id in database.keys():
        if int(student_id) in recognized_ids:
            results.append({
                "id": int(student_id),
                "statut": "present"
            })
        else:
            results.append({
                "id": int(student_id),
                "statut": "absent"
            })

    return jsonify(results)

if __name__ == "__main__":
    app.run(debug=True, host="0.0.0.0", port=5000)
