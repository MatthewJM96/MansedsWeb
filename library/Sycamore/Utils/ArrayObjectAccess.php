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
     * ArrayObjectAccess extends ArrayAccess to allow object interactions also.
     */
    abstract class ArrayObjectAccess implements \ArrayAccess
    {
        /**
         * Holds data array of ArrayObjectAccess instance.
         *
         * @var array
         */
        protected $data;
        
        /**
         * Get a data by key
         *
         * @param string - The key data to retrieve
         * @access public
         */
        public function &__get ($key) 
        {
            return $this->data[$key];
        }

        /**
         * Assigns a value to the specified data
         * 
         * @param string - The data key to assign the value to
         * @param mixed - The value to set
         * @access public 
         */
        public function __set($key,$value) 
        {
            $this->data[$key] = $value;
        }

        /**
         * Whether or not an data exists by key
         *
         * @param string - An data key to check for
         * @access public
         * @return boolean
         * @abstracting ArrayAccess
         */
        public function __isset ($key) 
        {
            return isset($this->data[$key]);
        }

        /**
         * Unsets an data by key
         *
         * @param string - The key to unset
         * @access public
         */
        public function __unset($key) 
        {
            unset($this->data[$key]);
        }

        /**
         * Assigns a value to the specified offset
         *
         * @param string - The offset to assign the value to
         * @param mixed - The value to set
         * @access public
         * @abstracting ArrayAccess
         */
        public function offsetSet($offset,$value) 
        {
            if (is_null($offset)) {
                $this->data[] = $value;
            } else {
                $this->data[$offset] = $value;
            }
        }

        /**
         * Whether or not an offset exists
         *
         * @param string - An offset to check for
         * @access public
         * @return boolean
         * @abstracting ArrayAccess
         */
        public function offsetExists($offset)
        {
            return isset($this->data[$offset]);
        }

        /**
         * Unsets an offset
         *
         * @param string - The offset to unset
         * @access public
         * @abstracting ArrayAccess
         */
        public function offsetUnset($offset) 
        {
            if ($this->offsetExists($offset)) {
                unset($this->data[$offset]);
            }
        }
        
        /**
         * Returns the value at specified offset
         *
         * @param string The offset to retrieve
         * @access public
         * @return mixed
         * @abstracting ArrayAccess
         */
        public function offsetGet($offset) 
        {
            return $this->offsetExists($offset) ? $this->data[$offset] : null;
        }
        
        /**
         * Merges given array into data.
         *
         * @param array - The array to merge.
         * @access public
         */
        public function arrayMerge($array)
        {
            foreach($array as $key => $value) {
                $this->data[$key] = $value;
            }
        }
    }