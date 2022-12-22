.PHONY: dist-gen dist-clean dist clean test formatting phpstan

composer:
	rm -r vendor  2> /dev/null || true
	composer install --prefer-dist --no-dev

dist-gen: clean composer
	@echo "packaging..."
	@mkdir Lychee
	@mkdir Lychee/public
	@mkdir Lychee/public/dist
	@mkdir Lychee/public/img
	@cp -r app                              Lychee
	@cp -r bootstrap                        Lychee
	@cp -r config                           Lychee
	@cp -r composer-cache                   Lychee
	@cp -r database                         Lychee
	@cp -r public/dist                      Lychee/public
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
	@rm -r Lychee 2> /dev/null || true

test:
	@if [ -x "vendor/bin/phpunit" ]; then \
		./vendor/bin/phpunit --verbose --stop-on-failure; \
	else \
		echo ""; \
		echo "Please install phpunit:"; \
		echo ""; \
		echo "  composer install"; \
		echo ""; \
	fi

formatting:
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

phpstan:
	vendor/bin/phpstan analyze

gen_minor:
	php scripts/gen_release.php
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
