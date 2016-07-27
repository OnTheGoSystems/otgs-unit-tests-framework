# PhpUnit

- Create a `tests` directory in your project
- Create a `phpunit` subdirectory in `tests`
- Copy `samples/phpunit.xml` file in that directory
  - Change `test-suite-name` into something appropriated
  - Most likely, you won't need to change anything else in that file
- Copy `samples/bootstrap.php` into `tests/phpunit`
  - Read the comments in this file and make the appropriated changes
- Copy `samples/composer.json` into `tests/phpunit`
  - Most likely, you won't need to make any change, except maybe for the "autoload" property, which you may wan to add, in case you want to autoload some additional classes in your test suite
- Create another `tests` subdirectory in `tests/phpunit`
  - Write your tests there
  - Unless you have customized the `phpunit.xml` file, you don't need to name the test files and classes in any particular way
- From `tests/phpunit` run `phpunit` to start your tests

## Resources

* Start from here for a general explanation: http://wordpress.stackexchange.com/a/164138/7291
* `WP_Mock`: https://github.com/10up/wp_mock
* Mockery: https://github.com/padraic/mockery
* Factory Muffin: https://github.com/thephpleague/factory-muffin