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

    /**
     * Sycamore response class.
     */
    class Response 
    {        
        /**
         * Headers holder.
         *
         * @var array - string array
         */
        protected $headers;
        
        /**
         * Prepares the version holder.
         *
         * @param string
         */
        public function __construct()
        {
        }
        
        /**
         * Passes the version for the instanced Response.
         *
         * @return string
         */
        public function getVersion()
        {
            return $this->version;
        }
        
        /**
         * Set the HTTP response code.
         * 
         * @param int $code
         * 
         * @return \Sycamore\Response
         */
        public function setResponseCode($code)
        {
            http_response_code($code);
            return $this;
        }
        
        /**
         * Adds a header to the headers array.
         *
         * @param string
         * 
         * @return \Sycamore\Response
         */
        public function addHeader($header)
        {
            $this->headers[] = $header;
            return $this;
        }
        
        /**
         * Adds multiple headers to the headers array.
         *
         * @param array - string array
         *
         * @return \Sycamore\Response
         */
        public function addHeaders(array $headers)
        {
            foreach ($headers as $header) {
                $this->addHeader($header);
            }
            return $this;
        }
        
        /**
         * Passes headers for the instanced Response.
         *
         * @return array
         */
        public function getHeaders()
        {
            return $this->headers;
        }
        
        /**
         * Sends the headers in this instance to the user.
         */
        public function send()
        {
            if (!headers_sent()) {
                foreach ($this->headers as $header) {
                    header("$header", true);
                }
            }
        }
    }