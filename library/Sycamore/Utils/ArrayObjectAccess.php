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
         * Get a data item by key, returns false if no item exists at given key.
         *
         * @param string $key
         * 
         * @return bool|mixed
         * 
         * @access public
         */
        public function &__get ($key) 
        {
            if (!isset($this->data[$key])) {
                return false;
            }
            return $this->data[$key];
        }

        /**
         * Sets a value under the given key.
         * 
         * @param string $key
         * @param mixed $value
         * 
         * @access public
         */
        public function __set($key, $value) 
        {
            $this->data[$key] = $value;
        }

        /**
         * Determines whether an item exists under a given key.
         *
         * @param string $key
         * 
         * @return boolean
         * 
         * @access public
         * @abstracting ArrayAccess
         */
        public function __isset ($key) 
        {
            return isset($this->data[$key]);
        }

        /**
         * Unsets a data item at the given key
         *
         * @param string $key
         */
        public function __unset($key) 
        {
            unset($this->data[$key]);
        }

        /**
         * Assigns a value to the specified offset.
         *
         * @param string $offset
         * @param mixed $value
         * 
         * @access public
         * @abstracting ArrayAccess
         */
        public function offsetSet($offset, $value) 
        {
            if (is_null($offset)) {
                $this->data[] = $value;
            } else {
                $this->data[$offset] = $value;
            }
        }

        /**
         * Determines if an item exists at the given offset.
         *
         * @param string $offset
         * 
         * @return boolean
         * 
         * @access public
         * @abstracting ArrayAccess
         */
        public function offsetExists($offset)
        {
            return isset($this->data[$offset]);
        }

        /**
         * Unsets the item at the given offset.
         *
         * @param string $offset
         * 
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
         * Returns the value at specified offset.
         *
         * @param string $offset
         * 
         * @return mixed
         * 
         * @access public
         * @abstracting ArrayAccess
         */
        public function offsetGet($offset) 
        {
            return $this->offsetExists($offset) ? $this->data[$offset] : null;
        }
        
        /**
         * Merges given array into data.
         *
         * @param array $array
         * 
         * @access public
         */
        public function arrayMerge($array)
        {
            foreach($array as $key => $value) {
                $this->data[$key] = $value;
            }
        }
    }