# techbash-snake (tjlytle/techbash-snake)

The Battlesnake PHP platform (a copy of `tjlytle/battle-snake-framework`) as used live in the
TechBash 2026 talk "BattleSnake in 50 minutes". Issues are filed during the talk; an agent
pipeline plans, builds and reviews them while the room watches. The map of the code is
`.claude/skills/build-snake/platform.md`; read it before touching `src/`.

## Working here

- Stack: `make build && make install && make migrate && make up` (http://localhost:8088).
  Tests: `make test` (PHPUnit) and `make behat` (ASCII-board scenarios); both run inside
  `docker compose run`, so they need the image but not a running stack. `make stan` and
  `make cs` fail on `main` already and are not gates.
- The demo snake is served from this checkout on :8088 and is public at
  https://snake.lytlelabs.com through a cloudflared tunnel. Never `make down` it during the
  talk; restart it with `make up` after a merge so the room plays the new code.
- A snake is one `Strategy` class under `src/Snake/`, bound in `src/Core/di/snake.php`, with a
  Behat scenario in `test/Behat/features/`. `/build-snake <idea>` is the thinking-first way to
  build one on stage. Do not touch the event-sourcing wiring, Docker or Composer for a snake.
- The pipeline: `bin/pipeline` watches open issues and runs `/shepherd` on each in a headless
  Claude Code session (`bin/pipeline --once` for a single pass, `bin/pipeline <#>` for one
  issue). Logs land in `var/pipeline/`. Headless sessions run with the allow list in
  `bin/pipeline`; a denied tool call shows in the log as the reason a moment stopped.

## GitHub Workflow

Bots: advocate=advocate, architect=architect, builder=builder, qa=qa, reviewer=reviewer, ops=ops
Models: builder=opus, qa=sonnet, reviewer=opus
Flow: approve=auto, review=auto, merge=human, fix-cycles=1
Exclusive: stack — the compose stack `make up` on :8088 (the demo snake); tests use `docker compose run` and need no lock

## SDLC

Work is a GitHub issue first. The advocate frames it (problem, acceptance criteria as board
situations where possible), the architect posts an Implementation Plan, the builder works in a
worktree on `issue-<#>-<slug>` and opens a draft PR, QA writes the Behat scenario the issue
asks for before the class exists, the reviewer reads and probes the PR, and the PR is ready to
merge with `Closes #<#>` once checks and threads are clean. Merging to `main` is the release:
the demo stack is restarted by hand (`make up`) so the public snake runs the new code.

Plan approval and review are automatic for the talk, so judgment replaces the human at those
gates. A moment that is unsure does not guess: it parks (`status:parked`, one comment, one
question, with the recommendation) and the comment @mentions `@tjlytle` so the question reaches
his phone. Park when the acceptance criteria can be read two ways, when the work would need to
change the event-sourcing wiring, Docker, Composer or the public API, when a test cannot go
green without changing what was asked, or when the review finds a defect the fix budget will
not cover. A snake behaviour that is clear from its board situations is not a question; build it.

Definition of done for a snake change: `make test` and `make behat` green in the worktree,
the new scenario in the PR, and the PR body saying which board situations it covers.
