Place face-api.js here to enable real Face ID recognition.

Recommended layout:
- assets/vendor/face-api/face-api.min.js
- assets/vendor/face-api/models/
  - tiny_face_detector_model-weights_manifest.json (+ .bin)
  - face_landmark_68_model-weights_manifest.json (+ .bin)
  - face_recognition_model-weights_manifest.json (+ .bin)

This project’s JS will try to load face-api from this local path first.
If missing, it may try a CDN (if your environment has internet access).
