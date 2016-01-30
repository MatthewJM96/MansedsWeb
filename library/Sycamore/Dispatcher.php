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
    use Sycamore\Response;

    /**
     * Sycamore dispatcher class.
     */
    class Dispatcher
    {
        /**
         * Creates a controller for the route, and then calls 
         * execute from within the controller passing in the request
         * and response.
         * 
         * @param \Sycamore\Route $route
         * @param \Sycamore\Request $request
         * @param \Sycamore\Response $response
         */
        public function dispatch(Route $route, Request& $request, Response& $response)
        {
            $controller = $this->createController($request, $response, $route);
            
            switch(filter_input(INPUT_SERVER, "REQUEST_METHOD")) {
                case "GET":
                    if (method_exists($controller, "getAction")) {
                        $controller->getAction();
                        return true;
                    }
                    break;
                case "POST":
                    if (method_exists($controller, "postAction")) {
                        $controller->postAction();
                        return true;
                    }
                    break;
                case "DELETE":
                    if (method_exists($controller, "deleteAction")) {
                        $controller->deleteAction();
                        return true;
                    }
                    break;
                case "PUT":
                    if (method_exists($controller, "putAction")) {
                        $controller->putAction();
                        return true;
                    }
                    break;
            }
            
            return false;
        }
  
        /**
         * Creates a new instance of the \Sycamore\Controller 
         * class stored in controllerClass.
         * 
         * @param \Sycamore\Request $request
         * @param \Sycamore\Resonse $response
         * @param \Sycamore\Route $route
         * 
         * @return \Sycamore\Controller
         */
        protected function createController(Request& $request, Response& $response, Route $route) {
            if ($request->getUriAsArray()[0] == "api") {
                $renderer = new \Sycamore\Renderer\JsonRenderer($response);
            } else {
                $renderer = new \Sycamore\Renderer\HtmlRenderer($response);
            }
            
            $controllerStr = $route->controller;
            return new $controllerStr($request, $response, $renderer);
        }
    }