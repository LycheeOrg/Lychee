VERSION=`cat version.md`
FILES=$(wildcard *)

.PHONY: dist clean

composer:
	rm -r vendor  2> /dev/null || true
	composer install --prefer-dist --no-dev

dist-gen: clean composer
	@echo "packaging..."
	@mkdir Lychee-v$(VERSION)
	@mkdir Lychee-v$(VERSION)/public
	@mkdir Lychee-v$(VERSION)/public/dist
	@mkdir Lychee-v$(VERSION)/public/img
	@mkdir Lychee-v$(VERSION)/public/uploads
	@mkdir Lychee-v$(VERSION)/public/uploads/small
	@mkdir Lychee-v$(VERSION)/public/uploads/medium
	@mkdir Lychee-v$(VERSION)/public/uploads/big
	@mkdir Lychee-v$(VERSION)/public/uploads/thumb
	@mkdir Lychee-v$(VERSION)/public/uploads/raw
	@mkdir Lychee-v$(VERSION)/public/uploads/import
	@mkdir Lychee-v$(VERSION)/public/sym
	@cp -r public/dist                      Lychee-v$(VERSION)/public
	@cp -r public/installer                 Lychee-v$(VERSION)/public
	@cp -r public/img/*                     Lychee-v$(VERSION)/public/img
	@cp -r app                              Lychee-v$(VERSION)
	@cp -r bootstrap                        Lychee-v$(VERSION)
	@cp -r config                           Lychee-v$(VERSION)
	@cp -r database                         Lychee-v$(VERSION)
	@cp -r resources                        Lychee-v$(VERSION)
	@cp -r index.php                        Lychee-v$(VERSION)
	@cp -r simple_error_template.html       Lychee-v$(VERSION)
	@cp -r routes                           Lychee-v$(VERSION)
	@cp -r storage                          Lychee-v$(VERSION)
	@cp -r vendor                           Lychee-v$(VERSION) 2> /dev/null || true
	@cp -r public/.htaccess                 Lychee-v$(VERSION)/public
	@cp -r public/.user.ini                 Lychee-v$(VERSION)/public
	@cp -r public/favicon.ico               Lychee-v$(VERSION)/public
	@cp -r public/index.php                 Lychee-v$(VERSION)/public
	@cp -r public/robots.txt                Lychee-v$(VERSION)/public
	@cp -r public/web.config                Lychee-v$(VERSION)/public
	@cp -r .env.example                     Lychee-v$(VERSION)
	@cp -r artisan                          Lychee-v$(VERSION)
	@cp -r composer.json                    Lychee-v$(VERSION)
	@cp -r composer.lock                    Lychee-v$(VERSION)
	@cp -r composer-cache                   Lychee-v$(VERSION)
	@cp -r LICENSE                          Lychee-v$(VERSION)
	@cp -r readme.md                        Lychee-v$(VERSION)
	@cp -r server.php                       Lychee-v$(VERSION)
	@cp -r version.md                       Lychee-v$(VERSION)
	@touch Lychee-v$(VERSION)/storage/logs/laravel.log
	@touch Lychee-v$(VERSION)/public/dist/user.css
	@touch Lychee-v$(VERSION)/public/uploads/big/index.html
	@touch Lychee-v$(VERSION)/public/uploads/small/index.html
	@touch Lychee-v$(VERSION)/public/uploads/medium/index.html
	@touch Lychee-v$(VERSION)/public/uploads/thumb/index.html
	@touch Lychee-v$(VERSION)/public/uploads/raw/index.html
	@touch Lychee-v$(VERSION)/public/uploads/import/index.html
	@touch Lychee-v$(VERSION)/public/sym/index.html

dist: dist-gen	
	find Lychee-v$(VERSION) -wholename '*/[Tt]ests/*' -delete
	find Lychee-v$(VERSION) -wholename '*/[Tt]est/*' -delete
	@rm -r Lychee-v$(VERSION)/storage/framework/cache/data/* 2> /dev/null || true
	@rm    Lychee-v$(VERSION)/storage/framework/sessions/* 2> /dev/null || true
	@rm    Lychee-v$(VERSION)/storage/framework/views/* 2> /dev/null || true
	@rm    Lychee-v$(VERSION)/storage/logs/* 2> /dev/null || true
	@zip -r Lychee-v$(VERSION).zip Lychee-v$(VERSION)

cd:
	cd Lychee-v$(VERSION)

contrib_add:
	@echo "npx all-contributors-cli add <user> <bug|code|design|doc|question|tool|test|translation>"

contrib_generate:
	npx all-contributors-cli generate

contrib_check:
	npx all-contributors-cli check

clean:
	@rm -r Lychee-v* 2> /dev/null || true

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
		./vendor/bin/php-cs-fixer fix -v --config=.php_cs; \
	else \
		echo ""; \
		echo "Please install php-cs-fixer:"; \
		echo ""; \
		echo "  composer install"; \
		echo ""; \
	fi

release_minor:
	php gen_release
	git add database
	git add version.md
	git commit -m "bump to version $(shell cat version.md)"

release_major:
	php gen_release major
	git add database
	git add version.md
	git commit -m "bump to version $(shell cat version.md)"
