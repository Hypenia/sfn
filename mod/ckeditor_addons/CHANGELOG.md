<a name="6.0.1"></a>
## [6.0.1](https://github.com/hypeJunction/Elgg-ckeditor_addons/compare/5.2.1...v6.0.1) (2017-05-08)


### Bug Fixes

* **upload:** clean up redudant settings ([276a2c3](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/276a2c3))

### Features

* **assets:** remove various hacks ([0444be2](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/0444be2))


### BREAKING CHANGES

* assets: Removes file upload and embedding logic, as it does not work consistenly
accross browsers and installations. Use hypeEmbed plugin for proper
embedding, and backward compatibility.
Removed linkembed plugin. Use hypeEmbed plugin for shortcode embedding of
links



<a name="6.0.0"></a>
# [6.0.0](https://github.com/hypeJunction/Elgg-ckeditor_addons/compare/5.2.1...v6.0.0) (2017-05-08)


### Features

* **assets:** remove various hacks ([0444be2](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/0444be2))


### BREAKING CHANGES

* assets: Removes file upload and embedding logic, as it does not work consistenly
accross browsers and installations. Use hypeEmbed plugin for proper
embedding, and backward compatibility.
Removed linkembed plugin. Use hypeEmbed plugin for shortcode embedding of
links



<a name="5.2.1"></a>
## [5.2.1](https://github.com/hypeJunction/Elgg-ckeditor_addons/compare/5.1.0...v5.2.1) (2017-04-09)


### Bug Fixes

* **addons:** properly add resize plugin ([e4ef55b](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/e4ef55b))

### Features

* **addons:** add autogrow plugin ([fb1fba7](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/fb1fba7))
* **css:** reset the ugly css ([44ccb19](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/44ccb19))



<a name="5.2.0"></a>
# [5.2.0](https://github.com/hypeJunction/Elgg-ckeditor_addons/compare/5.1.0...v5.2.0) (2017-04-09)


### Features

* **addons:** add autogrow plugin ([fb1fba7](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/fb1fba7))



<a name="5.1.0"></a>
# [5.1.0](https://github.com/hypeJunction/Elgg-ckeditor_addons/compare/5.0.0...v5.1.0) (2017-04-08)


### Bug Fixes

* **uploads:** correctly display (new) uploaded images in all image filetypes supported by cked ([7a9ed53](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/7a9ed53))
* **uploads:** display existing images on browsing ([0284f0f](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/0284f0f))
* **uploads:** save ckeditor_file entity on upload processing ([273f85f](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/273f85f))
* **uploads:** uploads works also with caching enabled ([8a9172c](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/8a9172c))



<a name="5.0.0"></a>
# [5.0.0](https://github.com/hypeJunction/Elgg-ckeditor_addons/compare/2.0.3...v5.0.0) (2016-12-23)


### Bug Fixes

* **classes:** add missing use statement ([46e834b](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/46e834b))
* **js:** ckeditor is again instantiated when appended via AJAX ([a9b918b](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/a9b918b))

### Features

* **deps:** now requires Elgg 2.3 ([f23d589](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/f23d589))
* **files:** switch to the improved file and upload handling API ([ea88b71](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/ea88b71))
* **js:** editor now responds to form reset event ([90428ed](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/90428ed))
* **releases:** upgrade to Elgg 2.2 ([5d22ef7](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/5d22ef7))
* **uploads:** upgrade embeded files to entities ([0243794](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/0243794))


### BREAKING CHANGES

* deps: Now requires Elgg 2.3
* releases: Now requires Elgg 2.2
Refactors the JS, some of the views were removed for compatibility with
the modified elgg/ckeditor module
* uploads: Images uploaded prior to this change will still be visible in posts, however
they will no longer be listed in the Browse the Server popup window



<a name="4.0.0"></a>
# [4.0.0](https://github.com/hypeJunction/Elgg-ckeditor_addons/compare/3.1.0...v4.0.0) (2016-09-15)


### Features

* **releases:** upgrade to Elgg 2.2 ([5d22ef7](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/5d22ef7))


### BREAKING CHANGES

* releases: Now requires Elgg 2.2
Refactors the JS, some of the views were removed for compatibility with
the modified elgg/ckeditor module



<a name="3.1.0"></a>
# [3.1.0](https://github.com/hypeJunction/Elgg-ckeditor_addons/compare/2.0.3...v3.1.0) (2016-04-06)


### Bug Fixes

* **classes:** add missing use statement ([46e834b](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/46e834b))
* **js:** ckeditor is again instantiated when appended via AJAX ([a9b918b](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/a9b918b))

### Features

* **js:** editor now responds to form reset event ([90428ed](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/90428ed))
* **uploads:** upgrade embeded files to entities ([0243794](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/0243794))


### BREAKING CHANGES

* uploads: Images uploaded prior to this change will still be visible in posts, however
they will no longer be listed in the Browse the Server popup window



<a name="3.0.2"></a>
## [3.0.2](https://github.com/hypeJunction/Elgg-ckeditor_addons/compare/2.0.3...v3.0.2) (2016-03-21)


### Bug Fixes

* **classes:** add missing use statement ([46e834b](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/46e834b))
* **js:** ckeditor is again instantiated when appended via AJAX ([a9b918b](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/a9b918b))

### Features

* **uploads:** upgrade embeded files to entities ([0243794](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/0243794))


### BREAKING CHANGES

* uploads: Images uploaded prior to this change will still be visible in posts, however
they will no longer be listed in the Browse the Server popup window



<a name="3.0.1"></a>
## [3.0.1](https://github.com/hypeJunction/Elgg-ckeditor_addons/compare/2.0.3...v3.0.1) (2016-02-24)


### Bug Fixes

* **classes:** add missing use statement ([46e834b](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/46e834b))

### Features

* **uploads:** upgrade embeded files to entities ([0243794](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/0243794))


### BREAKING CHANGES

* uploads: Images uploaded prior to this change will still be visible in posts, however
they will no longer be listed in the Browse the Server popup window



<a name="3.0.0"></a>
# [3.0.0](https://github.com/hypeJunction/Elgg-ckeditor_addons/compare/2.0.3...v3.0.0) (2016-02-19)


### Features

* **uploads:** upgrade embeded files to entities ([0243794](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/0243794))


### BREAKING CHANGES

* uploads: Images uploaded prior to this change will still be visible in posts, however
they will no longer be listed in the Browse the Server popup window



<a name="2.0.3"></a>
## [2.0.3](https://github.com/hypeJunction/Elgg-ckeditor_addons/compare/2.0.1...v2.0.3) (2016-01-06)


### Bug Fixes

* **browser:** fix file browser styling and JS ([9b34f2e](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/9b34f2e))
* **browser:** fix file browser styling and JS ([bdf2ed0](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/bdf2ed0))
* **embed:** fix placeholder image URL ([b8ba243](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/b8ba243))
* **manifest:** fix minimum require Elgg version ([f318620](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/f318620))
* **settings:** fix default values on activation ([7c00df0](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/7c00df0))



<a name="2.0.2"></a>
## [2.0.2](https://github.com/hypeJunction/Elgg-ckeditor_addons/compare/2.0.1...v2.0.2) (2016-01-06)


### Bug Fixes

* **browser:** fix file browser styling and JS ([9b34f2e](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/9b34f2e))
* **browser:** fix file browser styling and JS ([bdf2ed0](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/bdf2ed0))
* **settings:** fix default values on activation ([7c00df0](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/7c00df0))



<a name="2.0.1"></a>
## [2.0.1](https://github.com/hypeJunction/Elgg-ckeditor_addons/compare/1.0.1...v2.0.1) (2015-11-01)


### Bug Fixes

* **js:** fixes syntax error ([3fd74df](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/3fd74df))
* **js:** update event binding ([a60b553](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/a60b553))

### Features

* **assets:** remove js and css prefixes ([fb29e57](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/fb29e57))
* **composer:** add composer.json ([68fc58c](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/68fc58c))
* **core:** reorganize assets into a more logical tree ([6549dfc](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/6549dfc))
* **grunt:** automated releases ([869b4fa](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/869b4fa))
* **js:** improve ckeditor initialization ([8616e96](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/8616e96))
* **js:** make config a simplecache view ([0af0a3e](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/0af0a3e))
* **releases:** changelog automation ([30fa70e](https://github.com/hypeJunction/Elgg-ckeditor_addons/commit/30fa70e))







