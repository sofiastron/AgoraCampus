from flask import Flask, request, jsonify
from flask_cors import CORS
import face_recognition
import cv2
import numpy as np
import os
import pickle
import base64

app = Flask(__name__)
CORS(app)

database_file = "database_embeddings.pkl"

if os.path.exists(database_file):
    with open(database_file, "rb") as f:
        database = pickle.load(f)
    print(f"Base de données chargée ! ({len(database)} personnes enregistrées)")
else:
    raise Exception("Fichier database_embeddings.pkl non trouvé !")

@app.route("/recognize", methods=["POST"])
def recognize_face():
    data = request.json
    if not data or "image" not in data:
        return jsonify({"error": "Aucune donnée image fournie"}), 400

    try:
        image_data = data["image"].split(",")[1]
        nparr = np.frombuffer(base64.b64decode(image_data), np.uint8)
        image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        rgb_image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
    except Exception as e:
        return jsonify({"error": f"Erreur décodage image: {str(e)}"}), 400

    # 1. Détecter TOUTES les positions et TOUS les encodages de visages
    face_locations = face_recognition.face_locations(rgb_image)
    face_encodings = face_recognition.face_encodings(rgb_image, face_locations)

    if not face_encodings:
        return jsonify({"status": "no_face", "message": "Aucun visage détecté"}), 200

    # 2. Liste pour stocker tous les étudiants reconnus
    recognized_students = []
    threshold = 0.5  # Seuil de précision

    # 3. Boucler sur chaque visage trouvé sur la photo
    for current_face_encoding in face_encodings:
        best_name = "Inconnu"
        min_dist = threshold

        # Comparer ce visage avec chaque personne de la base .pkl
        for name, known_encodings in database.items():
            # Comparaison entre le visage actuel et les photos enregistrées de 'name'
            distances = face_recognition.face_distance(known_encodings, current_face_encoding)
            
            if len(distances) > 0:
                current_min_dist = np.min(distances)
                if current_min_dist < min_dist:
                    min_dist = current_min_dist
                    best_name = name
        
        # On ajoute à la liste si la personne est reconnue
        if best_name != "Inconnu":
            recognized_students.append(best_name)

    # 4. Retourner la liste complète
    if recognized_students:
        # On retire les doublons potentiels avec list(set(...))
        unique_students = list(set(recognized_students))
        return jsonify({
            "status": "success",
            "names": unique_students, # Renvoie un tableau : ["sofia", "sara"]
            "count": len(unique_students),
            "message": f"{len(unique_students)} étudiant(s) reconnu(s)"
        })
    else:
        return jsonify({"status": "unknown", "message": "Aucun étudiant reconnu sur cette photo"}), 200

if __name__ == "__main__":
    app.run(debug=True, host="0.0.0.0", port=5000)