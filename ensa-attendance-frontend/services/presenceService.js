import axios from 'axios';

const API_URL = 'http://127.0.0.1:8000/api';

export function sendFaceImage(imageFile, seanceId) {
  const formData = new FormData();
  formData.append('image', imageFile);
  formData.append('seance_id', seanceId);

  return axios.post(
    `${API_URL}/presences/face-recognition`,
    formData,
    {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    }
  );
}
