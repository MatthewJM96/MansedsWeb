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

    namespace Sycamore\Row;
    
    use Sycamore\Request;
    use Sycamore\Row\Row;
    
    class Route extends Row
    {
        public $id;
        /**
         * Describes the string with filters. E.g.:
         * (1) "/admin/blogs/edit/([a-zA-Z0-9_\\-]+)"
         * (2) "/api/newsletter/subscriber"
         * 
         * @var string 
         */
        public $path;
        /**
         * The path to the controller to run via. E.g.:
         * (1) "\\Sycamore\\Controller\\API\\Newsletter\\SubscriberController"
         * 
         * @var string
         */
        public $controller;
        /**
         * Provides the keys for the set of components 
         * to be passed into the request as params. E.g.:
         * (1) "year,month,day"
         * (2) "blogName"
         * 
         * @var string
         */
        public $keys;
        /**
         * Set of tinyint columns defining which HTTP 
         * request types are valid.
         * 
         * @var bool
         * @var bool
         * @var bool
         * @var bool
         */
        public $get;
        public $post;
        public $delete;
        public $put;
        
        /**
         * Assesses if the request matches this route.
         * If so, passes parameters in URI to the request.
         * 
         * @param Request $request
         * 
         * @return boolean
         */
        public function match(Request& $request)
        {
            $keys = explode(",", $this->keys);
            // Compile pattern.
            $pattern = "/" . preg_replace("/\//", "\/", $this->path) . "/";
            $captures = array();
            if (preg_match($pattern, $request->getUri(), $captures)) {
                // Get rid of full capture - we know that will be the full URI.
                array_shift($captures);
                // Pass parameters in URI to request.
                foreach ($captures as $i => $capture) {
                    $request->setParam($keys[$i], $capture);
                }
                return true;
            }
            return false;
        }
    }