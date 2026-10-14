# fr-validator

[![CI](https://github.com/petroslabs/fr-validator/actions/workflows/ci.yml/badge.svg)](https://github.com/petroslabs/fr-validator/actions/workflows/ci.yml)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fpetroslabs%2Ffr-validator%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/petroslabs/fr-validator/main)
[![Packagist](https://img.shields.io/packagist/v/petroslabs/fr-validator)](https://packagist.org/packages/petroslabs/fr-validator)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)

Framework-agnostic validators for French identifiers: NIR (social security
number), RIB key, SIRET/SIREN, IBAN/BIC, phone numbers.

No dependency on any framework. A Symfony bridge exposing these as
`Constraint`/`ConstraintValidator` lives in a separate package,
[`petroslabs/fr-validator-symfony`](https://github.com/petroslabs/fr-validator-symfony).

## Why not just a bag of regexes

Most "French validators" packages stop at a boolean. This one returns *why*
a value is invalid (wrong length, bad check digit, unknown department code...)
as a typed [`FailureReason`](src/FailureReason.php), and ships algorithms
verified against primary sources rather than copy-pasted from blog posts —
several widely circulated "example NIR" numbers online don't actually satisfy
the official checksum formula.

Tests are checked with [mutation testing](https://infection.github.io/)
(Infection), not just line coverage: CI deliberately mutates the code (flips
a `||` to `&&`, drops a regex anchor, changes a checksum constant...) and
fails the build if any test suite still passes. 100% line coverage can hide
assertions that never actually run; a 100% mutation score means every
intentionally broken behavior gets caught by an actual test. Equivalent
mutants (changes that provably can't affect behavior, e.g. a redundant cast
needed only for static analysis) are explicitly marked `@infection-ignore-all`
with a one-line reason instead of padding the required score.

## Installation

```bash
composer require petroslabs/fr-validator
```

## Usage

```php
use PetrosLabs\FrValidator\Nir\NirValidator;
use PetrosLabs\FrValidator\Rib\RibValidator;
use PetrosLabs\FrValidator\Siret\SiretValidator;

$result = (new NirValidator())->validate('1 85 03 75 116 012 16');
$result = (new RibValidator())->validate('30003 01890 00037553485 21');
$result = (new SiretValidator())->validate('732 829 320 00074');

if (!$result->isValid()) {
    echo $result->failureReason()->value; // e.g. "invalid_checksum"
}
```

## Validators

| Validator | Status |
|---|---|
| NIR (social security number) | ✅ available |
| RIB key | ✅ available |
| SIRET / SIREN | ✅ available |
| IBAN / BIC (France-specific checks) | planned |
| Phone number | planned |

### NIR — known limitations (v1)

Only standard numbers are supported: sex digit `1` or `2`, month `01`-`12`.
Temporary/technical registration numbers (sex digit `3`, `4`, `7`, `8`) and the
special month codes used for incomplete birth records are **not** validated —
their exact format could not be confirmed against a reliable primary source,
so they're left out rather than guessed at.

### SIRET/SIREN — what the check actually proves

Validates the Luhn checksum (and SIRET's SIREN+sum-of-5 La Poste exception)
only. A number can be checksum-valid and still not correspond to a real,
active establishment — that requires an actual lookup against the INSEE
Sirene API, which is out of scope here (this package never makes network
calls).

## Development

```bash
composer install
composer check  # tests + PHPStan (level max) + code style check
```

See [CONTRIBUTING.md](CONTRIBUTING.md) for the bar new validators are held to
— in short, no algorithm without a primary source, no test vector copied from
an unverified third-party page.

## Security

See [SECURITY.md](SECURITY.md) to report a vulnerability.

## License

MIT — see [LICENSE](LICENSE).
