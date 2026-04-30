"""Factory for creating the appropriate EmbeddingStore backend."""

from __future__ import annotations

from typing import TYPE_CHECKING

if TYPE_CHECKING:
    from app.config import AppSettings
    from app.embeddings.store import EmbeddingStore


def create_store(settings: AppSettings) -> EmbeddingStore:
    """Return a configured :class:`EmbeddingStore` for the given settings.

    Args:
        settings: Application settings (reads ``storage_backend``).

    Returns:
        A ready-to-use :class:`EmbeddingStore` instance.

    Raises:
        ValueError: If ``storage_backend`` is not ``"sqlite"`` or ``"pgvector"``.
    """
    backend = settings.storage_backend.lower()

    if backend == "sqlite":
        from app.embeddings.sqlite_store import SQLiteEmbeddingStore

        return SQLiteEmbeddingStore(storage_path=settings.storage_path)

    if backend == "pgvector":
        from app.embeddings.pgvector_store import PgVectorEmbeddingStore

        return PgVectorEmbeddingStore(
            host=settings.pg_host,
            port=settings.pg_port,
            database=settings.pg_database,
            user=settings.pg_user,
            password=settings.pg_password,
        )

    raise ValueError(f"Unknown storage_backend {backend!r}. Expected 'sqlite' or 'pgvector'.")
