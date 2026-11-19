# Open Questions – Feature 073

Open questions for [Feature 073](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-073-08~~ | 073 – Size-Variant Format | Low | After the maintainer's refactor of `BaseSizeVariantNamingStrategy` (commit a9d34495), `SizeVariantFormat::extension()` has no caller. Delete it, route the new `match` through it, or keep it? | Resolved (Option A — delete; the maintainer's `generatedExtension()` owns the mapping; owner, 2026-09-27) | 2026-09-27 | 2026-09-27 |
| ~~Q-073-07~~ | 073 – Size-Variant Format | Medium | Review of PR #4790: `IMG_WEBP_LOSSLESS` exists only when libgd defines `gdWebpLossless` (libgd ≥ 2.3.3), while `imagewebp()` exists with any WebP-enabled GD (PHP allows external libgd ≥ 2.1.0). With quality `0` + `webp` on such builds, GD raises `Undefined constant`. How should GD behave? | Resolved (Option C — clear `MediaFileOperationException` plus a `GDSupportCheck` warning; owner, 2026-09-26; FR-073-07, FR-073-11) | 2026-09-26 | 2026-09-26 |
| ~~Q-073-06~~ | 073 – Size-Variant Format | Medium | Review of PR #4790 (CodeRabbit): with `size_variant_format = jpeg`, GD still writes PNG bytes into `.jpeg` files for PNG originals (the Q-073-04 quirk now reaches `small`/`medium` too, and the setting explicitly promises JPEG). Revisit Q-073-04? | Resolved (Option A — GD encodes by target extension for every format; owner, 2026-09-26: "a mismatch between extension and real format is worse than any other possible problem"; supersedes Q-073-04; FR-073-09) | 2026-09-26 | 2026-09-26 |
| ~~Q-073-05~~ | 073 – Size-Variant Format | Medium | Upstream issue LycheeOrg/Lychee#1888 asks for a **per-size** quality (e.g. medium 90 / small 80 / thumb 70); Feature 073 ships one global quality (N-073-06). Extend 073, or ship 073 as a partial answer and do per-size quality as a follow-up? | Resolved (Option A — ship 073 as is, PR references #1888 as partially addressed, per-size quality is a follow-up; owner, 2026-09-26: "Выбираю А") | 2026-09-26 | 2026-09-26 |
| ~~Q-073-01~~ | 073 – Size-Variant Format | Medium | Where does "lossless" live: reuse `compression_quality` with `0` = lossless, or add a dedicated WebP quality key? | Resolved directly (Option A — reuse `compression_quality`, re-typed `positive` → `int:0:100`; follows the owner's own suggestion "accept 0 … as lossless"; FR-073-06) | 2026-09-26 | 2026-09-26 |
| ~~Q-073-02~~ | 073 – Size-Variant Format | Medium | What does `compression_quality = 0` mean for formats with no lossless mode (JPEG, auto-rotated originals, HEIC)? | Resolved directly (Option A — encode at max quality `100`; FR-073-08) | 2026-09-26 | 2026-09-26 |
| ~~Q-073-03~~ | 073 – Size-Variant Format | Medium | Default for `size_variant_format`: `original` (no change on upgrade) or `webp`? | Resolved directly (Option A — `original`; upgrade is a no-op until an admin opts in; FR-073-01/04) | 2026-09-26 | 2026-09-26 |
| ~~Q-073-04~~ | 073 – Size-Variant Format | Low | GD encodes by **source** type, not target extension (pre-existing: a PNG original yields PNG bytes inside a `.jpeg` thumb). Fix fully, or only route `.webp` targets? | Superseded by Q-073-06 (Option A — GD now encodes by target extension for every format) | 2026-09-26 | 2026-09-26 |

## Question Details

### ~~Q-073-08~~ · Unused `SizeVariantFormat::extension()` ✅ RESOLVED

**Status:** Resolved by the owner, 2026-09-27 — Option A.  
**Feature:** F-073  
**Priority:** Low

- **Option A (chosen) — delete the method.** Dead code, and the maintainer's `generatedExtension()` is now the single place that maps the enum to `.jpeg`/`.webp`.
- **Option B — use it inside `generatedExtension()`.** It would keep the mapping on the enum, but it rewrites code the maintainer had just refactored.
- **Option C — keep it unused.** Dead code that reviewers or tooling would flag later.

---

### ~~Q-073-07~~ · Lossless WebP on GD builds without `IMG_WEBP_LOSSLESS` ✅ RESOLVED

**Status:** Resolved by the owner, 2026-09-26 — Option C. Encoded in spec FR-073-07 (failure path) and FR-073-11. The warning was later extended to `size_variant_format = original` too, because small/medium of a WebP upload keep `.webp` (automated review of PR #4790, 2026-09-27).  
**Feature:** F-073  
**Priority:** Medium

Raised by the automated review on LycheeOrg/Lychee#4790 and verified against php-src PHP-8.4 (`ext/gd/gd.stub.php`: the constant sits under `#ifdef gdWebpLossless`, `imagewebp()` under `#ifdef HAVE_GD_WEBP`) and libgd (`gdWebpLossless` first appears in 2.3.3; `config.m4` accepts external `gdlib >= 2.1.0`). The earlier reply on the PR, which said the constant is only missing without WebP support, was wrong.

- **Option A (recommended) — throw a clear `MediaFileOperationException('Lossless WebP encoding is not supported by this GD build')`, as the reviewer suggested.** One line, and the failure is explicit. The admin sees exactly why and can set quality 1–100 or enable Imagick.
- **Option B — fall back to lossy quality 100 and log a warning.** Uploads keep working, but the admin asked for lossless and silently gets lossy output. This is also a fallback AGENTS.md discourages unless requested.
- **Option C — Option A plus a diagnostics warning (`GDSupportCheck`) when `compression_quality = 0`, `size_variant_format = webp`, and `IMG_WEBP_LOSSLESS` is undefined.** The problem shows on the diagnostics page before uploads fail. It needs a few more lines and a test.

---

### ~~Q-073-06~~ · GD writes PNG bytes into `.jpeg` variants (revisits Q-073-04) ✅ RESOLVED

**Status:** Resolved by the owner, 2026-09-26 — Option A. Encoded in spec FR-073-09; N-073-04 withdrawn. Supersedes Q-073-04.  
**Feature:** F-073  
**Priority:** Medium

Raised by the automated review on LycheeOrg/Lychee#4790. Q-073-04 kept GD's source-type dispatch for non-WebP targets. With the new `jpeg` format, though, a PNG original yields PNG bytes in `.jpeg` small/medium files, not just in thumbs, even though the setting explicitly promises JPEG. Consumers that trust the extension or MIME type can reject such files.

- **Option A (recommended) — GD dispatches on the target extension for every format (`.jpg/.jpeg/.png/.gif/.webp`), falling back to the source type for unknown extensions.** This is Q-073-04 Option B. It is a small table lookup, and the test can then assert the JPEG signature. Behaviour change: on GD installs, the `.jpeg` thumbs of PNG/GIF/WebP originals become real JPEGs and lose transparency. Imagick already behaves this way (it writes by extension), and it is the handler in the official Docker image.
- **Option B — keep Q-073-04 and reply on the PR that it is a known pre-existing GD limitation, out of scope.** No behaviour change, but the `jpeg` setting stays wrong on GD.
- **Option C — Option A plus flattening transparency onto white for JPEG targets.** Nicer output for transparent PNGs, but more code, and it would diverge from Imagick's behaviour.

---

### ~~Q-073-05~~ · Per-size quality requested by issue #1888 ✅ RESOLVED

**Status:** Resolved by the owner, 2026-09-26 — Option A. Encoded in spec N-073-06 and plan Follow-ups.  
**Feature:** F-073 – Configurable Size-Variant Format and Quality  
**Priority:** Medium

[LycheeOrg/Lychee#1888](https://github.com/LycheeOrg/Lychee/issues/1888) (open, labelled "Project for volunteers") asks for different quality per size family and notes that WebP quality cannot be set. The maintainer pointed volunteers to `app/Image/Handlers`. Feature 073 covers the WebP part (format + quality + lossless), but not per-size quality.

- **Option A (recommended) — ship 073 as is, referencing #1888 as partially addressed; per-size quality becomes a separate follow-up feature.** The PR stays small and reviewable (18 files, no interface change). The maintainer doubted per-size quality is worth it, and switching the format to WebP saves more than 90→80 JPEG. Con: the issue stays open.
- **Option B — extend 073 with `compression_quality_thumb`/`_small`/`_medium` (`int:0:100`, each applying to its 2x too).** This closes #1888. Con: `save()` does not know the variant type, so the quality has to be passed from `SizeVariantDefaultFactory` into the handler (`ImageHandlerInterface::save()` signature or a setter on the clone). That touches every `save()` caller (upload, rotate, watermark), adds three configs, and adds tests.
- **Option C — Option B with a fallback: an empty per-size value inherits the global `compression_quality`.** Most flexible, but it needs a new "empty or int" config type and more branching for little gain.

---

### ~~Q-073-01~~ · Where does "lossless" live? ✅ RESOLVED DIRECTLY

**Status:** Resolved directly, 2026-09-26 — Option A. Encoded in spec FR-073-06.  
**Feature:** F-073 – Configurable Size-Variant Format and Quality  
**Priority:** Medium

- **Option A (chosen) — reuse `compression_quality`, re-type to `int:0:100`, `0` = lossless.** One knob, as the owner asked ("the settings should configure both the format and the quality … accept 0 or lossless as lossless"). `int:min:max` is already an established `type_range` with validation (`Configs::sanity()`) and a bounded number widget (`ConfigGroup.vue`), so it needs no new code. It also caps the old unbounded `positive` type at 100.
  Con: `0` has to mean something for formats that cannot be lossless (see Q-073-02).
- **Option B — a new `webp_quality` key (`int:0:100`), leaving `compression_quality` as is.** Keeps JPEG semantics untouched.
  Con: two quality knobs, and admins must know which one applies when.

---

### ~~Q-073-02~~ · `compression_quality = 0` for formats without a lossless mode ✅ RESOLVED DIRECTLY

**Status:** Resolved directly, 2026-09-26 — Option A. Encoded in spec FR-073-08.  
**Feature:** F-073  
**Priority:** Medium

- **Option A (chosen) — encode at quality `100`.** Closest honest meaning of "lossless" for a lossy codec. It also keeps auto-rotated originals (which go through the same `save()`) from being encoded at quality 0.
- **Option B — refuse `0` unless the format is `webp`.** It is not expressible: `Configs::sanity()` validates one key at a time.

---

### ~~Q-073-03~~ · Default `size_variant_format` ✅ RESOLVED DIRECTLY

**Status:** Resolved directly, 2026-09-26 — Option A. Encoded in spec FR-073-01/FR-073-04.  
**Feature:** F-073  
**Priority:** Medium

- **Option A (chosen) — `original`.** Byte-identical naming to pre-feature behaviour, so upgrading is a no-op. This suits an upstream contribution.
- **Option B — `webp`.** Smaller files by default, but it changes output for every installation on upgrade.

---

### ~~Q-073-04~~ · GD encodes by source type, not target extension ✅ RESOLVED DIRECTLY

**Status:** Superseded by Q-073-06, 2026-09-26. Option B was adopted after review of the pull request.  
**Feature:** F-073  
**Priority:** Low

`GdHandler::save()` dispatches on `$this->gd_image_type` (the source type). Today, a PNG original therefore yields PNG bytes in a `.jpeg` thumb. Browsers sniff the content, so nobody noticed.

- **Option A (chosen) — route only `.webp` targets to `imagewebp()` and leave the rest untouched.** This is the smallest change that makes FR-073-02 true on GD.
- **Option B — dispatch on the target extension for every format.** This is the correct long-term fix, but it changes existing GD output: transparent PNGs become JPEGs with a black background unless flattening is added. Out of scope; left as a follow-up.
