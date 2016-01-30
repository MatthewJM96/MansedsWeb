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

    namespace Sycamore\Renderer;
    
    use Sycamore\Application;
    use Sycamore\Response;

    abstract class Renderer
    {
        /**
         * Stores the response object. This should only
         * be used for adding HTTP headers.
         *
         * @var \Sycamore\Response
         */
        protected $response;
        
        /**
         * Constructs the renderer with appropriate X-Frame-Options header.s
         * 
         * @param \Sycamore\Response $response
         */
        public function __construct(Response& $response)
        {
            $this->response = $response;
            
            if (!Application::getConfig() || Application::getConfig()->security->enableClickjackingProtection) {
                $this->response->addHeader("X-Frame-Options: SAMEORIGIN");
            }
        }
        
        /**
         * Renderers implement this function, sending the body of to-be-rendered content to client.
         * 
         * @param string $content
         */
        abstract public function render($content);
    }