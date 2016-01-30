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

    namespace Sycamore\Utils;

    /**
     * TableCache handles caching of tables for future use.
     */
    class TableCache
    {
        /**
         * Cache of loaded tables in this application instance.
         *
         * @var array
         */
        protected static $tableCache = array();
        
        /**
	 * Gets the specified table object from the cache. If it does not exist,
         * it will be instantiated.
         *
         * @param string
         *
         * @return \Sycamore\Table\Table
         */
        public static function getTableFromCache($class)
        {
            if (!isset(self::$tableCache[$class]))
            {
                $classPath = "Sycamore\\Model\\$class";
                self::$tableCache[$class] = new $classPath();
            }

            return self::$tableCache[$class];
        }
    }
