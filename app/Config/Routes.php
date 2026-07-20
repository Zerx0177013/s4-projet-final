<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/operator', 'CompteController::afficherComptes');
$routes->get('rapport/gain/(:num)', 'DashboardController::afficherGainParOperateur/$1');