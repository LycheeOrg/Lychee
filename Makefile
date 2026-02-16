.PHONY: dist-gen dist-clean dist clean test formatting phpstan

composer:
	rm -r vendor  2> /dev/null || true
	composer install --prefer-dist --no-dev
	php artisan vendor:publish --tag=log-viewer-asset

npm-build:
	rm -r public/build 2> /dev/null || true
	rm -r public/embed 2> /dev/null || true
	rm -r node_modules 2> /dev/null || true
	npm ci
	npm run build
	npm run build:embed

dist-gen: clean composer npm-build
	@echo "packaging..."
	@mkdir Lychee
	@mkdir Lychee/public
	@mkdir Lychee/public/dist
	@mkdir Lychee/public/embed
	@mkdir Lychee/public/img
	@mkdir Lychee/public/uploads
	@mkdir Lychee/public/uploads/import
	@mkdir Lychee/public/sym
	@cp -r app                              Lychee
	@cp -r bootstrap                        Lychee
	@cp -r config                           Lychee
	@cp -r composer-cache                   Lychee
	@cp -r database                         Lychee
	@cp -r public/build                     Lychee/public
	@cp -r public/dist                      Lychee/public
	@cp -r public/embed                     Lychee/public
	@cp -r public/vendor                    Lychee/public
	@cp -r public/installer                 Lychee/public
	@cp -r public/img/*                     Lychee/public/img
	@cp -r public/.htaccess                 Lychee/public
	@cp -r public/.user.ini                 Lychee/public
	@cp -r public/favicon.ico               Lychee/public
	@cp -r public/index.php                 Lychee/public
	@cp -r public/robots.txt                Lychee/public
	@cp -r public/web.config                Lychee/public
	@cp -r lang                             Lychee
	@cp -r resources                        Lychee
	@cp -r routes                           Lychee
	@cp -r scripts                          Lychee
	@cp -r storage                          Lychee
	@cp -r vendor                           Lychee 2> /dev/null || true
	@cp -r .env.example                     Lychee
	@cp -r artisan                          Lychee
	@cp -r composer.json                    Lychee
	@cp -r composer.lock                    Lychee
	@cp -r index.php                        Lychee
	@cp -r LICENSE                          Lychee
	@cp -r README.md                        Lychee
	@cp -r simple_error_template.html       Lychee
	@cp -r version.md                       Lychee
	@touch Lychee/storage/logs/laravel.log
	@touch Lychee/public/dist/user.css
	@touch Lychee/public/dist/custom.js
	@touch Lychee/public/uploads/import/index.html

dist-clean: dist-gen
	find Lychee -wholename '*/[Tt]ests/*' -delete
	find Lychee -wholename '*/[Tt]est/*' -delete
	@rm -r Lychee/storage/framework/cache/data/* 2> /dev/null || true
	@rm    Lychee/storage/framework/sessions/* 2> /dev/null || true
	@rm    Lychee/storage/framework/views/* 2> /dev/null || true
	@rm    Lychee/storage/logs/* 2> /dev/null || true

dist: dist-clean
	@zip -r Lychee.zip Lychee

clean:
	@rm build/* 2> /dev/null || true
	@rm -r Lychee 2> /dev/null || true
	@rm -r public/build 2> /dev/null || true
	@rm -r node_modules 2> /dev/null || true
	@rm -r vendor  2> /dev/null || true

install: composer npm-build
	php artisan migrate

test:
	@if [ -x "vendor/bin/phpunit" ]; then \
		./vendor/bin/phpunit --stop-on-failure; \
	else \
		echo ""; \
		echo "Please install phpunit:"; \
		echo ""; \
		echo "  composer install"; \
		echo ""; \
	fi

formatting: rector
	@rm .php_cs.cache 2> /dev/null || true
	@if [ -x "vendor/bin/php-cs-fixer" ]; then \
		./vendor/bin/php-cs-fixer fix -v --config=.php-cs-fixer.php; \
	else \
		echo ""; \
		echo "Please install php-cs-fixer:"; \
		echo ""; \
		echo "  composer install"; \
		echo ""; \
	fi
	npm run format

rector:
	 vendor/bin/rector process

phpstan:
	vendor/bin/phpstan analyze --memory-limit 512M

# Generating new versions
gen_patch:
	php scripts/gen_release.php
	git add database
	git add version.md

release_patch: gen_patch
	git commit -S -m "bump to version $(shell cat version.md)"

gen_minor:
	php scripts/gen_release.php minor
	git add database
	git add version.md

release_minor: gen_minor
	git commit -S -m "bump to version $(shell cat version.md)"

gen_major:
	php scripts/gen_release.php major
	git add database
	git add version.md

release_major: gen_major
	git commit -m "bump to version $(shell cat version.md)"

build:
	mkdir build

test_unit:
	vendor/bin/phpunit --testsuite Unit --stop-on-failure --stop-on-error --no-coverage --log-junit report_unit.xml

test_webshop:
	vendor/bin/phpunit --testsuite Webshop --stop-on-failure --stop-on-error --no-coverage --log-junit report_webshop.xml

test_install:
	vendor/bin/phpunit --testsuite Install --stop-on-failure --stop-on-error --no-coverage --log-junit report_install.xml

test_ImageProcessing:
	vendor/bin/phpunit --testsuite ImageProcessing --stop-on-failure --stop-on-error --no-coverage --log-junit report_imageprocessing.xml

test_precomputing:
	vendor/bin/phpunit --testsuite Precomputing --stop-on-failure --stop-on-error --no-coverage --log-junit report_precomputing.xml

test_v2:
	vendor/bin/phpunit --testsuite Feature_v2 --stop-on-failure --stop-on-error --no-coverage --log-junit report_v2.xml

gen_typescript_types:
	php artisan typescript:transform

class-leak:
	vendor/bin/class-leak check app database/migrations config --skip-type Illuminate\\View\\Component

docker-build:
	docker build -t lychee-frankenphp .

docker-build-no-cache:
	docker build -t lychee-frankenphp . --no-cache

docker-run:
	docker compose up

test_pgsql_v2:
	docker compose -f docs/specs/2-how-to/docker-compose/docker-compose-pgsql.yaml up -d
	vendor/bin/phpunit --testsuite Feature_v2 --stop-on-failure --stop-on-error --no-coverage --log-junit report_v2.xml --configuration phpunit.pgsql.xml
	docker compose -f docs/specs/2-how-to/docker-compose/docker-compose-pgsql.yaml down