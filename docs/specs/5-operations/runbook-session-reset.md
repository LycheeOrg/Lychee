# Runbook: Session Reset

Symptom:
- New chat session starts without prior conversation context.

Detection (Alerts/Queries):
- User opens with "Project status" or otherwise indicates a fresh session.

Immediate actions:
1. Read [AGENTS.md](AGENTS.md) to refresh global working agreements and the project constitution link.
2. Review [docs/specs/4-architecture/roadmap.md](docs/specs/4-architecture/roadmap.md) for current workstreams and milestones.
3. Inspect the active feature specification(s) in `docs/specs/4-architecture/features/<NNN>-<feature-name>/spec.md`.
4. Inspect the corresponding feature plan(s) in `docs/specs/4-architecture/features/<NNN>-<feature-name>/plan.md`.
5. Review the per-feature tasks in `docs/specs/4-architecture/tasks/`.
6. Check [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) for unresolved questions.
7. Consult [docs/specs/_current-session.md](docs/specs/_current-session.md) for the latest workstream snapshot; update it as you discover new status.
8. Reconfirm the Specification-Driven Development cadence: spec updates lead, failing tests follow, then tasks and implementation proceed.
9. Expect that parallel or prior sessions may introduce new files or directories; if you encounter unfamiliar or untracked paths, surface them for user guidance rather than removing them.
    - Batch P3 ownership map: Feature 009 (operator console/UI docs), Feature 010 (documentation & knowledge automation), Feature 011 (governance/runbooks/hooks—see `docs/specs/4-architecture/features/011-feature-name/{spec,plan,tasks}.md` for FR-011-01..08 + NFR-011-01..05), Feature 012 (core cryptography & persistence docs), Feature 013 (toolchain & quality automation).

Diagnosis tree:
- If open questions exist, prepare a clarification request before planning.
- If no open questions but feature plan tasks remain, select the highest-priority task (marked `☐`).
- If all plans are complete, coordinate with the user to queue new scope.

Remediation:
- Summarise project status back to the user (roadmap state, open questions, next suggested action).
- Request clarifications where needed and wait for responses before coding; when answers arrive, update the governing feature spec’s requirements/NFR/behaviour/telemetry sections, mark the question as resolved in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) with links to those sections, and create or reference an ADR for architecturally significant decisions.
- Once direction is confirmed, ensure the analysis gate ([docs/specs/5-operations/analysis-gate-checklist.md](docs/specs/5-operations/analysis-gate-checklist.md)) is satisfied, then proceed with planning/implementation per [AGENTS.md](AGENTS.md) guidelines.

## Handoff Prompt Template
Copy/paste the template in [docs/specs/5-operations/session-quick-reference.md](docs/specs/5-operations/session-quick-reference.md) when opening a new chat so the next agent inherits the full context quickly. Replace bracketed sections with the current details.

Owner/On-call escalation:
- Escalate to the user when:
  * Scope is ambiguous or conflicting across documents.
  * Required approvals (dependencies, destructive commands) are not documented.

Post-incident notes:
- Update feature plans, roadmap, and open-questions log to reflect decisions made during the new session kickoff.
- Reconfirm the commit/push protocol: assistants stage (or enumerate) the relevant files, review the staged diffs under `docs/specs/4-architecture/`, generate a Conventional Commit message via [./scripts/codex-commit-review.sh](./scripts/codex-commit-review.sh) including any required `Spec impact:` line, ensure the message contains no semicolons (use multiple `-m` flags for multi-line bodies), and hand the operator copy/paste-ready fenced-code-block `git commit …` / `git push …` commands (with timeout guidance). The operator executes those commands unless they explicitly delegate execution back to the assistant.
- When committing (or running any command that triggers the managed pre-commit pipeline), set a generous CLI timeout so the Gradle checks finish. Use `timeout_ms >= 300000` when calling `git commit` to avoid premature termination.