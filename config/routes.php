<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * This file is loaded in the context of the `Application` class.
 * So you can use `$this` to reference the application class instance
 * if required.
 */
return function (RouteBuilder $routes): void {
    /*
     * The default class to use for all routes
     *
     * The following route classes are supplied with CakePHP and are appropriate
     * to set as the default:
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * If no call is made to `Router::defaultRouteClass()`, the class used is
     * `Route` (`Cake\Routing\Route\Route`)
     *
     * Note that `Route` does not do any inflections on URLs which will result in
     * inconsistently cased URLs when used with `{plugin}`, `{controller}` and
     * `{action}` markers.
     */
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        /*
         * Frontend routes - accessible without authentication
         */
        $builder->connect('/', ['controller' => 'Pages', 'action' => 'home']);
        $builder->connect('/home', ['controller' => 'Pages', 'action' => 'home']);
        $builder->connect('/about', ['controller' => 'Pages', 'action' => 'display', 'about']);
        $builder->connect('/services', ['controller' => 'Pages', 'action' => 'display', 'services']);
        $builder->connect('/contact', ['controller' => 'Pages', 'action' => 'display', 'contact']);
        
        /*
         * About and contact pages
         */
        $builder->connect('/about', ['controller' => 'Pages', 'action' => 'about']);
        $builder->connect('/contact', ['controller' => 'Pages', 'action' => 'contact']);
        $builder->connect('/services', ['controller' => 'Pages', 'action' => 'services']);

        /*
         * Debug routes - for troubleshooting hospital redirection
         */
        $builder->connect('/debug/test', ['controller' => 'Debug', 'action' => 'test']);
        $builder->connect('/debug/raw', ['controller' => 'Debug', 'action' => 'raw']);
        $builder->connect('/debug/force-redirect', ['controller' => 'Debug', 'action' => 'forceRedirect']);
        $builder->connect('/test/redirect', ['controller' => 'Test', 'action' => 'check']);
        
        /*
         * Session test routes - for testing session middleware
         */
        $builder->connect('/test-session', ['controller' => 'TestSession', 'action' => 'index']);
        $builder->connect('/test-session/check', ['controller' => 'TestSession', 'action' => 'check']);

        /*
         * Authentication routes - for Okta OAuth2 callback
         */
            /**
     * Authentication routes - for Okta OAuth2 callback
     */
    $builder->connect('/auth/callback', ['controller' => 'Auth', 'action' => 'callback']);
    $builder->connect('/auth/logout-callback', ['controller' => 'Auth', 'action' => 'logoutCallback']);
    
    /**
     * Debug route - for configuration testing
     */
    $builder->connect('/debug-config', ['controller' => 'DebugConfig', 'action' => 'index']);

        /*
         * Connect catchall routes for all controllers.
         *
         * The `fallbacks` method is a shortcut for
         *
         * ```
         * $builder->connect('/{controller}', ['action' => 'index']);
         * $builder->connect('/{controller}/{action}/*', []);
         * ```
         *
         * It is NOT recommended to use fallback routes after your initial prototyping phase!
         * See https://book.cakephp.org/5/en/development/routing.html#fallbacks-method for more information
         */
        $builder->fallbacks();
    });

    /*
     * Doctor routing scope
     * All doctor routes will be prefixed with /doctor
     * For medical professionals managing patients and medical records
     */
    $routes->prefix('Doctor', function (RouteBuilder $routes): void {
        // Doctor dashboard route
        $routes->connect('/', ['controller' => 'Dashboard', 'action' => 'index']);
        
        // Doctor login and logout routes
        $routes->connect('/login', ['controller' => 'Login', 'action' => 'login']);
        $routes->connect('/logout', ['controller' => 'Login', 'action' => 'logout']);
        
        // Connect fallback routes for doctor controllers
        $routes->fallbacks();
    });

    /*
     * Scientist routing scope
     * All scientist routes will be prefixed with /scientist
     * For researchers managing studies and data analysis
     */
    $routes->prefix('Scientist', function (RouteBuilder $routes): void {
        // Scientist dashboard route
        $routes->connect('/', ['controller' => 'Dashboard', 'action' => 'index']);
        
        // Scientist login and logout routes
        $routes->connect('/login', ['controller' => 'Login', 'action' => 'login']);
        $routes->connect('/logout', ['controller' => 'Login', 'action' => 'logout']);
        
        // Connect fallback routes for scientist controllers
        $routes->fallbacks();
    });

    /*
     * Technician routing scope
     * All technician routes will be prefixed with /technician
     * For technical staff managing equipment and systems
     */
    $routes->prefix('Technician', function (RouteBuilder $routes): void {
        // Technician dashboard route
        $routes->connect('/', ['controller' => 'Dashboard', 'action' => 'index']);
        
        // Technician login and logout routes
        $routes->connect('/login', ['controller' => 'Login', 'action' => 'login']);
        $routes->connect('/logout', ['controller' => 'Login', 'action' => 'logout']);
        
        // AJAX endpoints
        $routes->connect('/patients/check-username', ['controller' => 'Patients', 'action' => 'checkUsername']);
        
        // Connect fallback routes for technician controllers
        $routes->fallbacks();
    });

    /*
     * Hospital Admin routing scope
     * All hospital admin routes will be prefixed with /admin
     * These are for hospital administrators managing their specific hospital
     */
    $routes->prefix('Admin', function (RouteBuilder $routes): void {
        // Hospital Admin dashboard route
        $routes->connect('/', ['controller' => 'Dashboard', 'action' => 'index']);
        
        // Hospital Admin login and logout routes
        $routes->connect('/login', ['controller' => 'Login', 'action' => 'login']);
        $routes->connect('/logout', ['controller' => 'Login', 'action' => 'logout']);
        
        // Connect fallback routes for hospital admin controllers
        $routes->fallbacks();
    });

    /*
     * System Admin routing scope
     * All system admin routes will be prefixed with /system
     * These are for super administrators managing the entire platform
     */
    $routes->prefix('System', function (RouteBuilder $routes): void {
        // System Admin dashboard route
        $routes->connect('/', ['controller' => 'Dashboard', 'action' => 'index']);
        
        // System Admin login and logout routes
        $routes->connect('/login', ['controller' => 'Login', 'action' => 'login']);
        $routes->connect('/logout', ['controller' => 'Login', 'action' => 'logout']);
        
        // Connect fallback routes for system admin controllers
        $routes->fallbacks();
    });

    /*
     * If you need a different set of middleware or none at all,
     * open new scope and define routes there.
     *
     * ```
     * $routes->scope('/api', function (RouteBuilder $builder): void {
     *     // No $builder->applyMiddleware() here.
     *
     *     // Parse specified extensions from URLs
     *     // $builder->setExtensions(['json', 'xml']);
     *
     *     // Connect API actions here.
     * });
     */
};