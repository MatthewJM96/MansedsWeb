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

    namespace Sycamore;
    
    use Sycamore\Model\Route;
    use Sycamore\Request;

    /**
     * Sycamore router class.
     */
    class Router
    {
        /**
         * Routes holder.
         *
         * @var array
         */
        protected $routes;
        
        /**
         * Prepares the routes.
         *
         * @param array
         */
        public function __construct($routes) {
            $this->addRoutes($routes);
        }
  
        /**
         * Adds a route to the routes stored.
         *
         * @param \Sycamore\Route $route
         * 
         * @return \Sycamore\Router
         */
        public function addRoute(Route $route) {
            $this->routes[] = $route;
            return $this;
        }
  
        /**
         * Adds multipe routes to the routes stored.
         *
         * @param array $routes
         * 
         * @return \Sycamore\Router
         */
        public function addRoutes(array $routes) {
            foreach ($routes as $route) {
                try {
                    $this->addRoute($route);
                } catch (Exception $ex) {
                    throw $ex;
                }
            }
            return $this;
        }
  
        /**
         * Passes routes stored.
         *
         * @return array
         */
        public function getRoutes() {
            return $this->routes;
        }
  
        /**
         * Checks all routes for if they match with the request passed in.
         * Returns matched route or false if no matches.
         *
         * @param \Sycamore\Request
         * @param \Sycamore\Response
         * 
         * @return \Sycamore\Route|bool
         */
        public function route(Request& $request) {
            foreach ($this->routes as $route) {
                if ($route->match($request)) {
                    return $route;
                }
            }
            return false;
        }
    }