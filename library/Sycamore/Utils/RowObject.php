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
//    
//    namespace Sycamore\Utils;
//    
//    /**
//     * Provides a set of functions for searching through collections of 
//     * row objects.
//     */
//    class RowObject
//    {
//        /**
//         * Assess if the filters provided match an item in the given data array.
//         * 
//         * @param array $data
//         * @param array $filters
//         * 
//         * @return boolean
//         */
//        public static function inArray($data, $filters)
//        {
//            $found = false;
//            foreach ($data as $datum) {
//                $potentialFind = true;
//                foreach ($filters as $filterKey => $filterValue) {
//                    if ($datum->$filterKey != $filterValue) {
//                        $potentialFind = false;
//                    }
//                }
//                $found = $potentialFind;
//            }
//            return $found;
//        }
//        
//        /**
//         * Finds all data points in provided array that match the provided 
//         * filters, returning the set of matches.
//         * 
//         * @param array $data
//         * @param array $filters
//         * 
//         * @return array
//         */
//        public static function findInArray($data, $filters)
//        {
//            $matches = array();
//            foreach ($data as $key => $datum) {
//                $potentialFind = true;
//                foreach ($filters as $filterKey => $filterValue) {
//                    if ($datum->$filterKey != $filterValue) {
//                        $potentialFind = false;
//                    }
//                }
//                if ($potentialFind) {
//                    $matches[$key] = $datum;
//                }
//            }
//            return $matches;
//        }
//    }
