# Feature <NNN> Tasks – <Descriptive Name>

_Status: Draft_  
_Last updated: YYYY-MM-DD_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under ``docs/specs/5-decisions`/` reflect the clarified behaviour.

## Checklist
- [ ] T-<NNN>-01 – <Task title> (F-<NNN>-01, N-<NNN>-01, S-<NNN>-01).  
  _Intent:_ What this task delivers (tests, implementation, docs).  
  _Verification commands:_  
  - `.php artisan test`  
  - `node --test …`  
  - `make phpstan`  
  _Notes:_ Link to related spec sections or follow-ups.

- [ ] T-<NNN>-02 – <Task title>.  
  _Intent:_ …  
  _Verification commands:_ …  

Add as many tasks as necessary, keeping stored vs inline, CLI vs REST, and UI increments separate.

## Notes / TODOs
Document temporary skips, deferred tests, or environment quirks so the next agent can follow up.