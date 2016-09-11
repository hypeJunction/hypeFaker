<a name="2.1.0"></a>
# [2.1.0](https://github.com/hypeJunction/hypeFaker/compare/2.0.1...v2.1.0) (2016-09-11)


### Bug Fixes

* **dependencies:** version 1.5 of faker has issues with autoloading, rolling back to 1.3 ([a77e26a](https://github.com/hypeJunction/hypeFaker/commit/a77e26a))

### Features

* **comments:** adds a script to generate fake comments ([c028dbb](https://github.com/hypeJunction/hypeFaker/commit/c028dbb))
* **generators:** adds discussions and replies generator ([421933d](https://github.com/hypeJunction/hypeFaker/commit/421933d))
* **generators:** integrates with countries plugin to generate real-world locations ([bffe8c6](https://github.com/hypeJunction/hypeFaker/commit/bffe8c6))
* **likes:** adds a script to generate likes ([143fb98](https://github.com/hypeJunction/hypeFaker/commit/143fb98))



<a name="2.0.1"></a>
## [2.0.1](https://github.com/hypeJunction/hypeFaker/compare/2.0.0...v2.0.1) (2016-02-05)


### Bug Fixes

* **manifest:** fix versioning in manifest ([6441f39](https://github.com/hypeJunction/hypeFaker/commit/6441f39))



<a name="2.0.0"></a>
# [2.0.0](https://github.com/hypeJunction/hypeFaker/compare/1.1.0...v2.0.0) (2016-02-05)


### Bug Fixes

* **core:** fix autoloading and namespaces ([cb70429](https://github.com/hypeJunction/hypeFaker/commit/cb70429))
* **core:** prefix function declarations ([67d31a7](https://github.com/hypeJunction/hypeFaker/commit/67d31a7))
* **views:** fix deprecated use of confirm action ([52551d9](https://github.com/hypeJunction/hypeFaker/commit/52551d9))

### Features

* **assets:** get rid of JS and CSS ([461c038](https://github.com/hypeJunction/hypeFaker/commit/461c038))
* **core:** drop support for earlier Elgg versions ([bba4644](https://github.com/hypeJunction/hypeFaker/commit/bba4644))
* **core:** remove function from global scope ([7228d6e](https://github.com/hypeJunction/hypeFaker/commit/7228d6e))
* **core:** remove global constants ([9efa227](https://github.com/hypeJunction/hypeFaker/commit/9efa227))
* **core:** remove hypeJunction\\Faker namespace from views and start ([5b94986](https://github.com/hypeJunction/hypeFaker/commit/5b94986))
* **releases:** add Gruntfile.js ([8384737](https://github.com/hypeJunction/hypeFaker/commit/8384737))
* **releases:** automate releases with grunt ([7057459](https://github.com/hypeJunction/hypeFaker/commit/7057459))
* **users:** improve user faking logic ([82e58ec](https://github.com/hypeJunction/hypeFaker/commit/82e58ec))


### BREAKING CHANGES

* core: this remove support for versions of Elgg earlier that 2.0
* core: this removes some functions from global scope and declares
them in actions that use them
* core: this remove global LOCALE constant and replaces it with a plugin
setting



