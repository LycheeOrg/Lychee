"""DeepFace wrapper for face detection and embedding generation."""

from __future__ import annotations

import logging
import threading
from dataclasses import dataclass, field
from typing import TYPE_CHECKING, Any

if TYPE_CHECKING:
    from pathlib import Path

logger = logging.getLogger(__name__)


@dataclass
class DetectedFace:
    """Raw face detection result, including the ArcFace embedding vector."""

    x: float
    """Left edge of bounding box as a fraction of image width (0.0-1.0)."""

    y: float
    """Top edge of bounding box as a fraction of image height (0.0-1.0)."""

    width: float
    """Bounding-box width as a fraction of image width (0.0-1.0)."""

    height: float
    """Bounding-box height as a fraction of image height (0.0-1.0)."""

    confidence: float
    """Detection confidence score from RetinaFace (0.0-1.0)."""

    embedding: list[float] = field(default_factory=list)
    """512-dimensional ArcFace embedding vector."""

    laplacian_variance: float = 0.0
    """Laplacian variance sharpness score (higher = sharper)."""


class FaceDetector:
    """Thread-safe wrapper around DeepFace for face detection and embedding generation.

    Uses ArcFace recognition with the RetinaFace detector backend by default.
    The model is loaded once at startup (via :meth:`load`) and shared across
    threads; a lock guards the non-thread-safe ``DeepFace.represent()`` call.
    """

    def __init__(
        self,
        model_name: str = "ArcFace",
        detection_threshold: float = 0.5,
        blur_threshold: float = 100.0,
        detector_backend: str = "retinaface",
    ) -> None:
        self._model_name = model_name
        self._detection_threshold = detection_threshold
        self._blur_threshold = blur_threshold
        self._detector_backend = detector_backend
        self._loaded: bool = False
        self._lock = threading.Lock()

    # ------------------------------------------------------------------
    # Lifecycle
    # ------------------------------------------------------------------

    def load(self) -> None:
        """Load the DeepFace model into memory.

        Idempotent - safe to call more than once.
        Must be called before :meth:`detect` or :meth:`detect_bytes`.
        """
        import numpy as np
        from deepface import DeepFace

        with self._lock:
            if self._loaded:
                return
            # Warmup: triggers model weight download/cache on first call.
            DeepFace.represent(
                img_path=np.zeros((1, 1, 3), dtype=np.uint8),
                model_name=self._model_name,
                detector_backend=self._detector_backend,
                enforce_detection=False,
            )
            self._loaded = True

    @property
    def is_loaded(self) -> bool:
        """Return ``True`` if the model has been successfully loaded."""
        return self._loaded

    # ------------------------------------------------------------------
    # Detection
    # ------------------------------------------------------------------

    def detect(self, image_path: Path) -> list[DetectedFace]:
        """Detect faces in an image file.

        Args:
            image_path: Absolute path to the image file.

        Returns:
            Detected faces sorted by descending confidence, with normalised
            bounding box coordinates (0.0-1.0) and 512-dim embeddings.

        Raises:
            RuntimeError: If :meth:`load` has not been called.
            ValueError: If the file cannot be decoded as an image.
        """
        import cv2

        img: Any = cv2.imread(str(image_path))
        if img is None:
            raise ValueError(f"Cannot read image: {image_path}")
        return self._detect_array(img)

    def detect_bytes(self, image_bytes: bytes) -> list[DetectedFace]:
        """Detect faces from raw image bytes.

        Useful when the caller already has the image in memory (e.g. for
        selfie matching so that no temporary file needs to be written).

        Args:
            image_bytes: Raw bytes of any image format supported by OpenCV.

        Returns:
            Detected faces sorted by descending confidence.

        Raises:
            RuntimeError: If :meth:`load` has not been called.
            ValueError: If the bytes cannot be decoded as an image.
        """
        import cv2
        import numpy as np

        nparr: Any = np.frombuffer(image_bytes, np.uint8)
        img: Any = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        if img is None:
            raise ValueError("Cannot decode image bytes")
        return self._detect_array(img)

    # ------------------------------------------------------------------
    # Internal helpers
    # ------------------------------------------------------------------

    def _detect_array(self, img: Any) -> list[DetectedFace]:
        """Run detection on a BGR numpy array (internal)."""
        import cv2
        from deepface import DeepFace

        if not self._loaded:
            raise RuntimeError("FaceDetector not loaded - call load() first.")

        h: int = int(img.shape[0])
        w: int = int(img.shape[1])

        with self._lock:
            raw_faces: list[Any] = DeepFace.represent(
                img_path=img,
                model_name=self._model_name,
                detector_backend=self._detector_backend,
                enforce_detection=False,
            )

        logger.info("Face detection: found %d raw face(s) in %dx%d image", len(raw_faces), w, h)

        results: list[DetectedFace] = []
        filtered_by_confidence = 0
        filtered_by_blur = 0

        for face in raw_faces:
            score: float = float(face["face_confidence"])
            if score < self._detection_threshold:
                filtered_by_confidence += 1
                continue

            area = face["facial_area"]
            x1 = float(area["x"])
            y1 = float(area["y"])
            x2 = x1 + float(area["w"])
            y2 = y1 + float(area["h"])

            # Compute Laplacian variance on the face crop region.
            # This sharpness score is always computed and sent to Lychee for filtering/tuning.
            px1 = max(0, int(x1))
            py1 = max(0, int(y1))
            px2 = min(w, int(x2))
            py2 = min(h, int(y2))
            variance: float = 0.0
            if px2 > px1 and py2 > py1:
                crop_region = img[py1:py2, px1:px2]
                gray = cv2.cvtColor(crop_region, cv2.COLOR_BGR2GRAY)
                variance = float(cv2.Laplacian(gray, cv2.CV_64F).var())

                # Blur filter: exclude faces below threshold if enabled (threshold > 0.0)
                if self._blur_threshold > 0.0 and variance < self._blur_threshold:
                    filtered_by_blur += 1
                    logger.info(
                        "Filtered blurry face: variance=%.2f < threshold=%.2f",
                        variance,
                        self._blur_threshold,
                    )
                    continue

            # Normalise to [0, 1] and clamp
            fx = max(0.0, min(1.0, x1 / w))
            fy = max(0.0, min(1.0, y1 / h))
            fw = max(0.0, min(1.0, (x2 - x1) / w))
            fh = max(0.0, min(1.0, (y2 - y1) / h))

            embedding: list[float] = [float(v) for v in face["embedding"]]

            results.append(
                DetectedFace(
                    x=fx,
                    y=fy,
                    width=fw,
                    height=fh,
                    confidence=score,
                    embedding=embedding,
                    laplacian_variance=variance,
                )
            )

        # Descending confidence order
        results.sort(key=lambda f: f.confidence, reverse=True)

        # Log summary
        if filtered_by_confidence > 0 or filtered_by_blur > 0:
            logger.info(
                "Face detection: %d face(s) passed filters (filtered %d by confidence, %d by blur)",
                len(results),
                filtered_by_confidence,
                filtered_by_blur,
            )
        else:
            logger.info("Face detection: %d face(s) detected (all passed filters)", len(results))

        return results
