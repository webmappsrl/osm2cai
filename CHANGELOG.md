# Changelog

## [233.4.1](https://github.com/webmappsrl/osm2cai/compare/v233.4.0...v233.4.1) (2023-12-21)


### Bug Fixes

* fixed nova global search ([7a0e24c](https://github.com/webmappsrl/osm2cai/commit/7a0e24c96b6543fc3ba7513fd66923a80adb26d4))

## [233.4.0](https://github.com/webmappsrl/osm2cai/compare/v233.3.2...v233.4.0) (2023-12-20)


### Features

* added automatic versioning workflow ([a84f265](https://github.com/webmappsrl/osm2cai/commit/a84f26543d39c84ae47b97287ad78380346f4f41))
* added codice sezione and nome sezione to regional csv export ([1bd6ec7](https://github.com/webmappsrl/osm2cai/commit/1bd6ec752e09c151862d0bfee05aad80af8a36ee))
* added dashboard to section detail nova ([903ed44](https://github.com/webmappsrl/osm2cai/commit/903ed44e416bcd4667e5f907fd7d385fce8382f1))
* added default value for issue_description in percorribilitá popup for hiking routes nova resource ([29142b6](https://github.com/webmappsrl/osm2cai/commit/29142b62306b4e6b9045a0965189effab27b5f55))
* added index query for regional users on hr. Now they can only see hiking routes for their region ([4a71aed](https://github.com/webmappsrl/osm2cai/commit/4a71aed488d21e8ad277514af4e7ba5af6ab40fa))
* added issues chronology to hiking routes ([d14e211](https://github.com/webmappsrl/osm2cai/commit/d14e211b984c86aef9497e8c42ecb71d4a6a631b))
* added osm2cai OSM import itinerary action ([d73a4d3](https://github.com/webmappsrl/osm2cai/commit/d73a4d35bfc35ce2256fa74e5857bd551a7680fa))
* added punti di interesse ([99541e9](https://github.com/webmappsrl/osm2cai/commit/99541e935f1d06c4dc203a16ad2fde2ec60ae837))
* added ref_reg and ref_reg_osm to hr nova ([a5caff9](https://github.com/webmappsrl/osm2cai/commit/a5caff99607f36fa330d9c1f48419a3c36d52c28))
* added rilievi section to side menu ([30fff20](https://github.com/webmappsrl/osm2cai/commit/30fff20aebc742cac2f895a8be203c7d8c7a4140))
* added ugc models ([d39bf2f](https://github.com/webmappsrl/osm2cai/commit/d39bf2f9e3bc15c3a6992edef47491fe21d6f73a))
* associate hr to itinerary reworked ([8797b28](https://github.com/webmappsrl/osm2cai/commit/8797b28ed22af120de2574efeb87624d4a515aae))
* improved nova dashboard ([f73d865](https://github.com/webmappsrl/osm2cai/commit/f73d8653f1c0385cc6b3ea7a32f22405cad41e0a))
* prevent detach hr from sections ([d61f824](https://github.com/webmappsrl/osm2cai/commit/d61f824cd31fcc52b4d7a98ef908dd3a3a9f3502))


### Bug Fixes

* Add ST_srid check for geometry in HikingRoutesRegionControllerV2 ([0181446](https://github.com/webmappsrl/osm2cai/commit/0181446a0347f5ee7435d62a39275a8603ffd512))
* fixed api v2 hiking-routes-collection bounding box ([77fd000](https://github.com/webmappsrl/osm2cai/commit/77fd000cf55c5fd121d81a3d5515cdc449fb52c9))
* fixed hr upload file text ([7524aa2](https://github.com/webmappsrl/osm2cai/commit/7524aa223fa36161d2c8199db2e5f09fc28014f3))
* fixed reg_ref ([bc561ac](https://github.com/webmappsrl/osm2cai/commit/bc561ac7503f75eba22b13305e767486a96de7d9))
* fixed restore user when emulate ([a73cca1](https://github.com/webmappsrl/osm2cai/commit/a73cca13435b89e60cc9bd986aaba444bb86d88b))
* moved cai_scale to tech tab in nova hikingroute resource ([f986213](https://github.com/webmappsrl/osm2cai/commit/f986213aa697e8d5297e1730892b5d3cd80c2577))
* policies for attach hr to sections ([2378614](https://github.com/webmappsrl/osm2cai/commit/23786147be2bdec846994ab9d7217c736e80ac65))
* restored dashboard ([036e9b0](https://github.com/webmappsrl/osm2cai/commit/036e9b0949acc83b17102cbfaef99d59d8a593ce))
* sections can now be created only from admin ([517220e](https://github.com/webmappsrl/osm2cai/commit/517220ee508ab1ec047640247dba5e79181ea266))
* typo on update release date script ([c0e24b4](https://github.com/webmappsrl/osm2cai/commit/c0e24b471e1c6d051e68c886a87a823b1acb7f0f))
* Update HikingRoutesRegionControllerV2 and app version ([1e2d7e2](https://github.com/webmappsrl/osm2cai/commit/1e2d7e26e38fde54de8627c926f60eb17770858e))
* updated confirm text in validate hr action ([c449e54](https://github.com/webmappsrl/osm2cai/commit/c449e54d6547151e5e8c762c9bee50eeb10cb8ef))
