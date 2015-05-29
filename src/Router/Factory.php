<?php

namespace WebChemistry\Images\Router;

use Nette, Nette\Application\Routers\Route;

use WebChemistry;

class Factory {

	const DELIMETER = ':';

	/**
	 * @param array $config
	 * @return array|Nette\Application\Routers\RouteList
	 */
	public static function createRouter(array $config) {
		$router = new Nette\Application\Routers\RouteList;

		$router[] = new Route($config['mask'], array(
			'module'     => 'ImageStorage', 'presenter' => 'Generate', 'action' => 'default', 'size' => array(
				Route::FILTER_IN     => function ($size) {
					return str_replace('%25', '%', $size);
				}, Route::FILTER_OUT => function ($size) {
					return str_replace('%', '%25', $size);
				}
			), 'name'    => array(
				Route::FILTER_IN     => function ($name) {
					return Helper::decodeName($name);
				}, Route::FILTER_OUT => function ($name) {
					return Helper::encodeName($name);
				}
			), 'noimage' => array(
				Route::FILTER_IN     => function ($noimage) {
					return Helper::decodeName($noimage);
				}, Route::FILTER_OUT => function ($noimage) {
					return Helper::encodeName($noimage);
				}

			)
		), $config['flag']);

		return $router;
	}

	/**
	 * @param Nette\Application\IRouter $router
	 * @param Nette\Application\IRouter $route
	 */
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
