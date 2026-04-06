"""PostgreSQL + pgvector embedding storage backend.

Requires the ``pgvector`` extension to be installed in the target database.
Use this backend for production-scale deployments where SQLite throughput
is a bottleneck.

Table layout (single table, lychee_face_id as primary key):
  face_embeddings: lychee_face_id TEXT PK, embedding vector(512)
"""

from __future__ import annotations

import threading
from typing import Any

_EMBEDDING_DIM = 512
"""ArcFace (buffalo_l) embedding dimension."""


def _to_pg_vector(embedding: list[float]) -> str:
    """Serialise a float list to the pgvector literal format ``[a,b,c,...]``."""
    return "[" + ",".join(f"{v:.8f}" for v in embedding) + "]"


class PgVectorEmbeddingStore:
    """Embedding store backed by PostgreSQL + pgvector.

    Thread-safe: each method acquires an RLock to serialise access to the
    connection pool (single connection for simplicity - swap for a real pool
    in high-throughput deployments).
    """

    def __init__(
        self,
        host: str = "localhost",
        port: int = 5432,
        database: str = "ai_vision",
        user: str = "ai_vision",
        password: str = "",
    ) -> None:
        self._dsn = f"host={host} port={port} dbname={database} user={user} password={password}"
        self._lock = threading.RLock()
        self._conn: Any = None
        self._init_db()

    # ------------------------------------------------------------------
    # EmbeddingStore protocol
    # ------------------------------------------------------------------

    def add(
        self,
        lychee_face_id: str,
        embedding: list[float],
        photo_id: str,
        laplacian_variance: float,
        crop_path: str,
    ) -> None:
        """Upsert an embedding row."""
        vec_str = _to_pg_vector(embedding)
        with self._lock:
            conn = self._get_conn()
            with conn.cursor() as cur:
                cur.execute(
                    """
                    INSERT INTO face_embeddings (lychee_face_id, embedding, photo_id, laplacian_variance, crop_path)
                    VALUES (%s, %s::vector, %s, %s, %s)
                    ON CONFLICT (lychee_face_id) DO UPDATE
                        SET embedding = EXCLUDED.embedding,
                            photo_id = EXCLUDED.photo_id,
                            laplacian_variance = EXCLUDED.laplacian_variance,
                            crop_path = EXCLUDED.crop_path
                    """,
                    (lychee_face_id, vec_str, photo_id, laplacian_variance, crop_path),
                )
            conn.commit()

    def delete(self, lychee_face_id: str) -> None:
        """Remove an embedding by Lychee Face ID."""
        with self._lock:
            conn = self._get_conn()
            with conn.cursor() as cur:
                cur.execute(
                    "DELETE FROM face_embeddings WHERE lychee_face_id = %s",
                    (lychee_face_id,),
                )
            conn.commit()

    def similarity_search(
        self,
        embedding: list[float],
        threshold: float,
        limit: int = 10,
    ) -> list[tuple[str, float]]:
        """Cosine-similarity search using pgvector's ``<=>`` operator."""
        vec_str = _to_pg_vector(embedding)
        with self._lock:
            conn = self._get_conn()
            with conn.cursor() as cur:
                cur.execute(
                    """
                    WITH q AS (SELECT %s::vector AS qvec)
                    SELECT f.lychee_face_id,
                           1.0 - (f.embedding <=> q.qvec) AS similarity
                    FROM face_embeddings f, q
                    WHERE 1.0 - (f.embedding <=> q.qvec) >= %s
                    ORDER BY f.embedding <=> q.qvec
                    LIMIT %s
                    """,
                    (vec_str, threshold, limit),
                )
                rows: list[Any] = cur.fetchall()
        return [(row[0], float(row[1])) for row in rows]

    def delete_many(self, lychee_face_ids: list[str]) -> int:
        """Remove multiple embeddings by Lychee Face ID."""
        if not lychee_face_ids:
            return 0
        with self._lock:
            conn = self._get_conn()
            with conn.cursor() as cur:
                # Use ANY(%s) with a list parameter for batch delete
                cur.execute(
                    "DELETE FROM face_embeddings WHERE lychee_face_id = ANY(%s)",
                    (lychee_face_ids,),
                )
                deleted: int = cur.rowcount
            conn.commit()
        return deleted

    def get_all(self) -> list[tuple[str, list[float]]]:
        """Return all stored embeddings as (face_id, embedding) pairs."""
        with self._lock:
            conn = self._get_conn()
            with conn.cursor() as cur:
                cur.execute("SELECT lychee_face_id, embedding FROM face_embeddings")
                rows: list[Any] = cur.fetchall()
        return [(row[0], [float(v) for v in row[1]]) for row in rows]

    def get_all_with_metadata(self) -> list[dict[str, str | float | None]]:
        """Return all stored embeddings with metadata."""
        with self._lock:
            conn = self._get_conn()
            with conn.cursor() as cur:
                cur.execute(
                    "SELECT lychee_face_id, photo_id, laplacian_variance, crop_path FROM face_embeddings"
                )
                rows: list[Any] = cur.fetchall()
        results: list[dict[str, str | float | None]] = []
        for row in rows:
            results.append({
                "lychee_face_id": row[0],
                "photo_id": row[1],
                "laplacian_variance": float(row[2]) if row[2] is not None else None,
                "crop_path": row[3],
            })
        return results

    def count(self) -> int:
        """Return the total number of stored embeddings."""
        with self._lock:
            conn = self._get_conn()
            with conn.cursor() as cur:
                cur.execute("SELECT COUNT(*) FROM face_embeddings")
                row: Any = cur.fetchone()
        return int(row[0]) if row else 0

    # ------------------------------------------------------------------
    # Internal helpers
    # ------------------------------------------------------------------

    def _get_conn(self) -> Any:
        """Return the shared connection, reconnecting if needed."""
        import psycopg2

        if self._conn is None or self._conn.closed:
            self._conn = psycopg2.connect(self._dsn)
        return self._conn

    def _init_db(self) -> None:
        """Create the table and index if they do not already exist."""
        with self._lock:
            conn = self._get_conn()
            with conn.cursor() as cur:
                cur.execute("CREATE EXTENSION IF NOT EXISTS vector")
                cur.execute(
                    f"""
                    CREATE TABLE IF NOT EXISTS face_embeddings (
                        lychee_face_id TEXT PRIMARY KEY,
                        embedding      vector({_EMBEDDING_DIM}) NOT NULL,
                        photo_id       TEXT,
                        laplacian_variance REAL,
                        crop_path      TEXT
                    )
                    """
                )
                # IVFFlat index for approx nearest-neighbour search.
                # ``lists`` should be tuned (~sqrt of row count).
                cur.execute(
                    """
                    CREATE INDEX IF NOT EXISTS face_embeddings_embedding_cosine_idx
                    ON face_embeddings USING ivfflat (embedding vector_cosine_ops)
                    WITH (lists = 100)
                    """
                )
            conn.commit()
