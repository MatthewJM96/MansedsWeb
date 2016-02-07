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
    
    use Sycamore\Application;
    use Sycamore\Dispatcher;
    use Sycamore\Request;
    use Sycamore\Response;
    use Sycamore\Router;
    use Sycamore\Visitor;
    use Sycamore\Enums\ActionState;
    use Sycamore\Utils\TableCache;
    
    use Zend\EventManager\EventManager;
    use Zend\EventManager\EventManagerAwareInterface;
    use Zend\EventManager\EventManagerInterface;

    /**
     * Sycamore front controller class.
     */
    class FrontController implements EventManagerAwareInterface
    {
        /**
         * Router object.
         *
         * @var \Sycamore\Router
         */
        protected $router;
        
        /**
         * Dispatcher object.
         *
         * @var \Sycamore\Dispatcher
         */
        protected $dispatcher;
        
        /**
         * The event manager.
         * 
         * @var \Zend\EventManager\EventManagerInterface
         */
        protected $eventManager;
    
        /**
         * Prepares the router and dispatcher managers.
         *
         * @param \Sycamore\Router
         * @param \Sycamore\Dispatcher
         */
        public function __construct()
        {
            // Prepare event manager.
            $this->setEventManager(new EventManager());
            $this->eventManager->setSharedManager(Application::getSharedEventsManager());
            
            // Get routes from database.
            $routesTable = TableCache::getTableFromCache("Route");
            $routes = $routesTable->fetchAll();
            
            // Prepare router and dispatcher.
            $this->router = new Router($routes);
            $this->dispatcher = new Dispatcher;
        }
        
        /**
         * Obtains the matched route from the router and 
         * dispatches via the dispatcher.
         *
         * @param \Sycamore\Request
         * @param \Sycamore\Response
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
                exit();
            }
            
            if (!$this->eventManager->trigger("postRouting", $this, array ( "route" => $route ))) {
                if (Visitor::getInstance()->isLoggedIn) {
                    // TODO(Matthew): Handle this better. I.e. provide screen explaining lack of permission to access page.
                    $response->setResponseCode(400)->send();
                    exit();
                } else {
                    // TODO(Matthew): Redirect to log in page or another landing page with message asking user to log in to access that page.
                    //                redirecting back to desired page on log in.
                    //                Consider how to deal with if the request is an API call.
                }
            }
            
            // Dispatch request to appropriate controller.
            $result = $this->dispatcher->dispatch($route, $request, $response);
            if ($result == ActionState::FAILED) {
                // TODO(Matthew): Handle 500 better.
                $response->setResponseCode(500)->send();
                exit();
            } else if ($result == ActionState::DENIED_NOT_LOGGED_IN) {
                // TODO(Matthew): Redirect to log in page or another landing page with message asking user to log in to access that page.
                //                redirecting back to desired page on log in.
                //                Consider how to deal with if the request is an API call.
            }
        }
        
        /**
         * Sets the event manager for the front controller.
         * 
         * @param \Zend\EventManager\EventManagerInterface $eventManager
         * 
         * @return \Sycamore\FrontController
         */
        public function setEventManager(EventManagerInterface $eventManager)
        {
            $eventManager->setIdentifiers(array (
                "route",
                __CLASS__,
                get_called_class(),
            ));
            $this->eventManager = $eventManager;
            return $this;
        }
        
        /**
         * Gets the event manager instance for this controller.
         * 
         * @return \Zend\EventManager\EventManagerInterface
         */
        public function getEventManager()
        {
            if (!$this->eventManager) {
                $this->setEventManager(new EventManager());
            }
            return $this->eventManager;
        }
    }