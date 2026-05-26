# SlimStatic

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/johnstevenson/slim-static/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/johnstevenson/slim-static/?branch=master)
[![Build Status](https://travis-ci.org/johnstevenson/slim-static.svg?branch=master)](https://travis-ci.org/johnstevenson/slim-static)

Slim PHP static proxy library.
## Contents
* [V4 Updats](#v4)
* [About](#About)
* [Usage](#Usage)
* [API](#Api)
* [Customizing](#Custom)
* [License](#License)

<a name="v4"></a>
## Version 4

Support for Slim v4 coincides with this fork version 4. While a container is not required, this package relies on the container for many of the features to work properly. 


### Main updates for v4

* `App::` resolves to the Slim Application
* `Container::` resolves to `$slim->getContainer()` if one is set
* ...and the following: 


### New Route Sugar

`Route::` resolves to a new `Router::class` for Laravel-like syntax. However, you must still include $request, $response... etc in the args as with Slim. The biggest update allows for methods, all utilizing Slim's underlying methods to achieve.


#### Http Verbs

- `Route::get()`
- `Route::post()`
- `Route::put()`
- `Route::patch()`
- `Route::delete()`
- `Route::options()`
- `Route::any()`
- `Route::match()`


#### Chainable methods

- `Route::middleware()`
- `Route::group()` 
- `Route::controller()->prefix(...)->group(...)`
-  ...etc. 

```php
Route::get('/', function($request, $response) {
    $response->getBody()->write('Route facade is cool.');
    return $response;
})->name('home');

Route::controller(PostController::class)->prefix('/posts')->group(function() {
    Route::get('', 'index')->name('posts.index');
    Route::get('', 'show')->name('posts.show');
});

# Utilizing PhpRenderer 
Route::view('/contact', $template, $data);
```


### RouteDecorator

The route decorators are required for some additional sugar regarding named routes syntax now uses laravel-style.

- `name()` instead of slim's `->setName(')`

Additionally, these decorators allow for pattern matching on routes:

- `where()`
- `whereIn()`
- `whereNumber()`
- `whereAlpha()`
- `whereSlug()`
- `whereUuid()`

```php
Route::get('/products/{slug}', function($request, $response) {
    //...
})  ->whereIn('status', ['active', 'inactive'])
    ->name('products.show');
```


### Config Sugar & Settings Interface

A `SettingsInterface` and `Settings` class has been included based on the Slim4 Skeleton and a forum post from odan.
 * @see https://github.com/slimphp/Slim-Skeleton/blob/main/src/Application/Settings/SettingsInterface.php
 * @see https://discourse.slimframework.com/t/di-container-and-settings/5770/11

```php
# In your container definitions, define config as new Setting(). 
$definitions = [
    'config' => fn() => new Settings([
        'app' => [
            'name' => 'SlimStatic',
            'someKey'  => 'someValue',
        ],
        'settings' => [
            'log' => null,
            'something' => 'else',
        ],
    ]),
];

Config::get();           # All config settings
Config::get('app');      # Gets the App array 
Config::get('app.name'); # Accepts up to one level of dot notation

```

<hr>

<a name="About"></a>
## About

SlimStatic provides a simple static interface to various features in the [Slim][slim]
micro framework. Turn this:

```php
$app->get('/hello-world', function()
{
    $app = Slim::getInstance();

    $app->view()->display('hello.html', array(
        'name' => $app->request()->get('name', 'world')
    ));
});

$app->run();
```

into this:

```php
Route::get('/hello-world', function()
{
    View::display('hello.html', array(
        'name' => Input::get('name', 'world')
    ));
});

App::run();
```

This library is based on [Slim-Facades][slim-facades] from Miroslav Rigler, but uses
[Statical][statical] to provide the static proxy interface.

<a name="Usage"></a>
## Usage
Install via [composer][composer]

```
# NOT CURRENTLY AVAILABLE 
composer require sirlionlabs/slim-static
```

Create your Slim app and boot SlimStatic:

```php
use Slim\App;
use Statical\SlimStatic\SlimStatic;

$app = new Slim();

SlimStatic::boot($app);
```

Now you can start using the static proxies listed below. In addition there is a proxy to
[Statical][statical] itself, aliased as `Statical` and available in any namespace, so you
can easily use the library to add your own proxies (see [Customizing](#Custom)) or define
namespaces.

If your app is namespaced you can avoid syntax like `\App::method` or *use* statements
by employing the namespacing feature:

```php
# Allow any registered proxy to be called anywhere in the `App\Name` namespace

Statical::addNamespace('*', 'App\\Name\\*');
```

<a name="Api"></a>
## API

The following static proxies are available:

Statical Alias          | Proxy
----------------------- | ----------------------------------------
[App](#App)             | to Slim instance
[Container](#Container) | UPDATED: to $slim->getContainer()
[Config](#Config)         | UPDATED: 'config' container definition, NEW: SettingsInterface::class
[Input](#Input)         | ~~to Slim\Http\Request instance~~ 
[Log](#Log)             | ~~to Slim\Log instance~~
[Request](#Request)     | ~~to Slim\Http\Request instance~~
[Response](#Response)   | ~~to Slim\Http\Response instance~~
[Route](#Route)         | NEW: calling Router::class 
[View](#View)           | UPDATED: 'view' container definition

<a name="App"></a>
#### App
Proxy to the Slim instance. Note that you cannot use the built-in resource locator statically,
because `App::foo = 'bar'` is not a method call. Use the [Container](#Container) proxy instead.

```php
App::expires('+1 week');
App::halt();
```

<a name="Config"></a>
#### Config
~~Sugar for Slim config, using the following methods:~~
Now utilizes included Settings::class (SettingsInterface)

- `get($key)` - returns value of `$app->getContainer()->get('config')->get($key)`
    - Now supports two levels of dot notation when using `SettingsInterface::class`
- `set($key, $value = null)` - calls `$app->getContainer()->get('config')->{$key} = $value;`

```php
$debug = Config::get('debug');
Config::set('log.enable', true);

# Note that you could also use:
$debug = App::config('debug');
App::config('log.enable', true);
```

<a name="Container"></a>
#### Container
Proxy to the Slim container instance. Use this to access the built-in resource locator.

```php
# $app->foo = 'bar'
Container::set('foo', 'bar'); # NOTE: \DI\Container cannot be set

# $bar = $app->foo
$bar = Container::get('foo');

```

<a name="Input"></a>
#### Input
~~Proxy to the Slim\Http\Request instance with an additional method:~~
NOTE: Slim\Http\Request is not in Slim4

- `file($name)` - returns `$_FILES[$name]`, or null if the file was not sent in the request

```php
# NOTE: Slim\Http\Request is not in Slim4
$avatar = Input::file('avatar');
$username = Input::get('username', 'default');
$password = Input::post('password');
```

<a name="Log"></a>
#### Log
~~Proxy to the Slim\Log instance.~~
NOTE: Slim4 relies on defining your own logger in the container

```php
# NOTE: Slim4 relies on defining your own logger in the container
Log::info('My info');
Log::debug('Degug info');
```

<a name="Request"></a>
#### Request
~~Proxy to the Slim\Http\Request instance.~~
NOTE: Slim\Http\Request is not in Slim4

```php
$path = Request::getPath();
$xhr = Request::isAjax();
```

<a name="Response"></a>
#### Response
Proxy to the Slim\Http\Response instance.
NOTE: Slim\Http\Response is not in Slim4

```php
# NOTE: Slim\Http\Request is not in Slim4
Response::redirect('/success');
Response::headers->set('Content-Type', 'application/json');
```

<a name="Route"></a>
#### Route
~~Sugar for the following Slim instance route-mapping methods:~~
NOTE: Classic Route methods are commented out in favor of new `Router::class`. 
A toggle may be implemented for developers to choose, but not currently available 

- `map`, `get`, `post`, `put`, `patch`, `delete`, `options`, `group`, `any`, `urlFor`

```php
# NOTE: 
Route::get('/users/:id', function ($id) {...});
Route::post('/users',  function () {...});
Route::urlFor('admin');
```

<a name="View"></a>
#### View
~~Proxy to the Slim\View instance~~
NOTE: now calls to container definition `view` which you can define as Slim\PhpRenderer
See also: `Route::view()`

```php
View::display('hello.html');
$output = View::render('world.html');
```

<a name="Custom"></a>
## Customizing
Since [Statical][statical] is already loaded, you can use it to create your own static proxies.
Let's take a `PaymentService` class as an example, that you want to alias as `Payment`.

The first step is to create a proxy class that extends the `Statical\BaseProxy` class.
It is normally empty and you can name it whatever you wish:

```php
class PaymentProxy extends \Statical\BaseProxy {}
```

You must then register this with Statical, using `addProxyInstance` if you use a class instance,
or `addProxyService` if you want to use the Slim container.
Using a class instance:

```php
# create our PaymentService class
$instance = new \PaymentService();

$alias = 'Payment';             # The static alias to call
$proxy = 'PaymentProxy';        # The proxy class you just created

Statical::addProxyInstance($alias, $proxy, $instance);

# Now we can call PaymentService methods via the static alias Payment
Payment::process();
```

Using the Slim container:

```php
# Register our service with Slim's DI container
Container::set('payment', function () {
    return new \PaymentService();
});


$alias = 'Payment';             # The static alias to call
$proxy = 'PaymentProxy';        # The proxy class you just created
$id = 'payment';                # The id of our service in the Slim container

Statical::addProxyService($alias, $proxy, Container::getInstance(), $id);

# Now we can call PaymentService methods via the static alias Payment
Payment::process();
```

Note that for namespaced code, the namespace must be included in the `$proxy` param.


<a name="License"></a>
## License

SlimStatic is licensed under the MIT License - see the `LICENSE` file for details


  [slim]: https://github.com/slimphp
  [slim-facades]: https://github.com/itsgoingd/slim-facades
  [statical]: https://github.com/johnstevenson/statical
  [composer]: https://getcomposer.org
