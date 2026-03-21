"""Face crop generation using Pillow.

Produces 150 x 150 JPEG crops, base64-encoded, ready to embed in JSON payloads.
"""

from __future__ import annotations

import base64
import io
from typing import TYPE_CHECKING

from PIL import Image

if TYPE_CHECKING:
    from pathlib import Path

CROP_SIZE: int = 150
"""Output crop dimensions in pixels (square)."""

_PADDING_FACTOR: float = 0.15
"""Fractional padding added around each bounding box side before cropping."""


def generate_crop(image_path: Path, x: float, y: float, width: float, height: float) -> str:
    """Generate a base64-encoded 150 x 150 JPEG face crop.

    Args:
        image_path: Absolute path to the source image.
        x: Normalised left edge of the bounding box (0.0-1.0).
        y: Normalised top edge of the bounding box (0.0-1.0).
        width: Normalised bounding-box width (0.0-1.0).
        height: Normalised bounding-box height (0.0-1.0).

    Returns:
        Base64-encoded JPEG bytes (ASCII string).
    """
    img = Image.open(image_path).convert("RGB")
    img_w, img_h = img.size

    # Absolute pixel coordinates
    abs_x = x * img_w
    abs_y = y * img_h
    abs_w = width * img_w
    abs_h = height * img_h

    # Add padding
    pad_x = abs_w * _PADDING_FACTOR
    pad_y = abs_h * _PADDING_FACTOR

    x1 = max(0.0, abs_x - pad_x)
    y1 = max(0.0, abs_y - pad_y)
    x2 = min(float(img_w), abs_x + abs_w + pad_x)
    y2 = min(float(img_h), abs_y + abs_h + pad_y)

    crop = img.crop((int(x1), int(y1), int(x2), int(y2)))
    crop = crop.resize((CROP_SIZE, CROP_SIZE), Image.Resampling.LANCZOS)

    buf = io.BytesIO()
    crop.save(buf, format="JPEG", quality=85)
    return base64.b64encode(buf.getvalue()).decode("ascii")


def generate_crop_from_bytes(image_bytes: bytes, x: float, y: float, width: float, height: float) -> str:
    """Generate a crop from raw image bytes (used in testing / matching).

    Args:
        image_bytes: Raw image file bytes.
        x: Normalised left edge of the bounding box (0.0-1.0).
        y: Normalised top edge of the bounding box (0.0-1.0).
        width: Normalised bounding-box width (0.0-1.0).
        height: Normalised bounding-box height (0.0-1.0).

    Returns:
        Base64-encoded JPEG bytes (ASCII string).
    """
    img = Image.open(io.BytesIO(image_bytes)).convert("RGB")
    img_w, img_h = img.size

    abs_x = x * img_w
    abs_y = y * img_h
    abs_w = width * img_w
    abs_h = height * img_h

    pad_x = abs_w * _PADDING_FACTOR
    pad_y = abs_h * _PADDING_FACTOR

    x1 = max(0.0, abs_x - pad_x)
    y1 = max(0.0, abs_y - pad_y)
    x2 = min(float(img_w), abs_x + abs_w + pad_x)
    y2 = min(float(img_h), abs_y + abs_h + pad_y)

    crop = img.crop((int(x1), int(y1), int(x2), int(y2)))
    crop = crop.resize((CROP_SIZE, CROP_SIZE), Image.Resampling.LANCZOS)

    buf = io.BytesIO()
    crop.save(buf, format="JPEG", quality=85)
    return base64.b64encode(buf.getvalue()).decode("ascii")
