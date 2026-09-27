# Open Questions – Feature 039

Open questions for [Feature 039](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|

## Question Details

### ~~Q-039-01: Custom Brand Name vs Generic "your-application" Placeholder~~ ✅ RESOLVED

**Feature:** 039 – Lychee White Label  
**Priority:** Medium  
**Status:** Resolved — **Option A**  
**Opened:** 2026-05-04  
**Resolved:** 2026-05-04

**Resolution:** Keep the hardcoded generic placeholder "your-application" / "your-application.example.com". No extra config key needed. Captured in spec FR-039-07.

---

### ~~Q-039-02: Blade Config Read Mechanism for `white_label_enabled`~~ ✅ RESOLVED

**Feature:** 039 – Lychee White Label  
**Priority:** High  
**Status:** Resolved — **Option A** (inline `resolve()`)  
**Opened:** 2026-05-04  
**Resolved:** 2026-05-04

**Resolution:** Use `resolve(\App\Repositories\ConfigManager::class)->getValueAsBool('white_label_enabled')` inline in Blade directives — the same pattern already used in `vueapp.blade.php` for `dark_mode_enabled`. Captured in spec FR-039-05/06/07.

---

### ~~Q-039-03: Gate `is_white_label_enabled` on SE Being Active at Runtime~~ ✅ RESOLVED

**Feature:** 039 – Lychee White Label  
**Priority:** Low  
**Status:** Resolved — **Option B**  
**Opened:** 2026-05-04  
**Resolved:** 2026-05-04

**Resolution:** `is_white_label_enabled` is SE-gated at runtime: `$this->is_se_enabled && request()->configs()->getValueAsBool('white_label_enabled')`. White label is a Lychee Supporter benefit; operators who stop their support accept that branding re-appears. Consistent with `is_live_metrics_enabled`. Captured in spec FR-039-02.
