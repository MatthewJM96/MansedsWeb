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
    
    class Type
    {
        public static function cast($value, $type)
        {
            switch($type) {
                case "int":
                case "integer":
                    return (int) $value;
                case "bool":
                case "boolean":
                    return (bool) $value;
                case "float":
                case "double":
                case "real":
                    return (real) $value;
                case "string":
                    return (string) $value;
                case "array":
                    return (array) $value;
                case "object":
                    return (object) $value;
                case "unset":
                    return (unset) $value;
                case "binary":
                    return (binary) $value;
            }
        }
    }