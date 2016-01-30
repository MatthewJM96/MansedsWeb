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
    
    use Sycamore\User\UserSession;
    use Sycamore\Utils\ArrayObjectAccess;

    /**
     * Visitor class, containing information regarding the current visitor.
     */
    class Visitor extends ArrayObjectAccess
    {
        /**
         * Singleton instance of Visitor class.
         */
        protected static $instance;
        
        public static function getInstance()
        {
            if (!self::$instance) {
                $this->setup();
            }
            return self::$instance;
        }
        
        /**
         * Protected constructor. Use {@link getInstance()} instead.
         */
        protected function __construct()
        {
        }
        
        protected function setup()
        {
            self::$instance = new self();
            $this->prepareVisitorInfo();
        }
        
        protected function prepareVisitorInfo()
        {
            $visitorData = UserSession::acquire();
            if ($visitorData <= 0) {
                self::$instance["isLoggedIn"] = false;
                return;
            }
            $visitorData["isLoggedIn"] = true;
            self::$instance->arrayMerge($visitorData);
        }
    }