# Changelog

## [233.17.0](https://github.com/webmappsrl/osm2cai/compare/v233.16.3...v233.17.0) (2024-03-21)


### Features

* added count card and trend for acqua sorgente dashboard ([d7d9b1e](https://github.com/webmappsrl/osm2cai/commit/d7d9b1ef21f719a782cfa119d941e86d1fdc6852))
* added nova action to csv download mountain groups ([325b895](https://github.com/webmappsrl/osm2cai/commit/325b8958418582404682d11f5e1b50dd59fbd4ae))
* added region filter to mountain groups ([f97b1de](https://github.com/webmappsrl/osm2cai/commit/f97b1dec5c258d45df10c48981aaf3be03d54a2f))
* added ugc download csv action ([fee06c1](https://github.com/webmappsrl/osm2cai/commit/fee06c1e2ce498b1f76d05f615c112df3abb1435))

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
