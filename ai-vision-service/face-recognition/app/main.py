"""FastAPI application factory.

Entry point for the AI Vision Service.  The ``create_app`` factory accepts an
optional ``lifespan`` parameter so that tests can inject a custom lifespan
context that pre-populates ``app.state`` with mock objects instead of loading
the real face recognition model.
"""

from __future__ import annotations

import logging
import os
from concurrent.futures import ThreadPoolExecutor
from contextlib import asynccontextmanager
from typing import TYPE_CHECKING, Any

import httpx
from fastapi import FastAPI

from app.api.routes import router
from app.config import AppSettings, get_settings

if TYPE_CHECKING:
    from collections.abc import AsyncGenerator

_LEVEL_COLORS = {
    logging.DEBUG: "\033[36m",     # cyan
    logging.INFO: "\033[32m",      # green
    logging.WARNING: "\033[33m",   # yellow
    logging.ERROR: "\033[31m",     # red
    logging.CRITICAL: "\033[35m",  # magenta
}
_DIM = "\033[2m"
_RESET = "\033[0m"


class _ColorFormatter(logging.Formatter):
    def formatTime(self, record: logging.LogRecord, datefmt: str | None = None) -> str:
        return f"{_DIM}{super().formatTime(record, datefmt)}{_RESET}"

    def format(self, record: logging.LogRecord) -> str:
        color = _LEVEL_COLORS.get(record.levelno, "")
        record.levelname = f"{color}{record.levelname}{_RESET}"
        return super().format(record)


logger = logging.getLogger(__name__)


@asynccontextmanager
async def _default_lifespan(app: FastAPI) -> AsyncGenerator[None]:
    """Production lifespan: load the face model and initialise the store."""
    settings: AppSettings = get_settings()

    # Configure logging
    handler = logging.StreamHandler()
    handler.setFormatter(_ColorFormatter("%(asctime)s %(levelname)s %(name)s %(message)s", datefmt="%Y-%m-%d %H:%M:%S"))
    logging.basicConfig(
        level=getattr(logging, settings.log_level.upper(), logging.INFO),
        handlers=[handler],
    )

    # Verify Lychee connectivity
    lychee_up_url = f"{settings.lychee_api_url}/up"
    logger.info("Checking Lychee connectivity at %s", lychee_up_url)
    try:
        async with httpx.AsyncClient(verify=settings.verify_ssl, timeout=10.0) as client:
            response = await client.get(lychee_up_url)
            response.raise_for_status()
            logger.info("✓ Lychee is reachable (status=%d)", response.status_code)
    except httpx.HTTPStatusError as e:
        logger.error("✗ Lychee /up endpoint returned status %d", e.response.status_code)
        raise RuntimeError(
            f"Lychee health check failed: /up returned {e.response.status_code}. "
            "Ensure VISION_FACE_LYCHEE_API_URL is correct and Lychee is running."
        ) from e
    except httpx.RequestError as e:
        logger.error("✗ Cannot connect to Lychee at %s: %s", lychee_up_url, e)
        raise RuntimeError(
            f"Cannot connect to Lychee at {lychee_up_url}. "
            "Ensure VISION_FACE_LYCHEE_API_URL is correct and Lychee is reachable."
        ) from e

    # Load detector
    from app.detection.detector import FaceDetector

    # Expose model_root as DEEPFACE_HOME before load() so deepface discovers cached weights.
    # deepface reads DEEPFACE_HOME lazily (via get_deepface_home() on each access), not at
    # import time, so setting it here — before detector.load() — is reliable.
    # In Docker deployments, ENV DEEPFACE_HOME is also set in the Dockerfile as a fallback.
    os.environ["DEEPFACE_HOME"] = settings.model_root

    detector = FaceDetector(
        model_name=settings.model_name,
        detection_threshold=settings.detection_threshold,
        blur_threshold=settings.blur_threshold,
        detector_backend=settings.detector_backend,
        min_face_size_pixels=settings.min_face_size_pixels,
    )
    detector.load()
    logger.info("DeepFace model '%s' (backend: %s) loaded successfully", settings.model_name, settings.detector_backend)

    # Initialise embedding store
    from app.embeddings.factory import create_store

    store = create_store(settings)
    logger.info(
        "Embedding store initialised (backend=%s, count=%d)",
        settings.storage_backend,
        store.count(),
    )

    # Thread pool for CPU-bound inference
    executor = ThreadPoolExecutor(max_workers=settings.thread_pool_size)

    app.state.detector = detector
    app.state.store = store
    app.state.executor = executor

    yield

    executor.shutdown(wait=False)
    logger.info("AI Vision Service shut down")


def create_app(lifespan: Any = None) -> FastAPI:
    """Create and configure the FastAPI application.

    Args:
        lifespan: Optional async context manager to use as the application
            lifespan.  When ``None``, :func:`_default_lifespan` is used.
            Override in tests to inject mock state without loading the model.

    Returns:
        A configured :class:`fastapi.FastAPI` instance.
    """
    used_lifespan = lifespan if lifespan is not None else _default_lifespan

    application = FastAPI(
        title="Lychee AI Vision Service",
        description="Facial recognition microservice for Lychee photo gallery.",
        version="0.1.0",
        lifespan=used_lifespan,
    )
    application.include_router(router)
    return application


# Module-level app instance used by uvicorn when started via the Dockerfile CMD.
app: FastAPI = create_app()
