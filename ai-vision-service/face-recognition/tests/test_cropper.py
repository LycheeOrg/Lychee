"""Tests for app.detection.cropper."""

from __future__ import annotations

import base64
import io

from PIL import Image

from app.detection.cropper import CROP_SIZE, generate_crop, generate_crop_from_bytes

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------


def _make_jpeg_bytes(width: int = 200, height: int = 200, color: tuple[int, int, int] = (255, 0, 0)) -> bytes:
    img = Image.new("RGB", (width, height), color=color)
    buf = io.BytesIO()
    img.save(buf, format="JPEG")
    return buf.getvalue()


# ---------------------------------------------------------------------------
# generate_crop
# ---------------------------------------------------------------------------


def test_crop_size_is_150x150(jpeg_image_path: object) -> None:
    """generate_crop must return a 150x150 JPEG regardless of input size."""
    from pathlib import Path

    path = jpeg_image_path  # type: ignore[assignment]
    result = generate_crop(Path(str(path)), x=0.0, y=0.0, width=0.5, height=0.5)

    decoded = base64.b64decode(result)
    img = Image.open(io.BytesIO(decoded))
    assert img.size == (CROP_SIZE, CROP_SIZE)


def test_crop_returns_base64_string(jpeg_image_path: object) -> None:
    """Output must be a valid base64-encoded ASCII string."""
    from pathlib import Path

    path = jpeg_image_path  # type: ignore[assignment]
    result = generate_crop(Path(str(path)), x=0.1, y=0.1, width=0.5, height=0.5)
    assert isinstance(result, str)
    # Must be valid base64
    base64.b64decode(result)


def test_crop_is_jpeg(jpeg_image_path: object) -> None:
    """Decoded bytes must be a JPEG image."""
    from pathlib import Path

    path = jpeg_image_path  # type: ignore[assignment]
    result = generate_crop(Path(str(path)), x=0.0, y=0.0, width=1.0, height=1.0)
    decoded = base64.b64decode(result)
    img = Image.open(io.BytesIO(decoded))
    assert img.format == "JPEG"


def test_crop_clamps_out_of_bounds_bbox(jpeg_image_path: object) -> None:
    """Bounding boxes that extend beyond image edges must be clamped."""
    from pathlib import Path

    path = jpeg_image_path  # type: ignore[assignment]
    # x + width > 1.0
    result = generate_crop(Path(str(path)), x=0.8, y=0.8, width=0.5, height=0.5)
    decoded = base64.b64decode(result)
    img = Image.open(io.BytesIO(decoded))
    assert img.size == (CROP_SIZE, CROP_SIZE)


def test_crop_full_image_bbox(jpeg_image_path: object) -> None:
    """Cropping the full image (0,0,1,1) must succeed."""
    from pathlib import Path

    path = jpeg_image_path  # type: ignore[assignment]
    result = generate_crop(Path(str(path)), x=0.0, y=0.0, width=1.0, height=1.0)
    decoded = base64.b64decode(result)
    img = Image.open(io.BytesIO(decoded))
    assert img.size == (CROP_SIZE, CROP_SIZE)


# ---------------------------------------------------------------------------
# generate_crop_from_bytes
# ---------------------------------------------------------------------------


def test_crop_from_bytes_matches_file_version(tmp_path: object) -> None:
    """generate_crop_from_bytes must produce the same output as generate_crop."""
    from pathlib import Path

    jpeg_bytes = _make_jpeg_bytes(200, 200, (0, 128, 255))

    # Write to disk
    img_path = Path(str(tmp_path)) / "img.jpg"
    img_path.write_bytes(jpeg_bytes)

    result_file = generate_crop(img_path, x=0.1, y=0.1, width=0.5, height=0.5)
    result_bytes = generate_crop_from_bytes(jpeg_bytes, x=0.1, y=0.1, width=0.5, height=0.5)

    # Sizes should match (exact byte equality may differ due to JPEG compression)
    img_a = Image.open(io.BytesIO(base64.b64decode(result_file)))
    img_b = Image.open(io.BytesIO(base64.b64decode(result_bytes)))
    assert img_a.size == img_b.size


def test_crop_from_bytes_150x150(jpeg_image_bytes: bytes) -> None:
    result = generate_crop_from_bytes(jpeg_image_bytes, x=0.0, y=0.0, width=0.5, height=0.5)
    decoded = base64.b64decode(result)
    img = Image.open(io.BytesIO(decoded))
    assert img.size == (CROP_SIZE, CROP_SIZE)
