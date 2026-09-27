# Open Questions – Feature 032

Open questions for [Feature 032](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-032-01: Advisory URL Field Missing from DTO/Resource~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** High  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option B** — Construct GitHub URL from GHSA ID. GitHub advisory URLs follow the pattern `https://github.com/advisories/{ghsa_id}`. The frontend computes the URL from the existing `ghsa_id` field. No DTO/Resource changes needed.

**Spec Impact:** Updated FR-032-06 to clarify that advisory links are computed client-side from `ghsa_id`. Updated UI-032-02 (modal) and UI-032-04 (diagnostic panel) to note clickable links. Updated tasks T-032-13, T-032-14 to include link rendering logic.

**Resolved:** 2026-04-06

---

### ~~Q-032-02: "Go to Diagnostics" Button Dismissal Behavior~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** High  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option A** — "Go to Diagnostics" also sets dismissal flag. Clicking "Go to Diagnostics" navigates to the diagnostics page **and** sets `sessionStorage.advisory_dismissed = '1'`. Modal does not re-appear in the same session. Consistent with "once per session" intent.

**Spec Impact:** Updated FR-032-07 to clarify both buttons set the dismissal flag. Updated UI-032-03 (modal dismissed state). Updated tasks T-032-13, T-032-14.

**Resolved:** 2026-04-06

---

### ~~Q-032-03: Advisory with Null/Missing `vulnerable_version_range`~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** Medium  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option A** — Treat null/empty range as "matches all versions". If `vulnerable_version_range` is null or empty, consider the advisory applicable to all Lychee versions and include it in the diagnostic/modal output. Conservative approach ensures critical advisories aren't missed.

**Spec Impact:** Added clause to FR-032-03: "If `vulnerable_version_range` is null or an empty string, the version range check passes (advisory matches all versions)." Updated tasks T-032-03 (VersionRangeChecker tests), T-032-07 (SecurityAdvisoriesService tests).

**Resolved:** 2026-04-06

---

### ~~Q-032-04: Multiple Vulnerability Ranges Per Advisory~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** Medium  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option A** — Deduplicate by advisory ID; show once. If one or more `vulnerabilities[].vulnerable_version_range` entries match the running version, include the advisory **once** in the results. Clean UX; no duplicate entries.

**Spec Impact:** Added clause to FR-032-03: "A single advisory with multiple matching `vulnerabilities[]` entries is included once (deduplicated by `ghsa_id`)." Updated tasks T-032-07 (SecurityAdvisoriesService deduplication logic), T-032-08 (SecurityAdvisoriesCheck single-entry behavior).

**Resolved:** 2026-04-06

---

### ~~Q-032-05: CVE/GHSA Display Format When `cve_id` is Null~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** Medium  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option A** — "GHSA-xxxx-xxxx-xxxx  CVSS {score}". Use the same format as CVE, but with the GHSA ID when `cve_id` is null. Consistent format; GHSA is GitHub's canonical ID.

**Spec Impact:** Updated FR-032-04 diagnostic error message format: "Security vulnerability: {cve_id ?? ghsa_id} (CVSS {score})". Updated UI-032-02, UI-032-04 mock-ups to show GHSA format example. Updated tasks T-032-08, T-032-13.

**Resolved:** 2026-04-06

---

### ~~Q-032-06: Diagnostic Panel Advisory Ordering~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** Medium  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Custom Option D** — Sort by CVSS score descending (highest severity first), then by CVE ID descending (higher = more recent). Sort matched advisories by `cvss_score DESC NULLS LAST, cve_id DESC NULLS LAST` before adding diagnostic entries and returning API results.

**Spec Impact:** Updated FR-032-04 to specify sorting: "Matching advisories are sorted by `cvss_score DESC NULLS LAST, cve_id DESC NULLS LAST` before being added to the diagnostic pipeline." Updated tasks T-032-07 (SecurityAdvisoriesService sorting), T-032-08 (SecurityAdvisoriesCheck ordered output).

**Resolved:** 2026-04-06

---

### ~~Q-032-07: CVSS Score Display Precision~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** Low  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option A** — Always format to 1 decimal place; show "(no CVSS score)" when null. Use `number_format($score, 1)` in PHP / `.toFixed(1)` in TypeScript. Consistent visual format.

**Spec Impact:** Updated FR-032-04 diagnostic error format and UI-032-02/UI-032-04 to specify 1-decimal formatting. Added note: "When `cvss_score` is null, display '(no CVSS score)' instead of the score." Updated tasks T-032-08, T-032-13.

**Resolved:** 2026-04-06

---

### ~~Q-032-08: Cache Invalidation — No Admin Force-Refresh Path~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** Low  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option C** — No force-refresh; cache TTL is acceptable. Accept the 1-day delay as tolerable. Operators who need faster updates can reduce `ADVISORIES_CACHE_TTL_DAYS` to hours (e.g., `0.042` for 1 hour). Force-refresh feature tracked as follow-up in plan backlog (CLI command or API endpoint).

**Spec Impact:** No spec changes required. Documented as follow-up in plan.md backlog.

**Resolved:** 2026-04-06

---

### ~~Q-032-09: Modal Trigger Timing — "On Each Admin Login" Ambiguous~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** Low  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option A** — After successful `POST /login` response only. The advisory check fires immediately after the login API call returns a successful response with `is_admin = true`. Does not fire on page refresh or navigation if the user is already logged in. **Important:** Check user access rights (`is_admin`) before calling the advisory endpoint to avoid a 403 response.

**Spec Impact:** Updated FR-032-06 to clarify: "The frontend checks `GET /api/v2/Security/Advisories` immediately after a successful login response (POST /login) when `is_admin` is true. The check does not fire on page refresh or navigation for already-authenticated users." Updated tasks T-032-14 to emphasize the admin rights check before the API call.

**Resolved:** 2026-04-06

---

### ~~Q-032-10: sessionStorage Multi-Tab Behavior~~ ✅ RESOLVED

**Feature:** 032 – Security Advisories Check  
**Priority:** Low  
**Status:** Resolved  
**Opened:** 2026-04-06

**Resolution:** **Option A** — Keep `sessionStorage` (per-tab dismissal). Each tab tracks dismissal independently. Opening a new tab shows the modal again (if advisories are present). Matches spec as written; ensures advisory is seen across different browser contexts. Note: This behavior is acceptable since the modal is only triggered after a login request, not on every page load.

**Spec Impact:** No spec changes required (already uses `sessionStorage` in FR-032-07). Added clarifying note to FR-032-07: "Dismissal is scoped per browser tab (sessionStorage semantics); opening a new tab triggers the modal again on login in that tab."

**Resolved:** 2026-04-06
