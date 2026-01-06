# UI Specification ASCII Mock-up Guideline

_Status: Draft_

> Use this guide together with the spec template at [docs/specs/templates/feature-spec-template.md](docs/specs/templates/feature-spec-template.md); fill the template’s “UI / Interaction Mock-ups” section using the practices below.

## Purpose
Every UI-facing feature **or** change to existing UI behaviour must include a lightweight, text-based visualisation so reviewers understand the intended layout, state transitions, and validation flow without opening external assets. ASCII sketches live directly inside the specification that governs the change.

## When to Include ASCII Mock-ups
- Introducing a new UI surface, screen, dialog, or control.
- Modifying styling or structure for existing elements (including mode toggles, validation messaging, accessibility affordances, etc.).
- Adjusting interaction flows or copy that meaningfully changes the information hierarchy.

If a change affects multiple screens, include either a composite sketch or one per screen/major state.

## Authoring Checklist
1. **Inline the sketch** inside the spec near the relevant requirement (use fenced code blocks).
2. **Label states**—show active vs. inactive controls, validation messaging, and hints.
3. **Highlight interactions** with annotations (for example `[ selected ]`, `⇄`, `⚠`, `✓`).
4. **Keep it maintainable**—update the sketch whenever the specification changes.
5. **Stay text-only**—avoid screenshots or binary assets; ASCII must stand on its own.

## Example Layout
```
+--------------------------------------------------------------+
| Shared secret                                                |
|                                                              |
|  Mode:  [ Hex ] [*Base32 ]          Length:  64 chars        |
|                                                              |
|  ┌────────────────────────────────────────────────────────┐  |
|  │                                                        │  |
|  │  JBSWY3DPEB3W64TMMQ======                               │  |
|  │                                                        │  |
|  └────────────────────────────────────────────────────────┘  |
|                                                              |
|  ⇄ Converts automatically when you flip the mode.           |
|  ⚠ Validation: {shared_secret_conflict | invalid_characters} |
+--------------------------------------------------------------+
```

Reuse and adapt this style to fit your scenario.