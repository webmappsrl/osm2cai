# Changelog

## [233.33.0](https://github.com/webmappsrl/osm2cai/compare/v233.32.0...v233.33.0) (2024-08-30)


### Features

* update survey policies OC:3873 ([#126](https://github.com/webmappsrl/osm2cai/issues/126)) ([88637b8](https://github.com/webmappsrl/osm2cai/commit/88637b8f2f267ee3d179c01638fdb86cf6364ab0))

## [233.32.0](https://github.com/webmappsrl/osm2cai/compare/v233.31.0...v233.32.0) (2024-08-30)


### Features

* add_gpx_api_to_hiking_route  oc:3591 ([9e9ddb6](https://github.com/webmappsrl/osm2cai/commit/9e9ddb6965074b4d78bd8cb0f47aa889f1da8d8d))
* added source_surveys endpoints to api v2 ([#125](https://github.com/webmappsrl/osm2cai/issues/125)) ([30f3447](https://github.com/webmappsrl/osm2cai/commit/30f344709098ebab35b61d5cd06e8d942390dbf2))
* added tdh computing and api caching to hr events observer OC:3608 ([e0c075d](https://github.com/webmappsrl/osm2cai/commit/e0c075d097035e7579e33ecd994e6a598e04de2c))
* cai huts reconciliation OC:3629 ([#121](https://github.com/webmappsrl/osm2cai/issues/121)) ([3ca2a14](https://github.com/webmappsrl/osm2cai/commit/3ca2a148f7c5a53e71fd68951e132b99439f46eb))
* cai_huts_sync_with_osmfeatures OC:3632 ([#122](https://github.com/webmappsrl/osm2cai/issues/122)) ([226c6b7](https://github.com/webmappsrl/osm2cai/commit/226c6b71c738180c39e97508179b16c16ba9115f))
* improve sync ugc from geohub OC:3868 ([#124](https://github.com/webmappsrl/osm2cai/issues/124)) ([dc934da](https://github.com/webmappsrl/osm2cai/commit/dc934daef2edd4798a7989ccbe3fb98726e2e085))
* updated mitur api caching for cai huts to get images from rifugi api ([5ba510e](https://github.com/webmappsrl/osm2cai/commit/5ba510e9aca61ec80a2025ca8cffd79a74fa9c05))


### Bug Fixes

* fixed cai huts links in mitur api ([c0edbc0](https://github.com/webmappsrl/osm2cai/commit/c0edbc0eca1800c465de485fb323f2b8f742b401))
* fixed ec pois type in mitur abruzzo api ([1ec798b](https://github.com/webmappsrl/osm2cai/commit/1ec798b261eabdd4d3ba8b02cd2e304062a7928a))
* fixed images caching to include uppercase version ([547a147](https://github.com/webmappsrl/osm2cai/commit/547a1472250a65bca8eb4bdef688b238d6edf7a7))
* get ec pois's hiking routes in buffer only for osm2cai_status 4 ([b8753df](https://github.com/webmappsrl/osm2cai/commit/b8753df083d1fc235db83b4dc6a4774919bc0c7a))


### Miscellaneous Chores

* added progress bar to calculate intersections command ([8a2486a](https://github.com/webmappsrl/osm2cai/commit/8a2486a9e68c8cc7dee22fd666f0447bbf2d82e4))
* changed mitur api properties order ([cfbb108](https://github.com/webmappsrl/osm2cai/commit/cfbb1083731c634114e009d42dbb26fba8badce2))
* updated enrich from osmfeatures command to accept --score option ([55d6408](https://github.com/webmappsrl/osm2cai/commit/55d640809400f4012f75379dea3169125ce6b902))
* updated type key in ec_pois mitur api ([3660494](https://github.com/webmappsrl/osm2cai/commit/36604945c3777945eacf2e2a501d57ec246bdbb2))

## [233.31.0](https://github.com/webmappsrl/osm2cai/compare/v233.30.5...v233.31.0) (2024-07-05)


### Features

* added area stats fields ([e4ec8a9](https://github.com/webmappsrl/osm2cai/commit/e4ec8a99397ac99b67fde4b78a5181c2455d121e))
* added description and abstract to region api data caching. Added filter for images files in the command ([eab055c](https://github.com/webmappsrl/osm2cai/commit/eab055cdf4c2bde965aa2905e6baeda928731559))
* added description and abstract to region api data caching. Added filter for images files in the command ([51aa5b7](https://github.com/webmappsrl/osm2cai/commit/51aa5b76c67ae838d4c30660cf803cb7bfe8a612))
* added enrich pois from osmfeatures ([4203d0d](https://github.com/webmappsrl/osm2cai/commit/4203d0d156817de3710f3c1090f33e7661f729b9))
* added enrich pois from osmfeatures ([96cb861](https://github.com/webmappsrl/osm2cai/commit/96cb861b1058b097243fc8f65656a08bc8c0def9))
* added enrich pois from osmfeatures ([59a4c71](https://github.com/webmappsrl/osm2cai/commit/59a4c7134aff4f77ed76083b362d2e226b5a3958))
* added enrich pois from osmfeatures ([76c666d](https://github.com/webmappsrl/osm2cai/commit/76c666dc7dbff2369a43b36a243da75aef59d725))
* added mountain groups hr map ([2f8e49c](https://github.com/webmappsrl/osm2cai/commit/2f8e49c2c420983dbad6e4c5844d508201c30fa4))
* added mountain groups map ([6207fa3](https://github.com/webmappsrl/osm2cai/commit/6207fa3d8bb63b4110e4ab559a2b9402f1feea7e))
* added poi map ([43e6e58](https://github.com/webmappsrl/osm2cai/commit/43e6e5801ee899f872b2d888a85d8b64f02598f6))
* cai-hut map ([df651b6](https://github.com/webmappsrl/osm2cai/commit/df651b61edb6173a5d06940f9f5f3dd8540ba5e8))
* implemented api data caching for ec pois ([06a2fb0](https://github.com/webmappsrl/osm2cai/commit/06a2fb001c5b2d378eca4e74c5af32002e9592e1))


### Bug Fixes

* added name to cai hut point ([7ea22dc](https://github.com/webmappsrl/osm2cai/commit/7ea22dcbdd7d32199eb72901b79e388ee33fc81e))
* minor bug fixes ([9186abd](https://github.com/webmappsrl/osm2cai/commit/9186abd0033457a5b68abe7f523b9834dc0f6a8e))
* minor bug fixes ([ecac246](https://github.com/webmappsrl/osm2cai/commit/ecac24610b696c1da6b1ec495a9284a34f68e80f))
* updated cache command to include new fields for mountain groups api data caching ([0ad8979](https://github.com/webmappsrl/osm2cai/commit/0ad897906bcb2cd4cc0d78574d49b0069756374f))
* updated osm2caistatus parameter for mountain grous api ([895b7f4](https://github.com/webmappsrl/osm2cai/commit/895b7f46efd6d5fe22f50a134eb93976fe3f9d18))


### Miscellaneous Chores

* refactored tdh command with dem.maphub ([652cf10](https://github.com/webmappsrl/osm2cai/commit/652cf10e3b305610da5fd86d1d1a881cc6c06d87))
* updated api documentation ([2f7f716](https://github.com/webmappsrl/osm2cai/commit/2f7f716585c88c0640577f2374acc91647891dfd))
* updated map controller ([94f4d51](https://github.com/webmappsrl/osm2cai/commit/94f4d51954a5774bacaf78cc9092d001f0f4c9d1))
* updated map routes ([3841e73](https://github.com/webmappsrl/osm2cai/commit/3841e73242a59b71a2b91af86d823742f48a7b24))

## [233.30.5](https://github.com/webmappsrl/osm2cai/compare/v233.30.4...v233.30.5) (2024-06-27)


### Bug Fixes

* fixed is active water source ([1139a88](https://github.com/webmappsrl/osm2cai/commit/1139a88330b7a597d78e42c37d291d8b08c6f0f5))

## [233.30.4](https://github.com/webmappsrl/osm2cai/compare/v233.30.3...v233.30.4) (2024-06-27)


### Bug Fixes

* fixed acquasorgente overlays api ([c7643ac](https://github.com/webmappsrl/osm2cai/commit/c7643acec73f321b1c07b14eef3545564a8ea4f5))

## [233.30.3](https://github.com/webmappsrl/osm2cai/compare/v233.30.2...v233.30.3) (2024-06-21)


### Bug Fixes

* flow rate calculated only when water flow rate is validated ([db70c1a](https://github.com/webmappsrl/osm2cai/commit/db70c1a5ed9d0beae26028763fd18d52982e0867))

## [233.30.2](https://github.com/webmappsrl/osm2cai/compare/v233.30.1...v233.30.2) (2024-06-21)


### Bug Fixes

* fixed volume and time calculation for source surveys ([fc2f958](https://github.com/webmappsrl/osm2cai/commit/fc2f958e613396194671fbebab12bcb05ce1da21))

## [233.30.1](https://github.com/webmappsrl/osm2cai/compare/v233.30.0...v233.30.1) (2024-06-19)


### Bug Fixes

* bug fix in edit mode source survey ([3fed8b7](https://github.com/webmappsrl/osm2cai/commit/3fed8b7312eaf9a7f4d4d0ece55cdfa66077af49))

## [233.30.0](https://github.com/webmappsrl/osm2cai/compare/v233.29.0...v233.30.0) (2024-06-13)


### Features

* added has_photo boolean column to ugc_pois table ([755ad64](https://github.com/webmappsrl/osm2cai/commit/755ad64889fee4fe2daf5df960e145d5f950dd26))
* added has_photo boolean column to ugc_pois table ([ccdfd8e](https://github.com/webmappsrl/osm2cai/commit/ccdfd8ed4475ab8e49bdbf45887dddf5c82b7e1d))


### Bug Fixes

* fixed source survey edit nova ([71f463c](https://github.com/webmappsrl/osm2cai/commit/71f463c2aacf8926cae14b006b8e5649b7c68a63))
* fixed source survey edit nova ([9d6b821](https://github.com/webmappsrl/osm2cai/commit/9d6b821af3ae47f32299d8d9da22266ba5d1f728))


### Miscellaneous Chores

* enhanced computing source surveys data process ([9283ce8](https://github.com/webmappsrl/osm2cai/commit/9283ce84d0556baf1300d9032baa98042c032b40))
* enhanced computing source surveys data process ([3c4d46a](https://github.com/webmappsrl/osm2cai/commit/3c4d46a47fde9f96e2d5607b15ee2f9aa4502ad3))
* mitur abruzzo api enhancement ([cab1609](https://github.com/webmappsrl/osm2cai/commit/cab1609ab9e06ce1382856483f4a248136ce3ec6))
* mitur abruzzo api enhancement ([9bb773b](https://github.com/webmappsrl/osm2cai/commit/9bb773b6fcb392a50b0a87f9d907c66783efebf3))
* updated authorizations for source surveys nova resource ([dff382e](https://github.com/webmappsrl/osm2cai/commit/dff382e5e5e074829cc5ed58b6652ef5c234b235))
* updated authorizations for source surveys nova resource ([92e8eca](https://github.com/webmappsrl/osm2cai/commit/92e8eca56c5ba934a2477be9af057aa31f444826))

## [233.29.0](https://github.com/webmappsrl/osm2cai/compare/v233.28.0...v233.29.0) (2024-06-07)


### Features

* added source survey nova resource oc:3431 ([3cda15d](https://github.com/webmappsrl/osm2cai/commit/3cda15db2ba569e1572f234ffc37dcde6b4e8632))
* added source survey nova resource oc:3431 ([3db4f25](https://github.com/webmappsrl/osm2cai/commit/3db4f25c690fb565cb09fe37bd9845e0973bf4e6))


### Bug Fixes

* abstract mitur abruzzo api ([8400b0a](https://github.com/webmappsrl/osm2cai/commit/8400b0a9f212ce7592636781d0ff6df1e61147e7))
* added geometry and validation to overlay geojson ([9a8771e](https://github.com/webmappsrl/osm2cai/commit/9a8771e259f54df70bc817a5fa54c02b9fbfda64))
* added geometry and validation to overlay geojson ([e57452e](https://github.com/webmappsrl/osm2cai/commit/e57452eeca4a2e73fefcb33f6648ff59c89ec195))


### Miscellaneous Chores

* enhanced mitur abruzzo apis ([b0d45c2](https://github.com/webmappsrl/osm2cai/commit/b0d45c2bc0dff6469a8b03740069637988b5f060))
* enhanced mitur abruzzo apis ([d9f1f77](https://github.com/webmappsrl/osm2cai/commit/d9f1f772d28aa8db96b8a7b5410c694e53ff4906))

## [233.28.0](https://github.com/webmappsrl/osm2cai/compare/v233.27.0...v233.28.0) (2024-06-06)

### Features

- added source survey nova resource oc:3431 ([1487c73](https://github.com/webmappsrl/osm2cai/commit/1487c73d8b4d494946cca5c6965f41a0a1a9bce2))

## [233.27.0](https://github.com/webmappsrl/osm2cai/compare/v233.26.0...v233.27.0) (2024-06-03)

### Features

- api mitur abruzzo enhancement ([b906d71](https://github.com/webmappsrl/osm2cai/commit/b906d715da5998d75d417795b47878c9840a1758))
- updated mitur abruzzo apis ([ddf4ed3](https://github.com/webmappsrl/osm2cai/commit/ddf4ed355d91f0c602fe17d28bbe17cec8a3a583))

### Bug Fixes

- fixed abstract in abruzzo api ([27cb398](https://github.com/webmappsrl/osm2cai/commit/27cb398f74c7930cafeb29c84e6c4e29eacbc475))
- fixed mitur abruzzo apis ([9c0696c](https://github.com/webmappsrl/osm2cai/commit/9c0696cb6fe08e0e3ce40a82f171019c67accacb))

## [233.26.0](https://github.com/webmappsrl/osm2cai/compare/v233.25.0...v233.26.0) (2024-05-07)

### Features

- updated export apis for ec pois user association ([7076821](https://github.com/webmappsrl/osm2cai/commit/70768219f71ce57a70052dba271b03fb49cc7df8))

## [233.25.0](https://github.com/webmappsrl/osm2cai/compare/v233.24.3...v233.25.0) (2024-05-02)

### Features

- added check user no match command to ugc poi ([003e60a](https://github.com/webmappsrl/osm2cai/commit/003e60ae78f73e30ca731ced98148ed2de70007c))

## [233.24.3](https://github.com/webmappsrl/osm2cai/compare/v233.24.2...v233.24.3) (2024-05-02)

### Bug Fixes

- fixed user no match in ugc import from geohub ([301bc92](https://github.com/webmappsrl/osm2cai/commit/301bc92544f15a1d5480fc7249792dfb0aa8b4fe))

## [233.24.2](https://github.com/webmappsrl/osm2cai/compare/v233.24.1...v233.24.2) (2024-04-30)

### Bug Fixes

- mock opening hours in mitur huts api ([0932731](https://github.com/webmappsrl/osm2cai/commit/093273109049aa76d7d3d266e89abd6ea58969d8))

## [233.24.1](https://github.com/webmappsrl/osm2cai/compare/v233.24.0...v233.24.1) (2024-04-30)

### Features

* added source survey nova resource oc:3431 ([1487c73](https://github.com/webmappsrl/osm2cai/commit/1487c73d8b4d494946cca5c6965f41a0a1a9bce2))

## [233.27.0](https://github.com/webmappsrl/osm2cai/compare/v233.26.0...v233.27.0) (2024-06-03)


### Features

* api mitur abruzzo enhancement ([b906d71](https://github.com/webmappsrl/osm2cai/commit/b906d715da5998d75d417795b47878c9840a1758))
* updated mitur abruzzo apis ([ddf4ed3](https://github.com/webmappsrl/osm2cai/commit/ddf4ed355d91f0c602fe17d28bbe17cec8a3a583))


### Bug Fixes

* fixed abstract in abruzzo api ([27cb398](https://github.com/webmappsrl/osm2cai/commit/27cb398f74c7930cafeb29c84e6c4e29eacbc475))
* fixed mitur abruzzo apis ([9c0696c](https://github.com/webmappsrl/osm2cai/commit/9c0696cb6fe08e0e3ce40a82f171019c67accacb))

## [233.26.0](https://github.com/webmappsrl/osm2cai/compare/v233.25.0...v233.26.0) (2024-05-07)


### Features

* updated export apis for ec pois user association ([7076821](https://github.com/webmappsrl/osm2cai/commit/70768219f71ce57a70052dba271b03fb49cc7df8))

## [233.25.0](https://github.com/webmappsrl/osm2cai/compare/v233.24.3...v233.25.0) (2024-05-02)


### Features

* added check user no match command to ugc poi ([003e60a](https://github.com/webmappsrl/osm2cai/commit/003e60ae78f73e30ca731ced98148ed2de70007c))

## [233.24.3](https://github.com/webmappsrl/osm2cai/compare/v233.24.2...v233.24.3) (2024-05-02)


### Bug Fixes

* fixed user no match in ugc import from geohub ([301bc92](https://github.com/webmappsrl/osm2cai/commit/301bc92544f15a1d5480fc7249792dfb0aa8b4fe))

## [233.24.2](https://github.com/webmappsrl/osm2cai/compare/v233.24.1...v233.24.2) (2024-04-30)


### Bug Fixes

* mock opening hours in mitur huts api ([0932731](https://github.com/webmappsrl/osm2cai/commit/093273109049aa76d7d3d266e89abd6ea58969d8))

## [233.24.1](https://github.com/webmappsrl/osm2cai/compare/v233.24.0...v233.24.1) (2024-04-30)


### Bug Fixes

* fixed hr id on huts api (was relation id before, now is taking osm2cai id) ([f280bd6](https://github.com/webmappsrl/osm2cai/commit/f280bd6ee31529967f3cfb751a7fb863ac140420))
* fixed mitur abruzzo apis ([6202699](https://github.com/webmappsrl/osm2cai/commit/62026996942c6cc8f9b26ff93df997ae97186519))
* fixed section ids key to mitur abruzzo apis ([fc4ba7c](https://github.com/webmappsrl/osm2cai/commit/fc4ba7cf95f63e19b8d53c51c127b43b47f0f9a9))


### Miscellaneous Chores

* enhanced mitur abruzzo huts api documentation ([8553eb5](https://github.com/webmappsrl/osm2cai/commit/8553eb597ed560fa03482aabef2e6592cea40f2c))
* 

## [233.24.0](https://github.com/webmappsrl/osm2cai/compare/v233.23.0...v233.24.0) (2024-04-18)

### Features

- updated export api ([36dc9a5](https://github.com/webmappsrl/osm2cai/commit/36dc9a52b62b33d0c726c6f4c0ffc64824138d5d))

## [233.23.0](https://github.com/webmappsrl/osm2cai/compare/v233.22.2...v233.23.0) (2024-04-18)

### Features

- implemented areas export api ([44f2990](https://github.com/webmappsrl/osm2cai/commit/44f29902eb9cf28d331a7242ca81624f778bb26a))
- implemented ec pois export api ([743ce23](https://github.com/webmappsrl/osm2cai/commit/743ce2306c05ffea864e21856580f8c9bb364a54))
- implemented export api for hiking_routes list and single feature ([7567c3d](https://github.com/webmappsrl/osm2cai/commit/7567c3d60b61dec0823b39d683ee0f9cf33f0f31))
- implemented huts export api ([6598f49](https://github.com/webmappsrl/osm2cai/commit/6598f49188a7c167315b882dfa443190615674de))
- implemented itineraries export api ([057f760](https://github.com/webmappsrl/osm2cai/commit/057f760f588f71d61832d948f58bfd7b3de7b100))
- implemented mountain groups export api ([662d549](https://github.com/webmappsrl/osm2cai/commit/662d5492bd9c89006be0c80f65714908de984c6d))
- implemented natural springs export api ([526cc31](https://github.com/webmappsrl/osm2cai/commit/526cc31ef1fd4b75d2de39de34b429632998e854))
- implemented sections export api ([0c6cf1a](https://github.com/webmappsrl/osm2cai/commit/0c6cf1a267353aadf316dc47951d4570dd844634))
- implemented sectors export api ([1300004](https://github.com/webmappsrl/osm2cai/commit/1300004de5dad9d59b8cbea22dd88320d7d0cedb))
- implemented ugc media export api ([431cbf8](https://github.com/webmappsrl/osm2cai/commit/431cbf8b18c177d69eec9b958218d2d24c1cb340))
- implemented ugc pois export api ([96b6d35](https://github.com/webmappsrl/osm2cai/commit/96b6d355c97e473230bcd306be4b0f148939b217))
- implemented ugc track export api ([ea95d16](https://github.com/webmappsrl/osm2cai/commit/ea95d165cf1e3baaf064f1e61b7b5588f514ca42))
- implemented users export api ([c0afd85](https://github.com/webmappsrl/osm2cai/commit/c0afd858a98ecd10e403c3d41543687d75021ebb))

## [233.22.2](https://github.com/webmappsrl/osm2cai/compare/v233.22.1...v233.22.2) (2024-04-17)

### Bug Fixes

- fixed natural springs sync command ([9efc067](https://github.com/webmappsrl/osm2cai/commit/9efc067768b74cc5100306e9ff49231e5514c05d))

## [233.22.1](https://github.com/webmappsrl/osm2cai/compare/v233.22.0...v233.22.1) (2024-04-17)

### Bug Fixes

- fixed poi api caching ([e302ba5](https://github.com/webmappsrl/osm2cai/commit/e302ba52ca2262fa16a4820dfaa07d12ebebd786))

## [233.22.0](https://github.com/webmappsrl/osm2cai/compare/v233.21.1...v233.22.0) (2024-04-17)

### Features

- improved poi mitur api performance ([c70b3d4](https://github.com/webmappsrl/osm2cai/commit/c70b3d408151d7ee7ea6484f79bf1cfaecbbd036))

## [233.21.1](https://github.com/webmappsrl/osm2cai/compare/v233.21.0...v233.21.1) (2024-04-16)

### Bug Fixes

- fixed calculate intersections command ([91ec821](https://github.com/webmappsrl/osm2cai/commit/91ec821c520c0136dfdb20b1fc1ed7308558b40e))

## [233.21.0](https://github.com/webmappsrl/osm2cai/compare/v233.20.1...v233.21.0) (2024-04-16)

### Features

- improved api performance for mountain groups. Added intersections columns to mountain groups table and created nova action and command to calculate intersections ([c55d152](https://github.com/webmappsrl/osm2cai/commit/c55d15234d3af4e9e4eb6387aa8a43e8e0f89a58))

## [233.20.1](https://github.com/webmappsrl/osm2cai/compare/v233.20.0...v233.20.1) (2024-04-16)

### Bug Fixes

- improved mitur hiking routes api examples ([8f0b7f2](https://github.com/webmappsrl/osm2cai/commit/8f0b7f213add38142bfd344153e2427be804541c))

## [233.20.0](https://github.com/webmappsrl/osm2cai/compare/v233.19.2...v233.20.0) (2024-04-16)

### Features

- updated api doc ([609170b](https://github.com/webmappsrl/osm2cai/commit/609170b20d46d011852ef942a9729dbf5cd1d174))
- updated api docs ([f1fdbbb](https://github.com/webmappsrl/osm2cai/commit/f1fdbbb8d4cb46628d90fd6c7bbd63a71d354912))

## [233.19.2](https://github.com/webmappsrl/osm2cai/compare/v233.19.1...v233.19.2) (2024-04-10)

### Bug Fixes

- fixed import ugc ([8f91d29](https://github.com/webmappsrl/osm2cai/commit/8f91d297f623f59251f77e4b9204123f54b0004f))

## [233.19.1](https://github.com/webmappsrl/osm2cai/compare/v233.19.0...v233.19.1) (2024-04-03)

### Bug Fixes

- fixed ec pois xlsx ([407b25b](https://github.com/webmappsrl/osm2cai/commit/407b25b2d45ed4838d79621013942ff39ff158cc))

## [233.19.0](https://github.com/webmappsrl/osm2cai/compare/v233.18.0...v233.19.0) (2024-04-02)

### Features

- implemented form_id update on creating new ugc poi ([e749f48](https://github.com/webmappsrl/osm2cai/commit/e749f48517eb1e7a3fa0e14c40cb308fa39223b0))

## [233.18.0](https://github.com/webmappsrl/osm2cai/compare/v233.17.0...v233.18.0) (2024-04-02)

### Features

- added fields for api to sections table and updated section sync to overpass command ([4d6e1bd](https://github.com/webmappsrl/osm2cai/commit/4d6e1bdfff2dfbb89475972a5bfb67c672194d90))
- created tags mapping config variable ([12707d9](https://github.com/webmappsrl/osm2cai/commit/12707d93a99855b8cd15cb79e4ac9e5e3574168d))
- created trait for tags mapping ([b9a4ebe](https://github.com/webmappsrl/osm2cai/commit/b9a4ebe845ea5daf323a0e1fd22d5f747c6fc41a))
- implemented apis ([d5ce2e9](https://github.com/webmappsrl/osm2cai/commit/d5ce2e95157991575330cded6e4d8f00ebf34074))
- implemented cai huts mitur abruzzo api ([bb8c220](https://github.com/webmappsrl/osm2cai/commit/bb8c220bd66d6d818a3be7d24e48e0719530e1c0))
- mitur abruzzo sections api updated ([4517967](https://github.com/webmappsrl/osm2cai/commit/45179674b985db53a3e72062c9a21abcf21fbe5d))
- updated poi apis for mitur abruzzo ([a519e87](https://github.com/webmappsrl/osm2cai/commit/a519e8746c38428841421265550c8e919effc7c7))

### Bug Fixes

- ecpois wiki fields clickable in xls file download ([e20d328](https://github.com/webmappsrl/osm2cai/commit/e20d328c892c5f26b1132a2e24c95636bde8833a))
- fix score update command ([957b0cb](https://github.com/webmappsrl/osm2cai/commit/957b0cb28119542910bd8f06371a027e47a89553))
- fixed hiking routes count in mitur abruzzo dashboard ([347f55b](https://github.com/webmappsrl/osm2cai/commit/347f55bff1cb8695d58ddd71f975134d7d89b2bc))
- fixed lat and lon in ugc poi csv download ([c1d914c](https://github.com/webmappsrl/osm2cai/commit/c1d914c76c4fb0b7a95a40890c9c0443ac447ae7))
- fixed lista percorsi in itinerary details ([f091c6f](https://github.com/webmappsrl/osm2cai/commit/f091c6fc50fe9a8b9c44be56829106acca92a654))
- fixed mitur dashboard values ([0723776](https://github.com/webmappsrl/osm2cai/commit/07237764c26612a964929aa17acb1317a816019d))
- fixed sync command ([4903cc2](https://github.com/webmappsrl/osm2cai/commit/4903cc224227adff80ec00b9f6abc20f84a818e3))

## [233.17.0](https://github.com/webmappsrl/osm2cai/compare/v233.16.3...v233.17.0) (2024-03-21)

### Features

- added count card and trend for acqua sorgente dashboard ([d7d9b1e](https://github.com/webmappsrl/osm2cai/commit/d7d9b1ef21f719a782cfa119d941e86d1fdc6852))
- added nova action to csv download mountain groups ([325b895](https://github.com/webmappsrl/osm2cai/commit/325b8958418582404682d11f5e1b50dd59fbd4ae))
- added region filter to mountain groups ([f97b1de](https://github.com/webmappsrl/osm2cai/commit/f97b1dec5c258d45df10c48981aaf3be03d54a2f))
- added ugc download csv action ([fee06c1](https://github.com/webmappsrl/osm2cai/commit/fee06c1e2ce498b1f76d05f615c112df3abb1435))

## [233.16.1](https://github.com/webmappsrl/osm2cai/compare/v233.16.0...v233.16.1) (2024-03-12)

### Bug Fixes

- added $route_cai-&gt;is_syncing = true; ([a97e5dc](https://github.com/webmappsrl/osm2cai/commit/a97e5dcfb9f6b7dce75e0cd701d956f9da0c5fe1))
- added $route_cai-&gt;is_syncing = true; to all ([1a081c5](https://github.com/webmappsrl/osm2cai/commit/1a081c58103c006609b7fdf4de7f55a84c2aad4f))
- fixed relation table for mountain groups and regions ([7756b98](https://github.com/webmappsrl/osm2cai/commit/7756b981d5a3973e51953793924379085ecd2348))

## [233.16.0](https://github.com/webmappsrl/osm2cai/compare/v233.15.1...v233.16.0) (2024-03-04)

### Features

- added mitur abruzzo apis for mountain groups ([02f0f0f](https://github.com/webmappsrl/osm2cai/commit/02f0f0fa8587b2012e526aad3134d2ed5a994484))
- added mitur abruzzo hiking routes apis ([1371ff9](https://github.com/webmappsrl/osm2cai/commit/1371ff9626ea150151898a52a45922e6f25f9ad4))
- added mitur single region api ([94c163b](https://github.com/webmappsrl/osm2cai/commit/94c163bca12617c53da964a528facb651786ad24))
- added swagger documentation for mitur region list ([73368b2](https://github.com/webmappsrl/osm2cai/commit/73368b256e21e00493da67e84cf6991149d584c1))
- api endpoints for mitur abruzzo and controller ([6d5c1f0](https://github.com/webmappsrl/osm2cai/commit/6d5c1f01b4366a979ff85da479f9b929377eaa95))
- created geoIntersect trait ([73ed5c4](https://github.com/webmappsrl/osm2cai/commit/73ed5c484012646ca8de479a7b8205902888d4e1))
- implemented api for mitur huts regions and sections ([6e5f513](https://github.com/webmappsrl/osm2cai/commit/6e5f513f63b2e1a185dbaf31ab6e903799041eb7))
- region_list api implemented ([d29f174](https://github.com/webmappsrl/osm2cai/commit/d29f174e166043247c427dcf44144c2c359d6bae))
- tests for mitur abruzzo api ([4a9ff1a](https://github.com/webmappsrl/osm2cai/commit/4a9ff1a5031a7b5e023b1ad0b155a5174da9603c))
- updated timestamp documentation in swagger ([a0d6d51](https://github.com/webmappsrl/osm2cai/commit/a0d6d510059355f94b98abeabecc805c4db65a00))

### Bug Fixes

- fixed hiking route api ([0d22403](https://github.com/webmappsrl/osm2cai/commit/0d224035c45a2206dd426fb7bbad1dfa3a323a93))

## [233.15.1](https://github.com/webmappsrl/osm2cai/compare/v233.15.0...v233.15.1) (2024-02-27)

### Bug Fixes

- fixed osm_id update command ([c73d539](https://github.com/webmappsrl/osm2cai/commit/c73d5398d25312b43a5c1445908d65d6fe0a95ea))

## [233.15.0](https://github.com/webmappsrl/osm2cai/compare/v233.14.0...v233.15.0) (2024-02-27)

### Features

- added filters to ecpois ([794176a](https://github.com/webmappsrl/osm2cai/commit/794176a07c0fdd3a33661e2793af75ea14124d96))
- added region favorite to content tab in metadata field in hiking route detail ([3aa7392](https://github.com/webmappsrl/osm2cai/commit/3aa7392e5dc385fd2837f44ab1c9f2a219dc1eb4))
- created command to update pois with missing osm type ([cab27df](https://github.com/webmappsrl/osm2cai/commit/cab27dfd6ec769143a0b525652bdd215ed751961))
- limited value length on regione field on hiking routes section ([7d68da5](https://github.com/webmappsrl/osm2cai/commit/7d68da52f05c721640ed03ea87f959035d9d43e3))

### Bug Fixes

- bug fix on osm type ([ca5a4e8](https://github.com/webmappsrl/osm2cai/commit/ca5a4e8ac2495eca437881764d2397530d84f0da))
- bug fix on stato di accatastamento card in hiking route details ([8d6d795](https://github.com/webmappsrl/osm2cai/commit/8d6d79543848ed19e7151071b065c681fe5bebff))
- command fix ([5c5d601](https://github.com/webmappsrl/osm2cai/commit/5c5d6011ee3d84fba7c0332abd8d1253cae3a095))
- fix zip download on ugc tracks ([5f19652](https://github.com/webmappsrl/osm2cai/commit/5f196528099a502e8afb956cb7a17d9c0a9b8e57))
- fixed ([add4b08](https://github.com/webmappsrl/osm2cai/commit/add4b080d74dad3c34dc46ee050e857b135cbb0e))
- hut sync sleep ([c05143c](https://github.com/webmappsrl/osm2cai/commit/c05143c601fa98df9e2cdaa5e940f7ad74e98a7e))
- sync huts attemps and sleep ([6440269](https://github.com/webmappsrl/osm2cai/commit/6440269f00c8b1c4d4bcaaf9fe043bc48155de68))

## [233.14.0](https://github.com/webmappsrl/osm2cai/compare/v233.13.1...v233.14.0) (2024-02-21)

### Features

- add cai huts tab to hiking routes ([376e28c](https://github.com/webmappsrl/osm2cai/commit/376e28c4ad7a5a06bee2ae21001cfea1b414f19d))
- add natural spring tab to hiking routes ([eba126b](https://github.com/webmappsrl/osm2cai/commit/eba126b684e8863e900f45098990eec18b769416))
- added caihuts ([cb35a44](https://github.com/webmappsrl/osm2cai/commit/cb35a4432426713daff24f71a7558b77fc0c0edc))
- added fillables to hiking routes ([65b4558](https://github.com/webmappsrl/osm2cai/commit/65b455867339c6e6d75e8f11dc87147e50459473))
- added logs to natural spring sync command ([c8f6400](https://github.com/webmappsrl/osm2cai/commit/c8f6400a3c0db47ddfe4045a695faf036e7d9af7))
- added migrations ([b216af0](https://github.com/webmappsrl/osm2cai/commit/b216af0b8097117ec3a6b21696e2d768807a5df5))
- added postgis to workflow git ([6d56197](https://github.com/webmappsrl/osm2cai/commit/6d56197ebf4ec0796d8ca89e6cb49174bbc4180b))
- added postgres service to workflow ([ee80e56](https://github.com/webmappsrl/osm2cai/commit/ee80e56a8c321a2345b2397b427dd0e21b02f28c))
- create sync command for huts ([df57941](https://github.com/webmappsrl/osm2cai/commit/df579414dfdf650a0a83081cd014f5e38c2ec4df))
- deploy_dev_workflow ([399051c](https://github.com/webmappsrl/osm2cai/commit/399051c180ab39f4d585fad990a05255d7420f31))
- installed laravel prompts ([9c3ed86](https://github.com/webmappsrl/osm2cai/commit/9c3ed8627605a256bf4ce85a549f66e021c62c74))
- ordered results by favorite routes count in descending order ([d63d95d](https://github.com/webmappsrl/osm2cai/commit/d63d95d41dcc62faa816fd7d9da50954fbe2ddaf))
- updated associate command to associate huts ([46008c4](https://github.com/webmappsrl/osm2cai/commit/46008c49d565f1f33ba02b89ac08a956d2c76a61))
- updated mitur abruzzo page and sync tool in nova ([46016d7](https://github.com/webmappsrl/osm2cai/commit/46016d7236e548cc1989e7d40a850024dbc689ef))

### Bug Fixes

- added role to psql git workflow ([3700af2](https://github.com/webmappsrl/osm2cai/commit/3700af22ee2f62d4e86a58c3fa67dae7b083a841))
- fixed region api ([ec9f343](https://github.com/webmappsrl/osm2cai/commit/ec9f343f229f7257ebe99c409aed73f9279f8937))
- improved associate to region command ([c810355](https://github.com/webmappsrl/osm2cai/commit/c810355ad1bb8439dc3935ec2b05ec2900dde137))
- workflow dev deploy fix 1 ([c9e9003](https://github.com/webmappsrl/osm2cai/commit/c9e900326ad6aed8adc1803d3a742365cb4b65fd))

### Miscellaneous Chores

- removed laravel prompts ([aebfb47](https://github.com/webmappsrl/osm2cai/commit/aebfb4785ea69791730adadbdf1df6b907d2df86))

## [233.13.1](https://github.com/webmappsrl/osm2cai/compare/v233.13.0...v233.13.1) (2024-02-13)

### Bug Fixes

- fix on sync mountain groups command ([c3fe98c](https://github.com/webmappsrl/osm2cai/commit/c3fe98cfa681791566969d62b9229bf2f7ffb852))

## [233.13.0](https://github.com/webmappsrl/osm2cai/compare/v233.12.0...v233.13.0) (2024-02-13)

### Features

- added action Percorso favorito ([1f15154](https://github.com/webmappsrl/osm2cai/commit/1f15154043ae3b549c05ad7311ef44d75d709697))
- added reg_ref to sync with osm data ([c25546f](https://github.com/webmappsrl/osm2cai/commit/c25546f3ac9739dcc421fcf5447f9939b56d6bbc))
- added region field in ecpoi nova ([d2a1dc5](https://github.com/webmappsrl/osm2cai/commit/d2a1dc52ee0c2f685cb88db0c7335a36bfb22e4d))
- added regions field to mountain groups nova ([2abb354](https://github.com/webmappsrl/osm2cai/commit/2abb3544283660c24bc33dfeb85fbe9bbc3a73b1))
- added script to associate mountain groups to regions based on geometric data ([22201d7](https://github.com/webmappsrl/osm2cai/commit/22201d7d324e2dc047784876d8c1ab0389486293))
- added sync ecpoi and mountaingroup tool for admins ([29c91d6](https://github.com/webmappsrl/osm2cai/commit/29c91d69eab33706a4a58c1f9098d865a3a40534))
- added ugcTrack download geojson zip ([ba45dba](https://github.com/webmappsrl/osm2cai/commit/ba45dbaedff46a68f4f79bacceb188c5646b5e67))
- dashboard percorsi favoriti ([cb6a903](https://github.com/webmappsrl/osm2cai/commit/cb6a9032136e3c8a45393cf53d75d54e5a57e2ef))

### Bug Fixes

- fixed issues_description in action percorribilità ([631942a](https://github.com/webmappsrl/osm2cai/commit/631942afb3d20d4caaebda1d9607feaea89d3e86))

## [233.12.0](https://github.com/webmappsrl/osm2cai/compare/v233.11.0...v233.12.0) (2024-01-31)

### Features

- added SAL dashboard table ([c616867](https://github.com/webmappsrl/osm2cai/commit/c6168670988e0721c4ad00e40a08e3bebf4ad033))
- added swagger docs for ecpois api ([503d192](https://github.com/webmappsrl/osm2cai/commit/503d192ff5b5ba5b10ca925996cea1b8d695e639))
- api for ecPois ([9a0ae8f](https://github.com/webmappsrl/osm2cai/commit/9a0ae8fa092d8839b7f10ede597b5c71b69fd4cd))
- enhanced default user overpass query ([c1b6755](https://github.com/webmappsrl/osm2cai/commit/c1b6755709760d14f9c3397181c69a526f40d34b))
- enhanced styles in sal table ([bf061be](https://github.com/webmappsrl/osm2cai/commit/bf061be9d68362a06b8e22be0c4d28e8ae499068))
- modified default query in cerca punti di interesse action ([0d4d783](https://github.com/webmappsrl/osm2cai/commit/0d4d783dc436782560d17bc4991ff48daab4907e))

### Bug Fixes

- fixed mitur abruzzo url ([a1ff1d6](https://github.com/webmappsrl/osm2cai/commit/a1ff1d6aacfa0bba0cdf5e8fb2ba10129293dce0))
- fixed osm sync action ([9fa1f28](https://github.com/webmappsrl/osm2cai/commit/9fa1f28e3d153439d9ba1eac9f4957aaaeca5d67))
- fixed SAL mitur-abruzzo ([d936e3f](https://github.com/webmappsrl/osm2cai/commit/d936e3f96ca5145fb87b4617374f714627c3f407))
- fixed swagger documentation for ecpoi apis ([9fd6923](https://github.com/webmappsrl/osm2cai/commit/9fd6923438f438c98af4b2ab085510f5e6ab689e))
- fixed table name ([f03e8c7](https://github.com/webmappsrl/osm2cai/commit/f03e8c70633fec54d1f9f8a277196db318498147))
- increased max length of default overpass query ([9bd8a9e](https://github.com/webmappsrl/osm2cai/commit/9bd8a9e6b7c8f41fd53a76917f79137f413c3614))
- problem with sync osm data action in hiking route ([5b4576d](https://github.com/webmappsrl/osm2cai/commit/5b4576d0f071929721fb6ca644307f4692795499))

### Miscellaneous Chores

- enhanced swagger api description ([7507217](https://github.com/webmappsrl/osm2cai/commit/750721720e286bbca2e3d4ce3977ad0a09558e03))

## [233.11.0](https://github.com/webmappsrl/osm2cai/compare/v233.10.0...v233.11.0) (2024-01-30)

### Features

- added poi table (1km buffer) to hiking routes ([f9de45b](https://github.com/webmappsrl/osm2cai/commit/f9de45bd329a299a2bbc729fb7ed3eac2dfe8ba3))
- default overpass query added to user ([e7614bc](https://github.com/webmappsrl/osm2cai/commit/e7614bc9f85658cf24d7b16bfcf8986a938061ea))
- itinerari default 50 ([9227697](https://github.com/webmappsrl/osm2cai/commit/9227697052ae8084585744e9d5dc676b9f4a4fba))
- poi relazionati ([c88f149](https://github.com/webmappsrl/osm2cai/commit/c88f14972c9605dddf6fddffdcaae6a770b68c41))

### Bug Fixes

- poi name import fixed ([941aadc](https://github.com/webmappsrl/osm2cai/commit/941aadc10a11bc726385032c9311bd790c55e7d8))

## [233.10.0](https://github.com/webmappsrl/osm2cai/compare/v233.9.0...v233.10.0) (2024-01-29)

### Features

- added osm_type to ecPoi table and enhanced nova to show type and osm url ([60f4605](https://github.com/webmappsrl/osm2cai/commit/60f46050ab1680455bb8928d4b8205f08a3a648e))

## [233.9.0](https://github.com/webmappsrl/osm2cai/compare/v233.8.1...v233.9.0) (2024-01-27)

### Features

- updated colors for section sal ([41c6e0d](https://github.com/webmappsrl/osm2cai/commit/41c6e0da028f81277fc97e52800f5a92c7419ebd))

### Bug Fixes

- fixed code column in natural spring table to be nullable ([f300215](https://github.com/webmappsrl/osm2cai/commit/f30021538e5cac6bc1dfe92ec8d89c059f6e5a37))
- fixed geometries for pois mountain groups and natural springs ([ac4bd0a](https://github.com/webmappsrl/osm2cai/commit/ac4bd0a8396c9486d8092844df162376bf08fd71))
- fixed import poi command ([d548f18](https://github.com/webmappsrl/osm2cai/commit/d548f1899ec4e18cab79396e3721aa11f3484359))
- fixed redirect path in associa utente action ([beb9d61](https://github.com/webmappsrl/osm2cai/commit/beb9d6110f14a45d885515b8ec789711db7b843f))
- fixed sections geojson and csv file names ([1b42dc5](https://github.com/webmappsrl/osm2cai/commit/1b42dc522fb60ef677fb4cf29941c9fbd328550f))

## [233.8.1](https://github.com/webmappsrl/osm2cai/compare/v233.8.0...v233.8.1) (2024-01-23)

### Bug Fixes

- fixed associa utente action ([42866bc](https://github.com/webmappsrl/osm2cai/commit/42866bc36431fa20e18231efd685bf827ab96e05))

## [233.8.0](https://github.com/webmappsrl/osm2cai/compare/v233.7.0...v233.8.0) (2024-01-20)

### Features

- added associa utente action in user index ([a8920ee](https://github.com/webmappsrl/osm2cai/commit/a8920eec4bfc2f888d39f4a5a7a4a3ba0d321ed0))
- added dark mode toggle ([84f438b](https://github.com/webmappsrl/osm2cai/commit/84f438be6346f22999756e0783c78ccb01380cd0))
- colors updated for percorribilità dashboard ([df2ad76](https://github.com/webmappsrl/osm2cai/commit/df2ad76c2fb19974b330448b298a4958d0e146f8))

### Bug Fixes

- fixed import poi policies ([99a1041](https://github.com/webmappsrl/osm2cai/commit/99a104115676170d66549e716c811ddafbf39891))
- fixewd download geojson action in section index ([32f8c98](https://github.com/webmappsrl/osm2cai/commit/32f8c988e8d33c6ec0e1e68e446461878cc113dd))

## [233.7.0](https://github.com/webmappsrl/osm2cai/compare/v233.6.0...v233.7.0) (2024-01-17)

### Features

- action import poi from osm_id implemented in hr ([3fb0635](https://github.com/webmappsrl/osm2cai/commit/3fb06356233601b40e3856c7af8272563a05eefb))

### Bug Fixes

- fix on import poi command ([52ecd08](https://github.com/webmappsrl/osm2cai/commit/52ecd0854be9f07c95b899600f51047d179ba6f4))

## [233.6.0](https://github.com/webmappsrl/osm2cai/compare/v233.5.3...v233.6.0) (2024-01-17)

### Features

- added action assign moderator to sectors ([6e5c848](https://github.com/webmappsrl/osm2cai/commit/6e5c84849bfb5a747a27534964818af46a23667d))
- added action download to sectioons ([5ed1473](https://github.com/webmappsrl/osm2cai/commit/5ed1473391dc6811efb5a376207e4ca0cba0cac7))
- added card stato-avanzamento-percorribilitá to dashboard-regional ([0ede7bd](https://github.com/webmappsrl/osm2cai/commit/0ede7bd8e2947d48b9c6ca24a3c379c1b2224ef9))
- added dashboard percorribilità ([96ef6f6](https://github.com/webmappsrl/osm2cai/commit/96ef6f642b709a2f7fa1d3f93ada626498b66028))
- added dashboard percorribilità to local user ([8f1fabc](https://github.com/webmappsrl/osm2cai/commit/8f1fabc0aad0e84182c84fb44d3f7b5fdb46d993))
- added download csv percorsi for referente locale ([425f1e7](https://github.com/webmappsrl/osm2cai/commit/425f1e77922dd93c5979bb32d7a95658449a60dd))
- added filtering to ugc for regional and local users ([f4ec44a](https://github.com/webmappsrl/osm2cai/commit/f4ec44a444c0bec517aba8b130526d050e83d143))
- added geometry check to hiking routes detail ([3284dc0](https://github.com/webmappsrl/osm2cai/commit/3284dc00b67fac82c433a04daab3c6718c01e56c))
- added indicazione download ([bdf12c9](https://github.com/webmappsrl/osm2cai/commit/bdf12c9f756605997f4c8e4a7134ec7e22ee8cc2))
- added metric for sda 3 hiking routes issue status ([574ee5f](https://github.com/webmappsrl/osm2cai/commit/574ee5f207a4d907b07e5c7c0f460276b85a42fa))
- added metric for sda 4 hiking routes issue status ([cfc45fb](https://github.com/webmappsrl/osm2cai/commit/cfc45fbc0c76a4bbd0fbb4b165df105d0456acf6))
- added metrics to section details ([0ae9955](https://github.com/webmappsrl/osm2cai/commit/0ae995532a4022607fbf0e198cdb952e6a772e1d))
- Added scheduled task to import UGC from Geohub ([1d602cb](https://github.com/webmappsrl/osm2cai/commit/1d602cb5a67a83c6f58b7e8e44b0f6458f0b7575))
- improved performance caching dashboard results ([73cc57c](https://github.com/webmappsrl/osm2cai/commit/73cc57cdc4fe7f898ac781c61354399dc26932d6))
- improved query performance for hr ([bdc61ee](https://github.com/webmappsrl/osm2cai/commit/bdc61ee07c2d874b458a1625f0b60aa1d0d5ea24))
- improved users dashboard ([368f110](https://github.com/webmappsrl/osm2cai/commit/368f11055356eb62b96b32ac9d8d38398029aeeb))
- improved users dashboard ([2a9d005](https://github.com/webmappsrl/osm2cai/commit/2a9d0055f86eeeb1d5263b1ebc67aa40861f5b67))
- updated no permission message ([ccbf242](https://github.com/webmappsrl/osm2cai/commit/ccbf242e2c4528f1bc75f215e124583fa9d3e37d))
- user nova resource improvement ([2749133](https://github.com/webmappsrl/osm2cai/commit/2749133be52cf74638e3665d3bac1d22681e26d6))

### Bug Fixes

- error fixed in ugcmedia ([33f4f02](https://github.com/webmappsrl/osm2cai/commit/33f4f0253f4c57ce01f03cbc7864056fa2e15ae4))
- fixed area n in regional dashboard ([3139dc6](https://github.com/webmappsrl/osm2cai/commit/3139dc63db35760b895e440b94cc20a34eb9e248))
- permission ecpoi user ([2e9e7ee](https://github.com/webmappsrl/osm2cai/commit/2e9e7ee8062ccce6aecd3efa93533048ce4e2326))
- SectionController imports error ([c4de66b](https://github.com/webmappsrl/osm2cai/commit/c4de66bea8681d4d59d8887b4c434a3e67515613))
- typo ([fa47db6](https://github.com/webmappsrl/osm2cai/commit/fa47db626f557123b4e3b1d881224ebdd8293e08))
- user_permission delete hr ([a90c7c3](https://github.com/webmappsrl/osm2cai/commit/a90c7c31d7170531abd1b08b611f825603fc82a2))

## [233.5.3](https://github.com/webmappsrl/osm2cai/compare/v233.5.2...v233.5.3) (2023-12-29)

### Bug Fixes

- fixed referente regionale issue ([170d9a1](https://github.com/webmappsrl/osm2cai/commit/170d9a117255b09a74bf58491f8d2bdac634e0a5))
- fixed revert validate for regional referent ([665af44](https://github.com/webmappsrl/osm2cai/commit/665af44e5de4c10ebcc832c894aac2cd7735422d))
- refactoring code ([0797f34](https://github.com/webmappsrl/osm2cai/commit/0797f34a193d30c59b0bafd50ff9dfa4a3e6124f))

## [233.5.2](https://github.com/webmappsrl/osm2cai/compare/v233.5.1...v233.5.2) (2023-12-29)

### Bug Fixes

- fixed permission for osm sync action ([a4db64c](https://github.com/webmappsrl/osm2cai/commit/a4db64c258af5166f46b294555b7ba72132dfd2b))
- permissions fixes on hiking routes actions ([a9da0e4](https://github.com/webmappsrl/osm2cai/commit/a9da0e4476b5840660a7e2e1070b8bbf9d527b85))

## [233.5.1](https://github.com/webmappsrl/osm2cai/compare/v233.5.0...v233.5.1) (2023-12-29)

### Bug Fixes

- fixed permissions on hiking routes actions ([74b588d](https://github.com/webmappsrl/osm2cai/commit/74b588d29b8846647aa0761182179646ae7c8136))
