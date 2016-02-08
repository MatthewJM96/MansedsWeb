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
    
    use Sycamore\Application;
    
    use Zend\Cache\StorageFactory;

    /**
     * DataCache offers utility functions for handling the Zend cache.
     */
    class DataCache
    {
        /**
         * Initialised status.
         *
         * @var boolean
         */
        protected $initialised = false;
        
        /**
         * Holds the cache object.
         *
         * @var Zend\Cache\Storage\StorageInterface
         */
        protected $cache = null;
        
        /**
         * Holds the cache name.
         *
         * @var string
         */
        protected $cacheName;
         
        public function initialise($location, $where = null, $extra = "")
        {
            $this->cacheName = $this->generateCacheName($location . $extra, $where);
            $this->cache = $this->getCache();
            $this->initialised = true;
        }
        
        /**
         * Gets the cache item associated with the cache name of this instance.
         *
         * @returns mixed False if uninitialised, null if failed, unserialized stored data otherwise.
         */
        public function getCachedData()
        {
            if (!$this->initialised) {
                return false;
            }
            $success = false;
            $result = $this->cache->getItem($this->cacheName, $success);
            if (!$success) {
                return null;
            }
            return unserialize($result);
        }
        
        /**
         * Sets the cache item associated with the cache name of this instance.
         *
         * @var mixed Data to be stored.
         *
         * @returns boolean
         */
        public function setCachedData($data)
        {
            if (!$this->initialised) {
                return false;
            }
            $serializedData = serialize($data);
            return $this->cache->setItem($this->cacheName, $serializedData);
        }
         
        /**
         * Empty constructor.
         */
        public function __construct()
        {
        }
        
        protected function getCache()
        {
            if ($this->cache === null) {
                $this->cache = StorageFactory::factory( array(
                    'adapter' => array (
                        'name' => 'memcache',
                        'options' => array ( 'ttl' => Application::getConfig()->cache->timeToLive ),
                        'namespace' => Application::getConfig()->cache->namespace
                    ),
                    'plugins' => array (
                        'exception_handler' => array ( 'throw_exceptions' => false )
                    )
                ) );
            }
            return $this->cache;
        }
        
        protected function generateCacheName($table, $where)
        {
            if (!is_string($table)) {
                return false;
            }
            
            $cacheName = $table;
            
            $cacheName .= static::generateCacheNameHelper($where);
            
            return $cacheName;
        }
       
        protected function generateCacheNameHelper($where)
        {
            $string = "_";
            switch (gettype($where))
            {
                case "string":
                    $string .= preg_replace("#[\\\/.]+#", "_", $where);
                    break;
                case "integer":
                case "double":
                    $string .= (string) $where;
                    break;
                case "boolean":
                    $string .= ($where ? "true" : "false");
                    break;
                case "array":
                case "object":
                    foreach ($where as $key => $val) 
                    {
                        if (is_string($key)) {
                            $string .= $key . "_";
                        }
                        $string .= static::generateCacheNameHelper($val) . "_";
                    }
                    break;
                case "NULL":
                    $string = "";
                    break;
                default:
                    $string .= preg_replace("#[\\\/.]+#", "_", strval($where));
                    break;
            }
            $string = str_replace(array("\\", "/"), "_", $string);
            return $string;
        }
    }