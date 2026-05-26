<?php
namespace Statical\SlimStatic;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Statical\SlimStatic\Route\RouteDecorator;
use Statical\SlimStatic\Route\RouteGroupDecorator;
use Statical\BaseProxy;
use Statical\SlimStatic\App;

/**
 * For developers who prefer to only use the classic Slim syntax, 
 * the commented code is still valid when extending Slim\Sugar
 * 
 * @since 4.0.0 This now is a proxy for \Statical\SlimStatic\Route\Router::class
 * This has been converted to an extension of BaseProxy from SlimSugar
 * 
 * @todo create a configuration paramter to toggle: Classic vs. Router::class
 * 
 * @method static RouteDecorator get(string $pattern, callable|string|array $callable)
 * @method static RouteDecorator post(string $pattern, callable|string|array $callable)
 * @method static RouteDecorator put(string $pattern, callable|string|array $callable)
 * @method static RouteDecorator patch(string $pattern, callable|string|array $callable)
 * @method static RouteDecorator delete(string $pattern, callable|string|array $callable)
 * @method static RouteDecorator options(string $pattern, callable|string|array $callable)
 * @method static RouteDecorator any(string $pattern, callable|string|array $callable)
 * @method static RouteDecorator match(array $methods, string $pattern, callable|string|array $callable)
 * @method static RouteDecorator view(string $pattern, string $template, \IteratorAggregate|array $data = [], int $status = 200, array $headers = [], bool $raw = false)
 * @method static RouteDecorator redirect(string $from, string $to, int $status = 302)
 * @method static RouteGroupDecorator group(callable|string $patternOrCallable, callable $callable = null)
 * @method static static middleware(string ...$middleware)
 * @method static static controller(string $controller)
 * @method static static prefix(string $prefix)
 */
class Route extends BaseProxy
{
    // public static function map()
    // {
    // 	return call_user_func_array([static::$slim, 'map'], func_get_args());
    // }

    // // public static function get()
    // // {
    // // 	return call_user_func_array([static::$slim, 'get'], func_get_args());
    // // }

    // public static function post()
    // {
    // 	return call_user_func_array([static::$slim, 'post'], func_get_args());
    // }

    // public static function put()
    // {
    // 	return call_user_func_array([static::$slim, 'put'], func_get_args());
    // }

    // public static function patch()
    // {
    // 	return call_user_func_array([static::$slim, 'patch'], func_get_args());
    // }

    // public static function delete()
    // {
    // 	return call_user_func_array([static::$slim, 'delete'], func_get_args());
    // }

    // public static function options()
    // {
    // 	return call_user_func_array([static::$slim, 'options'], func_get_args());
    // }

    // public static function group()
    // {
    // 	return call_user_func_array([static::$slim, 'group'], func_get_args());
    // }

    // public static function any()
    // {
    // 	return call_user_func_array([static::$slim, 'any'], func_get_args());
    // }

    public static function urlFor(string $routeName, array $data = [], array $queryParams = [])
    {
        return App::getRouteCollector()->getRouteParser()->urlFor($routeName, $data, $queryParams);
    }

    // /**
    //  * A helper for routes that only return a view. Container definition 'view'
    //  * should resolve PhpRenderer::class, see composer package: slim/php-view.
    //  * 
    //  * @param string $pattern The route URI
    //  * @param string $template See PhpRenderer's $templatePath
    //  * @param array<string, mixed> $data Attributes passed to the view
    //  * @param int|array $status
    //  * @param array $headers
    //  * @return \Slim\Routing\Route
    //  * 
    //  * @see https://www.slimframework.com/docs/v4/features/php-view.html
    //  * @since 4.0.0
    //  * 
    //  * @todo Check the withHeader loop is working as intended
    //  */
    // public static function view(string $pattern, string $template, array $data, $status = 200, array $headers = []): \Slim\Routing\Route
    // {
    //     return call_user_func_array([static::$slim, 'get'], [
    //         $pattern,
    //         function(ResponseInterface $response) use ($template, $data, $status, $headers) {
    //             $response = $response->withStatus($status);

    //             if (!empty($headers)) {
    //                 foreach ($headers as $key => $value) {
    //                     $response = $response->withHeader($key, $value);
    //                 }
    //             }
    //             return View::render($response, $template, $data);
    //         }
    //     ]);
    // }

    // /**
    //  * Extra sugar to generate a response from a string, or use traditionally
    //  * by passing a callable as one would normally do using vanilla Slim.
    //  * 
    //  * @param string $pattern The route URI
    //  * @param callable|array{class-string, string}|string $callable The route callable
    //  * @return \Slim\Interfaces\RouteInterface
    //  * 
    //  * @since 4.0.0
    //  * 
    //  * @todo Other use-cases may not be covered here, check back
    //  */
    // public static function get(string $pattern, $callable)
    // {
    // 	return call_user_func_array([static::$slim, 'get'], [
    //         $pattern,
    //         function(ServerRequestInterface $request, ResponseInterface $response, array $args = []) use ($pattern, $callable) {
    //             # Set the args
    //             $args = $request->getAttribute(\Slim\Routing\RouteContext::ROUTE)->getArguments() ?? [];
                
    //             # Array such as: [HomeController::class, 'index']
    //             if (is_array($callable) && class_exists($callable[0])) {
    //                 $callable = App::getCallableResolver()->resolveRoute($callable);
    //                 return $response = $callable($request, $response, $args);
    //             } 

    //             # Auto Response with strings
    //             if (is_string($callable($request, $response, $args))) {
    //                 $response->getBody()->write($callable($response));
    //             }
    //             return $response;
    //         }
    //     ]);
    // }

}