<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('auth', function($routes) {
    $routes->post('login', 'Auth2FA::login');
    $routes->post('setup', 'Auth2FA::setup');
    $routes->post('verify', 'Auth2FA::verify');
});
