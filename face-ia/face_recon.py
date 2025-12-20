import face_recognition
import os
import numpy as np
import cv2  # OpenCV
import time
import pickle

valid_extensions = (".jpg", ".jpeg", ".png", ".bmp")
dataset_path = "data/students"  # dossier contenant les sous-dossiers par étudiant
database_file = "database_embeddings.pkl"  # fichier pour sauvegarder les embeddings

# -------------------------------
# Créer ou charger la base de données
# -------------------------------
if os.path.exists(database_file):
    # Charger les embeddings si déjà sauvegardés
    with open(database_file, "rb") as f:
        database = pickle.load(f)
    print("Base de données chargée depuis pickle !")
else:
    database = {}
    for student_name in os.listdir(dataset_path):
        student_folder = os.path.join(dataset_path, student_name)
        embeddings = []
        for photo_name in os.listdir(student_folder):
            if not photo_name.lower().endswith(valid_extensions):
                continue
            photo_path = os.path.join(student_folder, photo_name)
            image = face_recognition.load_image_file(photo_path)
            encoding = face_recognition.face_encodings(image)
            if encoding:
                embeddings.append(encoding[0])
        database[student_name] = embeddings
        print(f"Embeddings créés pour {student_name}")
        time.sleep(0.5)  # petite pause pour suivre le processus

    # Sauvegarder la base de données
    with open(database_file, "wb") as f:
        pickle.dump(database, f)
    print("Base de données sauvegardée !")
    time.sleep(1)

# -------------------------------
# 2️⃣ Charger la photo test
# -------------------------------
test_image_path = "test/test.jpg"
image = cv2.imread(test_image_path)
if image is None:
    print(f"Impossible de charger l'image : {test_image_path}")
    exit()

rgb_image = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)

# -------------------------------
# 3️⃣ Détecter tous les visages
# -------------------------------
face_locations = face_recognition.face_locations(rgb_image)
face_encodings = face_recognition.face_encodings(rgb_image, face_locations)

if not face_encodings:
    print(f"⚠️ Aucun visage détecté dans {test_image_path}")
else:
    # -------------------------------
    # 4️⃣ Comparer chaque visage avec la base
    # -------------------------------
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

        if recognized_name:
            label = recognized_name
            print(f"✅ Visage {i+1} reconnu : {recognized_name}")
        else:
            label = "Inconnu"
            print(f" Visage {i+1} non reconnu")

        # Dessiner un rectangle et ajouter le nom
        cv2.rectangle(image, (left, top), (right, bottom), (0, 255, 0), 2)
        cv2.putText(image, label, (left, top-10), cv2.FONT_HERSHEY_SIMPLEX, 0.8, (0, 255, 0), 2)

        # Petite pause pour observer chaque visage
        time.sleep(1)

    # -------------------------------
    # 5️⃣ Afficher l'image finale
    # -------------------------------
    cv2.imshow("Résultat Reconnaissance Faciale", image)
    cv2.waitKey(0)
    cv2.destroyAllWindows()
