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

    namespace Manseds\Utils;

    /**
     * ArrayValidation holds functions for checking the existence of keys and values in arrays.
     */
    class ArrayValidation
    {
        /**
         * Recursively checks if a value exists in an array.
         *
         * @var string - The value to check for.
         * @var array - The array to search through.
         * @var boolean - Should value type match?
         *
         * @return boolean
         */
        public static function inArrayRecursive($needle, $haystack, $strict = false) 
        {
            foreach ($haystack as $item) {
                if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && static::inArrayRecursive($needle, $item, $strict))) {
                    return true;
                }
            }
            return false;
        }
        
        /**
         * Recursively checks if a key exists in an array.
         *
         * @var string - The key to check for.
         * @var array - The array to search through.
         *
         * @return boolean
         */
        public static function arrayKeyExistsRecursive($key, $array)
        {
            foreach ($array as $k => $v) {
                if (($k == $key) || (is_array($v) && static::arrayKeyExistsRecursive($key, $v))) {
                    return true;
                }
            }
            return false;
        }
    }