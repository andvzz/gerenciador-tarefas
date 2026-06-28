<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'TarefaController::index');

$routes->group('tarefas', static function (RouteCollection $routes) {
    $routes->get('/', 'TarefaController::index');
    $routes->post('salvar', 'TarefaController::salvar');
    $routes->post('atualizar-status', 'TarefaController::atualizarStatus');
    $routes->post('atualizar/(:num)', 'TarefaController::atualizar/$1');
    $routes->get('excluir/(:num)', 'TarefaController::excluir/$1');
});

$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function (RouteCollection $routes) {
    $routes->resource('tarefas', ['controller' => 'TarefaApiController']);
});
