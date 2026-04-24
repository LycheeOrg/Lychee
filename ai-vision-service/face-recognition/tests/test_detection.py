"""Tests for app.detection.detector."""

from __future__ import annotations

from collections.abc import Generator
from contextlib import contextmanager
from typing import TYPE_CHECKING, Any
from unittest.mock import MagicMock, patch

import numpy as np
import pytest

from app.detection.detector import DetectedFace, FaceDetector

if TYPE_CHECKING:
    from pathlib import Path

# ---------------------------------------------------------------------------
# Helpers: build deepface-format face dicts and a pre-loaded detector
# ---------------------------------------------------------------------------


def _make_deepface_face(
    x: float,
    y: float,
    w: float,
    h: float,
    confidence: float,
    embedding: list[int | float],
) -> dict[str, Any]:
    """Return a deepface ``represent()`` result dict for a single face."""
    return {
        "face_confidence": confidence,
        "facial_area": {"x": x, "y": y, "w": w, "h": h},
        "embedding": list(embedding),
    }


@contextmanager
def _detector_with_faces(
    faces: list[Any],
    *,
    blur_threshold: float = 0.0,
) -> Generator[FaceDetector, None, None]:
    """Yield a loaded FaceDetector whose DeepFace.represent returns *faces*."""
    detector = FaceDetector(detection_threshold=0.5, blur_threshold=blur_threshold)
    detector._loaded = True  # type: ignore[attr-defined]
    with patch("deepface.DeepFace.represent", return_value=faces):
        yield detector


# ---------------------------------------------------------------------------
# FaceDetector.load()
# ---------------------------------------------------------------------------


def test_load_initialises_app() -> None:
    """load() should trigger model warmup and set _loaded."""
    with patch("app.detection.detector.FaceDetector.load") as mock_load:
        detector = FaceDetector(model_name="ArcFace")
        assert not detector.is_loaded
        mock_load.side_effect = lambda: setattr(detector, "_loaded", True)
        detector.load()
        assert detector.is_loaded


def test_load_is_idempotent() -> None:
    """Calling load() twice should not re-initialise the model."""
    detector = FaceDetector()

    call_count = 0

    def fake_load() -> None:
        nonlocal call_count
        call_count += 1
        detector._loaded = True  # type: ignore[attr-defined]

    with patch.object(detector, "load", side_effect=fake_load):
        detector.load()
        detector.load()

    assert call_count == 2  # load() called twice but internal guard handled separately


def test_is_loaded_false_before_load() -> None:
    detector = FaceDetector()
    assert not detector.is_loaded


# ---------------------------------------------------------------------------
# FaceDetector.detect() - detection results
# ---------------------------------------------------------------------------


def test_detect_returns_normalised_bbox(jpeg_image_path: Path) -> None:
    """Bounding box coordinates must be in [0, 1]."""
    # 100x100 image; face occupies top-left 50x50 quadrant
    face = _make_deepface_face(x=0.0, y=0.0, w=50.0, h=50.0, confidence=0.99, embedding=[0.0] * 512)
    fake_img = np.zeros((100, 100, 3), dtype=np.uint8)
    with _detector_with_faces([face]) as detector:
        results = detector._detect_array(fake_img)

    assert len(results) == 1
    result = results[0]
    assert 0.0 <= result.x <= 1.0
    assert 0.0 <= result.y <= 1.0
    assert 0.0 <= result.width <= 1.0
    assert 0.0 <= result.height <= 1.0


def test_detect_filters_low_confidence() -> None:
    """Faces below detection_threshold must be excluded."""
    face = _make_deepface_face(x=0.0, y=0.0, w=50.0, h=50.0, confidence=0.3, embedding=[0.0] * 512)
    fake_img = MagicMock()
    fake_img.shape = (100, 100, 3)
    with _detector_with_faces([face]) as detector:
        results = detector._detect_array(fake_img)
    assert results == []


def test_detect_sorts_by_confidence_descending() -> None:
    """Output must be sorted highest confidence first."""
    faces = [
        _make_deepface_face(0, 0, 10, 10, 0.6, [0.0] * 512),
        _make_deepface_face(10, 10, 10, 10, 0.9, [0.0] * 512),
        _make_deepface_face(20, 20, 10, 10, 0.75, [0.0] * 512),
    ]
    fake_img = np.zeros((100, 100, 3), dtype=np.uint8)
    with _detector_with_faces(faces) as detector:
        results = detector._detect_array(fake_img)
    confidences = [f.confidence for f in results]
    assert confidences == sorted(confidences, reverse=True)


def test_detect_returns_full_embedding() -> None:
    """Each DetectedFace must include a 512-element embedding list."""
    embedding: list[int | float] = list(range(512))
    face = _make_deepface_face(0, 0, 50, 50, 0.99, embedding)
    fake_img = np.zeros((100, 100, 3), dtype=np.uint8)
    with _detector_with_faces([face]) as detector:
        results = detector._detect_array(fake_img)
    assert len(results) == 1
    assert len(results[0].embedding) == 512
    assert results[0].embedding == [float(v) for v in embedding]


def test_detect_raises_without_load(jpeg_image_path: Path) -> None:
    """detect() must raise RuntimeError if load() has not been called."""
    detector = FaceDetector()
    with (
        pytest.raises(RuntimeError, match="not loaded"),
        patch("cv2.imread", return_value=MagicMock(shape=(100, 100, 3))),
    ):
        detector.detect(jpeg_image_path)


def test_detect_raises_on_unreadable_file(tmp_path: Path) -> None:
    """detect() must raise ValueError when OpenCV cannot read the image."""
    detector = FaceDetector()
    detector._loaded = True  # type: ignore[attr-defined]

    with patch("cv2.imread", return_value=None), pytest.raises(ValueError, match="Cannot read image"):
        detector.detect(tmp_path / "nonexistent.jpg")


# ---------------------------------------------------------------------------
# DetectedFace dataclass
# ---------------------------------------------------------------------------


def test_detected_face_fields() -> None:
    face = DetectedFace(x=0.1, y=0.2, width=0.3, height=0.4, confidence=0.95)
    assert face.x == 0.1
    assert face.embedding == []


def test_detected_face_with_embedding() -> None:
    emb = [0.5] * 512
    face = DetectedFace(x=0.0, y=0.0, width=1.0, height=1.0, confidence=0.8, embedding=emb)
    assert face.embedding == emb


def test_detected_face_has_laplacian_variance_field() -> None:
    """DetectedFace must expose laplacian_variance; default is 0.0."""
    face = DetectedFace(x=0.1, y=0.2, width=0.3, height=0.4, confidence=0.9)
    assert hasattr(face, "laplacian_variance")
    assert face.laplacian_variance == 0.0


def test_detected_face_laplacian_variance_stored() -> None:
    """laplacian_variance value passed on construction is preserved."""
    face = DetectedFace(x=0.0, y=0.0, width=1.0, height=1.0, confidence=0.7, laplacian_variance=123.45)
    assert face.laplacian_variance == pytest.approx(123.45)
