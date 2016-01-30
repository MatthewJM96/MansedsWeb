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
    
    use Manseds\Model\Route;
    use Manseds\Request;
    use Manseds\Response;

    /**
     * Manseds dispatcher class.
     */
    class Dispatcher
    {
        /**
         * Creates a controller for the route, and then calls 
         * execute from within the controller passing in the request
         * and response.
         * 
         * @param Manseds\Route
         */
        public function dispatch(Route $route, Request& $request, Response& $response)
        {
            $controller = $this->createController($request, $response, $route);
            
            switch(filter_input(INPUT_SERVER, "REQUEST_METHOD")) {
                case "GET":
                    if ($route->get) {
                        $controller->getAction();
                        return true;
                    }
                    break;
                case "POST":
                    if ($route->post) {
                        $controller->postAction();
                        return true;
                    }
                    break;
                case "DELETE":
                    if ($route->delete) {
                        $controller->deleteAction();
                        return true;
                    }
                    break;
                case "PUT":
                    if ($route->put) {
                        $controller->putAction();
                        return true;
                    }
                    break;
            }
            
            return false;
        }
  
        /**
         * Creates a new instance of the Manseds\Controller 
         * class stored in controllerClass.
         * 
         * @return Manseds\Controller
         */
        protected function createController(Request& $request, Response& $response, Route $route) {
            if ($request->getUriAsArray()[0] == "api") {
                $renderer = new \Manseds\Renderer\JsonRenderer($response);
            } else {
                $renderer = new \Manseds\Renderer\HtmlRenderer($response);
            }
            
            $controllerStr = $route->controller;
            return new $controllerStr($request, $response, $renderer);
        }
    }