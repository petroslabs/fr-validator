# fr-validator

[![CI](https://github.com/petroslabs/fr-validator/actions/workflows/ci.yml/badge.svg)](https://github.com/petroslabs/fr-validator/actions/workflows/ci.yml)
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

## Installation

```bash
composer require petroslabs/fr-validator
```

## Usage

```php
use PetrosLabs\FrValidator\Nir\NirValidator;

$result = (new NirValidator())->validate('1 85 03 75 116 012 16');

if (!$result->isValid()) {
    echo $result->failureReason()->value; // e.g. "invalid_checksum"
}
```

## Validators

| Validator | Status |
|---|---|
| NIR (social security number) | ✅ available |
| RIB key | planned |
| SIRET / SIREN | planned |
| IBAN / BIC (France-specific checks) | planned |
| Phone number | planned |

### NIR — known limitations (v1)

Only standard numbers are supported: sex digit `1` or `2`, month `01`-`12`.
Temporary/technical registration numbers (sex digit `3`, `4`, `7`, `8`) and the
special month codes used for incomplete birth records are **not** validated —
their exact format could not be confirmed against a reliable primary source,
so they're left out rather than guessed at.

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
