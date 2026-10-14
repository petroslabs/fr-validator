# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
