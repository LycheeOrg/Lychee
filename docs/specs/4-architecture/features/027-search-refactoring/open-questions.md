# Open Questions – Feature 027

Open questions for [Feature 027](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-027-01: Colour Distance Metric and Named-Colour Lookup~~ ✅ RESOLVED

**Decision:** `palette.colour_N` values are foreign keys to `colours.id` (the packed 0xRRGGBB integer); the `colours` table already has separate `R`, `G`, `B` integer columns. Use a JOIN `palette → colours ON colours.id IN (p.colour_1, …, p.colour_5)` and compute Manhattan distance directly on `colours.R/G/B`. No schema migration required. Named colours resolved via `Colour::fromHex()` / `colours` table lookup.  
**Rationale:** The separate R/G/B columns are already present in the DB; no bit-shift needed, fully portable across SQLite/MySQL/PostgreSQL.  
**Updated in spec:** FR-027-09 (colour query mechanism), NFR-027-04 (SQL portability note updated).

---

### ~~Q-027-02: Rating Filter — Own Rating vs Average Rating~~ ✅ RESOLVED

**Decision:** Option C — Support both sub-modifier forms: `rating:avg:>=4` (filters by `photos.rating_avg`) and `rating:own:>=4` (filters by the requesting user's own rating via JOIN on `photo_ratings WHERE user_id = Auth::id()`). Unauthenticated users may only use `rating:avg:`.  
**Rationale:** Maximum flexibility; users with personal rating habits benefit from `own:` while gallery visitors can still filter by average.  
**Updated in spec:** FR-027-14 (rating sub-modifiers), grammar reference updated, scenarios S-027-21/S-027-22 added.

---

### ~~Q-027-03: Album Search Modifier Support — This Feature or Follow-up?~~ ✅ RESOLVED

**Decision:** Option B — Include album modifier support (`title:`, `description:`, `date:`) in Feature 027. `AlbumSearch` is wired to the same `SearchTokenParser`; a new `AlbumSearchTokenStrategy` interface mirrors `PhotoSearchTokenStrategy`.  
**Rationale:** Consistent user experience in a single release; the token infrastructure from the photo search is directly reusable.  
**Updated in spec:** FR-027-15 (album modifiers), Non-Goals updated (album modifiers removed), scenarios S-027-23/S-027-24 added.

---

### ~~Q-027-04: Named-Colour Name→Hex Mapping Mechanism~~ ✅ RESOLVED

**Decision:** Option A — Hardcode a PHP `ColourNameMap` class (e.g. `app/Actions/Search/ColourNameMap.php`) containing a `const` array mapping lowercase CSS colour names to `#rrggbb` hex strings, covering the 16 basic CSS Level 1 colours. `ColourStrategy` consults this map when the token value does not start with `#`. Unknown names throw `InvalidTokenException` → HTTP 400. No schema migration required.  
**Rationale:** No DB dependency; stateless; testable in isolation; fast. The `colours` table has no `name` column and `Colour::fromHex()` only accepts hex strings, so a hardcoded PHP map is the only viable no-migration path.  
**Updated in spec:** FR-027-09 (named-colour resolution description updated), T-027-03 and T-027-22 notes updated.

---

### ~~Q-027-05: Invalid SQL Syntax in Colour-Similarity EXISTS Subquery~~ ✅ RESOLVED

**Decision:** Option A — Replace the invalid `JOIN … ON c.id IN (…)` with an explicit OR expansion in the `ON` clause:

```sql
EXISTS (
  SELECT 1 FROM palette p
  JOIN colours c ON (c.id = p.colour_1 OR c.id = p.colour_2 OR c.id = p.colour_3 OR c.id = p.colour_4 OR c.id = p.colour_5)
  WHERE p.photo_id = photos.id
    AND ABS(c.R - :R) + ABS(c.G - :G) + ABS(c.B - :B) <= :dist
)
```

**Rationale:** Standard SQL valid across SQLite, MySQL, and PostgreSQL. Within an `EXISTS` the five-OR join is harmless — multiple matching `colours` rows per palette row are irrelevant since `EXISTS` short-circuits on the first match.  
**Updated in spec:** FR-027-09, NFR-027-04 (both SQL snippets corrected); plan.md I7; tasks.md T-027-22.
