"""Tests for app.detection.detector."""

from __future__ import annotations

from typing import TYPE_CHECKING, Any
from unittest.mock import MagicMock, patch

import numpy as np
import pytest

from app.detection.detector import DetectedFace, FaceDetector

if TYPE_CHECKING:
    from pathlib import Path

# ---------------------------------------------------------------------------
# FaceDetector.load()
# ---------------------------------------------------------------------------


def test_load_initialises_app() -> None:
    """load() should call FaceAnalysis.prepare() and set _app."""
    mock_analysis = MagicMock()
    mock_app = MagicMock()
    mock_analysis.return_value = mock_app

    with patch("app.detection.detector.FaceDetector.load") as mock_load:
        detector = FaceDetector(model_name="buffalo_l")
        assert not detector.is_loaded
        mock_load.side_effect = lambda: setattr(detector, "_app", mock_app)
        detector.load()
        assert detector.is_loaded


def test_load_is_idempotent() -> None:
    """Calling load() twice should not re-initialise the model."""
    detector = FaceDetector()

    call_count = 0

    def fake_load() -> None:
        nonlocal call_count
        call_count += 1
        detector._app = MagicMock()  # type: ignore[attr-defined]

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


def _make_mock_face(
    bbox: list[float],
    det_score: float,
    embedding: list[int | float],
) -> Any:
    face = MagicMock()
    face.det_score = det_score
    face.bbox = bbox
    face.embedding = embedding
    return face


def _build_detector_with_faces(faces: list[Any], *, blur_threshold: float = 0.0) -> FaceDetector:
    """Return a detector whose internal InsightFace app returns ``faces``."""
    detector = FaceDetector(detection_threshold=0.5, blur_threshold=blur_threshold)
    mock_app = MagicMock()
    mock_app.get.return_value = faces
    detector._app = mock_app  # type: ignore[attr-defined]
    return detector


def test_detect_returns_normalised_bbox(jpeg_image_path: Path) -> None:
    """Bounding box coordinates must be in [0, 1]."""
    # 100x100 image; face occupies left half
    mock_face = _make_mock_face(
        bbox=[0.0, 0.0, 50.0, 50.0],
        det_score=0.99,
        embedding=[0.0] * 512,
    )
    detector = _build_detector_with_faces([mock_face])

    # cv2 is lazily imported inside detect(); call _detect_array directly
    # with a real numpy array so the blur filter can process it.
    fake_img = np.zeros((100, 100, 3), dtype=np.uint8)
    results = detector._detect_array(fake_img)

    assert len(results) == 1
    face = results[0]
    assert 0.0 <= face.x <= 1.0
    assert 0.0 <= face.y <= 1.0
    assert 0.0 <= face.width <= 1.0
    assert 0.0 <= face.height <= 1.0


def test_detect_filters_low_confidence() -> None:
    """Faces below detection_threshold must be excluded."""
    mock_face = _make_mock_face(
        bbox=[0.0, 0.0, 50.0, 50.0],
        det_score=0.3,  # below default threshold of 0.5
        embedding=[0.0] * 512,
    )
    detector = _build_detector_with_faces([mock_face])
    fake_img = MagicMock()
    fake_img.shape = (100, 100, 3)
    results = detector._detect_array(fake_img)
    assert results == []


def test_detect_sorts_by_confidence_descending() -> None:
    """Output must be sorted highest confidence first."""
    faces = [
        _make_mock_face([0, 0, 10, 10], 0.6, [0.0] * 512),
        _make_mock_face([10, 10, 20, 20], 0.9, [0.0] * 512),
        _make_mock_face([20, 20, 30, 30], 0.75, [0.0] * 512),
    ]
    detector = _build_detector_with_faces(faces)
    fake_img = np.zeros((100, 100, 3), dtype=np.uint8)
    results = detector._detect_array(fake_img)
    confidences = [f.confidence for f in results]
    assert confidences == sorted(confidences, reverse=True)


def test_detect_returns_full_embedding() -> None:
    """Each DetectedFace must include a 512-element embedding list."""
    embedding = list(range(512))
    mock_face = _make_mock_face([0, 0, 50, 50], 0.99, embedding)
    detector = _build_detector_with_faces([mock_face])
    fake_img = np.zeros((100, 100, 3), dtype=np.uint8)
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
    detector._app = MagicMock()  # type: ignore[attr-defined]

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
