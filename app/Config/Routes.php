<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'TaskController::index');

$routes->group('tasks', static function (RouteCollection $routes) {
    $routes->get('/', 'TaskController::index');
    $routes->post('store', 'TaskController::store');
    $routes->post('update-status', 'TaskController::updateStatus');
    $routes->post('update/(:num)', 'TaskController::update/$1');
    $routes->get('delete/(:num)', 'TaskController::delete/$1');
});

$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function (RouteCollection $routes) {
    $routes->resource('tasks', ['controller' => 'TaskApiController']);
});
