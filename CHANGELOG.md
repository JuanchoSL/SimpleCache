# Change Simple Cache


## [1.0.4] - 2024-12-12

### Added
- LoggerAwwaitinterface in order to log debugs or any info from functions
- Throws exceptions on connection error
- setMaxTtl to library interface in order to be able to set the max time to live
- check for required modules before try to connect
- check PHP 8.4 compatibility

### Changed
- composer update

### Fixed
- Modify test-keys in order to enable test from Memcache and Memcached drivers
- PsrSimpleCacheAdapter check if key is a valid value or throw exception
- Fix composer require for Exceptions

## [1.0.3] - 2024-06-20

### Added
- Engine Enums
- Factory using Engine enums

### Changed
- Clean Unit tests using data providers

### Fixed
- clear method for Session and Process caches, remove all keys but not deleted the master repository


## [1.0.2] - 2024-03-05

### Added

- Create adapter PsrSimpleCacheAdapter for compatibility with PSR-16
- ALl libraries are compatibles with PSR-16 Simple CacheInterface
- Tests for every cache type
- Tests for PSR-16 SimpleCache using adapter

### Changed

- nullable ttl for setter

### Fixed

- minor fix for session start


## [1.0.1] - 2024-01-21

### Added

- Git attributes, in order to clean the output
- Strict types declaration

### Changed

- Quality code

### Fixed


## [1.0.0] - 2023-09-25

### Added

- Initial release, first version

### Changed

### Fixed
