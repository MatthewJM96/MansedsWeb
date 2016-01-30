<?php

/* 
 * Copyright (C) 2016 Matthew Marshall
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
    
    namespace Manseds;
    
    use Manseds\Dispatcher;
    use Manseds\Request;
    use Manseds\Response;
    use Manseds\Router;
    use Manseds\Utils\TableCache;

    /**
     * Manseds front controller class.
     */
    class FrontController
    {
        /**
         * Router object.
         *
         * @var Manseds\Router
         */
        protected $router;
        
        /**
         * Dispatcher object.
         *
         * @var Manseds\Dispatcher
         */
        protected $dispatcher;
    
        /**
         * Prepares the router and dispatcher managers.
         *
         * @param Manseds\Router
         * @param Manseds\Dispatcher
         */
        public function __construct()
        {
            // Get routes from database.
            $routesTable = TableCache::getTableFromCache("RoutesTable");
            $routes = $routesTable->fetchAll();
            
            // Prepare router and dispatcher.
            $this->router = new Router($routes);
            $this->dispatcher = new Dispatcher;
        }
        
        /**
         * Obtains the matched route from the router and 
         * dispatches via the dispatcher.
         *
         * @param Manseds\Request
         * @param Manseds\Response
         */
        public function run(Request& $request)
        {
            // Prepare the response.
            $response = new Response;
            
            // Try to route request, 404 if fail.
            $route = $this->router->route($request);
            if (!$route) {
                // TODO(Matthew): Handle 404 better.
                $response->setResponseCode(404)->send();
                return;
            }
            
            // Dispatch request to appropriate controller.
            if (!$this->dispatcher->dispatch($route, $request, $response)) {
                // TODO(Matthew): Handle 500 better.
                $response->setResponseCode(500)->send();
                return;
            }
        }
    }