"""FastAPI application factory.

Entry point for the AI Vision Service.  The ``create_app`` factory accepts an
optional ``lifespan`` parameter so that tests can inject a custom lifespan
context that pre-populates ``app.state`` with mock objects instead of loading
the real InsightFace model.
"""

from __future__ import annotations

import logging
import warnings
from concurrent.futures import ThreadPoolExecutor
from contextlib import asynccontextmanager
from typing import TYPE_CHECKING, Any

import httpx
from fastapi import FastAPI

from app.api.routes import router
from app.config import AppSettings, get_settings

# insightface uses the deprecated SimilarityTransform.estimate() API from scikit-image >=0.26.
# Suppress until insightface ships a fix upstream.
warnings.filterwarnings(
    "ignore",
    message=r"`estimate` is deprecated",
    category=FutureWarning,
    module=r"skimage",
)

if TYPE_CHECKING:
    from collections.abc import AsyncGenerator

logger = logging.getLogger(__name__)


@asynccontextmanager
async def _default_lifespan(app: FastAPI) -> AsyncGenerator[None]:
    """Production lifespan: load the face model and initialise the store."""
    settings: AppSettings = get_settings()

    # Configure logging
    logging.basicConfig(level=getattr(logging, settings.log_level.upper(), logging.INFO))

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

    detector = FaceDetector(
        model_name=settings.model_name,
        detection_threshold=settings.detection_threshold,
        blur_threshold=settings.blur_threshold,
    )
    detector.load()
    logger.info("InsightFace model '%s' loaded successfully", settings.model_name)

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
