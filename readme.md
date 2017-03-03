# PhpUnit

* Create a `tests` directory in your project
* Create a `phpunit` subdirectory in `tests`[^1]
* Copy `samples/phpunit.xml` file in the root of your project[^2]
  * Change `test-suite-name` into something appropriated
  * Most likely, you won't need to change anything else in that file
* Copy `samples/bootstrap.php` into `tests/phpunit`
  * Read the comments in this file and make the appropriated changes
* Run `composer require --dev otgs/unit-tests-framework`[^3]
* Create another `tests` subdirectory in `tests/phpunit`
  * Write your tests there
  * Unless you have customized the `phpunit.xml` file, you don't need to name the test files and classes in any particular way
* Run `phpunit` from your project's root to start your tests[^4]

[^1]: it is recommended to keep your PHPUnit tests into a subfolder, because there are good changes you want to add other kind of tests (e.g. QUnit, PHPSpec, etc.)
[^2]: in case, for whatever reason, you can't do that, copy this file in `tests/phpunit` or wherever you find it more convenient 
[^3]: In some cases, you may want to use `composer require --dev otgs/unit-tests-framework:dev-develop`. When you do that, you may also need to run this command first `composer config minimum-stability dev`
[^4]: If you've placed the `phpunit.xml` you need to either move to that directory first, or tell phpunit where the file is with `phpunit --configuration path/to/phpunit.xml`

## How to use the OTGS_TestCase class

In order to take the most of this library, all your tests classes should extext `OTGS_TestCase`.

Once you do that, this will happen:

1. `\OTGS_TestCase::setupBeforeClass`:
  - `$_GET` and `$_POST` are set to an empty array
  - An instance of `FactoryMuffin` is provided: you can refer to it as `self::$fm` (see "Resources and dependencies" for more details)
2. `\OTGS_TestCase::setUp`
  - `FunctionMocker` is initialized (see "Resources and dependencies" for more details)
  - `WP_Mock` is initialized (see "Resources and dependencies" for more details)
3. `\OTGS_TestCase::tearDown`
  - `WP_Mock` is destroyed
  - `FunctionMocker ` is destroyed
  - `Mockery` is destroyed (just in case it has been used)
4. `\OTGS_TestCase::tearDownAfterClass`
  - Deletes all models created with `FactoryMuffin`

### Mock WP Core functions
This class also provide an helper method to quickly mock the functions defined by WordPress by using the `\OTGS_TestCase::get_mocked_wp_core_functions` which returns an instance of `OTGS_Mocked_WP_Core_Functions`.

`OTGS_Mocked_WP_Core_Functions` organize mocks in methods named using the same name of the file where the function is defined in WordPress codebase.

For instance, to mock of all functions defined in `post.php` like `get_post`, in your test you should simply call `$this->get_mocked_wp_core_functions()->post()`.

To mock `add_query_arg` yo call `$this->get_mocked_wp_core_functions()->functions()` because `add_query_arg` is defined in `functions.php`.

`OTGS_Mocked_WP_Core_Functions` tries to handle dependencies.

So, if you call `$this->get_mocked_wp_core_functions()->post()` to mock `wp_insert_post`, you automatically call `$this->get_mocked_wp_core_functions()->post()`, so to get all the meta related functions mocked as well.  
Finally, there is a "mock all" method you could use (though is discouraged) with `$this->mock_all_core_functions()`.

### Stub WP common classes
`\OTGS_TestCase` provides a helpful way to quickly get a stub of some of the most commonly used classes in WordPress.

By calling `$this->stubs->wpdb()` you will get a stub you can pass as a dependency of the classes you are testing.
If you need to control the behavior of this stub, you just use the standard PHPUnit mock helpers.

E.g. 1:  

```php
$wpdb = $this->stubs->wpdb();  
$wpdb->method( 'get_var' )->willReturn( 1 );
```
E.g. 2:
 
```php
$results = array(
	array( 'translation_id' => 1, 'element_id' => 1, 'language_code' => 'en', 'source_language_code' => null, 'trid' => 1, 'element_type' => 'post_page' ),
	array( 'translation_id' => 2, 'element_id' => 2, 'language_code' => 'fr', 'source_language_code' => 'en', 'trid' => 1, 'element_type' => 'post_page' ),
);
$wpdb = $this->this->wpdb();
$wpdb->expects( $this->exactly( 2 ) )->method( 'get_results' )->willReturn( $results );
```

Other stubs you can get:

- `WP_Widget` with `$this->stubs->WP_Widget()`
- `WP_Theme` with `$this->stubs->WP_Theme()`
- `WP_Filesystem_Direct` with `$this->stubs->WP_Filesystem_Direct()`
- `WP_Query` with `$this->stubs->WP_Query()`

It is important to know that, if you only need the class to be defined (e.g. hard-dependency, or sub-classing), you don't need to assig the stub to a variable: just call the method.

A good example is with WordPress' widgets, where you may have your own widget which is supposed to extend `WP_Widget`.

In this case, unless you want to mock some of the `WP_Widget` methods, you simply call `$this->stubs->WP_Widget()`, then write your tests.  
The class which extends `WP_Widget` will find a definition of this class, with all the methods (doing nothing).

## Resources and dependencies

Below are some resources on writing unit tests which lead to the creation of this library and links to the libraries included here:

* Start from here for a general explanation: http://wordpress.stackexchange.com/a/164138/7291
* 10up's WP_Mock`: https://github.com/10up/wp_mock
* Mockery: https://github.com/padraic/mockery
* Function mocker: https://github.com/lucatume/function-mocker
* Factory Muffin: https://github.com/thephpleague/factory-muffin
* Factory Muffin Faker: https://github.com/thephpleague/factory-muffin-faker
* The DomCrawler Component: http://symfony.com/doc/current/components/dom_crawler.html
* The CssSelector Component: http://symfony.com/doc/current/components/css_selector.html
* php-loremipsum: https://github.com/joshtronic/php-loremipsum