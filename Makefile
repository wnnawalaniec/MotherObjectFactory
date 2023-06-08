.PHONY: test

test:
	./vendor/bin/phpunit --configuration=config/phpunit.xml

mutations:
	./vendor/bin/infection --configuration=config/infection.json5