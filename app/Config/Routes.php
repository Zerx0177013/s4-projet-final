<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Espace opérateur : choix de l'opérateur puis tableau de bord (idOperateur en session)
$routes->get('/operator', 'OperateurController::choisir');
$routes->get('/operator/select/(:num)', 'OperateurController::selectionner/$1');
$routes->get('/operator/logout', 'OperateurController::deconnecter');
$routes->get('/operator/dashboard', 'CompteController::afficherComptes');
$routes->get('/operator/gains', 'DashboardController::afficherGainParOperateur');

$routes->get('/client/login', 'ClientController::login');
$routes->post('/client/login', 'ClientController::authenticate');
$routes->get('/client/logout', 'ClientController::logout');
$routes->get('/client/dashboard', 'ClientController::dashboard');
$routes->post('/client/operation', 'ClientController::operate');
