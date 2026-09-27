# Open Questions – Decision Card Format

Status: Draft | Last updated: 2026-09-27

This document defines the standard “Decision Card” format for all medium‑ and high‑impact open questions that are presented to humans (for example in chat, design docs, or reviews).

> Scope: This format is for **presentation** of a single open question (for example in chat or in a spec section). The tracking table in the feature's own `docs/specs/4-architecture/features/<NNN>-<feature-name>/open-questions.md` remains the lightweight index of questions/options; it should reference options in the same A/B/C order and preferred option as the Decision Card.

## 1. Decision Card Template

When formatting an open question, use the following structure verbatim, adapting only IDs, titles, and content. Keep the emoji and heading levels as shown.

```markdown
### ❓ Q-XXX · Short descriptive title

**Status:** Open  
**Feature:** F-XXX – Short feature name  
**Preferred option:** 🅰️ (**recommended**) Option A – Option title  

**Question**  
Short, human-readable question text (one or a few sentences).

---

#### 🅰️ (**recommended**) Option A – Option title
- **Idea:** Short description of what this option proposes.
- **Spec impact:** How this option changes or constrains the spec.
- **Pros:**  
  - ✅ Bullet point 1  
  - ✅ Bullet point 2  
  - ✅ Bullet point 3 (optional)
- **Cons:**  
  - ❌ Bullet point 1  
  - ❌ Bullet point 2  
  - ❌ Bullet point 3 (optional)

---

#### 🅱️ Option B – Option title
- **Idea:** Short description of what this option proposes.
- **Spec impact:** How this option changes or constrains the spec.
- **Pros:**  
  - ✅ Bullet point 1  
  - ✅ Bullet point 2  
- **Cons:**  
  - ❌ Bullet point 1  
  - ❌ Bullet point 2  

---

#### 🅲 Option C – Option title
- **Idea:** Short description of what this option proposes.
- **Spec impact:** How this option changes or constrains the spec.
- **Pros:**  
  - ✅ Bullet point 1  
  - ✅ Bullet point 2  
- **Cons:**  
  - ❌ Bullet point 1  
  - ❌ Bullet point 2  

---

**Next action**  
Who needs to decide what, and where/when (for example:  
“IAM WG to choose between 🅰️ and 🅱️ in WG-003 on 2025-12-10; update ADR-00XX accordingly.”)
```

### 1.1 Rules

- Always mark exactly one option as preferred in the metadata line and in its section heading, using `(**recommended**)` **immediately after the emoji**, before the option title.
- Options must be listed in **preference order** (A is most preferred, then B, then C, etc.), consistent with the feature's `open-questions.md`.
- If there are more or fewer options than A/B/C, extend or shrink the list while keeping the same pattern (🅰️, 🅱️, 🅲, 🅳, …).
- Do **not** add extra meta sections (no TL;DR, summary, criticism, etc.) beyond what is defined in the template.

## 2. Relationship to the feature's `open-questions.md`

- Each feature owns its own `docs/specs/4-architecture/features/<NNN>-<feature-name>/open-questions.md` (created from [feature-open-questions-template.md](../../templates/feature-open-questions-template.md)); it is the single scratchpad for that feature's questions, their IDs, and their options in compressed form. There is no central open-questions log.
- Question IDs (`Q-<NNN>-<YY>`) are allocated from the feature's `open-questions.md` only; do not number Decision Cards independently elsewhere (for example in a spec appendix).
- When presenting a question to a human (for example in chat or in a spec/ADR discussion), render it as a Decision Card using this format.
- The “Options (A preferred)” column in the feature's `open-questions.md` must:
  - List options in the same order and with the same labels (A/B/C/…) as the Decision Card.
  - Match the preferred option from the Decision Card (Option A is the recommended path unless a different option is explicitly marked as preferred).
