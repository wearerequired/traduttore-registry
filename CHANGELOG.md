# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.1.0] - 2020-07-20

### Fixed
* Prevent PHP fatal error for malformed date strings in PO files. Props @nickcernis. [#32]

### Changed
* Only install updates for actually available languages. [#28]
* Performance: Make closures static where possible. [#30]
* Performance: Use fully qualified name for special compiled PHP functions. [#36]

## [2.0.0] - 2019-03-15

### Fixed
* Various documentation improvements.

### Changed
* Delete cached API results when plugins/themes are updated to avoid stale or missing translation updates.

### Added
* Integration tests.

### Removed
* Support for PHP 7.0.

## [1.0.2] - 2018-08-31

### Changed
* Improve support for new `wp language plugin|theme install|update` commands in WP-CLI 2.0.

## [1.0.1] - 2018-06-29

### Fixed
* Prevent a PHP notice that could occur under some circumstances.

## 1.0.0 - 2018-06-19

## Added
* Initial release

[Unreleased]: https://github.com/wearerequired/traduttore-registry/compare/2.1.0...HEAD
[2.1.0]: https://github.com/wearerequired/traduttore-registry/compare/2.0.0...2.1.0
[2.0.0]: https://github.com/wearerequired/traduttore-registry/compare/1.0.2...2.0.0
[1.0.2]: https://github.com/wearerequired/traduttore-registry/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/wearerequired/traduttore-registry/compare/1.0.0...1.0.1

[#28]: https://github.com/wearerequired/traduttore-registry/issues/28
[#30]: https://github.com/wearerequired/traduttore-registry/issues/30
[#32]: https://github.com/wearerequired/traduttore-registry/issues/32
[#36]: https://github.com/wearerequired/traduttore-registry/issues/36
