"""Tests for app.matching.matcher."""

from __future__ import annotations

from unittest.mock import MagicMock

import pytest

from app.api.schemas import MatchResponse
from app.detection.detector import DetectedFace, FaceDetector
from app.embeddings.store import EmbeddingStore
from app.matching.matcher import FaceMatcher

# ---------------------------------------------------------------------------
# Helpers / fixtures
# ---------------------------------------------------------------------------


def _mock_detector(faces: list[DetectedFace]) -> FaceDetector:
    m = MagicMock(spec=FaceDetector)
    m.is_loaded = True
    m.detect_bytes.return_value = faces
    return m  # type: ignore[return-value]


def _mock_store(matches: list[tuple[str, float]]) -> EmbeddingStore:
    m = MagicMock(spec=EmbeddingStore)
    m.similarity_search.return_value = matches
    return m  # type: ignore[return-value]


def _detected_face(confidence: float = 0.95) -> DetectedFace:
    return DetectedFace(x=0.1, y=0.1, width=0.3, height=0.3, confidence=confidence, embedding=[0.5] * 512)


# ---------------------------------------------------------------------------
# FaceMatcher.match()
# ---------------------------------------------------------------------------


def test_match_raises_when_no_face_detected() -> None:
    """match() must raise ValueError when the selfie contains no detectable face."""
    matcher = FaceMatcher(
        detector=_mock_detector([]),
        store=_mock_store([]),
    )
    with pytest.raises(ValueError, match="No face detected"):
        matcher.match(b"\xff\xd8\xff")  # arbitrary bytes


def test_match_returns_matches_from_store() -> None:
    """match() must return MatchResult items built from the store search results."""
    store_matches = [("face-abc", 0.95), ("face-def", 0.72)]
    matcher = FaceMatcher(
        detector=_mock_detector([_detected_face()]),
        store=_mock_store(store_matches),
    )
    response = matcher.match(b"fake-image-bytes")

    assert isinstance(response, MatchResponse)
    assert len(response.matches) == 2
    assert response.matches[0].lychee_face_id == "face-abc"
    assert response.matches[0].confidence == pytest.approx(0.95)


def test_match_uses_highest_confidence_face() -> None:
    """When multiple faces are detected, the one with highest confidence is used."""
    low_conf = _detected_face(confidence=0.6)
    high_conf = _detected_face(confidence=0.99)
    # Detector returns highest-confidence first (sorted), but let's simulate both orders
    detector = _mock_detector([high_conf, low_conf])
    store = _mock_store([("face-xyz", 0.88)])

    matcher = FaceMatcher(detector=detector, store=store)
    matcher.match(b"fake")

    # store.similarity_search should have been called with high_conf.embedding
    store.similarity_search.assert_called_once_with(high_conf.embedding, matcher._threshold, limit=10)  # ty: ignore


def test_match_passes_threshold_to_store() -> None:
    """The configured threshold must be forwarded to similarity_search."""
    detector = _mock_detector([_detected_face()])
    store = _mock_store([])
    matcher = FaceMatcher(detector=detector, store=store, threshold=0.8)
    matcher.match(b"fake")

    store.similarity_search.assert_called_once()  # ty: ignore
    _, _kwargs = store.similarity_search.call_args  # ty: ignore
    # threshold may be passed as positional or keyword
    call_args = store.similarity_search.call_args[0]  # ty: ignore
    assert call_args[1] == 0.8  # second positional arg = threshold


def test_match_returns_empty_list_when_no_store_matches() -> None:
    """An empty match list (no faces above threshold) must be returned cleanly."""
    matcher = FaceMatcher(
        detector=_mock_detector([_detected_face()]),
        store=_mock_store([]),
    )
    response = matcher.match(b"fake")
    assert response.matches == []
