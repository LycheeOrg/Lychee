{composerEnv, fetchurl, fetchgit ? null, fetchhg ? null, fetchsvn ? null, noDev ? false}:

let
  packages = {
    "alchemy/binary-driver" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "alchemy-binary-driver-e0615cdff315e6b4b05ada67906df6262a020d22";
        src = fetchurl {
          url = "https://api.github.com/repos/alchemy-fr/BinaryDriver/zipball/e0615cdff315e6b4b05ada67906df6262a020d22";
          sha256 = "1xfxillfyyvfhc3h4q5rsgip7d6x5xj959pchvx1mr18wl9yzpcv";
        };
      };
    };
    "beberlei/assert" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "beberlei-assert-5e721d7e937ca3ba2cdec1e1adf195f9e5188372";
        src = fetchurl {
          url = "https://api.github.com/repos/beberlei/assert/zipball/5e721d7e937ca3ba2cdec1e1adf195f9e5188372";
          sha256 = "0mikybjprhbmvijjl83991zziliqygv32gqym77agqq6xcjwqpzs";
        };
      };
    };
    "bepsvpt/secure-headers" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "bepsvpt-secure-headers-be5948516c10dab75a863a98dabcc3cb151711aa";
        src = fetchurl {
          url = "https://api.github.com/repos/bepsvpt/secure-headers/zipball/be5948516c10dab75a863a98dabcc3cb151711aa";
          sha256 = "0hrrrnc6r4y5y4q0ljxmr5wcyq1sf0pyng82c1k9ax8mfav912qx";
        };
      };
    };
    "brick/math" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "brick-math-dff976c2f3487d42c1db75a3b180e2b9f0e72ce0";
        src = fetchurl {
          url = "https://api.github.com/repos/brick/math/zipball/dff976c2f3487d42c1db75a3b180e2b9f0e72ce0";
          sha256 = "11k4h3mvgf9fn2mj0f67jccgkwr1zyjjjx1czmkvxzkkydq3g3nk";
        };
      };
    };
    "clue/stream-filter" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "clue-stream-filter-aeb7d8ea49c7963d3b581378955dbf5bc49aa320";
        src = fetchurl {
          url = "https://api.github.com/repos/clue/stream-filter/zipball/aeb7d8ea49c7963d3b581378955dbf5bc49aa320";
          sha256 = "085640ipq4nc4fpc4422n6cjg0wv36y8cbi8ljndfh0f484ix8jm";
        };
      };
    };
    "darkghosthunter/larapass" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "darkghosthunter-larapass-0d03a9ad17f32b5cdbad5667c8312078e5a57f49";
        src = fetchurl {
          url = "https://api.github.com/repos/LycheeOrg/Larapass/zipball/0d03a9ad17f32b5cdbad5667c8312078e5a57f49";
          sha256 = "0i7fmng2r61i3x2wq6w6afw2jzjxi0s028k6gkarj0yma3bnirna";
        };
      };
    };
    "doctrine/cache" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "doctrine-cache-9c53086695937c50c47936ed86d96150ffbcf60d";
        src = fetchurl {
          url = "https://api.github.com/repos/doctrine/cache/zipball/9c53086695937c50c47936ed86d96150ffbcf60d";
          sha256 = "0xn3h7pgr44lpxwd8babwp93ipqcc0m3274js19f101ma8qmg96a";
        };
      };
    };
    "doctrine/dbal" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "doctrine-dbal-c800380457948e65bbd30ba92cc17cda108bf8c9";
        src = fetchurl {
          url = "https://api.github.com/repos/doctrine/dbal/zipball/c800380457948e65bbd30ba92cc17cda108bf8c9";
          sha256 = "1x6bij89yaj0d52ffx683rlpxrgxl0vx9q6a121mkz1zplnif647";
        };
      };
    };
    "doctrine/deprecations" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "doctrine-deprecations-9504165960a1f83cc1480e2be1dd0a0478561314";
        src = fetchurl {
          url = "https://api.github.com/repos/doctrine/deprecations/zipball/9504165960a1f83cc1480e2be1dd0a0478561314";
          sha256 = "04kpbzk5iw86imspkg7dgs54xx877k9b5q0dfg2h119mlfkvxil6";
        };
      };
    };
    "doctrine/event-manager" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "doctrine-event-manager-41370af6a30faa9dc0368c4a6814d596e81aba7f";
        src = fetchurl {
          url = "https://api.github.com/repos/doctrine/event-manager/zipball/41370af6a30faa9dc0368c4a6814d596e81aba7f";
          sha256 = "0pn2aiwl4fvv6fcwar9alng2yrqy8bzc58n4bkp6y2jnpw5gp4m8";
        };
      };
    };
    "doctrine/inflector" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "doctrine-inflector-9cf661f4eb38f7c881cac67c75ea9b00bf97b210";
        src = fetchurl {
          url = "https://api.github.com/repos/doctrine/inflector/zipball/9cf661f4eb38f7c881cac67c75ea9b00bf97b210";
          sha256 = "0gkaw5aqkdppd7cz1n46kdms0bv8kzbnpjh75jnhv98p9fik7f24";
        };
      };
    };
    "doctrine/lexer" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "doctrine-lexer-e864bbf5904cb8f5bb334f99209b48018522f042";
        src = fetchurl {
          url = "https://api.github.com/repos/doctrine/lexer/zipball/e864bbf5904cb8f5bb334f99209b48018522f042";
          sha256 = "11lg9fcy0crb8inklajhx3kyffdbx7xzdj8kwl21xsgq9nm9iwvv";
        };
      };
    };
    "dragonmantank/cron-expression" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "dragonmantank-cron-expression-7a8c6e56ab3ffcc538d05e8155bb42269abf1a0c";
        src = fetchurl {
          url = "https://api.github.com/repos/dragonmantank/cron-expression/zipball/7a8c6e56ab3ffcc538d05e8155bb42269abf1a0c";
          sha256 = "0pl9zrj9254qbwr7vyiilzhmb7bq2ss631iwvlq1mqky2bwinj2l";
        };
      };
    };
    "egulias/email-validator" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "egulias-email-validator-0dbf5d78455d4d6a41d186da50adc1122ec066f4";
        src = fetchurl {
          url = "https://api.github.com/repos/egulias/EmailValidator/zipball/0dbf5d78455d4d6a41d186da50adc1122ec066f4";
          sha256 = "00kwb8rhk1fq3a1i152xniipk3y907q1v5r3szqbkq5rz82dwbck";
        };
      };
    };
    "evenement/evenement" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "evenement-evenement-531bfb9d15f8aa57454f5f0285b18bec903b8fb7";
        src = fetchurl {
          url = "https://api.github.com/repos/igorw/evenement/zipball/531bfb9d15f8aa57454f5f0285b18bec903b8fb7";
          sha256 = "02mi1lrga41caa25whr6sj9hmmlfjp10l0d0fq8kc3d4483pm9rr";
        };
      };
    };
    "fgrosse/phpasn1" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "fgrosse-phpasn1-20299033c35f4300eb656e7e8e88cf52d1d6694e";
        src = fetchurl {
          url = "https://api.github.com/repos/fgrosse/PHPASN1/zipball/20299033c35f4300eb656e7e8e88cf52d1d6694e";
          sha256 = "0lmgsk3kh5v2qj48fyskkhwwyk8bdc9z72g67gy0favrypgjs4pn";
        };
      };
    };
    "fideloper/proxy" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "fideloper-proxy-c073b2bd04d1c90e04dc1b787662b558dd65ade0";
        src = fetchurl {
          url = "https://api.github.com/repos/fideloper/TrustedProxy/zipball/c073b2bd04d1c90e04dc1b787662b558dd65ade0";
          sha256 = "05jzgjj4fy5p1smqj41b5qxj42zn0mnczvsaacni4fmq174mz4gy";
        };
      };
    };
    "geocoder-php/cache-provider" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "geocoder-php-cache-provider-094e272069b4dffda18f10e75f7143a9548da53c";
        src = fetchurl {
          url = "https://api.github.com/repos/geocoder-php/cache-provider/zipball/094e272069b4dffda18f10e75f7143a9548da53c";
          sha256 = "1sdwi2kpmyvc3klgc03kd45b0khg10h5a3l1mbn8pwqyd90r2zf4";
        };
      };
    };
    "geocoder-php/common-http" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "geocoder-php-common-http-9f44a006d4b45d01dd31ea9b38ee7fb5724cd73e";
        src = fetchurl {
          url = "https://api.github.com/repos/geocoder-php/php-common-http/zipball/9f44a006d4b45d01dd31ea9b38ee7fb5724cd73e";
          sha256 = "0xk90q15hgjns0jwm8ipxhapc2x3w5d74x6n94bb32kjrjf86a32";
        };
      };
    };
    "geocoder-php/nominatim-provider" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "geocoder-php-nominatim-provider-80f39ce41bcd0e4d9de3e83c40caf92d089fecf2";
        src = fetchurl {
          url = "https://api.github.com/repos/geocoder-php/nominatim-provider/zipball/80f39ce41bcd0e4d9de3e83c40caf92d089fecf2";
          sha256 = "1zs859ax4mp1l2168zylxcgxcaimal5gzmhyfac160qp17c019ij";
        };
      };
    };
    "graham-campbell/markdown" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "graham-campbell-markdown-d25b873e5c5870edc4de7d980808f1a8e092a9c7";
        src = fetchurl {
          url = "https://api.github.com/repos/GrahamCampbell/Laravel-Markdown/zipball/d25b873e5c5870edc4de7d980808f1a8e092a9c7";
          sha256 = "07mlqjs1pi5nj9pgcrrllrmy135gkijzh98cnlpjnaax998l62ch";
        };
      };
    };
    "graham-campbell/result-type" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "graham-campbell-result-type-7e279d2cd5d7fbb156ce46daada972355cea27bb";
        src = fetchurl {
          url = "https://api.github.com/repos/GrahamCampbell/Result-Type/zipball/7e279d2cd5d7fbb156ce46daada972355cea27bb";
          sha256 = "0hvbv2svljw2hyshbby7wrh29nck98rpbhfl911gyb89i8mzx1zm";
        };
      };
    };
    "guzzlehttp/guzzle" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "guzzlehttp-guzzle-7008573787b430c1c1f650e3722d9bba59967628";
        src = fetchurl {
          url = "https://api.github.com/repos/guzzle/guzzle/zipball/7008573787b430c1c1f650e3722d9bba59967628";
          sha256 = "10fiv9ifhz5vg78z4xa41dkwic5ql4m6xf8bglyvpw3x7b76l81m";
        };
      };
    };
    "guzzlehttp/promises" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "guzzlehttp-promises-8e7d04f1f6450fef59366c399cfad4b9383aa30d";
        src = fetchurl {
          url = "https://api.github.com/repos/guzzle/promises/zipball/8e7d04f1f6450fef59366c399cfad4b9383aa30d";
          sha256 = "158wd8nmvvl386c24lkr4jkwdhqpdj0dxdbjwh8iv6a2rgccjr2q";
        };
      };
    };
    "guzzlehttp/psr7" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "guzzlehttp-psr7-dc960a912984efb74d0a90222870c72c87f10c91";
        src = fetchurl {
          url = "https://api.github.com/repos/guzzle/psr7/zipball/dc960a912984efb74d0a90222870c72c87f10c91";
          sha256 = "06nc60wf8k6kcl89kprk04khsrrik5lnis615mx4a0m0pjn8bliz";
        };
      };
    };
    "hamcrest/hamcrest-php" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "hamcrest-hamcrest-php-8c3d0a3f6af734494ad8f6fbbee0ba92422859f3";
        src = fetchurl {
          url = "https://api.github.com/repos/hamcrest/hamcrest-php/zipball/8c3d0a3f6af734494ad8f6fbbee0ba92422859f3";
          sha256 = "1ixmmpplaf1z36f34d9f1342qjbcizvi5ddkjdli6jgrbla6a6hr";
        };
      };
    };
    "kalnoy/nestedset" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "kalnoy-nestedset-789a70bce94a7c3bd206fb05fa4b747cf27acbe2";
        src = fetchurl {
          url = "https://api.github.com/repos/lazychaser/laravel-nestedset/zipball/789a70bce94a7c3bd206fb05fa4b747cf27acbe2";
          sha256 = "1mwqx4v87cp461p1jm5zq567jz9f2g99q91g7v9313abl79w75cd";
        };
      };
    };
    "laravel/framework" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "laravel-framework-41ec4897a70eb8729cf0ff34a8354413c54e42a6";
        src = fetchurl {
          url = "https://api.github.com/repos/laravel/framework/zipball/41ec4897a70eb8729cf0ff34a8354413c54e42a6";
          sha256 = "0kmdjg3if2rpqj14pqlkgvwd4snhncfz6rl0ys7mnl9f8n8j091v";
        };
      };
    };
    "league/commonmark" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "league-commonmark-7d70d2f19c84bcc16275ea47edabee24747352eb";
        src = fetchurl {
          url = "https://api.github.com/repos/thephpleague/commonmark/zipball/7d70d2f19c84bcc16275ea47edabee24747352eb";
          sha256 = "1clfi4k0xp15pzg8c344qj5jk54k9dm9xbg4sd6l6iv66md1xasn";
        };
      };
    };
    "league/flysystem" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "league-flysystem-f3ad69181b8afed2c9edf7be5a2918144ff4ea32";
        src = fetchurl {
          url = "https://api.github.com/repos/thephpleague/flysystem/zipball/f3ad69181b8afed2c9edf7be5a2918144ff4ea32";
          sha256 = "0s4sx4j7c16qkk7m6k2r4ajfjidlv15z18ybxhfmmz4jb4wsmv94";
        };
      };
    };
    "league/mime-type-detection" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "league-mime-type-detection-3b9dff8aaf7323590c1d2e443db701eb1f9aa0d3";
        src = fetchurl {
          url = "https://api.github.com/repos/thephpleague/mime-type-detection/zipball/3b9dff8aaf7323590c1d2e443db701eb1f9aa0d3";
          sha256 = "0pmq486v2nf6672y2z53cyb3mfrxcc8n7z2ilpzz9zkkf2yb990j";
        };
      };
    };
    "league/uri" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "league-uri-09da64118eaf4c5d52f9923a1e6a5be1da52fd9a";
        src = fetchurl {
          url = "https://api.github.com/repos/thephpleague/uri/zipball/09da64118eaf4c5d52f9923a1e6a5be1da52fd9a";
          sha256 = "0y7vfwfkq5q8fwkndz0bby3yv2sdpkn0pv48mjgavjsgpcx4xbkz";
        };
      };
    };
    "league/uri-interfaces" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "league-uri-interfaces-667f150e589d65d79c89ffe662e426704f84224f";
        src = fetchurl {
          url = "https://api.github.com/repos/thephpleague/uri-interfaces/zipball/667f150e589d65d79c89ffe662e426704f84224f";
          sha256 = "1vi2sf6gvmif0sk9w3fly5js6qzjg9jk7mh8b56pjs2y333n78lg";
        };
      };
    };
    "livewire/livewire" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "livewire-livewire-33101c83b75728651b9e668a4559f97def7c9138";
        src = fetchurl {
          url = "https://api.github.com/repos/livewire/livewire/zipball/33101c83b75728651b9e668a4559f97def7c9138";
          sha256 = "0zfhc4y2p04qd936nmfx89dnbjgaci14cga8k1xjsqiixzv7j76g";
        };
      };
    };
    "lychee-org/php-exif" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "lychee-org-php-exif-4fd2b9e325982f476fdd8bd356cb51d7fa312021";
        src = fetchurl {
          url = "https://api.github.com/repos/LycheeOrg/php-exif/zipball/4fd2b9e325982f476fdd8bd356cb51d7fa312021";
          sha256 = "1i6rjnrzk2s8mwx24hh2s8ms47fp8fdw9vbzy8536n2hkmiz67m9";
        };
      };
    };
    "maennchen/zipstream-php" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "maennchen-zipstream-php-6373eefe0b3274d7b702d81f2c99aa977ff97dc2";
        src = fetchurl {
          url = "https://api.github.com/repos/maennchen/ZipStream-PHP/zipball/6373eefe0b3274d7b702d81f2c99aa977ff97dc2";
          sha256 = "0spn3643b1b1z6ypl2jpnxc3vz1svh58n6vihfygj49q7pbwpn18";
        };
      };
    };
    "mockery/mockery" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "mockery-mockery-d1339f64479af1bee0e82a0413813fe5345a54ea";
        src = fetchurl {
          url = "https://api.github.com/repos/mockery/mockery/zipball/d1339f64479af1bee0e82a0413813fe5345a54ea";
          sha256 = "03ivhqdghsg5brgfq117ff5nj2s74d83rh34pzfqqpgca45h3w6d";
        };
      };
    };
    "monolog/monolog" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "monolog-monolog-1cb1cde8e8dd0f70cc0fe51354a59acad9302084";
        src = fetchurl {
          url = "https://api.github.com/repos/Seldaek/monolog/zipball/1cb1cde8e8dd0f70cc0fe51354a59acad9302084";
          sha256 = "1gymdiymwrjw25fjqapq3jlmf6wnp1h26ms74sckd70d53c4m52k";
        };
      };
    };
    "myclabs/php-enum" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "myclabs-php-enum-46cf3d8498b095bd33727b13fd5707263af99421";
        src = fetchurl {
          url = "https://api.github.com/repos/myclabs/php-enum/zipball/46cf3d8498b095bd33727b13fd5707263af99421";
          sha256 = "14amncs8wm38b6jn04dqbgmixd52j0dl3wvwz7zlzcgf5rwqbmxy";
        };
      };
    };
    "neitanod/forceutf8" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "neitanod-forceutf8-c1fbe70bfb5ad41b8ec5785056b0e308b40d4831";
        src = fetchurl {
          url = "https://api.github.com/repos/neitanod/forceutf8/zipball/c1fbe70bfb5ad41b8ec5785056b0e308b40d4831";
          sha256 = "1fvh2iapy7q22n65p6xkcbxcmp68x917gkv2cb0gs59671fwxsjf";
        };
      };
    };
    "nesbot/carbon" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "nesbot-carbon-d3c447f21072766cddec3522f9468a5849a76147";
        src = fetchurl {
          url = "https://api.github.com/repos/briannesbitt/Carbon/zipball/d3c447f21072766cddec3522f9468a5849a76147";
          sha256 = "01v2zv1lwdsd80hhwmjd4rfp7xkp26mild1kkh3w7hivnf72nad8";
        };
      };
    };
    "neutron/temporary-filesystem" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "neutron-temporary-filesystem-60e79adfd16f42f4b888e351ad49f9dcb959e3c2";
        src = fetchurl {
          url = "https://api.github.com/repos/romainneutron/Temporary-Filesystem/zipball/60e79adfd16f42f4b888e351ad49f9dcb959e3c2";
          sha256 = "1fx9l8dvlcy0yv53k32hi2lhidc6wllw8r84hy75hikllakx97ki";
        };
      };
    };
    "nyholm/psr7" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "nyholm-psr7-23ae1f00fbc6a886cbe3062ca682391b9cc7c37b";
        src = fetchurl {
          url = "https://api.github.com/repos/Nyholm/psr7/zipball/23ae1f00fbc6a886cbe3062ca682391b9cc7c37b";
          sha256 = "1lww7xhzlxsp0i65sg2xrz3n9m0nrz1ckgdcqdd75lxlw9s0c8vn";
        };
      };
    };
    "opis/closure" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "opis-closure-06e2ebd25f2869e54a306dda991f7db58066f7f6";
        src = fetchurl {
          url = "https://api.github.com/repos/opis/closure/zipball/06e2ebd25f2869e54a306dda991f7db58066f7f6";
          sha256 = "0fpa1w0rmwywj67jgaldmw563p7gycahs8gpkpjvrra9zhhj4yyc";
        };
      };
    };
    "php-ffmpeg/php-ffmpeg" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "php-ffmpeg-php-ffmpeg-a5147d1ae041e78e7870bf2443d4e2dfa7635856";
        src = fetchurl {
          url = "https://api.github.com/repos/PHP-FFMpeg/PHP-FFMpeg/zipball/a5147d1ae041e78e7870bf2443d4e2dfa7635856";
          sha256 = "11758gf2yjmj4bh0c0szxi6ahrca7fc5h4y15davgkahwiisxgn5";
        };
      };
    };
    "php-http/discovery" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "php-http-discovery-788f72d64c43dc361e7fcc7464c3d947c64984a7";
        src = fetchurl {
          url = "https://api.github.com/repos/php-http/discovery/zipball/788f72d64c43dc361e7fcc7464c3d947c64984a7";
          sha256 = "1vvmn1zpzwmlkwc29pxibfrgn3yr7gz3mfa379l3nfnb1i2sfnf5";
        };
      };
    };
    "php-http/guzzle7-adapter" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "php-http-guzzle7-adapter-1967de656b9679a2a6a66d0e4e16fa99bbed1ad1";
        src = fetchurl {
          url = "https://api.github.com/repos/php-http/guzzle7-adapter/zipball/1967de656b9679a2a6a66d0e4e16fa99bbed1ad1";
          sha256 = "1qwpxhn9j092yclzgy20yc1pa254a0r6qqj0qsxkfic7gisgggdv";
        };
      };
    };
    "php-http/httplug" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "php-http-httplug-191a0a1b41ed026b717421931f8d3bd2514ffbf9";
        src = fetchurl {
          url = "https://api.github.com/repos/php-http/httplug/zipball/191a0a1b41ed026b717421931f8d3bd2514ffbf9";
          sha256 = "0a0aaikwnbb76hj0ldqyg85b94awiw71i03n8al6rmssbpr8q2x4";
        };
      };
    };
    "php-http/message" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "php-http-message-fb0dbce7355cad4f4f6a225f537c34d013571f29";
        src = fetchurl {
          url = "https://api.github.com/repos/php-http/message/zipball/fb0dbce7355cad4f4f6a225f537c34d013571f29";
          sha256 = "0w0mpnc0660pc39ay5b285gma6c2rs0fc6w89kza75flndsca2a7";
        };
      };
    };
    "php-http/message-factory" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "php-http-message-factory-a478cb11f66a6ac48d8954216cfed9aa06a501a1";
        src = fetchurl {
          url = "https://api.github.com/repos/php-http/message-factory/zipball/a478cb11f66a6ac48d8954216cfed9aa06a501a1";
          sha256 = "13drpc83bq332hz0b97whibkm7jpk56msq4yppw9nmrchzwgy7cs";
        };
      };
    };
    "php-http/promise" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "php-http-promise-4c4c1f9b7289a2ec57cde7f1e9762a5789506f88";
        src = fetchurl {
          url = "https://api.github.com/repos/php-http/promise/zipball/4c4c1f9b7289a2ec57cde7f1e9762a5789506f88";
          sha256 = "0xjprpx6xlsjr599vrbmf3cb9726adfm1p9q59xcklrh4p8grwbz";
        };
      };
    };
    "phpoption/phpoption" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phpoption-phpoption-994ecccd8f3283ecf5ac33254543eb0ac946d525";
        src = fetchurl {
          url = "https://api.github.com/repos/schmittjoh/php-option/zipball/994ecccd8f3283ecf5ac33254543eb0ac946d525";
          sha256 = "1snrnfvqhnr5z9llf8kbqk9l97gfyp8gghmhi1ng8qx5xzv1anr7";
        };
      };
    };
    "psr/cache" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-cache-d11b50ad223250cf17b86e38383413f5a6764bf8";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/cache/zipball/d11b50ad223250cf17b86e38383413f5a6764bf8";
          sha256 = "06i2k3dx3b4lgn9a4v1dlgv8l9wcl4kl7vzhh63lbji0q96hv8qz";
        };
      };
    };
    "psr/container" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-container-8622567409010282b7aeebe4bb841fe98b58dcaf";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/container/zipball/8622567409010282b7aeebe4bb841fe98b58dcaf";
          sha256 = "0qfvyfp3mli776kb9zda5cpc8cazj3prk0bg0gm254kwxyfkfrwn";
        };
      };
    };
    "psr/event-dispatcher" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-event-dispatcher-dbefd12671e8a14ec7f180cab83036ed26714bb0";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/event-dispatcher/zipball/dbefd12671e8a14ec7f180cab83036ed26714bb0";
          sha256 = "05nicsd9lwl467bsv4sn44fjnnvqvzj1xqw2mmz9bac9zm66fsjd";
        };
      };
    };
    "psr/http-client" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-http-client-2dfb5f6c5eff0e91e20e913f8c5452ed95b86621";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/http-client/zipball/2dfb5f6c5eff0e91e20e913f8c5452ed95b86621";
          sha256 = "0cmkifa3ji1r8kn3y1rwg81rh8g2crvnhbv2am6d688dzsbw967v";
        };
      };
    };
    "psr/http-factory" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-http-factory-12ac7fcd07e5b077433f5f2bee95b3a771bf61be";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/http-factory/zipball/12ac7fcd07e5b077433f5f2bee95b3a771bf61be";
          sha256 = "0inbnqpc5bfhbbda9dwazsrw9xscfnc8rdx82q1qm3r446mc1vds";
        };
      };
    };
    "psr/http-message" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-http-message-f6561bf28d520154e4b0ec72be95418abe6d9363";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/http-message/zipball/f6561bf28d520154e4b0ec72be95418abe6d9363";
          sha256 = "195dd67hva9bmr52iadr4kyp2gw2f5l51lplfiay2pv6l9y4cf45";
        };
      };
    };
    "psr/log" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-log-d49695b909c3b7628b6289db5479a1c204601f11";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/log/zipball/d49695b909c3b7628b6289db5479a1c204601f11";
          sha256 = "0sb0mq30dvmzdgsnqvw3xh4fb4bqjncx72kf8n622f94dd48amln";
        };
      };
    };
    "psr/simple-cache" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "psr-simple-cache-408d5eafb83c57f6365a3ca330ff23aa4a5fa39b";
        src = fetchurl {
          url = "https://api.github.com/repos/php-fig/simple-cache/zipball/408d5eafb83c57f6365a3ca330ff23aa4a5fa39b";
          sha256 = "1djgzclkamjxi9jy4m9ggfzgq1vqxaga2ip7l3cj88p7rwkzjxgw";
        };
      };
    };
    "ralouphie/getallheaders" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "ralouphie-getallheaders-120b605dfeb996808c31b6477290a714d356e822";
        src = fetchurl {
          url = "https://api.github.com/repos/ralouphie/getallheaders/zipball/120b605dfeb996808c31b6477290a714d356e822";
          sha256 = "1bv7ndkkankrqlr2b4kw7qp3fl0dxi6bp26bnim6dnlhavd6a0gg";
        };
      };
    };
    "ramsey/collection" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "ramsey-collection-28a5c4ab2f5111db6a60b2b4ec84057e0f43b9c1";
        src = fetchurl {
          url = "https://api.github.com/repos/ramsey/collection/zipball/28a5c4ab2f5111db6a60b2b4ec84057e0f43b9c1";
          sha256 = "18ka3y51a21bf7mv3hxxxnn1dj1mn3vg8y1i3j3ajsfi49xl6r03";
        };
      };
    };
    "ramsey/uuid" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "ramsey-uuid-cd4032040a750077205918c86049aa0f43d22947";
        src = fetchurl {
          url = "https://api.github.com/repos/ramsey/uuid/zipball/cd4032040a750077205918c86049aa0f43d22947";
          sha256 = "00hnl12crjs7kh67jhhjg157pma4ka5c5rpz46sdx8m207vhylzq";
        };
      };
    };
    "spatie/guzzle-rate-limiter-middleware" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-guzzle-rate-limiter-middleware-8679f7a22e46edc182046f18b83bacbc627b0600";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/guzzle-rate-limiter-middleware/zipball/8679f7a22e46edc182046f18b83bacbc627b0600";
          sha256 = "1bhckvjzp3kkglpf87q88n4aja613rycdchlhdcmxyx7b4c9wjqk";
        };
      };
    };
    "spatie/image-optimizer" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-image-optimizer-c22202fdd57856ed18a79cfab522653291a6e96a";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/image-optimizer/zipball/c22202fdd57856ed18a79cfab522653291a6e96a";
          sha256 = "0f4qyaphskxdb51x9aap9z5jj3lgz7wi4yc3wf29cxl9ssb3c538";
        };
      };
    };
    "spatie/laravel-feed" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-laravel-feed-1a9cbeb21de25b8a4f6a30ddc53e038fb5ae1651";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/laravel-feed/zipball/1a9cbeb21de25b8a4f6a30ddc53e038fb5ae1651";
          sha256 = "07sp8xkavjkb8ph6ggdwpfd21vkj1ibl7zrj6a5qn2fhhvgsjn96";
        };
      };
    };
    "spatie/laravel-image-optimizer" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-laravel-image-optimizer-c39e9ea77dee6b6eddfc26800adb1aa06a624294";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/laravel-image-optimizer/zipball/c39e9ea77dee6b6eddfc26800adb1aa06a624294";
          sha256 = "1z67ycij8mrcp8prl9iib1dmw9s2bin0xr6jqh5sgmybgkjqsd45";
        };
      };
    };
    "spatie/laravel-package-tools" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spatie-laravel-package-tools-1150930205dbe32f4e594ba2805cd56563120145";
        src = fetchurl {
          url = "https://api.github.com/repos/spatie/laravel-package-tools/zipball/1150930205dbe32f4e594ba2805cd56563120145";
          sha256 = "0a48wimx5xislv4n1wjvv97nyrm10y67h1bzpf11niy22kczjzlc";
        };
      };
    };
    "spomky-labs/base64url" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spomky-labs-base64url-7752ce931ec285da4ed1f4c5aa27e45e097be61d";
        src = fetchurl {
          url = "https://api.github.com/repos/Spomky-Labs/base64url/zipball/7752ce931ec285da4ed1f4c5aa27e45e097be61d";
          sha256 = "04xjhggcf6zc80ikva0flqis16q9b5lywld73g007m3y8b97q23l";
        };
      };
    };
    "spomky-labs/cbor-php" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "spomky-labs-cbor-php-9776578000be884cd7864eeb7c37a4ac92d8c995";
        src = fetchurl {
          url = "https://api.github.com/repos/Spomky-Labs/cbor-php/zipball/9776578000be884cd7864eeb7c37a4ac92d8c995";
          sha256 = "01bqyvpdrv7mr9qqwby6cyf4bi2j0cd34li9hyxzk5vrsm1k1zi7";
        };
      };
    };
    "swiftmailer/swiftmailer" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "swiftmailer-swiftmailer-15f7faf8508e04471f666633addacf54c0ab5933";
        src = fetchurl {
          url = "https://api.github.com/repos/swiftmailer/swiftmailer/zipball/15f7faf8508e04471f666633addacf54c0ab5933";
          sha256 = "1xiisdaxlmkzi16szh7lm3ay9vr9pdz0q2ah7vqaqrm2b4mwd90g";
        };
      };
    };
    "symfony/cache" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-cache-17a6d585603fade3838bc692548b619d97ded67e";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/cache/zipball/17a6d585603fade3838bc692548b619d97ded67e";
          sha256 = "171f3c6sl3wmly74kqpbyfa75gdlq09nrsl0yrsv7dnxvlmzf4j1";
        };
      };
    };
    "symfony/cache-contracts" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-cache-contracts-c0446463729b89dd4fa62e9aeecc80287323615d";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/cache-contracts/zipball/c0446463729b89dd4fa62e9aeecc80287323615d";
          sha256 = "132dszn4d7nm6p8rjh60qcx6jdylf82vj9gxh26ma5nw39n9crfn";
        };
      };
    };
    "symfony/console" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-console-864568fdc0208b3eba3638b6000b69d2386e6768";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/console/zipball/864568fdc0208b3eba3638b6000b69d2386e6768";
          sha256 = "0nb42nc00w0s10b8iz4xa84gfrix4yha9nw12mdb4wgi8jl59zhy";
        };
      };
    };
    "symfony/css-selector" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-css-selector-5d5f97809015102116208b976eb2edb44b689560";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/css-selector/zipball/5d5f97809015102116208b976eb2edb44b689560";
          sha256 = "03gaq78ah9bsk63g1p1mr9yr7rhx0rv4ag3yn2n7phrwzid5kcda";
        };
      };
    };
    "symfony/deprecation-contracts" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-deprecation-contracts-5f38c8804a9e97d23e0c8d63341088cd8a22d627";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/deprecation-contracts/zipball/5f38c8804a9e97d23e0c8d63341088cd8a22d627";
          sha256 = "11k6a8v9b6p0j788fgykq6s55baba29lg37fwvmn4igxxkfwmbp3";
        };
      };
    };
    "symfony/error-handler" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-error-handler-1416bc16317a8188aabde251afef7618bf4687ac";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/error-handler/zipball/1416bc16317a8188aabde251afef7618bf4687ac";
          sha256 = "19xh2kbwimgvvxr7ihnpsnajpxc1v9j9hzdzzb722h79rfsmj59a";
        };
      };
    };
    "symfony/event-dispatcher" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-event-dispatcher-d08d6ec121a425897951900ab692b612a61d6240";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/event-dispatcher/zipball/d08d6ec121a425897951900ab692b612a61d6240";
          sha256 = "16fhr3yj6rm6ax09s7ll7kqjlqgzkcsj8vlj5qrlwasw40nj0agx";
        };
      };
    };
    "symfony/event-dispatcher-contracts" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-event-dispatcher-contracts-69fee1ad2332a7cbab3aca13591953da9cdb7a11";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/event-dispatcher-contracts/zipball/69fee1ad2332a7cbab3aca13591953da9cdb7a11";
          sha256 = "1xajgmj8fnix4q1p93mhhiwvxspm8p4ksgzyyh31sj4xsp1c41x7";
        };
      };
    };
    "symfony/filesystem" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-filesystem-056e92acc21d977c37e6ea8e97374b2a6c8551b0";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/filesystem/zipball/056e92acc21d977c37e6ea8e97374b2a6c8551b0";
          sha256 = "1swja2x0wc24417cizpnfphv3qdfyyj5fbiafi0gh6cfvk1z83vr";
        };
      };
    };
    "symfony/finder" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-finder-ccccb9d48ca42757dd12f2ca4bf857a4e217d90d";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/finder/zipball/ccccb9d48ca42757dd12f2ca4bf857a4e217d90d";
          sha256 = "18qnl0dh9s48nvy9ckcmw6jjyypiwy36lvcwbpvmjv3z342kayq1";
        };
      };
    };
    "symfony/http-client-contracts" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-http-client-contracts-7e82f6084d7cae521a75ef2cb5c9457bbda785f4";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/http-client-contracts/zipball/7e82f6084d7cae521a75ef2cb5c9457bbda785f4";
          sha256 = "04mszmb94y0xjs0cwqxzhpf65kfqhhqznldifbxvrrlxb9nn23qc";
        };
      };
    };
    "symfony/http-foundation" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-http-foundation-e8fbbab7c4a71592985019477532629cb2e142dc";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/http-foundation/zipball/e8fbbab7c4a71592985019477532629cb2e142dc";
          sha256 = "14j4z1i3gvpqzl99cnlgdyyxvvp5ka3j4s30wg1lqp0pcm1inrj6";
        };
      };
    };
    "symfony/http-kernel" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-http-kernel-eb540ef6870dbf33c92e372cfb869ebf9649e6cb";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/http-kernel/zipball/eb540ef6870dbf33c92e372cfb869ebf9649e6cb";
          sha256 = "0fwzrqbl0cyxcw2430v8zrpsbc9kcj9c5h5rgby1f2v63vqq2b3k";
        };
      };
    };
    "symfony/mime" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-mime-64258e870f8cc75c3dae986201ea2df58c210b52";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/mime/zipball/64258e870f8cc75c3dae986201ea2df58c210b52";
          sha256 = "14s26zam8dxhbskizcw6mpyizg6n4i1ambq5ni6r9gv3rs6cfr9p";
        };
      };
    };
    "symfony/polyfill-ctype" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-ctype-c6c942b1ac76c82448322025e084cadc56048b4e";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-ctype/zipball/c6c942b1ac76c82448322025e084cadc56048b4e";
          sha256 = "0jpk859wx74vm03q5s9z25f4ak2138p2x5q3b587wvy8rq2m4pbd";
        };
      };
    };
    "symfony/polyfill-iconv" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-iconv-06fb361659649bcfd6a208a0f1fcaf4e827ad342";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-iconv/zipball/06fb361659649bcfd6a208a0f1fcaf4e827ad342";
          sha256 = "0glb56w5q4v2j629rkndp2c7v4mcs6xdl14nwaaxy85lr5w4ixnq";
        };
      };
    };
    "symfony/polyfill-intl-grapheme" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-intl-grapheme-5601e09b69f26c1828b13b6bb87cb07cddba3170";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-intl-grapheme/zipball/5601e09b69f26c1828b13b6bb87cb07cddba3170";
          sha256 = "1k3xk8iknyjaslzvhdl1am3jlyndvb6pw0509znmwgvc2jhkb4jr";
        };
      };
    };
    "symfony/polyfill-intl-idn" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-intl-idn-2d63434d922daf7da8dd863e7907e67ee3031483";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-intl-idn/zipball/2d63434d922daf7da8dd863e7907e67ee3031483";
          sha256 = "0sk592qrdb6dvk6v8msjva8p672qmhmnzkw1lw53gks0xrc20xjy";
        };
      };
    };
    "symfony/polyfill-intl-normalizer" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-intl-normalizer-43a0283138253ed1d48d352ab6d0bdb3f809f248";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-intl-normalizer/zipball/43a0283138253ed1d48d352ab6d0bdb3f809f248";
          sha256 = "04irkl6aks8zyfy17ni164060liihfyraqm1fmpjbs5hq0b14sc9";
        };
      };
    };
    "symfony/polyfill-mbstring" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-mbstring-5232de97ee3b75b0360528dae24e73db49566ab1";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-mbstring/zipball/5232de97ee3b75b0360528dae24e73db49566ab1";
          sha256 = "1mm670fxj2x72a9mbkyzs3yifpp6glravq2ss438bags1xf6psz8";
        };
      };
    };
    "symfony/polyfill-php72" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-php72-cc6e6f9b39fe8075b3dabfbaf5b5f645ae1340c9";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-php72/zipball/cc6e6f9b39fe8075b3dabfbaf5b5f645ae1340c9";
          sha256 = "12dmz2n1b9pqqd758ja0c8h8h5dxdai5ik74iwvaxc5xn86a026b";
        };
      };
    };
    "symfony/polyfill-php73" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-php73-a678b42e92f86eca04b7fa4c0f6f19d097fb69e2";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-php73/zipball/a678b42e92f86eca04b7fa4c0f6f19d097fb69e2";
          sha256 = "10rq2x2q9hsdzskrz0aml5qcji27ypxam324044fi24nl60fyzg0";
        };
      };
    };
    "symfony/polyfill-php80" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-php80-dc3063ba22c2a1fd2f45ed856374d79114998f91";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-php80/zipball/dc3063ba22c2a1fd2f45ed856374d79114998f91";
          sha256 = "1mhfjibk7mqyzlqpz6jjpxpd93fnfw0nik140x3mq1d2blg5cbvd";
        };
      };
    };
    "symfony/process" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-process-98cb8eeb72e55d4196dd1e36f1f16e7b3a9a088e";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/process/zipball/98cb8eeb72e55d4196dd1e36f1f16e7b3a9a088e";
          sha256 = "0xzxrgarkcbbb7y2gq92fj2hmdmm5hl139lnqgx13swfdm3v2z9b";
        };
      };
    };
    "symfony/psr-http-message-bridge" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-psr-http-message-bridge-81db2d4ae86e9f0049828d9343a72b9523884e5d";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/psr-http-message-bridge/zipball/81db2d4ae86e9f0049828d9343a72b9523884e5d";
          sha256 = "0np4vrz4caslj83q7xabp6y8i15j2k5bm0y317r80zx9iyhpx6wm";
        };
      };
    };
    "symfony/routing" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-routing-4a7b2bf5e1221be1902b6853743a9bb317f6925e";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/routing/zipball/4a7b2bf5e1221be1902b6853743a9bb317f6925e";
          sha256 = "031wxlw2nx9v2r8mma09pqkkdvqg5mg4g4xfwwcph4xa7rd0pxs0";
        };
      };
    };
    "symfony/service-contracts" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-service-contracts-f040a30e04b57fbcc9c6cbcf4dbaa96bd318b9bb";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/service-contracts/zipball/f040a30e04b57fbcc9c6cbcf4dbaa96bd318b9bb";
          sha256 = "1i573rmajc33a9nrgwgc4k3svg29yp9xv17gp133rd1i705hwv1y";
        };
      };
    };
    "symfony/string" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-string-01b35eb64cac8467c3f94cd0ce2d0d376bb7d1db";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/string/zipball/01b35eb64cac8467c3f94cd0ce2d0d376bb7d1db";
          sha256 = "094avxmawqd00hfnwyqs6p2njrj202ibjrrv8vs8yx5az1875mlz";
        };
      };
    };
    "symfony/translation" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-translation-61af68dba333e2d376a325a29c2a3f2a605b4876";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/translation/zipball/61af68dba333e2d376a325a29c2a3f2a605b4876";
          sha256 = "1bqjbdkqr325jn6mafcrh945yals73qvklpb1blh9in2z802fj0g";
        };
      };
    };
    "symfony/translation-contracts" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-translation-contracts-95c812666f3e91db75385749fe219c5e494c7f95";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/translation-contracts/zipball/95c812666f3e91db75385749fe219c5e494c7f95";
          sha256 = "073l1pbmwbkaviwwjq9ypb1w7dk366nn2vn1vancbal0zqk0zx7b";
        };
      };
    };
    "symfony/var-dumper" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-var-dumper-d693200a73fae179d27f8f1b16b4faf3e8569eba";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/var-dumper/zipball/d693200a73fae179d27f8f1b16b4faf3e8569eba";
          sha256 = "0yr2lqnk3wmv43iw0qag4nl2248f17cwhmv41k6w84x8kzrq1rc9";
        };
      };
    };
    "symfony/var-exporter" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-var-exporter-d26db2d2b2d7eb2c1adb8545179f8803998b8237";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/var-exporter/zipball/d26db2d2b2d7eb2c1adb8545179f8803998b8237";
          sha256 = "0pkr7g36b0xxbwlphfdaj957ghjqdvz2zmvvv2jq904mfb4k1xhp";
        };
      };
    };
    "thecodingmachine/safe" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "thecodingmachine-safe-a8ab0876305a4cdaef31b2350fcb9811b5608dbc";
        src = fetchurl {
          url = "https://api.github.com/repos/thecodingmachine/safe/zipball/a8ab0876305a4cdaef31b2350fcb9811b5608dbc";
          sha256 = "1l6n5gixh8ahs8bzbpjzixfm8g93vy9hzvivvivs332h85n3p96s";
        };
      };
    };
    "tijsverkoyen/css-to-inline-styles" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "tijsverkoyen-css-to-inline-styles-b43b05cf43c1b6d849478965062b6ef73e223bb5";
        src = fetchurl {
          url = "https://api.github.com/repos/tijsverkoyen/CssToInlineStyles/zipball/b43b05cf43c1b6d849478965062b6ef73e223bb5";
          sha256 = "0lc6jviz8faqxxs453dbqvfdmm6l2iczxla22v2r6xhakl58pf3w";
        };
      };
    };
    "vlucas/phpdotenv" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "vlucas-phpdotenv-b3eac5c7ac896e52deab4a99068e3f4ab12d9e56";
        src = fetchurl {
          url = "https://api.github.com/repos/vlucas/phpdotenv/zipball/b3eac5c7ac896e52deab4a99068e3f4ab12d9e56";
          sha256 = "1w8gylm0qwgwx2y3na9s2knpvc00yfhwf01p662l1cn9b3h33i11";
        };
      };
    };
    "voku/portable-ascii" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "voku-portable-ascii-80953678b19901e5165c56752d087fc11526017c";
        src = fetchurl {
          url = "https://api.github.com/repos/voku/portable-ascii/zipball/80953678b19901e5165c56752d087fc11526017c";
          sha256 = "112sz1jl55l3qm3041ijyzxy7qbv0sa6535hx6sp7nk2c76wjq0d";
        };
      };
    };
    "web-auth/cose-lib" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "web-auth-cose-lib-ed172d2dc1a6b87b5c644c07c118cd30c1b3819b";
        src = fetchurl {
          url = "https://api.github.com/repos/web-auth/cose-lib/zipball/ed172d2dc1a6b87b5c644c07c118cd30c1b3819b";
          sha256 = "0nhclkjmr5dp3ywr8ykykgkv5p4sn05czrdrd1wy4nnm7sgrdzqb";
        };
      };
    };
    "web-auth/metadata-service" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "web-auth-metadata-service-8488d3a832a38cc81c670fce05de1e515c6e64b1";
        src = fetchurl {
          url = "https://api.github.com/repos/web-auth/webauthn-metadata-service/zipball/8488d3a832a38cc81c670fce05de1e515c6e64b1";
          sha256 = "09lh8yd6dhnxj4msg7s4rn271wwapjqpw6y58hrfs7yc4wv2k24k";
        };
      };
    };
    "web-auth/webauthn-lib" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "web-auth-webauthn-lib-04b98ee3d39cb79dad68a7c15c297c085bf66bfe";
        src = fetchurl {
          url = "https://api.github.com/repos/web-auth/webauthn-lib/zipball/04b98ee3d39cb79dad68a7c15c297c085bf66bfe";
          sha256 = "1kbihpxjjz3a3lvsnpcrym6j4lg5q0vlg4s4bmw52l3bxvzmsa7a";
        };
      };
    };
    "webmozart/assert" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "webmozart-assert-6964c76c7804814a842473e0c8fd15bab0f18e25";
        src = fetchurl {
          url = "https://api.github.com/repos/webmozarts/assert/zipball/6964c76c7804814a842473e0c8fd15bab0f18e25";
          sha256 = "17xqhb2wkwr7cgbl4xdjf7g1vkal17y79rpp6xjpf1xgl5vypc64";
        };
      };
    };
    "whichbrowser/parser" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "whichbrowser-parser-bcf642a1891032de16a5ab976fd352753dd7f9a0";
        src = fetchurl {
          url = "https://api.github.com/repos/WhichBrowser/Parser-PHP/zipball/bcf642a1891032de16a5ab976fd352753dd7f9a0";
          sha256 = "081sv2g34ms1k9cr8cshvvmwnciic8kmy6rqvdiwwmjx3rq8yfc9";
        };
      };
    };
    "willdurand/geocoder" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "willdurand-geocoder-3e86f5b10ab0cef1cf03f979fe8e34b6476daff0";
        src = fetchurl {
          url = "https://api.github.com/repos/geocoder-php/php-common/zipball/3e86f5b10ab0cef1cf03f979fe8e34b6476daff0";
          sha256 = "1z0rqvxb9gwxbfa7c06hvbp4hvvs8xjg5gm1v8p2sigwdgw8f0zh";
        };
      };
    };
  };
  devPackages = {
    "barryvdh/laravel-debugbar" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "barryvdh-laravel-debugbar-88fd9cfa144b06b2549e9d487fdaec68265e791e";
        src = fetchurl {
          url = "https://api.github.com/repos/barryvdh/laravel-debugbar/zipball/88fd9cfa144b06b2549e9d487fdaec68265e791e";
          sha256 = "1pra2f2h8v59g3mh1mwr9ygn17igclw5xlk6xswpbkvbw2szlfhv";
        };
      };
    };
    "barryvdh/laravel-ide-helper" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "barryvdh-laravel-ide-helper-73b1012b927633a1b4cd623c2e6b1678e6faef08";
        src = fetchurl {
          url = "https://api.github.com/repos/barryvdh/laravel-ide-helper/zipball/73b1012b927633a1b4cd623c2e6b1678e6faef08";
          sha256 = "040zh3gmkd11pddnkgahbwrw09lcwdq8cgb1i40mmi5sippc4c6a";
        };
      };
    };
    "barryvdh/reflection-docblock" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "barryvdh-reflection-docblock-6b69015d83d3daf9004a71a89f26e27d27ef6a16";
        src = fetchurl {
          url = "https://api.github.com/repos/barryvdh/ReflectionDocBlock/zipball/6b69015d83d3daf9004a71a89f26e27d27ef6a16";
          sha256 = "14ssv90ls93cfivp8vdic9zj2cprmdy32pgky85bwkmc6vxfjw82";
        };
      };
    };
    "composer/ca-bundle" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "composer-ca-bundle-78a0e288fdcebf92aa2318a8d3656168da6ac1a5";
        src = fetchurl {
          url = "https://api.github.com/repos/composer/ca-bundle/zipball/78a0e288fdcebf92aa2318a8d3656168da6ac1a5";
          sha256 = "0fqx8cn7b0mrc7mvp8mdrl4g0y65br6wrbhizp4mk1qc7rf0xrvk";
        };
      };
    };
    "composer/composer" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "composer-composer-92b2ccbef65292ba9f2004271ef47c7231e2eed5";
        src = fetchurl {
          url = "https://api.github.com/repos/composer/composer/zipball/92b2ccbef65292ba9f2004271ef47c7231e2eed5";
          sha256 = "1q4ndc8bjwvcqijxdlamacvgnx2vah56mg0zkz07wgnyb9dgjbp9";
        };
      };
    };
    "composer/metadata-minifier" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "composer-metadata-minifier-c549d23829536f0d0e984aaabbf02af91f443207";
        src = fetchurl {
          url = "https://api.github.com/repos/composer/metadata-minifier/zipball/c549d23829536f0d0e984aaabbf02af91f443207";
          sha256 = "0ldblf3haw1q02zdbckq0v0dh81a948n9bmpfjs4zpj1zmxymmlg";
        };
      };
    };
    "composer/semver" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "composer-semver-a02fdf930a3c1c3ed3a49b5f63859c0c20e10464";
        src = fetchurl {
          url = "https://api.github.com/repos/composer/semver/zipball/a02fdf930a3c1c3ed3a49b5f63859c0c20e10464";
          sha256 = "0dd8m30jmjy2x64jv50xjva5x36hn3wrwcqnc38jrdaq2hcg1092";
        };
      };
    };
    "composer/spdx-licenses" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "composer-spdx-licenses-de30328a7af8680efdc03e396aad24befd513200";
        src = fetchurl {
          url = "https://api.github.com/repos/composer/spdx-licenses/zipball/de30328a7af8680efdc03e396aad24befd513200";
          sha256 = "0yamrbw2br8v3775pmlmvlqaylgvrd51ar274963cpkhxv1a7xfg";
        };
      };
    };
    "composer/xdebug-handler" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "composer-xdebug-handler-964adcdd3a28bf9ed5d9ac6450064e0d71ed7496";
        src = fetchurl {
          url = "https://api.github.com/repos/composer/xdebug-handler/zipball/964adcdd3a28bf9ed5d9ac6450064e0d71ed7496";
          sha256 = "1drd6sfah4l1bjikr2m8v2cc82qnm398k48vhlg9b93v5n3k8x32";
        };
      };
    };
    "doctrine/annotations" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "doctrine-annotations-e6e7b7d5b45a2f2abc5460cc6396480b2b1d321f";
        src = fetchurl {
          url = "https://api.github.com/repos/doctrine/annotations/zipball/e6e7b7d5b45a2f2abc5460cc6396480b2b1d321f";
          sha256 = "090vizq3xy9p151cjx5fa2izgvypc756wrnclswiiiac4h6mzpyf";
        };
      };
    };
    "doctrine/instantiator" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "doctrine-instantiator-d56bf6102915de5702778fe20f2de3b2fe570b5b";
        src = fetchurl {
          url = "https://api.github.com/repos/doctrine/instantiator/zipball/d56bf6102915de5702778fe20f2de3b2fe570b5b";
          sha256 = "04rihgfjv8alvvb92bnb5qpz8fvqvjwfrawcjw34pfnfx4jflcwh";
        };
      };
    };
    "facade/ignition-contracts" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "facade-ignition-contracts-3c921a1cdba35b68a7f0ccffc6dffc1995b18267";
        src = fetchurl {
          url = "https://api.github.com/repos/facade/ignition-contracts/zipball/3c921a1cdba35b68a7f0ccffc6dffc1995b18267";
          sha256 = "1nsjwd1k9q8qmfvh7m50rs42yxzxyq4f56r6dq205gwcmqsjb2j0";
        };
      };
    };
    "filp/whoops" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "filp-whoops-c13c0be93cff50f88bbd70827d993026821914dd";
        src = fetchurl {
          url = "https://api.github.com/repos/filp/whoops/zipball/c13c0be93cff50f88bbd70827d993026821914dd";
          sha256 = "0janbd93xvr5hy2bms05q1l31gmwbqrgjfvbzkmv3bfw4gcksq0i";
        };
      };
    };
    "friendsofphp/php-cs-fixer" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "friendsofphp-php-cs-fixer-d5b8a9d852b292c2f8a035200fa6844b1f82300b";
        src = fetchurl {
          url = "https://api.github.com/repos/FriendsOfPHP/PHP-CS-Fixer/zipball/d5b8a9d852b292c2f8a035200fa6844b1f82300b";
          sha256 = "0pq2yfx9z7x8bg7307fn81kx1l8qyfx8q4n5gdlc448djak0v0mw";
        };
      };
    };
    "itsgoingd/clockwork" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "itsgoingd-clockwork-01686ebbf75d8e121dfb1b60e52f334858793830";
        src = fetchurl {
          url = "https://api.github.com/repos/itsgoingd/clockwork/zipball/01686ebbf75d8e121dfb1b60e52f334858793830";
          sha256 = "0pdybw0my0wvrjnpwjgaw1lhrbnfc801w027cp4pl1lkmqb4hkn9";
        };
      };
    };
    "justinrainbow/json-schema" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "justinrainbow-json-schema-2ba9c8c862ecd5510ed16c6340aa9f6eadb4f31b";
        src = fetchurl {
          url = "https://api.github.com/repos/justinrainbow/json-schema/zipball/2ba9c8c862ecd5510ed16c6340aa9f6eadb4f31b";
          sha256 = "18hqybnyfcyvnkjzgq91nqgb2c05gmziliq5ck8l8cy7s75wm6xf";
        };
      };
    };
    "laravel/homestead" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "laravel-homestead-41a628deed9b601ee80689cf3ae0815195b5040f";
        src = fetchurl {
          url = "https://api.github.com/repos/laravel/homestead/zipball/41a628deed9b601ee80689cf3ae0815195b5040f";
          sha256 = "0d3xls3mh4zl83almkl9cgf91sq5qjicdw58zxcz3ka4db7yxjw0";
        };
      };
    };
    "maximebf/debugbar" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "maximebf-debugbar-6d51ee9e94cff14412783785e79a4e7ef97b9d62";
        src = fetchurl {
          url = "https://api.github.com/repos/maximebf/php-debugbar/zipball/6d51ee9e94cff14412783785e79a4e7ef97b9d62";
          sha256 = "13lh63wnsp2a6564h3if3925x4maf2plkhzyd1byv995g7bhi68i";
        };
      };
    };
    "myclabs/deep-copy" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "myclabs-deep-copy-776f831124e9c62e1a2c601ecc52e776d8bb7220";
        src = fetchurl {
          url = "https://api.github.com/repos/myclabs/DeepCopy/zipball/776f831124e9c62e1a2c601ecc52e776d8bb7220";
          sha256 = "181f3fsxs6s2wyy4y7qfk08qmlbvz1wn3mn3lqy42grsb8g8ym0k";
        };
      };
    };
    "nikic/php-parser" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "nikic-php-parser-4432ba399e47c66624bc73c8c0f811e5c109576f";
        src = fetchurl {
          url = "https://api.github.com/repos/nikic/PHP-Parser/zipball/4432ba399e47c66624bc73c8c0f811e5c109576f";
          sha256 = "0372c09xdgdr9dhd9m7sblxyqxk9xdk2r9s0i13ja3ascsz3zvpd";
        };
      };
    };
    "nunomaduro/collision" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "nunomaduro-collision-41b7e9999133d5082700d31a1d0977161df8322a";
        src = fetchurl {
          url = "https://api.github.com/repos/nunomaduro/collision/zipball/41b7e9999133d5082700d31a1d0977161df8322a";
          sha256 = "019bmg1wdxh74a2fx0fjz34m8pixxhsrj2dvkzih30yri340w1ns";
        };
      };
    };
    "phar-io/manifest" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phar-io-manifest-85265efd3af7ba3ca4b2a2c34dbfc5788dd29133";
        src = fetchurl {
          url = "https://api.github.com/repos/phar-io/manifest/zipball/85265efd3af7ba3ca4b2a2c34dbfc5788dd29133";
          sha256 = "13cqrx7iikx2aixszhxl55ql6hikblvbalix0kr05pbiccipg6fv";
        };
      };
    };
    "phar-io/version" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phar-io-version-bae7c545bef187884426f042434e561ab1ddb182";
        src = fetchurl {
          url = "https://api.github.com/repos/phar-io/version/zipball/bae7c545bef187884426f042434e561ab1ddb182";
          sha256 = "0hqmrihb4wv53rl3fg93wjldwrz79jyad5bv29ynbdklsirh7b2l";
        };
      };
    };
    "php-cs-fixer/diff" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "php-cs-fixer-diff-dbd31aeb251639ac0b9e7e29405c1441907f5759";
        src = fetchurl {
          url = "https://api.github.com/repos/PHP-CS-Fixer/diff/zipball/dbd31aeb251639ac0b9e7e29405c1441907f5759";
          sha256 = "0wz8m2knrr8jhqbvkqayzykmxhgixxjivlkxmh5n8291sfgc2win";
        };
      };
    };
    "phpdocumentor/reflection-common" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phpdocumentor-reflection-common-1d01c49d4ed62f25aa84a747ad35d5a16924662b";
        src = fetchurl {
          url = "https://api.github.com/repos/phpDocumentor/ReflectionCommon/zipball/1d01c49d4ed62f25aa84a747ad35d5a16924662b";
          sha256 = "1wx720a17i24471jf8z499dnkijzb4b8xra11kvw9g9hhzfadz1r";
        };
      };
    };
    "phpdocumentor/reflection-docblock" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phpdocumentor-reflection-docblock-069a785b2141f5bcf49f3e353548dc1cce6df556";
        src = fetchurl {
          url = "https://api.github.com/repos/phpDocumentor/ReflectionDocBlock/zipball/069a785b2141f5bcf49f3e353548dc1cce6df556";
          sha256 = "0qid63bsfjmc3ka54f1ijl4a5zqwf7jmackjyjmbw3gxdnbi69il";
        };
      };
    };
    "phpdocumentor/type-resolver" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phpdocumentor-type-resolver-6a467b8989322d92aa1c8bf2bebcc6e5c2ba55c0";
        src = fetchurl {
          url = "https://api.github.com/repos/phpDocumentor/TypeResolver/zipball/6a467b8989322d92aa1c8bf2bebcc6e5c2ba55c0";
          sha256 = "01g6mihq5wd1396njjb7ibcdfgk26ix1kmbjb6dlshzav0k3983h";
        };
      };
    };
    "phpspec/prophecy" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phpspec-prophecy-be1996ed8adc35c3fd795488a653f4b518be70ea";
        src = fetchurl {
          url = "https://api.github.com/repos/phpspec/prophecy/zipball/be1996ed8adc35c3fd795488a653f4b518be70ea";
          sha256 = "167snpasy7499pbxpyx2bj607qa1vrg07xfpa30dlpbwi7f34dji";
        };
      };
    };
    "phpunit/php-code-coverage" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phpunit-php-code-coverage-f6293e1b30a2354e8428e004689671b83871edde";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/php-code-coverage/zipball/f6293e1b30a2354e8428e004689671b83871edde";
          sha256 = "0q7az9h109jchlsgkzlnvzl90f39ifqp53k9bih85lbkaiz5w329";
        };
      };
    };
    "phpunit/php-file-iterator" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phpunit-php-file-iterator-aa4be8575f26070b100fccb67faabb28f21f66f8";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/php-file-iterator/zipball/aa4be8575f26070b100fccb67faabb28f21f66f8";
          sha256 = "0vxnrzwb573ddmiw1sd77bdym6jiimwjhcz7yvmsr9wswkxh18l6";
        };
      };
    };
    "phpunit/php-invoker" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phpunit-php-invoker-5a10147d0aaf65b58940a0b72f71c9ac0423cc67";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/php-invoker/zipball/5a10147d0aaf65b58940a0b72f71c9ac0423cc67";
          sha256 = "1vqnnjnw94mzm30n9n5p2bfgd3wd5jah92q6cj3gz1nf0qigr4fh";
        };
      };
    };
    "phpunit/php-text-template" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phpunit-php-text-template-5da5f67fc95621df9ff4c4e5a84d6a8a2acf7c28";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/php-text-template/zipball/5da5f67fc95621df9ff4c4e5a84d6a8a2acf7c28";
          sha256 = "0ff87yzywizi6j2ps3w0nalpx16mfyw3imzn6gj9jjsfwc2bb8lq";
        };
      };
    };
    "phpunit/php-timer" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phpunit-php-timer-5a63ce20ed1b5bf577850e2c4e87f4aa902afbd2";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/php-timer/zipball/5a63ce20ed1b5bf577850e2c4e87f4aa902afbd2";
          sha256 = "0g1g7yy4zk1bidyh165fsbqx5y8f1c8pxikvcahzlfsr9p2qxk6a";
        };
      };
    };
    "phpunit/phpunit" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "phpunit-phpunit-c73c6737305e779771147af66c96ca6a7ed8a741";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/phpunit/zipball/c73c6737305e779771147af66c96ca6a7ed8a741";
          sha256 = "1j7iz17jpf69ijidylsavqxn67qf3n8xvs8g9gk6wkl4z6db7gc7";
        };
      };
    };
    "react/promise" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "react-promise-f3cff96a19736714524ca0dd1d4130de73dbbbc4";
        src = fetchurl {
          url = "https://api.github.com/repos/reactphp/promise/zipball/f3cff96a19736714524ca0dd1d4130de73dbbbc4";
          sha256 = "0wg9260q99z7sapsm43nhh1gl588z238aixjkp081x1h0c8j500m";
        };
      };
    };
    "sebastian/cli-parser" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sebastian-cli-parser-442e7c7e687e42adc03470c7b668bc4b2402c0b2";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/cli-parser/zipball/442e7c7e687e42adc03470c7b668bc4b2402c0b2";
          sha256 = "074qzdq19k9x4svhq3nak5h348xska56v1sqnhk1aj0jnrx02h37";
        };
      };
    };
    "sebastian/code-unit" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sebastian-code-unit-1fc9f64c0927627ef78ba436c9b17d967e68e120";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/code-unit/zipball/1fc9f64c0927627ef78ba436c9b17d967e68e120";
          sha256 = "04vlx050rrd54mxal7d93pz4119pas17w3gg5h532anfxjw8j7pm";
        };
      };
    };
    "sebastian/code-unit-reverse-lookup" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sebastian-code-unit-reverse-lookup-ac91f01ccec49fb77bdc6fd1e548bc70f7faa3e5";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/code-unit-reverse-lookup/zipball/ac91f01ccec49fb77bdc6fd1e548bc70f7faa3e5";
          sha256 = "1h1jbzz3zak19qi4mab2yd0ddblpz7p000jfyxfwd2ds0gmrnsja";
        };
      };
    };
    "sebastian/comparator" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sebastian-comparator-55f4261989e546dc112258c7a75935a81a7ce382";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/comparator/zipball/55f4261989e546dc112258c7a75935a81a7ce382";
          sha256 = "1d4bgf4m2x0kn3nw9hbb45asbx22lsp9vxl74rp1yl3sj2vk9sch";
        };
      };
    };
    "sebastian/complexity" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sebastian-complexity-739b35e53379900cc9ac327b2147867b8b6efd88";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/complexity/zipball/739b35e53379900cc9ac327b2147867b8b6efd88";
          sha256 = "1y4yz8n8hszbhinf9ipx3pqyvgm7gz0krgyn19z0097yq3bbq8yf";
        };
      };
    };
    "sebastian/diff" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sebastian-diff-3461e3fccc7cfdfc2720be910d3bd73c69be590d";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/diff/zipball/3461e3fccc7cfdfc2720be910d3bd73c69be590d";
          sha256 = "0967nl6cdnr0v0z83w4xy59agn60kfv8gb41aw3fpy1n2wpp62dj";
        };
      };
    };
    "sebastian/environment" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sebastian-environment-388b6ced16caa751030f6a69e588299fa09200ac";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/environment/zipball/388b6ced16caa751030f6a69e588299fa09200ac";
          sha256 = "022vn8zss3sm7hg83kg3y0lmjw2ak6cy64b584nbsgxfhlmf6msd";
        };
      };
    };
    "sebastian/exporter" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sebastian-exporter-d89cc98761b8cb5a1a235a6b703ae50d34080e65";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/exporter/zipball/d89cc98761b8cb5a1a235a6b703ae50d34080e65";
          sha256 = "1s8v0cbcjdb0wvwyh869y5f8d55mpjkr0f3gg2kvvxk3wh8nvvc7";
        };
      };
    };
    "sebastian/global-state" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sebastian-global-state-a90ccbddffa067b51f574dea6eb25d5680839455";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/global-state/zipball/a90ccbddffa067b51f574dea6eb25d5680839455";
          sha256 = "0pad9gz2y38rziywdliylhhgz6762053pm57254xf7hywfpqsa3a";
        };
      };
    };
    "sebastian/lines-of-code" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sebastian-lines-of-code-c1c2e997aa3146983ed888ad08b15470a2e22ecc";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/lines-of-code/zipball/c1c2e997aa3146983ed888ad08b15470a2e22ecc";
          sha256 = "0fay9s5cm16gbwr7qjihwrzxn7sikiwba0gvda16xng903argbk0";
        };
      };
    };
    "sebastian/object-enumerator" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sebastian-object-enumerator-5c9eeac41b290a3712d88851518825ad78f45c71";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/object-enumerator/zipball/5c9eeac41b290a3712d88851518825ad78f45c71";
          sha256 = "11853z07w8h1a67wsjy3a6ir5x7khgx6iw5bmrkhjkiyvandqcn1";
        };
      };
    };
    "sebastian/object-reflector" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sebastian-object-reflector-b4f479ebdbf63ac605d183ece17d8d7fe49c15c7";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/object-reflector/zipball/b4f479ebdbf63ac605d183ece17d8d7fe49c15c7";
          sha256 = "0g5m1fswy6wlf300x1vcipjdljmd3vh05hjqhqfc91byrjbk4rsg";
        };
      };
    };
    "sebastian/recursion-context" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sebastian-recursion-context-cd9d8cf3c5804de4341c283ed787f099f5506172";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/recursion-context/zipball/cd9d8cf3c5804de4341c283ed787f099f5506172";
          sha256 = "1k0ki1krwq6329vsbw3515wsyg8a7n2l83lk19pdc12i2lg9nhpy";
        };
      };
    };
    "sebastian/resource-operations" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sebastian-resource-operations-0f4443cb3a1d92ce809899753bc0d5d5a8dd19a8";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/resource-operations/zipball/0f4443cb3a1d92ce809899753bc0d5d5a8dd19a8";
          sha256 = "0p5s8rp7mrhw20yz5wx1i4k8ywf0h0ximcqan39n9qnma1dlnbyr";
        };
      };
    };
    "sebastian/type" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sebastian-type-81cd61ab7bbf2de744aba0ea61fae32f721df3d2";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/type/zipball/81cd61ab7bbf2de744aba0ea61fae32f721df3d2";
          sha256 = "0mar746dr79v1phlfhv5k6kk10615yc0vz6afnmr6r36irqdazya";
        };
      };
    };
    "sebastian/version" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "sebastian-version-c6c1022351a901512170118436c764e473f6de8c";
        src = fetchurl {
          url = "https://api.github.com/repos/sebastianbergmann/version/zipball/c6c1022351a901512170118436c764e473f6de8c";
          sha256 = "1bs7bwa9m0fin1zdk7vqy5lxzlfa9la90lkl27sn0wr00m745ig1";
        };
      };
    };
    "seld/jsonlint" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "seld-jsonlint-9ad6ce79c342fbd44df10ea95511a1b24dee5b57";
        src = fetchurl {
          url = "https://api.github.com/repos/Seldaek/jsonlint/zipball/9ad6ce79c342fbd44df10ea95511a1b24dee5b57";
          sha256 = "1ywni3i7zi2bsh7qpbf710qixd3jhpvz4l1bavrw9vnkxl38qj8p";
        };
      };
    };
    "seld/phar-utils" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "seld-phar-utils-8674b1d84ffb47cc59a101f5d5a3b61e87d23796";
        src = fetchurl {
          url = "https://api.github.com/repos/Seldaek/phar-utils/zipball/8674b1d84ffb47cc59a101f5d5a3b61e87d23796";
          sha256 = "14q8b6c7k1172nml5v88z244xy0vqbk6dhc68j2iv0l9yww2722d";
        };
      };
    };
    "symfony/debug" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-debug-45b2136377cca5f10af858968d6079a482bca473";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/debug/zipball/45b2136377cca5f10af858968d6079a482bca473";
          sha256 = "0p7g2mwrvg8x264kl9kn7a23adnqxh66jy1kjczq5c5xlpw2rxdb";
        };
      };
    };
    "symfony/options-resolver" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-options-resolver-5d0f633f9bbfcf7ec642a2b5037268e61b0a62ce";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/options-resolver/zipball/5d0f633f9bbfcf7ec642a2b5037268e61b0a62ce";
          sha256 = "1rk3wcxn08s0wdjxi2byj1mhr3xf0ql55wxwik8cbx57i8p5r2sw";
        };
      };
    };
    "symfony/polyfill-php70" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-polyfill-php70-5f03a781d984aae42cebd18e7912fa80f02ee644";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/polyfill-php70/zipball/5f03a781d984aae42cebd18e7912fa80f02ee644";
          sha256 = "0yzw1gp2q46pk8fmgvz4nyiz34m6d4kiardyr9ajdmfrlqsiy202";
        };
      };
    };
    "symfony/stopwatch" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-stopwatch-d99310c33e833def36419c284f60e8027d359678";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/stopwatch/zipball/d99310c33e833def36419c284f60e8027d359678";
          sha256 = "029ymn8z4fa51cka6292n913sll9031kiskw3wzzs937q88ldsw0";
        };
      };
    };
    "symfony/yaml" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "symfony-yaml-d23115e4a3d50520abddccdbec9514baab1084c8";
        src = fetchurl {
          url = "https://api.github.com/repos/symfony/yaml/zipball/d23115e4a3d50520abddccdbec9514baab1084c8";
          sha256 = "19l78p2cssznsnxd2r8q11kf4km8hn5j5kdf3j8r6jifd96xg7xk";
        };
      };
    };
    "theseer/tokenizer" = {
      targetDir = "";
      src = composerEnv.buildZipPackage {
        name = "theseer-tokenizer-75a63c33a8577608444246075ea0af0d052e452a";
        src = fetchurl {
          url = "https://api.github.com/repos/theseer/tokenizer/zipball/75a63c33a8577608444246075ea0af0d052e452a";
          sha256 = "1cj1lb99hccsnwkq0i01mlcldmy1kxwcksfvgq6vfx8mgz3iicij";
        };
      };
    };
  };
in
composerEnv.buildPackage {
  inherit packages devPackages noDev;
  name = "lycheeorg-lychee-laravel";
  src = ./.;
  executable = false;
  symlinkDependencies = false;
  meta = {
    homepage = "https://lycheeorg.github.io/";
    license = "MIT";
  };
}
