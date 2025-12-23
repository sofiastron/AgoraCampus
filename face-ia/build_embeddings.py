import face_recognition
import os
import pickle

DATASET_DIR = "data/students"
OUTPUT_FILE = "database_embeddings.pkl"

database = {}

for student_id in os.listdir(DATASET_DIR):
    student_path = os.path.join(DATASET_DIR, student_id)
    if not os.path.isdir(student_path):
        continue

    encodings = []

    for img_name in os.listdir(student_path):
        img_path = os.path.join(student_path, img_name)

        image = face_recognition.load_image_file(img_path)
        faces = face_recognition.face_encodings(image)

        if len(faces) > 0:
            encodings.append(faces[0])

    if encodings:
        database[student_id] = encodings
        print(f"Embeddings créés pour ID {student_id}")

with open(OUTPUT_FILE, "wb") as f:
    pickle.dump(database, f)

print("Base d'embeddings créée avec succès")
