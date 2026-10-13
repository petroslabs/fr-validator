# Contributing

Thanks for considering a contribution.

## Setup

```bash
composer install
```

PHP 8.2+ is required. If you use [mise](https://mise.jdx.dev), the pinned
version is picked up automatically from `.mise.toml`.

## Before opening a PR

```bash
composer check   # runs tests, PHPStan (level max) and the code style check
```

Or individually: `composer test`, `composer stan`, `composer cs` (check only)
/ `composer cs-fix` (auto-fix).

Mutation testing (`composer infection`) runs in CI on every PR and requires a
coverage driver (pcov or Xdebug) that isn't set up locally by default — don't
worry about running it yourself, the CI job covers it.

## Adding or changing a validator

This package's whole point is rigor over copy-pasted regexes. If you touch a
validator:

1. **Cite a primary source** for the algorithm (official spec, Wikipedia
   article with references, INSEE/Banque de France documentation) in the
   class docblock — not a random blog post.
2. **Don't reuse "example" numbers found online** without recomputing them
   yourself. Several widely circulated examples (NIR, SIRET...) turn out to
   not satisfy their own checksum formula. Generate test vectors from the
   documented algorithm (a short standalone script in another language is a
   fine independent oracle) and say so in a comment.
3. **Return a typed failure reason**, not just `false` — see
   [`FailureReason`](src/FailureReason.php) and the existing `Nir` validator
   for the pattern.
4. Document known limitations explicitly in the README rather than silently
   under-handling edge cases.
5. Test each branch, not just the happy path and one failure — mutation
   testing (Infection) runs in CI and will flag conditions your tests don't
   actually exercise.

## Commit messages

[Conventional Commits](https://www.conventionalcommits.org/), imperative,
English: `feat:`, `fix:`, `refactor:`, `docs:`, `test:`, `chore:`.

## Code of conduct

This project follows the [Code of Conduct](CODE_OF_CONDUCT.md).
