VERSION=`cat version.md`
FILES=$(wildcard *)

.PHONY: dist

dist: clean
	@echo "packaging..."
	@mkdir Lychee-v$(VERSION)
	@mkdir Lychee-v$(VERSION)/public
	@mkdir Lychee-v$(VERSION)/public/dist
	@mkdir Lychee-v$(VERSION)/public/docs
	@mkdir Lychee-v$(VERSION)/public/img
	@mkdir Lychee-v$(VERSION)/public/Lychee-front
	@mkdir Lychee-v$(VERSION)/public/uploads
	@mkdir Lychee-v$(VERSION)/public/uploads/small
	@mkdir Lychee-v$(VERSION)/public/uploads/medium
	@mkdir Lychee-v$(VERSION)/public/uploads/big
	@mkdir Lychee-v$(VERSION)/public/uploads/thumb
	@mkdir Lychee-v$(VERSION)/public/uploads/import
	@mkdir Lychee-v$(VERSION)/public/sym
	@cp -r public/dist                      Lychee-v$(VERSION)/public
	@cp -r public/docs/*                    Lychee-v$(VERSION)/public/docs
	@cp -r public/img/*                     Lychee-v$(VERSION)/public/img
	@cp -r public/Lychee-front/images       Lychee-v$(VERSION)/public/Lychee-front/images
	@cp -r public/Lychee-front/scripts      Lychee-v$(VERSION)/public/Lychee-front/scripts
	@cp -r public/Lychee-front/styles       Lychee-v$(VERSION)/public/Lychee-front/styles
	@cp -r public/Lychee-front/API.md       Lychee-v$(VERSION)/public/Lychee-front
	@cp -r public/Lychee-front/gulpfile.js  Lychee-v$(VERSION)/public/Lychee-front
	@cp -r public/Lychee-front/LICENSE      Lychee-v$(VERSION)/public/Lychee-front
	@cp -r public/Lychee-front/package.json Lychee-v$(VERSION)/public/Lychee-front
	@cp -r public/Lychee-front/README.md    Lychee-v$(VERSION)/public/Lychee-front
	@cp -r app                              Lychee-v$(VERSION)
	@cp -r bootstrap                        Lychee-v$(VERSION)
	@cp -r config                           Lychee-v$(VERSION)
	@cp -r database                         Lychee-v$(VERSION)
	@cp -r resources                        Lychee-v$(VERSION)
	@cp -r routes                           Lychee-v$(VERSION)
	@cp -r storage                          Lychee-v$(VERSION)
	@cp -r tests                            Lychee-v$(VERSION)
	@cp -r vendor                           Lychee-v$(VERSION) 2> /dev/null || true
	@cp -r public/.htaccess                 Lychee-v$(VERSION)/public
	@cp -r public/.gitignore                Lychee-v$(VERSION)/public
	@cp -r public/.user.ini                 Lychee-v$(VERSION)/public
	@cp -r public/CODE_OF_CONDUCT.md        Lychee-v$(VERSION)/public
	@cp -r public/favicon.ico               Lychee-v$(VERSION)/public
	@cp -r public/index.php                 Lychee-v$(VERSION)/public
	@cp -r public/robots.txt                Lychee-v$(VERSION)/public
	@cp -r public/web.config                Lychee-v$(VERSION)/public
	@cp -r .env.example                     Lychee-v$(VERSION)
	@cp -r artisan                          Lychee-v$(VERSION)
	@cp -r composer.json                    Lychee-v$(VERSION)
	@cp -r composer.lock                    Lychee-v$(VERSION)
	@cp -r LICENSE                          Lychee-v$(VERSION)
	@cp -r phpunit.xml                      Lychee-v$(VERSION)
	@cp -r readme.md                        Lychee-v$(VERSION)
	@cp -r server.php                       Lychee-v$(VERSION)
	@cp -r version.md                       Lychee-v$(VERSION)
	@rm Lychee-v$(VERSION)/storage/framework/sessions/* 2> /dev/null || true
	@rm Lychee-v$(VERSION)/storage/framework/views/* 2> /dev/null || true
	@rm Lychee-v$(VERSION)/storage/logs/* 2> /dev/null || true
	@touch Lychee-v$(VERSION)/storage/logs/laravel.log
	@touch Lychee-v$(VERSION)/public/dist/user.css
	@touch Lychee-v$(VERSION)/public/uploads/big/index.html
	@touch Lychee-v$(VERSION)/public/uploads/small/index.html
	@touch Lychee-v$(VERSION)/public/uploads/medium/index.html
	@touch Lychee-v$(VERSION)/public/uploads/thumb/index.html
	@touch Lychee-v$(VERSION)/public/uploads/import/index.html
	@touch Lychee-v$(VERSION)/public/sym/index.html
	@zip -r Lychee-v$(VERSION).zip Lychee-v$(VERSION)

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
		./vendor/bin/phpunit --verbose; \
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
