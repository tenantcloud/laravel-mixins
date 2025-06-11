## [3.2.2](https://github.com/tenantcloud/laravel-mixins/compare/v3.2.1...v3.2.2) (2025-06-11)


### Bug Fixes

* Added support laravel 11 ([#39](https://github.com/tenantcloud/laravel-mixins/issues/39)) ([4a67704](https://github.com/tenantcloud/laravel-mixins/commit/4a677042dd809a5411095d5f73ad783f8ae8a34e))

## [3.2.1](https://github.com/tenantcloud/laravel-mixins/compare/v3.2.0...v3.2.1) (2024-09-12)


### Bug Fixes

* Fixed chunk with queue delay ([#38](https://github.com/tenantcloud/laravel-mixins/issues/38)) ([43e7963](https://github.com/tenantcloud/laravel-mixins/commit/43e79635b92430c043fc0d174d193a1b6d3e6a08))

# [3.2.0](https://github.com/tenantcloud/laravel-mixins/compare/v3.1.0...v3.2.0) (2024-08-28)


### Features

* Move conditionalUpdate to regular query builder ([#37](https://github.com/tenantcloud/laravel-mixins/issues/37)) ([66f7fe9](https://github.com/tenantcloud/laravel-mixins/commit/66f7fe99f89d025c5f2e0c5bc3863e53898405e2))

# [3.1.0](https://github.com/tenantcloud/laravel-mixins/compare/v3.0.1...v3.1.0) (2024-04-25)


### Features

* Template sync ([#31](https://github.com/tenantcloud/laravel-mixins/issues/31)) ([f97041d](https://github.com/tenantcloud/laravel-mixins/commit/f97041d53fc12ca75c61bcffc897244d7c0c11c8)), closes [#30](https://github.com/tenantcloud/laravel-mixins/issues/30)

## [3.0.1](https://github.com/tenantcloud/laravel-mixins/compare/v3.0.0...v3.0.1) (2024-03-26)


### Bug Fixes

* Added $minChunkNumber to chunkWithQueue mixin ([#29](https://github.com/tenantcloud/laravel-mixins/issues/29)) ([83511c7](https://github.com/tenantcloud/laravel-mixins/commit/83511c78d8bde138248800d695d9974d92024e1c))

# [3.0.0](https://github.com/tenantcloud/laravel-mixins/compare/v2.8.1...v3.0.0) (2024-02-13)


### Bug Fixes

* Change method name as it conflicts with Laravel method ([#28](https://github.com/tenantcloud/laravel-mixins/issues/28)) ([06ec775](https://github.com/tenantcloud/laravel-mixins/commit/06ec775a6d1d88a204b8609ade49cc64d11c7399))


### BREAKING CHANGES

* Renamed Arr::select() to Arr::selectWildcard()
