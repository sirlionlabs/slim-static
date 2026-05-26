<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Statical\SlimStatic\Config;
use Statical\SlimStatic\Route;
use Statical\SlimStatic\Settings\Settings;
use Statical\SlimStatic\SlimStatic;

require __DIR__ . '/../vendor/autoload.php';

$container = new \DI\Container([
    'config' => fn() => new Settings([
        'app' => [
            'name' => 'SlimStatic',
            'someKey' => 'someValue',
        ],
        'settings' => [
            'log' => null,
            'something' => 'else',
        ]
    ]),
]);

# Slim Bridge with Container
$app = \DI\Bridge\Slim\Bridge::create($container);

# Boot SlimStatic
SlimStatic::boot($app);

# HOME
Route::get('/', function(Request $request, Response $response) {
    $response->getBody()->write( 
        Config::get('app.name').' version 4\'s Route facade is cool.'
    );
    return $response;
})->name('home');

# PRODUCTS 
Route::prefix('/products')->group(function() {
    
    # PRODUCTS.INDEX 
    Route::get('', function(Response $response) {
        $response->getBody()->write('
            <h1>Products Showcase</h1>
            <ul><li><a href="'.Route::urlFor('products.show', ['slug' => 'widget']).'">Widget</a>
            </li><li><a href="'.Route::urlFor('products.show', ['slug' => 'gadget']).'">Gadget</a></li></ul>
        ');
        return $response;
    })->name('products.index');

    # PRODUCTS.SHOW
    Route::get('/{slug}', function(Request $request, Response $response) {
        $response->getBody()->write('
            <h1>Buy this '. $request->getAttribute('slug').'</h1>
            <p>It is really cool.</p>
            <p>&larr;<a href="'.Route::urlFor('products.index').'">Back to Products</a>
        ');
        return $response;
    })->whereIn('slug', ['widget', 'gadget'])
        ->name('products.show');
});

# Requires container definition 'view' set to PhpRenderer and view template
Route::view('/view', 'default.php', []); 

$app->run();