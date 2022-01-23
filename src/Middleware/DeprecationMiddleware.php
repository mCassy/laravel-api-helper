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
use Mpokket\Annotations\Deprecation;
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
        try {
            $response = $next($request);
            $routeObj = RouteFacade::getCurrentRoute();
            $controllerObj = $routeObj->controller;
            $controllerClass = get_class($controllerObj);
            $rf = new ReflectionClass($controllerClass);
            $methodObj = $rf->getMethod($routeObj->getActionMethod());
            $reader = new AnnotationReader();
            $date = $reader->getMethodAnnotation(
                $methodObj,
                Deprecation::class
            );
            $links = [];

            if ($date->since) {
                switch ($date) {
                    case 'true':
                    case 1:
                    case true:
                        $response->header('Deprecation', 'true');
                        return $response;
                        break;
                    default:
                        $carbon = new Carbon($date->value);
                        $response->header('Deprecation', $carbon->format(DateTime::RFC7231));
                        return $response;
                }
            }

            if ($date->alternate) {
                $parsedAlternate = parse_url($date->alternate);
                if ($parsedAlternate) {
                    $links[] = $date->alternate . '; rel=alternate';
                }
            }

            if ($date->policy) {
                $response = $next($request);
                $parsedAlternate = parse_url($date->alternate);
                if ($parsedAlternate) {
                    $links[] = $date->policy . '; rel=deprecation';
                }
            }

            if ($links) {
                $response->header('Link', implode(',', $links));
            }

            if ($date->sunset) {
                $carbon = new Carbon($date->sunset);
                $response->header('Sunset', $carbon->format(DateTime::RFC7231));
                return $response;
            }
        } catch (ReflectionException|Exception $exception) {
            // do nothing
        }

        return $next($request);
    }
}
