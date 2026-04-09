"""SQLite + sqlite-vec embedding storage backend.

Uses the sqlite-vec extension for approximate nearest-neighbour (ANN) search
over 512-dimensional float vectors. This is the default backend, suitable
for single-container deployments.

Table layout:
  vec_faces (vec0 virtual table) - stores raw float vectors; indexed by rowid.
  face_meta (regular table)      - stores lychee_face_id -> vec_rowid mapping.
"""

from __future__ import annotations

import sqlite3
import struct
import threading
from pathlib import Path

_EMBEDDING_DIM = 512
"""ArcFace (buffalo_l) embedding dimension."""


def _to_blob(embedding: list[float]) -> bytes:
    """Serialise a float list to a little-endian float32 byte blob."""
    return struct.pack(f"{len(embedding)}f", *embedding)


class SQLiteEmbeddingStore:
    """Embedding store backed by SQLite + sqlite-vec.

    Thread-safe: all write operations are protected by a reentrant lock.
    """

    def __init__(self, storage_path: str) -> None:
        """Initialise the store.

        Args:
            storage_path: Directory where the SQLite database file will be
                created (filename ``embeddings.db``).
        """
        db_dir = Path(storage_path)
        db_dir.mkdir(parents=True, exist_ok=True)
        self._db_path = str(db_dir / "embeddings.db")
        self._lock = threading.RLock()
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
        with self._lock:
            conn = self._connect()
            try:
                # Remove existing entry for this face (if any) before inserting.
                self._delete_internal(conn, lychee_face_id)
                cursor = conn.execute(
                    "INSERT INTO vec_faces(face_embedding) VALUES (?)",
                    [_to_blob(embedding)],
                )
                vec_rowid: int = cursor.lastrowid  # ty: ignore
                conn.execute(
                    "INSERT INTO face_meta(vec_rowid, lychee_face_id, photo_id, laplacian_variance, "
                    "crop_path) VALUES (?, ?, ?, ?, ?)",
                    [vec_rowid, lychee_face_id, photo_id, laplacian_variance, crop_path],
                )
                conn.commit()
            finally:
                conn.close()

    def delete(self, lychee_face_id: str) -> None:
        """Remove an embedding by Lychee Face ID."""
        with self._lock:
            conn = self._connect()
            try:
                self._delete_internal(conn, lychee_face_id)
                conn.commit()
            finally:
                conn.close()

    def similarity_search(
        self,
        embedding: list[float],
        threshold: float,
        limit: int = 10,
    ) -> list[tuple[str, float]]:
        """KNN cosine-similarity search.

        Fetches up to ``limit * 5`` candidates from vec0 (to allow for
        threshold filtering) and returns at most ``limit`` results that
        exceed ``threshold``.
        """
        conn = self._connect()
        try:
            k = limit * 5  # over-fetch so we have headroom after threshold filter
            rows = conn.execute(
                """
                SELECT m.lychee_face_id, v.distance
                FROM vec_faces v
                JOIN face_meta m ON m.vec_rowid = v.rowid
                WHERE v.face_embedding MATCH ? AND k = ?
                ORDER BY v.distance
                """,
                [_to_blob(embedding), k],
            ).fetchall()

            results: list[tuple[str, float]] = []
            for lychee_face_id, distance in rows:
                similarity = 1.0 - float(distance)
                if similarity >= threshold:
                    results.append((lychee_face_id, similarity))
                if len(results) >= limit:
                    break
            return results
        finally:
            conn.close()

    def delete_many(self, lychee_face_ids: list[str]) -> int:
        """Remove multiple embeddings by Lychee Face ID."""
        if not lychee_face_ids:
            return 0
        deleted = 0
        with self._lock:
            conn = self._connect()
            try:
                for fid in lychee_face_ids:
                    row = conn.execute(
                        "SELECT vec_rowid FROM face_meta WHERE lychee_face_id = ?",
                        [fid],
                    ).fetchone()
                    if row is not None:
                        conn.execute("DELETE FROM vec_faces WHERE rowid = ?", [row[0]])
                        conn.execute("DELETE FROM face_meta WHERE lychee_face_id = ?", [fid])
                        deleted += 1
                conn.commit()
            finally:
                conn.close()
        return deleted

    def get_all(self) -> list[tuple[str, list[float]]]:
        """Return all stored embeddings as (face_id, embedding) pairs."""
        conn = self._connect()
        try:
            rows = conn.execute(
                """
                SELECT m.lychee_face_id, v.face_embedding
                FROM face_meta m
                JOIN vec_faces v ON v.rowid = m.vec_rowid
                """,
            ).fetchall()
            results: list[tuple[str, list[float]]] = []
            for lychee_face_id, blob in rows:
                count = len(blob) // 4  # float32 = 4 bytes
                embedding = list(struct.unpack(f"{count}f", blob))
                results.append((lychee_face_id, embedding))
            return results
        finally:
            conn.close()

    def get_all_with_metadata(self) -> list[dict[str, str | float | None]]:
        """Return all stored embeddings with metadata."""
        conn = self._connect()
        try:
            rows = conn.execute(
                """
                SELECT lychee_face_id, photo_id, laplacian_variance, crop_path
                FROM face_meta
                """,
            ).fetchall()
            results: list[dict[str, str | float | None]] = []
            for lychee_face_id, photo_id, laplacian_variance, crop_path in rows:
                results.append(
                    {
                        "lychee_face_id": lychee_face_id,
                        "photo_id": photo_id,
                        "laplacian_variance": laplacian_variance,
                        "crop_path": crop_path,
                    }
                )
            return results
        finally:
            conn.close()

    def count(self) -> int:
        """Return the number of stored embeddings."""
        conn = self._connect()
        try:
            row = conn.execute("SELECT COUNT(*) FROM face_meta").fetchone()
            return int(row[0]) if row else 0
        finally:
            conn.close()

    def count_by_photo_id(self, photo_id: str) -> int:
        """Count how many faces have been stored for a given photo."""
        conn = self._connect()
        try:
            row = conn.execute(
                "SELECT COUNT(*) FROM face_meta WHERE photo_id = ?",
                [photo_id],
            ).fetchone()
            return int(row[0]) if row else 0
        finally:
            conn.close()

    # ------------------------------------------------------------------
    # Internal helpers
    # ------------------------------------------------------------------

    def _connect(self) -> sqlite3.Connection:
        """Open a new SQLite connection with sqlite-vec loaded."""
        import sqlite_vec

        conn = sqlite3.connect(self._db_path, check_same_thread=False)
        conn.enable_load_extension(True)
        sqlite_vec.load(conn)
        conn.enable_load_extension(False)
        return conn

    def _init_db(self) -> None:
        """Create tables if they do not already exist."""
        with self._lock:
            conn = self._connect()
            try:
                conn.execute(
                    f"CREATE VIRTUAL TABLE IF NOT EXISTS vec_faces USING vec0(face_embedding float[{_EMBEDDING_DIM}])"
                )
                conn.execute(
                    """
                    CREATE TABLE IF NOT EXISTS face_meta (
                        vec_rowid  INTEGER UNIQUE NOT NULL,
                        lychee_face_id TEXT PRIMARY KEY NOT NULL,
                        photo_id TEXT,
                        laplacian_variance REAL,
                        crop_path TEXT
                    )
                    """
                )
                conn.commit()
            finally:
                conn.close()

    def _delete_internal(self, conn: sqlite3.Connection, lychee_face_id: str) -> None:
        """Delete an entry without committing (caller must commit)."""
        row = conn.execute(
            "SELECT vec_rowid FROM face_meta WHERE lychee_face_id = ?",
            [lychee_face_id],
        ).fetchone()
        if row is not None:
            conn.execute("DELETE FROM vec_faces WHERE rowid = ?", [row[0]])
            conn.execute("DELETE FROM face_meta WHERE lychee_face_id = ?", [lychee_face_id])
