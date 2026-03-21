"""Shared pytest fixtures for the AI Vision Service test suite."""

from __future__ import annotations

import io
from concurrent.futures import ThreadPoolExecutor
from contextlib import asynccontextmanager
from typing import TYPE_CHECKING, Any
from unittest.mock import MagicMock

import pytest
from fastapi.testclient import TestClient
from PIL import Image

from app.config import AppSettings, get_settings
from app.detection.detector import DetectedFace, FaceDetector
from app.embeddings.store import EmbeddingStore
from app.main import create_app

if TYPE_CHECKING:
    from collections.abc import AsyncGenerator
    from pathlib import Path

    from fastapi import FastAPI

# ---------------------------------------------------------------------------
# Environment / settings
# ---------------------------------------------------------------------------


@pytest.fixture
def mock_settings() -> AppSettings:
    """Return an ``AppSettings``-like mock pre-populated with test values."""
    m = MagicMock(spec=AppSettings)
    m.api_key = "test-api-key"
    m.lychee_api_url = "http://lychee-test"
    m.lychee_api_key = "test-lychee-key"
    m.photos_path = "/tmp"  # overridden where needed
    m.match_threshold = 0.5
    m.max_faces_per_photo = 10
    m.detection_threshold = 0.5
    m.thread_pool_size = 1
    m.storage_backend = "sqlite"
    m.log_level = "info"
    return m


# ---------------------------------------------------------------------------
# Detector mock
# ---------------------------------------------------------------------------


@pytest.fixture
def mock_detector() -> FaceDetector:
    """Return a mock :class:`FaceDetector` that returns no faces by default."""
    m = MagicMock(spec=FaceDetector)
    m.is_loaded = True
    m.detect.return_value = []
    m.detect_bytes.return_value = []
    return m  # type: ignore[return-value]


@pytest.fixture
def detected_face() -> DetectedFace:
    """Return a sample :class:`DetectedFace` for use in tests."""
    return DetectedFace(
        x=0.1,
        y=0.2,
        width=0.3,
        height=0.4,
        confidence=0.95,
        embedding=[0.1] * 512,
    )


# ---------------------------------------------------------------------------
# Store mock
# ---------------------------------------------------------------------------


@pytest.fixture
def mock_store() -> EmbeddingStore:
    """Return a mock :class:`EmbeddingStore` with sensible defaults."""
    m = MagicMock(spec=EmbeddingStore)
    m.count.return_value = 0
    m.similarity_search.return_value = []
    return m  # type: ignore[return-value]


# ---------------------------------------------------------------------------
# FastAPI test client
# ---------------------------------------------------------------------------


@pytest.fixture
def test_app(mock_detector: FaceDetector, mock_store: EmbeddingStore, tmp_path: Any) -> FastAPI:
    """Return a FastAPI app wired with mock state (no real model loaded)."""

    @asynccontextmanager
    async def _test_lifespan(app: FastAPI) -> AsyncGenerator[None]:
        app.state.detector = mock_detector
        app.state.store = mock_store
        app.state.executor = ThreadPoolExecutor(max_workers=1)
        yield

    application = create_app(lifespan=_test_lifespan)

    # Override settings so required env vars are not needed
    def _override_settings() -> AppSettings:
        m = MagicMock(spec=AppSettings)
        m.api_key = "test-api-key"
        m.lychee_api_url = "http://lychee-test"
        m.lychee_api_key = "test-lychee-key"
        m.photos_path = str(tmp_path)
        m.match_threshold = 0.5
        m.max_faces_per_photo = 10
        m.detection_threshold = 0.5
        m.thread_pool_size = 1
        return m  # type: ignore[return-value]

    application.dependency_overrides[get_settings] = _override_settings
    return application


@pytest.fixture
def client(test_app: FastAPI) -> TestClient:
    """Return a synchronous :class:`TestClient` bound to the test app."""
    with TestClient(test_app) as c:
        return c


# ---------------------------------------------------------------------------
# Synthetic test image
# ---------------------------------------------------------------------------


@pytest.fixture
def jpeg_image_bytes() -> bytes:
    """Return raw JPEG bytes for a simple 100x100 green image."""
    img = Image.new("RGB", (100, 100), color=(34, 139, 34))
    buf = io.BytesIO()
    img.save(buf, format="JPEG")
    return buf.getvalue()


@pytest.fixture
def jpeg_image_path(tmp_path: Any, jpeg_image_bytes: bytes) -> Any:
    """Write a JPEG test image to a temp path and return the :class:`Path`."""

    path: Path = tmp_path / "test_photo.jpg"
    path.write_bytes(jpeg_image_bytes)
    return path
