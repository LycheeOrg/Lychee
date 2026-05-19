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


def _calculate_square_crop_coords(
    x: float, y: float, width: float, height: float, img_w: int, img_h: int, padding_factor: float
) -> tuple[int, int, int, int]:
    """Calculate square crop coordinates centered on the face bounding box.

    Attempts to create a square crop centered on the face. If the square would
    extend beyond image boundaries, it shifts the crop to fit. If the square is
    larger than the image dimensions, the crop will be the maximum square that
    fits within the image.

    Args:
        x: Normalised left edge of the bounding box (0.0-1.0).
        y: Normalised top edge of the bounding box (0.0-1.0).
        width: Normalised bounding-box width (0.0-1.0).
        height: Normalised bounding-box height (0.0-1.0).
        img_w: Image width in pixels.
        img_h: Image height in pixels.
        padding_factor: Fractional padding to add around the bounding box.

    Returns:
        Tuple of (x1, y1, x2, y2) defining a square crop region in absolute pixels.
    """
    # Convert to absolute pixels
    abs_x = x * img_w
    abs_y = y * img_h
    abs_w = width * img_w
    abs_h = height * img_h

    # Add padding
    pad_x = abs_w * padding_factor
    pad_y = abs_h * padding_factor

    padded_x = abs_x - pad_x
    padded_y = abs_y - pad_y
    padded_w = abs_w + 2 * pad_x
    padded_h = abs_h + 2 * pad_y

    # Determine square size (use the larger dimension)
    square_size = max(padded_w, padded_h)

    # Cap square size to image dimensions (can't crop larger than the image)
    max_possible_size = min(img_w, img_h)
    square_size = min(square_size, max_possible_size)

    # Calculate center point of the padded bounding box
    center_x = padded_x + padded_w / 2
    center_y = padded_y + padded_h / 2

    # Calculate square crop coordinates centered on the face
    x1 = center_x - square_size / 2
    y1 = center_y - square_size / 2
    x2 = center_x + square_size / 2
    y2 = center_y + square_size / 2

    # Adjust to keep square within image boundaries
    # If the square extends beyond the left edge, shift it right
    if x1 < 0:
        shift = -x1
        x1 = 0
        x2 = min(float(img_w), x2 + shift)
    # If the square extends beyond the right edge, shift it left
    if x2 > img_w:
        shift = x2 - img_w
        x2 = img_w
        x1 = max(0.0, x1 - shift)

    # If the square extends beyond the top edge, shift it down
    if y1 < 0:
        shift = -y1
        y1 = 0
        y2 = min(float(img_h), y2 + shift)
    # If the square extends beyond the bottom edge, shift it up
    if y2 > img_h:
        shift = y2 - img_h
        y2 = img_h
        y1 = max(0.0, y1 - shift)

    return int(x1), int(y1), int(x2), int(y2)


def _pad_to_square(img: Image.Image) -> Image.Image:
    """Pad a non-square image to square with black borders.

    Args:
        img: Input PIL Image.

    Returns:
        Square PIL Image with black padding if needed.
    """
    width, height = img.size
    if width == height:
        return img

    size = max(width, height)
    square_img = Image.new("RGB", (size, size), (0, 0, 0))
    paste_x = (size - width) // 2
    paste_y = (size - height) // 2
    square_img.paste(img, (paste_x, paste_y))
    return square_img


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

    # Calculate square crop coordinates
    x1, y1, x2, y2 = _calculate_square_crop_coords(x, y, width, height, img_w, img_h, _PADDING_FACTOR)

    # Crop and ensure it's square (pad if needed due to edge constraints)
    crop = img.crop((x1, y1, x2, y2))
    crop = _pad_to_square(crop)
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

    # Calculate square crop coordinates
    x1, y1, x2, y2 = _calculate_square_crop_coords(x, y, width, height, img_w, img_h, _PADDING_FACTOR)

    # Crop and ensure it's square (pad if needed due to edge constraints)
    crop = img.crop((x1, y1, x2, y2))
    crop = _pad_to_square(crop)
    crop = crop.resize((CROP_SIZE, CROP_SIZE), Image.Resampling.LANCZOS)

    buf = io.BytesIO()
    crop.save(buf, format="JPEG", quality=85)
    return base64.b64encode(buf.getvalue()).decode("ascii")
