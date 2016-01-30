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

    /**
     * Manseds request class.
     */
    class Request 
    {
        /**
         * URI holder.
         *
         * @var string
         */
        protected $uri = "";
        
        /**
         * Parameters holder.
         *
         * @var array - string array.
         */
        protected $params;
    
        /**
         * Prepares the URI and params.
         *
         * @param string
         * @param array - string array
         */
        public function __construct($uri, $params = array()) 
        { 
            $this->uri = $uri;
            $this->params = $params;
        }

        /**
         * Passes the URI for the instanced Manseds\Request
         *
         * @return string
         */
        public function getUri() 
        {
            return $this->uri;
        }
        
        /**
         * Passes the URI path components for the instanced Manseds\Request.
         * 
         * @return array - string array
         */
        public function getUriAsArray()
        {
            $path = explode("/", $this->uri);
            array_shift($path);
            $uriAsArray = array_merge($path, explode("?", array_pop($path)));
            array_pop($uriAsArray);
            if (count($uriAsArray) == 1 && $uriAsArray[0] == "") {
                return array();
            }
            return $uriAsArray;
        }
  
        /**
         * Passes the URI parameter components for the instanced Manseds\Request.
         *
         * @return array - string key-val array
         */
        public function getUriParamsAsArray()
        {
            $paramsString = end(explode("?", $this->uri));
            $paramsArray = explode("&", $paramsString);
            $params = array();
            foreach($paramsArray as $paramString) {
                $keyVal = explode("=", $paramString);
                $params[$keyVal[0]] = $keyVal[1];
            }
            return $params;
        }
    
        /**
         * Sets a parameter to the given value.
         *
         * @param string
         * @param string
         *
         * @return Manseds\Request
         */
        public function setParam($key, $value) 
        {
            $this->params[$key] = $value;
            return $this;
        }
  
        /**
         * Returns the value for the given parameter
         *
         * @param string
         *
         * @return string
         */
        public function getParam($key) 
        {
            if (!isset($this->params[$key])) {
                throw new \InvalidArgumentException("The request parameter with key '$key' is invalid."); 
            }
            return $this->params[$key];
        }
  
        /**
         * Returns the array of all parameters.
         * 
         * @return array - string array
         */
        public function getParams() 
        {
            return $this->params;
        } 
    }