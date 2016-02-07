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
    
    abstract class Row
    {
        public function exchangeArray($data)
        {
            foreach (get_object_vars($this) as $key => $_) {
                $this->$key = (!empty($data[$key])) ? $data[$key] : null;
            }
        }
        
        public function toArray()
        {
            return get_object_vars($this);
        }
        
        /*
         * Legend:
         * 
         * //, ///, ////, ...  -  Describe protection requirements on changing values for a given instance.
         * ///*  - Describes what a variable does. 
         */
    }