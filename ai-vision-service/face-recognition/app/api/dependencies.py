"""FastAPI dependency providers.

Centralises per-request dependency resolution.  All functions follow
FastAPI's ``Depends()`` contract so they can be overridden in tests via
``app.dependency_overrides``.
"""

from __future__ import annotations

from typing import TYPE_CHECKING

from fastapi import Depends, Header, HTTPException, Request

from app.config import AppSettings, get_settings

if TYPE_CHECKING:
    from app.detection.detector import FaceDetector
    from app.embeddings.store import EmbeddingStore


async def require_api_key(
    x_api_key: str = Header(..., alias="X-API-Key"),
    settings: AppSettings = Depends(get_settings),
) -> None:
    """FastAPI dependency that validates the ``X-API-Key`` request header.

    Raises:
        HTTPException(401): If the header is missing or does not match
            ``VISION_FACE_API_KEY``.
    """
    if x_api_key != settings.api_key:
        raise HTTPException(status_code=401, detail="Invalid or missing API key")


def get_detector(request: Request) -> FaceDetector:
    """Return the :class:`FaceDetector` stored in ``app.state``."""
    return request.app.state.detector


def get_store(request: Request) -> EmbeddingStore:
    """Return the :class:`EmbeddingStore` stored in ``app.state``."""
    return request.app.state.store
