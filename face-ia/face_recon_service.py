from flask import Flask, request, jsonify
import face_recognition
import cv2
import numpy as np
import os
import pickle

app = Flask(__name__)

# Paramètres
valid_extensions = (".jpg", ".jpeg", ".png", ".bmp")
database_file = "database_embeddings.pkl"

# Charger la base de données
if os.path.exists(database_file):
    with open(database_file, "rb") as f:
        database = pickle.load(f)
    print("Base de données chargée !")
else:
    raise Exception("Base de données embeddings non trouvée !")

# Endpoint principal pour la reconnaissance faciale
@app.route("/recognize", methods=["POST"])
def recognize_face():
    if "image" not in request.files:
        return jsonify({"error": "Aucune image fournie"}), 400

    file = request.files["image"]
    file_path = f"temp_{file.filename}"
    file.save(file_path)

    # Charger l'image
    image = cv2.imread(file_path)
    if image is None:
        return jsonify({"error": "Impossible de lire l'image"}), 400

    rgb_image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)

    # Détecter les visages
    face_locations = face_recognition.face_locations(rgb_image)
    face_encodings = face_recognition.face_encodings(rgb_image, face_locations)

    results = []

    for i, (top, right, bottom, left) in enumerate(face_locations):
        test_encoding = face_encodings[i]
        min_distance = 1.0
        recognized_name = None

        for name, encodings in database.items():
            distances = face_recognition.face_distance(encodings, test_encoding)
            if len(distances) > 0:
                best_distance = np.min(distances)
                if best_distance < 0.5 and best_distance < min_distance:
                    min_distance = best_distance
                    recognized_name = name

        results.append({
            "face_index": i+1,
            "location": [top, right, bottom, left],
            "name": recognized_name if recognized_name else "Inconnu"
        })

    os.remove(file_path)  # Supprimer l'image temporaire
    return jsonify(results)

# Lancer le microservice
if __name__ == "__main__":
    app.run(debug=True, host="0.0.0.0", port=5000)
