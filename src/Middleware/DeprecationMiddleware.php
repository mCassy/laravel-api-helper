<?php

namespace Mpokket\APIHelper\Middleware;

use Carbon\Carbon;
use Doctrine\Common\Annotations\AnnotationReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Closure;
use Exception;
use Mpokket\APIHelper\Annotations\Deprecation;
use ReflectionClass;
use ReflectionException;
use DateTime;

/**
 *    Sunset: Sat, 31 Dec 2018 23:59:59 GMT
 */
class DeprecationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (RedirectResponse)  $next
     * @return Response| RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        try {
            $routeObj = RouteFacade::getCurrentRoute();
            $controllerObj = $routeObj->controller;
            $controllerClass = get_class($controllerObj);
            $rf = new ReflectionClass($controllerClass);
            $methodObj = $rf->getMethod($routeObj->getActionMethod());
            $reader = new AnnotationReader();
            $deprecationHeaderValue = false;
            $deprecationAnnotation = $reader->getMethodAnnotation(
                $methodObj,
                Deprecation::class
            );
            $links = [];

            if (!$deprecationAnnotation) {
                throw new Exception('Deprecation annotation not found');
            }
            if (property_exists($deprecationAnnotation, 'since') && $deprecationAnnotation->since) {
                try {
                    $carbon = new Carbon($deprecationAnnotation->since);
                    $response->header('Deprecation', $carbon->format(DateTime::RFC7231));
                } catch (Exception $exception) {
                    $response->header('Deprecation', 'true');
                }
            }

            if (property_exists($deprecationAnnotation, 'alternate') && $deprecationAnnotation->alternate) {
                $parsedAlternate = parse_url($deprecationAnnotation->alternate);
                if ($parsedAlternate) {
                    $links[] = $deprecationAnnotation->alternate . '; rel=alternate';
                }
            }

            if (property_exists($deprecationAnnotation, 'policy') && $deprecationAnnotation->policy) {
                $parsedAlternate = parse_url($deprecationAnnotation->policy);
                if ($parsedAlternate) {
                    $links[] = $deprecationAnnotation->policy . '; rel=deprecation';
                }
            }

            if ($links) {
                $response->header('Link', implode(', ', $links));
            }

            if (property_exists($deprecationAnnotation, 'sunset') && $deprecationAnnotation->sunset) {
                $carbon = new Carbon($deprecationAnnotation->sunset);
                $response->header('Sunset', $carbon->format(DateTime::RFC7231));
            }
            
        } catch (ReflectionException|Exception $exception) {
            if (app('env') !== 'production') {
                $response->header(
                    'X-Deprecation-Middleware-Exception',
                    $exception->getLine() . '#' . $exception->getLine()
                );
            }
        }
        return $response;
    }
}
