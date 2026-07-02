# Symfony 6.4 Backport Runbook

This repository is a fork of [symfony/ai](https://github.com/symfony/ai) that adds
**Symfony 6.4 / 7.0 support** on top of upstream (which targets `^7.3|^8.0`).

This document describes the repeatable process for **resyncing with upstream and
re-applying the 6.4 compatibility changes**. Follow it whenever you want to pull the
latest upstream changes into the fork.

> TL;DR: reset `main` to `upstream/main`, re-apply two mechanical change sets
> (composer constraints + Config-builder method swaps), then scan for any *new*
> 7.x-only APIs upstream introduced and patch them.

---

## 0. Prerequisites — remotes

The fork needs an `upstream` remote pointing at the real Symfony AI repo. Check with
`git remote -v`. If `upstream` is missing, add it:

```bash
git remote add upstream https://github.com/symfony/ai.git
```

Typical remotes in this repo:

| Remote      | Purpose                                   |
|-------------|-------------------------------------------|
| `origin`    | Your GitHub fork (`taffy41/ai`)           |
| `bitbucket` | Internal mirror (`wrenkitchens/ai`)       |
| `upstream`  | The canonical `symfony/ai` (read-only)    |

---

## 1. Back up the current state (always reversible)

```bash
# Snapshot the current 6.4 commit on a dated backup branch
git branch backup/sf6.4-pre-reset-$(date +%Y-%m-%d)

# If there are uncommitted 6.4 fixes in the working tree, save them too
git diff -- src/ai-bundle/config > /tmp/sf64-config-fixes.patch
```

To roll back at any point: `git reset --hard backup/sf6.4-pre-reset-<date>`.

---

## 2. Reset to the latest upstream

```bash
git fetch upstream
git log --oneline HEAD..upstream/main | wc -l   # how many new commits (informational)
git reset --hard upstream/main                   # DESTRUCTIVE — backup branch protects you
```

The fork's history has always been **linear**: just upstream + a single 6.4 commit
(+ working-tree config edits). A hard reset to `upstream/main` gives a clean base.

---

## 3. Re-apply the 6.4 compatibility changes

There are exactly **two mechanical change sets**, plus a **scan-and-patch** step for
anything new upstream added.

### 3a. composer.json — widen Symfony version constraints

**Scope:** every `composer.json` under `src/**` and `examples/composer.json`.
**Do NOT touch** `demo/`, `ai.symfony.com/`, or the root `composer.json` (those are
apps / dev tooling that already target `^8.0` or already include `6.4`).

**Transformation rules:**

| Current constraint                | New constraint        | Notes                                            |
|-----------------------------------|-----------------------|--------------------------------------------------|
| `^7.3\|^8.0`, `^7.4\|^8.0`, `^7.x` | `^6.4\|^7.0\|^8.0`     | Most Symfony components                          |
| `symfony/type-info`               | `^7.2\|^8.0`          | TypeInfo doesn't exist in 6.4; needs **7.2** (uses `NullableType`) |
| `symfony/json-path`               | *leave as `^7.3\|^8.0`* | 7.3-only, **require-dev** only, guarded by `class_exists()` |
| Already contains `6.4` or `5.4`   | *leave unchanged*     | e.g. `^5.4\|^6.4\|^7.3\|^8.0`                     |
| `*-contracts`, `polyfill-*`, `models-dev`, `monolog-bundle` | *leave unchanged* | Version-independent / already 6.4-OK |

Run the helper script (idempotent — safe to re-run):

```bash
python3 .dev/fix_composer_64.py     # see script in section 6 below
```

### 3b. Config builder — replace 7.x-only fluent methods

Upstream uses Config-component methods that **only exist in Symfony 7.2+/7.3**.
All occurrences live under `src/ai-bundle/config/`.

| 7.x-only method              | 6.4-compatible replacement            | Introduced |
|------------------------------|---------------------------------------|------------|
| `->stringNode('x')`          | `->scalarNode('x')`                   | 7.2        |
| `->enumFqcn(Foo::class)`     | `->values(Foo::cases())`              | 7.3        |
| `->enumPrototype(Foo::class)` | `->scalarPrototype()` + validation closure (see below) | 7.2 |

```bash
# stringNode -> scalarNode (all config files)
grep -rl -e '->stringNode(' src --include='*.php' \
  | xargs sed -i '' 's/->stringNode(/->scalarNode(/g'

# enumFqcn(...) -> values(...::cases())  — currently only options.php / Capability
#   ->enumFqcn(Capability::class)  becomes  ->values(Capability::cases())
```

**enumPrototype replacement** (cannot use `sed` — needs manual editing):

`enumPrototype()` does NOT exist in Symfony 6.4. Replace:

```php
// BEFORE (7.2+):
->enumPrototype(Capability::class)
    ->enumFqcn(Capability::class)
->end()
```

with:

```php
// AFTER (6.4-compatible):
->scalarPrototype()->end()
->validate()
    ->always(static function (array $values) {
        return array_map(static function ($v) {
            if ($v instanceof Capability) {
                return $v;
            }

            $case = Capability::tryFrom($v);
            if (null === $case) {
                throw new \InvalidArgumentException(\sprintf(
                    'The value "%s" is not a valid capability. Permissible values: %s',
                    $v,
                    implode(', ', array_map(static fn (Capability $c) => $c->value, Capability::cases()))
                ));
            }

            return $case;
        }, $values);
    })
->end()
```

This coerces string config values (e.g. `'input-text'`) into the backed enum
instances (e.g. `Capability::INPUT_TEXT`), matching the behaviour of `enumFqcn()`.

Verify none remain:

```bash
grep -rnE 'stringNode|enumFqcn|enumPrototype' src --include='*.php'   # expect: no output
```

### 3c. Scan for NEW 7.x-only APIs in code

Upstream adds features over time, so each resync must re-scan production code
(`src/**`, excluding tests) for APIs that don't exist in 6.4. Known offenders and
how to find them:

```bash
# Console invokable-command attributes (Symfony 7.3) — MUST be rewritten as classic commands
grep -rnE 'Console\\Attribute\\(Argument|Option)' src --include='*.php'

# Components that don't exist in 6.4 at all
grep -rnE 'Clock\\DatePoint|JsonStreamer|ObjectMapper|Component\\Scheduler' src --include='*.php'

# TypeInfo Type subclasses — confirm the composer constraint covers the version used
grep -rn 'TypeInfo\\Type\\' src --include='*.php'

# Controller/HTTP attributes that are 7.x
grep -rnE 'MapRequestPayload|MapQueryString|#\[Route' src --include='*.php'

# DI attributes introduced in 7.x
grep -rnE 'AsDecorator|AutowireInline|#\[Lazy|#\[When' src --include='*.php'
```

**Known finding (must fix on each resync if still present):**
Invokable commands with `#[Argument]`/`#[Option]` on `__invoke()` are a **7.3**
feature. These files must be rewritten to classic `Command` subclasses
(`configure()` + `execute()`):

- `src/platform/src/Bridge/HuggingFace/Command/ModelListCommand.php`
- `src/platform/src/Bridge/HuggingFace/Command/ModelInfoCommand.php`
- `src/platform/src/Bridge/Bedrock/Command/ModelListCommand.php`

The pattern is: replace `__invoke(SymfonyStyle $io, #[Option(...)] ?string $x)` with
`configure()` using `addOption()/addArgument()` + `execute(InputInterface, OutputInterface)`
using `$input->getOption()/getArgument()`.

**Confirmed NON-issues** (do not waste time on these):

- `Serializer\Attribute\*` — the `Attribute` namespace has existed as aliases since **6.4**.
- `DependencyInjection\Attribute\Target` — available since **6.3**.
- `symfony/json-path` — require-dev only and guarded by `class_exists()`.
- `#[AsCommand]` — available since **6.1**.

---

## 4. Verify

```bash
# 1. PHP syntax of changed config files
for f in $(grep -rl 'scalarNode' src/ai-bundle/config --include='*.php'); do php -l "$f"; done

# 2. Every changed composer.json is still valid JSON
for f in $(git diff --name-only | grep 'composer.json$'); do
  python3 -c "import json,sys; json.load(open(sys.argv[1]))" "$f" || echo "BAD: $f"
done

# 3. Refresh dev tooling, then run the suites (tooling config evolves upstream)
rm -f composer.lock   # upstream lock is stale against widened constraints
composer update
vendor/bin/php-cs-fixer fix
cd src/platform && composer install --ignore-platform-reqs && vendor/bin/phpunit && cd -
cd src/agent && composer install --ignore-platform-reqs && vendor/bin/phpunit && cd -
cd src/ai-bundle && composer install --ignore-platform-reqs && vendor/bin/phpunit && cd -
```

> **Note:** A full *cross-package* install pinned to Symfony 6.4 can't be done with a
> plain per-package `composer install` because sibling packages resolve from Packagist
> at `^0.10` (still 7.x). Use the monorepo CI's path-repository setup, or the `./link`
> script, to test true 6.4 resolution end-to-end.

> **Expected test failures (not caused by 6.4 changes):** The ai-bundle tests that
> reference features added after the last Packagist release (e.g. `Schema(provider:)`,
> `StoreFactory::create` for new bridges) will fail because the vendored sibling
> packages lag behind HEAD. These are cross-package resolution issues, not 6.4 regressions.

---

## 5. Restore fork-specific workflows

After resetting to upstream, all upstream workflows (CI, changelog validation, etc.)
will be present in `.github/workflows/`. We only need **one** workflow from the fork:

```bash
# Remove all upstream workflows (we don't use them)
rm -f .github/workflows/*.yml

# Restore our monorepo-split workflow from the backup branch
git show backup/sf6.4-pre-reset-$(date +%Y-%m-%d):.github/workflows/monorepo-split.yml \
  > .github/workflows/monorepo-split.yml
```

The `monorepo-split.yml` workflow splits each `src/` component into its own read-only
repo on push to `main` (uses splitsh-lite + a GitHub App token). Target repos:

| Prefix          | Target repo                          |
|-----------------|--------------------------------------|
| `src/platform`  | `taffy41/ai-platform`                |
| `src/agent`     | `taffy41/ai-agent`                   |
| `src/store`     | `taffy41/ai-store`                   |
| `src/chat`      | `taffy41/ai-chat`                    |
| `src/ai-bundle` | `taffy41/ai-bundle`                  |
| `src/mcp-bundle`| `taffy41/mcp-bundle`                 |

---

## 6. Commit

```bash
git add -A -- . ':!.ai'
git commit -m "Symfony 6.4 support (resync with upstream)"
```

Per repo conventions (`AGENTS.md`): run PHP-CS-Fixer first, never add Claude as a
co-author, and keep the changes scoped to constraint widening + the documented
API swaps.

---

## 7. Reference: composer constraint fixer script

Save as `.dev/fix_composer_64.py` (create the `.dev/` folder if needed). It is
idempotent and only edits `src/**` + `examples/composer.json`.

```python
import json, glob, os, re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
files = glob.glob(os.path.join(ROOT, "src", "**", "composer.json"), recursive=True)
files += glob.glob(os.path.join(ROOT, "examples", "composer.json"))

LEAVE_EXACT = {
    "symfony/event-dispatcher-contracts", "symfony/http-client-contracts",
    "symfony/service-contracts", "symfony/translation-contracts",
    "symfony/models-dev", "symfony/monolog-bundle",
}
SKIP = {"symfony/json-path"}            # 7.3-only, require-dev, guarded by class_exists()
TARGET = "^6.4|^7.0|^8.0"

def new_value(key, value):
    if key == "symfony/type-info":
        return "^7.2|^8.0"              # uses NullableType (7.2)
    if key in SKIP or key in LEAVE_EXACT or key.startswith("symfony/ai-"):
        return None
    if key.startswith("symfony/polyfill-") or not key.startswith("symfony/"):
        return None
    if "6.4" in value or "5.4" in value:
        return None                      # already 6.4-compatible
    if re.fullmatch(r"\^7\.\d+(\|\^8\.0)?", value):
        return TARGET
    return None

total = 0
for f in files:
    text = open(f).read()
    data = json.loads(text)
    changes = []
    for section in ("require", "require-dev"):
        for k, v in data.get(section, {}).items():
            nv = new_value(k, v)
            if nv and nv != v:
                changes.append((k, v, nv))
    for k, v, nv in changes:
        text = text.replace(f'"{k}": "{v}"', f'"{k}": "{nv}"')
    if changes:
        open(f, "w").write(text)
        print(os.path.relpath(f, ROOT))
        for k, v, nv in changes:
            print(f"   {k:<40} {v} -> {nv}"); total += 1
print("\nTotal constraint changes:", total)
```

---

## 8. Quick checklist

- [ ] `upstream` remote exists
- [ ] Backup branch created
- [ ] `git reset --hard upstream/main`
- [ ] Ran `fix_composer_64.py` (composer constraints)
- [ ] `stringNode` → `scalarNode`, `enumFqcn` → `values(...::cases())`
- [ ] `enumPrototype` → `scalarPrototype()` + validation closure (coerces strings to enum)
- [ ] Scanned for new 7.x APIs (Console `#[Argument]`/`#[Option]`, etc.) and patched
- [ ] Removed upstream workflows, restored `monorepo-split.yml` from backup
- [ ] PHP lint + composer JSON valid + tests pass
- [ ] Committed (no Claude co-author, CS-Fixer run)

---

## 9. Sync history

| Date       | Upstream HEAD        | Notes                                    |
|------------|---------------------|------------------------------------------|
| 2026-07-02 | `56eeda68` (post v0.10.0, 168 commits) | Added enumPrototype→scalarPrototype fix, rewrote 3 invokable commands to classic style |
| 2026-06-11 | `01a24cb6` (v0.10.0) | Initial sync with new runbook            |
