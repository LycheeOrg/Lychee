# Multi-stage build: keep the runtime image lean.

# ---------------------------------------------------------------------------
# Stage 1 – builder: install dependencies and bake the model weights.
# ---------------------------------------------------------------------------
FROM python:3.13-slim@sha256:739e7213785e88c0f702dcdc12c0973afcbd606dbf021a589cab77d6b00b579d AS builder

# Install uv from the official image.
COPY --from=ghcr.io/astral-sh/uv:latest /uv /usr/local/bin/uv

# Runtime libraries required by opencv-python.
RUN apt-get update && apt-get install -y --no-install-recommends \
        libgl1 \
        libglib2.0-0 \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Install dependencies only (no source code) so layer cache is reused when
# only application code changes.
COPY pyproject.toml uv.lock README.md ./
RUN uv sync --frozen --no-dev

# Bake ArcFace + RetinaFace model weights into the image at build time.
# The resulting image starts instantly and works in airgapped environments.
# Model updates require an image rebuild.
RUN DEEPFACE_HOME=/root/.deepface uv run python -c \
    "from deepface import DeepFace; \
     import numpy as np; \
     DeepFace.represent( \
         img_path=np.zeros((1, 1, 3), dtype='uint8'), \
         model_name='ArcFace', \
         detector_backend='retinaface', \
         enforce_detection=False, \
     ); \
     print('ArcFace + RetinaFace models downloaded.')"

# ---------------------------------------------------------------------------
# Stage 2 – runtime: copy only what's needed to run.
# ---------------------------------------------------------------------------
FROM python:3.13-slim@sha256:739e7213785e88c0f702dcdc12c0973afcbd606dbf021a589cab77d6b00b579d AS runtime

WORKDIR /app

# Copy the pre-built virtualenv and baked model weights from the builder stage.
COPY --from=builder /app/.venv /app/.venv
COPY --from=builder /root/.deepface /root/.deepface

# Copy application source.
COPY app/ ./app/

ENV PATH="/app/.venv/bin:$PATH"
ENV DEEPFACE_HOME=/root/.deepface

EXPOSE 8000

# Use a shell-form CMD so that the ${VISION_FACE_WORKERS:-1} variable is
# expanded at container startup, not at image build time.
CMD ["sh", "-c", "uvicorn app.main:app --host 0.0.0.0 --port 8000 --workers ${VISION_FACE_WORKERS:-1}"]
