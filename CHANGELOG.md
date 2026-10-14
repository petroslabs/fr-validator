# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.1] - 2026-10-14

### Fixed

- `FailureReason` now extends `\BackedEnum`, so `->value` is statically
  known wherever code is typed against the generic interface instead of a
  specific validator's enum — needed by the Symfony bridge package.

## [0.1.0] - 2026-10-14

### Added

- NIR (French social security number) validator.
- RIB key validator.
- SIREN and SIRET validators.
- IBAN validator (French IBANs only).
- BIC validator (general ISO 9362 format).
- Phone number validator (format only, national and +33/0033 international).
- French VAT number validator (standard numeric key only).
- NAF/APE activity code validator (lookup against the 732-code INSEE nomenclature).

### Changed

- Every `*ValidatorInterface` now extends a common `ValidatorInterface`
  (purely additive — existing code is unaffected).

[Unreleased]: https://github.com/petroslabs/fr-validator/compare/v0.1.1...HEAD
[0.1.1]: https://github.com/petroslabs/fr-validator/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/petroslabs/fr-validator/releases/tag/v0.1.0
