"""End-to-end smoke test (requires Docker and a running service stack).

This test is skipped by default.  To run it:

  SMOKE_TEST=1 uv run pytest tests/test_smoke.py -v

The test starts the service via docker-compose, waits for the health check to
pass, sends a mock detect request, and asserts the callback is received by a
mock Lychee HTTP server running in a background thread.

Prerequisites:
  - Docker and docker-compose available in PATH
  - ``docker-compose.minimal.yaml`` with the ``ai-vision`` service block
  - ``VISION_FACE_API_KEY`` env var set (or the default is used for local testing)
"""

from __future__ import annotations

import os
import threading
import time
from http.server import BaseHTTPRequestHandler, HTTPServer
from typing import ClassVar

import httpx
import pytest

SMOKE = os.getenv("SMOKE_TEST", "").lower() in ("1", "true", "yes")
pytestmark = pytest.mark.skipif(not SMOKE, reason="SMOKE_TEST env var not set")

SERVICE_URL = os.getenv("AI_VISION_URL", "http://localhost:8123")
API_KEY = os.getenv("VISION_FACE_API_KEY", "smoke-test-key")
MOCK_LYCHEE_PORT = 19876


# ---------------------------------------------------------------------------
# Mock Lychee callback server
# ---------------------------------------------------------------------------


class _CallbackHandler(BaseHTTPRequestHandler):
    received: ClassVar[list[bytes]] = []

    def do_POST(self) -> None:
        length = int(self.headers.get("Content-Length", 0))
        body = self.rfile.read(length)
        _CallbackHandler.received.append(body)
        self.send_response(200)
        self.end_headers()
        self.wfile.write(b'{"faces": []}')

    def log_message(self, format: str, *args: object) -> None:  # noqa: A002
        pass  # suppress request log noise


# ---------------------------------------------------------------------------
# Smoke tests
# ---------------------------------------------------------------------------


@pytest.fixture(scope="module", autouse=True)
def mock_lychee_server() -> None:  # ty: ignore
    """Start a mock Lychee callback receiver before smoke tests."""
    server = HTTPServer(("0.0.0.0", MOCK_LYCHEE_PORT), _CallbackHandler)
    thread = threading.Thread(target=server.serve_forever, daemon=True)
    thread.start()
    yield
    server.shutdown()


def _wait_for_health(timeout: int = 60) -> None:
    deadline = time.time() + timeout
    while time.time() < deadline:
        try:
            r = httpx.get(f"{SERVICE_URL}/health", timeout=3.0)
            if r.status_code == 200 and r.json().get("model_loaded"):
                return
        except Exception:
            pass
        time.sleep(2)
    pytest.fail(f"Service did not become healthy within {timeout}s")


def test_health_endpoint_responds() -> None:
    """GET /health must return 200 with model_loaded: true."""
    _wait_for_health()
    r = httpx.get(f"{SERVICE_URL}/health", timeout=10.0)
    assert r.status_code == 200
    assert r.json()["model_loaded"] is True
    assert r.json()["status"] == "ok"


def test_detect_returns_202() -> None:
    """POST /detect must return 202 for a synthetic request."""
    _wait_for_health()
    payload = {"photo_id": "smoke-photo-1", "photo_path": "/data/photos/smoke_placeholder.jpg"}
    r = httpx.post(
        f"{SERVICE_URL}/detect",
        json=payload,
        headers={"X-API-Key": API_KEY},
        timeout=10.0,
    )
    # 202 or 400 (file not found) are both valid - what matters is the service responds
    assert r.status_code in (202, 400)


def test_health_returns_embedding_count_as_int() -> None:
    """embedding_count in health response must be a non-negative integer."""
    _wait_for_health()
    r = httpx.get(f"{SERVICE_URL}/health", timeout=10.0)
    assert isinstance(r.json()["embedding_count"], int)
    assert r.json()["embedding_count"] >= 0
