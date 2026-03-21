"""InsightFace wrapper for face detection and embedding generation."""

from __future__ import annotations

import threading
from dataclasses import dataclass, field
from typing import TYPE_CHECKING, Any

if TYPE_CHECKING:
    from pathlib import Path


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


class FaceDetector:
    """Thread-safe wrapper around InsightFace FaceAnalysis.

    Uses the ONNX Runtime CPU execution provider so no GPU is required.
    The model is loaded once at startup (via :meth:`load`) and shared across
    threads; a lock guards the non-thread-safe ``app.get()`` call.
    """

    def __init__(
        self,
        model_name: str = "buffalo_l",
        detection_threshold: float = 0.5,
        model_root: str = "/root/.insightface",
    ) -> None:
        self._model_name = model_name
        self._detection_threshold = detection_threshold
        self._model_root = model_root
        self._app: Any = None  # insightface.app.FaceAnalysis - untyped library
        self._lock = threading.Lock()

    # ------------------------------------------------------------------
    # Lifecycle
    # ------------------------------------------------------------------

    def load(self) -> None:
        """Load the InsightFace model pack into memory.

        Idempotent - safe to call more than once.
        Must be called before :meth:`detect` or :meth:`detect_bytes`.
        """
        with self._lock:
            if self._app is not None:
                return
            from insightface.app import FaceAnalysis

            app = FaceAnalysis(
                name=self._model_name,
                root=self._model_root,
                providers=["CPUExecutionProvider"],
            )
            app.prepare(ctx_id=-1)
            self._app = app

    @property
    def is_loaded(self) -> bool:
        """Return ``True`` if the model has been successfully loaded."""
        return self._app is not None

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
        if self._app is None:
            raise RuntimeError("FaceDetector not loaded - call load() first.")

        h: int = int(img.shape[0])
        w: int = int(img.shape[1])

        with self._lock:
            raw_faces: list[Any] = self._app.get(img)

        results: list[DetectedFace] = []
        for face in raw_faces:
            score: float = float(face.det_score)
            if score < self._detection_threshold:
                continue

            bbox: Any = face.bbox  # [x1, y1, x2, y2] absolute pixels
            x1, y1, x2, y2 = float(bbox[0]), float(bbox[1]), float(bbox[2]), float(bbox[3])

            # Normalise to [0, 1] and clamp
            fx = max(0.0, min(1.0, x1 / w))
            fy = max(0.0, min(1.0, y1 / h))
            fw = max(0.0, min(1.0, (x2 - x1) / w))
            fh = max(0.0, min(1.0, (y2 - y1) / h))

            embedding: list[float] = [float(v) for v in face.embedding]

            results.append(DetectedFace(x=fx, y=fy, width=fw, height=fh, confidence=score, embedding=embedding))

        # Descending confidence order
        results.sort(key=lambda f: f.confidence, reverse=True)
        return results
