<?php

namespace WebChemistry\Images\Router;

use Nette,
    Nette\Application\Routers\Route;

use WebChemistry;

class Factory {
    
    const DELIMETER = ':';
    
    /**
     * @return Nette\Application\IRouter
     */
    public static function createRouter($config, WebChemistry\Images\Storage $imageStorage) {
        $router = new Nette\Application\Routers\RouteList;
        
        $router[] = new Route($config['mask'], array(
            'module' => 'ImageStorage',
            'presenter' => 'Generate',
            'action' => 'default',
            'name' => array(
                Route::FILTER_IN => function ($name) {
                    return Helper::decodeName($name);
                },
                Route::FILTER_OUT => function ($name) {
                    return Helper::encodeName($name);
                }
            ),
            'noimage' => array(
                Route::FILTER_IN => function ($noimage) {
                    return Helper::decodeName($noimage);
                },
                Route::FILTER_OUT => function ($noimage) {
                    return Helper::encodeName($noimage);
                }
            
            )
        ), $config['flag']);

        return $router;
    }

    public static function prepend(Nette\Application\IRouter &$router, Nette\Application\IRouter $route) {
        $router[] = $route;
        
        $last = count($router) - 1;
        foreach ($router as $i => $r) {
            if ($i === $last) {
                break;
            }
            $router[$i + 1] = $r;
        }

        $router[0] = $route;
    }
}
