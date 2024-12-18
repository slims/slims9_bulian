# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [1.3.1] - 2022-03-18
### Fixed
- Support for concatenated strings [#14].

## [1.3.0] - 2021-04-01
### Added
- The translations with sprintf expressions have the `php-format` flag [#12]

## [1.2.2] - 2021-01-12
### Fixed
- Support for PHP 8 [#11]

## [1.2.1] - 2020-05-28
### Added
- Support for static calls [#9]

## [1.2.0] - 2020-05-23
### Added
- Function scanner extracts not only functions calls but also class methods calls.

### Fixed
- Support for `gettext/gettext v5.5.0`
- Extract comments prepending variable assignments [#8]

## [1.1.1] - 2019-11-25
### Fixed
- Extract comments of functions prepended with echo, print or return [#6]
- Tested extracted comments from code

## [1.1.0] - 2019-11-19
### Added
- In v1.0, non-scalar arguments (others than string, int and float) were discarded. Now the arrays are included too [#5]

## [1.0.1] - 2019-11-11
### Fixed
- Anonimous function produce fatal errors [#1]

## [1.0.0] - 2019-11-05
First version

[#1]: https://github.com/php-gettext/PHP-Scanner/issues/1
[#5]: https://github.com/php-gettext/PHP-Scanner/issues/5
[#6]: https://github.com/php-gettext/PHP-Scanner/issues/6
[#8]: https://github.com/php-gettext/PHP-Scanner/issues/8
[#9]: https://github.com/php-gettext/PHP-Scanner/issues/9
[#11]: https://github.com/php-gettext/PHP-Scanner/issues/11
[#12]: https://github.com/php-gettext/PHP-Scanner/issues/12
[#14]: https://github.com/php-gettext/PHP-Scanner/issues/14

[1.3.1]: https://github.com/php-gettext/PHP-Scanner/compare/v1.3.0...v1.3.1
[1.3.0]: https://github.com/php-gettext/PHP-Scanner/compare/v1.2.2...v1.3.0
[1.2.2]: https://github.com/php-gettext/PHP-Scanner/compare/v1.2.1...v1.2.2
[1.2.1]: https://github.com/php-gettext/PHP-Scanner/compare/v1.2.0...v1.2.1
[1.2.0]: https://github.com/php-gettext/PHP-Scanner/compare/v1.1.1...v1.2.0
[1.1.1]: https://github.com/php-gettext/PHP-Scanner/compare/v1.1.0...v1.1.1
[1.1.0]: https://github.com/php-gettext/PHP-Scanner/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/php-gettext/PHP-Scanner/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/php-gettext/PHP-Scanner/releases/tag/v1.0.0
