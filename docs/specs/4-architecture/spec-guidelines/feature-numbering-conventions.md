# Feature Numbering Conventions

_Status: Active | Last updated: September 27, 2026_

This document defines the numbering and naming conventions used throughout the specification system to ensure consistent, predictable identifiers across features, questions, and decisions.

---

## Feature Identifiers

### Feature Number Format: `<NNN>`

**Format:** Three-digit zero-padded sequential numbers  
**Examples:** `001`, `002`, `010`, `099`, `100`

**Rules:**
- Start at `001` for the first feature
- Increment sequentially for each new feature
- Zero-pad to maintain three digits (e.g., `007`, not `7`)
- Never reuse retired or cancelled feature numbers
- Feature numbers are permanent once assigned

**Usage:**
```
docs/specs/4-architecture/features/001-user-auth/
docs/specs/4-architecture/features/002-photo-upload/
docs/specs/4-architecture/features/010-oauth-integration/
```

### Feature Name Format: `<feature-name>`

**Format:** Hyphen-separated, 2–4 words in action-noun format  
**Examples:** `user-auth`, `photo-upload`, `oauth2-api-integration`, `analytics-dashboard`

**Rules:**
- Use lowercase letters and hyphens only (no underscores, spaces, or special characters)
- Aim for 2–4 words that capture the essence of the feature
- Use action-noun format when possible: `add-tags`, `fix-payment-timeout`, `implement-caching`
- Avoid generic names like `feature1` or `new-thing`
- Keep names concise but descriptive

**Deriving feature names from user requests:**
- "I want to add user authentication" → `user-auth`
- "Implement OAuth2 integration for the API" → `oauth2-api-integration`
- "Create a dashboard for analytics" → `analytics-dashboard`
- "Fix payment processing timeout bug" → `fix-payment-timeout`

### Complete Feature Path

**Full path format:**
```
docs/specs/4-architecture/features/<NNN>-<feature-name>/
```

**Example structure:**
```
docs/specs/4-architecture/features/
├── 001-user-auth/
│   ├── spec.md
│   ├── plan.md
│   └── tasks.md
├── 002-photo-upload/
│   ├── spec.md
│   ├── plan.md
│   └── tasks.md
└── 010-oauth-integration/
    ├── spec.md
    ├── plan.md
    └── tasks.md
```

---

## Question Identifiers

### Question ID Format: `Q-XXX-YY`

**Format:** `Q-` prefix + feature number + `-` + question sequence  
**Examples:** `Q-001-01`, `Q-002-03`, `Q-010-12`

**Components:**
- `Q-` = Fixed prefix indicating a question
- `XXX` = Three-digit feature number (matches the feature it relates to)
- `-` = Separator
- `YY` = Two-digit question sequence within that feature (01, 02, 03, etc.)

**Rules:**
- Question numbers are scoped to their feature
- Each feature starts question numbering at `01`
- Questions are tracked in their feature's `docs/specs/4-architecture/features/<NNN>-<feature-name>/open-questions.md`; there is no central log
- Question IDs remain permanent even after resolution (for traceability)

**Examples:**
- First question for feature 001: `Q-001-01`
- Fifth question for feature 010: `Q-010-05`
- Cross-feature question affecting multiple features: assign to primary feature

---

## Decision Record Identifiers

### ADR ID Format: `ADR-<NNN>-<YY>`

**Format:** `ADR-` prefix + three-digit feature number + two-digit sequence within that feature  
**Examples:** `ADR-069-01`, `ADR-072-01`, `ADR-072-02`

**Rules:**
- `<NNN>` is the feature that raised the decision; a decision spanning several features belongs to the feature that raised it
- Each feature starts its ADR sequence at `01`; check `docs/specs/6-decisions/` for that feature's highest existing ID
- Numbering per feature means parallel feature branches cannot collide on the same ID
- ADR numbers are permanent and never reused
- Superseded ADRs remain in history with status updated
- ADRs created before this rule keep their global `ADR-XXXX` numbers (`ADR-0003` … `ADR-0011`)

**File naming:**
```
docs/specs/6-decisions/ADR-069-01-v3-collection-bounding-strategies.md
docs/specs/6-decisions/ADR-072-01-<short-slug>.md
```

---

## Task Identifiers

Tasks within a feature's `tasks.md` file use checkbox format without explicit IDs:

```markdown
- [ ] Task description here
- [ ] Another task description
- [x] Completed task (marked with x)
```

**Rules:**
- Tasks are tracked by position within the feature's tasks file
- Use descriptive task text rather than task IDs
- Mark completed tasks with `[x]`
- Reference tasks by feature + description, not by numeric ID

---

## Increment Identifiers

Increments are identified by commit SHA or feature milestone, not by explicit numbering:

- Reference commits: `a1b2c3d` (short SHA)
- Reference features: "Feature 010 increment 3" or "Photo upload - metadata extraction"

---

## Related Documents

- [Feature Spec Template](../../templates/feature-spec-template.md) - Template for creating new feature specifications
- [Feature Plan Template](../../templates/feature-plan-template.md) - Template for feature implementation plans
- [Feature Tasks Template](../../templates/feature-tasks-template.md) - Template for feature task checklists
- [Open Questions Format](open-questions-format.md) - Format for presenting decision cards
- [ADR Template](../../templates/adr-template.md) - Template for architectural decision records

---

*Last updated: December 22, 2025*
