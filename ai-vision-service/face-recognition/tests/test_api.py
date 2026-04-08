"""Tests for FastAPI routes (app.api.routes)."""

from __future__ import annotations

import io
from typing import TYPE_CHECKING
from unittest.mock import patch

import pytest

from app.detection.detector import DetectedFace

if TYPE_CHECKING:
    from pathlib import Path

    from fastapi.testclient import TestClient

# ---------------------------------------------------------------------------
# GET /health
# ---------------------------------------------------------------------------


def test_health_returns_ok(client: TestClient, mock_detector: object, mock_store: object) -> None:
    response = client.get("/health")
    assert response.status_code == 200
    data = response.json()
    assert data["model_loaded"] is True
    assert data["status"] == "ok"
    assert isinstance(data["embedding_count"], int)


def test_health_is_unauthenticated(client: TestClient) -> None:
    """GET /health must not require X-API-Key."""
    response = client.get("/health")
    assert response.status_code == 200


# ---------------------------------------------------------------------------
# Authentication
# ---------------------------------------------------------------------------


def test_detect_requires_api_key(client: TestClient, tmp_path: Path) -> None:
    photo = tmp_path / "photo.jpg"
    photo.touch()
    response = client.post(
        "/detect",
        json={"photo_id": "p1", "photo_path": str(photo)},
    )
    assert response.status_code == 422  # missing header


def test_detect_rejects_wrong_api_key(client: TestClient, tmp_path: Path) -> None:
    photo = tmp_path / "photo.jpg"
    photo.touch()
    response = client.post(
        "/detect",
        json={"photo_id": "p1", "photo_path": str(photo)},
        headers={"X-API-Key": "wrong-key"},
    )
    assert response.status_code == 401


def test_match_requires_api_key(client: TestClient) -> None:
    img_buf = io.BytesIO(b"fake-image")
    response = client.post("/match", files={"file": ("selfie.jpg", img_buf, "image/jpeg")})
    assert response.status_code == 422  # missing header


# ---------------------------------------------------------------------------
# POST /detect
# ---------------------------------------------------------------------------


def test_detect_returns_202(client: TestClient, photos_path: Path) -> None:
    """Valid request to POST /detect must return 202 Accepted."""
    photo = photos_path / "photo.jpg"
    photo.touch()

    with patch("app.api.routes._run_detection_job") as mock_job:
        response = client.post(
            "/detect",
            json={"photo_id": "photo-123", "photo_path": "photo.jpg"},
            headers={"X-API-Key": "test-api-key"},
        )
    assert response.status_code == 202
    mock_job.assert_called_once()


def test_detect_rejects_path_traversal(client: TestClient, tmp_path: Path) -> None:
    """photo_path outside photos_path must be rejected with 400."""
    # The test app sets photos_path = tmp_path; /etc is outside it
    response = client.post(
        "/detect",
        json={"photo_id": "p1", "photo_path": "/etc/passwd"},
        headers={"X-API-Key": "test-api-key"},
    )
    assert response.status_code == 400


def test_detect_rejects_nonexistent_file(client: TestClient) -> None:
    """photo_path that does not exist must be rejected with 400."""
    response = client.post(
        "/detect",
        json={"photo_id": "p1", "photo_path": "missing.jpg"},
        headers={"X-API-Key": "test-api-key"},
    )
    assert response.status_code == 400


def test_detect_background_task_called_with_correct_args(client: TestClient, photos_path: Path) -> None:
    photo = photos_path / "photo.jpg"
    photo.touch()

    with patch("app.api.routes._run_detection_job") as mock_job:
        client.post(
            "/detect",
            json={"photo_id": "photo-xyz", "photo_path": "photo.jpg"},
            headers={"X-API-Key": "test-api-key"},
        )
        args = mock_job.call_args[0]
        assert args[0] == "photo-xyz"
        assert args[1] == photo.resolve()


# ---------------------------------------------------------------------------
# POST /match
# ---------------------------------------------------------------------------


def test_match_returns_422_when_no_face(client: TestClient, mock_detector: object, jpeg_image_bytes: bytes) -> None:
    """When the detector finds no face, /match must return 422."""
    # mock_detector.detect_bytes already returns [] by default
    img_buf = io.BytesIO(jpeg_image_bytes)
    response = client.post(
        "/match",
        files={"file": ("selfie.jpg", img_buf, "image/jpeg")},
        headers={"X-API-Key": "test-api-key"},
    )
    assert response.status_code == 422


def test_match_returns_matches(
    client: TestClient, mock_detector: object, mock_store: object, jpeg_image_bytes: bytes
) -> None:
    """When matches are found, /match must return them in the response body."""

    mock_detector.detect_bytes.return_value = [  # ty: ignore
        DetectedFace(x=0.1, y=0.1, width=0.5, height=0.5, confidence=0.99, embedding=[0.5] * 512)
    ]
    mock_store.similarity_search.return_value = [("face-abc", 0.91)]  # ty: ignore

    img_buf = io.BytesIO(jpeg_image_bytes)
    response = client.post(
        "/match",
        files={"file": ("selfie.jpg", img_buf, "image/jpeg")},
        headers={"X-API-Key": "test-api-key"},
    )
    assert response.status_code == 200
    data = response.json()
    assert data["matches"][0]["lychee_face_id"] == "face-abc"
    assert data["matches"][0]["confidence"] == pytest.approx(0.91)


def test_match_returns_empty_matches_when_below_threshold(
    client: TestClient, mock_detector: object, mock_store: object, jpeg_image_bytes: bytes
) -> None:
    """If no stored face exceeds threshold, matches must be an empty list."""
    mock_detector.detect_bytes.return_value = [  # ty: ignore
        DetectedFace(x=0.1, y=0.1, width=0.5, height=0.5, confidence=0.99, embedding=[0.5] * 512)
    ]
    mock_store.similarity_search.return_value = []  # ty: ignore

    img_buf = io.BytesIO(jpeg_image_bytes)
    response = client.post(
        "/match",
        files={"file": ("selfie.jpg", img_buf, "image/jpeg")},
        headers={"X-API-Key": "test-api-key"},
    )
    assert response.status_code == 200
    assert response.json()["matches"] == []


# ---------------------------------------------------------------------------
# FaceResult schema
# ---------------------------------------------------------------------------


def test_face_result_includes_laplacian_variance() -> None:
    """FaceResult schema must expose laplacian_variance so it is included in callback payloads."""
    from app.api.schemas import FaceResult

    face = FaceResult(
        x=0.1,
        y=0.1,
        width=0.4,
        height=0.4,
        confidence=0.9,
        embedding_id="emb-001",
        crop="base64data",
        laplacian_variance=42.7,
    )
    assert face.laplacian_variance == pytest.approx(42.7)
    payload = face.model_dump()
    assert "laplacian_variance" in payload
    assert payload["laplacian_variance"] == pytest.approx(42.7)
