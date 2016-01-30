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

    // Polyfill in-case PHP7 not supported.
    // TODO(Matthew): Remove this, and random_compat, at soonest convenience.
    if (version_compare(PHP_VERSION, '7.0.0', '<')) {
        require(MANSEDS_DIRECTORY . "/Utils/random_compat/random_int.php");
    }
    
    /**
     * Random provides extra utility functions for generating random data.
     */
    class Random
    {
        /**
         * Credit: http://stackoverflow.com/questions/4356289/php-random-string-generator/31107425#31107425
         * 
         * Generate a random string, using a cryptographically secure 
         * pseudorandom number generator (random_int)
         * 
         * For PHP 7, random_int is a PHP core function
         * For PHP 5.x, depends on https://github.com/paragonie/random_compat
         * 
         * 
         * @param int $length      How many characters do we want?
         * @param string $keyspace A string of all possible characters
         *                         to select from
         * @return string
         */
        public static function randomString($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
        {
            $str = '';
            $max = mb_strlen($keyspace, '8bit') - 1;
            for ($i = 0; $i < $length; ++$i) {
                $str .= $keyspace[random_int(0, $max)];
            }
            return $str;
        }
    }