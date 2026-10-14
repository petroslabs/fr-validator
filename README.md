# fr-validator

[![CI](https://github.com/petroslabs/fr-validator/actions/workflows/ci.yml/badge.svg)](https://github.com/petroslabs/fr-validator/actions/workflows/ci.yml)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fpetroslabs%2Ffr-validator%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/petroslabs/fr-validator/main)
[![Packagist](https://img.shields.io/packagist/v/petroslabs/fr-validator)](https://packagist.org/packages/petroslabs/fr-validator)
[![License](https://img.shields.io/badge/license-MIT-blue)](LICENSE)

Framework-agnostic validators for French identifiers: NIR (social security
number), RIB key, SIRET/SIREN, IBAN/BIC, VAT number, phone numbers.

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
use PetrosLabs\FrValidator\Iban\IbanValidator;
use PetrosLabs\FrValidator\Bic\BicValidator;
use PetrosLabs\FrValidator\Phone\PhoneValidator;
use PetrosLabs\FrValidator\Vat\VatValidator;
use PetrosLabs\FrValidator\Naf\NafValidator;

$result = (new NirValidator())->validate('1 85 03 75 116 012 16');
$result = (new RibValidator())->validate('30003 01890 00037553485 21');
$result = (new SiretValidator())->validate('732 829 320 00074');
$result = (new IbanValidator())->validate('FR76 3000 3018 9000 0375 5348 521');
$result = (new BicValidator())->validate('BNPAFRPP');
$result = (new PhoneValidator())->validate('06 12 34 56 78');
$result = (new VatValidator())->validate('FR 44 732 829 320');
$result = (new NafValidator())->validate('62.01Z');

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
| IBAN (France-specific) | ✅ available |
| BIC (general ISO 9362 format) | ✅ available |
| VAT number (France-specific) | ✅ available |
| Phone number | ✅ available |
| NAF/APE activity code | ✅ available |

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

### IBAN/BIC — scope

`IbanValidator` only accepts French IBANs (`FR` + 23-character BBAN). It
checks both the generic ISO 7064 check digits and, independently, that the
embedded BBAN is itself a valid RIB — a forged account number can still end
up wrapped in "correct-looking" IBAN check digits, since those only protect
the IBAN as a whole, not the bank/branch/account consistency inside it.

`BicValidator` is the odd one out in this package: BIC/SWIFT (ISO 9362) has
no French-specific variant, so it validates the general international
format (8 or 11 characters) rather than being restricted to French banks —
restricting it to country code `FR` would make it useless for validating
the counterparty side of a SEPA transfer. There is no checksum digit for a
BIC, so this only proves the format is well-formed, not that the code is
actually registered.

### VAT number — known limitations (v1)

Only the standard, fully numeric key (`FR` + 2 digits + 9-digit SIREN) is
supported — like SIRET, the embedded SIREN must independently pass its own
Luhn check in addition to the key formula matching. Some historical French
VAT numbers are known to use a key containing letters instead; that format
couldn't be confirmed against a reliable primary source, so it isn't
guessed at.

### Phone number — known limitations (v1)

Format only: 10 digits starting with `0` (second digit `1`-`9`), or the
international `+33`/`0033` equivalent. It does **not** classify the number
as mobile/landline/VoIP (that depends on the exact second-digit allocation,
which isn't needed to just validate the shape) and doesn't special-case
DOM/TOM numbers (they already follow the same 10-digit structure). The
`+33(0)...` notation some websites display is **not** accepted — strip the
parenthesized `0` before validating.

### NAF/APE — what the check actually proves

Unlike every other validator in this package, there's no checksum: a NAF
code is valid only if it's one of the 732 sub-classes of the official INSEE
nomenclature (rev. 2, 2008), embedded as a lookup table generated from a
republication of that nomenclature on data.gouv.fr (Métropole de Lyon,
Open Licence 2.0) — not hand-typed, and not the full list with labels,
just the codes needed to validate membership. This proves the code exists
in the nomenclature, not that it's the code actually assigned to a given
company (that needs an INSEE Sirene lookup, out of scope here).

Since this package never makes network calls, the list is a snapshot, not
a live lookup: a real code INSEE adds after this version was published
will be rejected as `unknown_code` until a new release regenerates it (see
[CONTRIBUTING.md](CONTRIBUTING.md)). To unblock that without waiting for a
release, pass it as an extra code:

```php
new NafValidator(additionalCodes: ['1234A']);
```

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
