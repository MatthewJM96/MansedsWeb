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

    namespace Sycamore\Table;
    
    use Sycamore\Application;
    use Sycamore\Row\Row;
    
    use Zend\Db\ResultSet\ResultSet;
    use Zend\Db\Sql\Select;
    use Zend\Db\Sql\Sql;
    use Zend\Db\TableGateway\TableGateway;

    /**
     * Sycamore abstract table class.
     */
    abstract class Table
    {
        /**
         * Holds the database table gateway object.
         *
         * @return Zend\Db\TableGateway\TableGateway
         */
        protected $tableGateway;
        
        /**
         * The table for the model.
         * 
         * @var string 
         */
        protected $table;
        
        /**
         * Constructs table gateway for table object.
         */
        public function __construct($table, Row $row, $features = null, Sql $sql = null)
        {
            $this->table = Application::getConfig()->db->tablePrefix . $table;
            
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype($row);
            $this->tableGateway = new TableGateway($this->table, Application::getDbAdapter(), $features, $resultSetPrototype, $sql);
        }
        
        /**
         * Fetches all rows matching the provided select parameters as stored in cache,
         * if none are present in cache or $forceDbFetch is true, fetches from the database.
         * 
         * @param mixed $select
         * @param mixed $cacheWhere
         * @param string $cacheExtra
         * @param string $outOfBoundsMessage
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         * 
         * @throws \OutOfBoundsException
         */
        protected function getBySelect($select, $cacheWhere, $cacheExtra, $outOfBoundsMessage, $forceDbFetch = false)
        {
            $cachedResult = null;
            if (!$forceDbFetch && !Application::forceDbFetch()) {
                $cacheManager = new DataCache;
                $cacheManager->initialise($this->table, $cacheWhere, $cacheExtra);

                $cachedResult = $cacheManager->getCachedData();
            }
            
            $result = null;
            if (is_null($cachedResult)) {
                $result = $this->tableGateway->select($select);
                if (!$result) {
                    throw new \OutOfBoundsException($outOfBoundsMessage);
                }
                $cacheManager->setCachedData($result);
            } else {
                $result = $cachedResult;
            }
            
            return $result;
        }
        
        /**
         * Fetches all rows matching the provided key value as stored in cache, 
         * if none are present in cache or $forceDbFetch is true, fetches from 
         * the database.
         * 
         * @param string $key
         * @param mixed $value
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        protected function getByKey($key, $value, $forceDbFetch = false)
        {
            return $this->getBySelect(
                array ($key => $value),
                $value,
                "get_by_$key",
                "Could not find row with $key, $value, of table $this->table.",
                $forceDbFetch
            );
        }
        
        /**
         * Fetches a row matching the provided unique key value as stored in cache, 
         * if none are present in cache or $forceDbFetch is true, fetches from 
         * the database.
         * 
         * @param string $key
         * @param mixed $value
         * @param bool $forceDbFetch
         * 
         * @return mixed
         */
        protected function getByUniqueKey($key, $value, $forceDbFetch = false)
        {
            return $this->getByKey($key, $value, $forceDbFetch)->current();
        }
        
        /**
         * Fetches all rows between the provided key values as stored in cache, 
         * if none are present in cache or $forceDbFetch is true, fetches from 
         * the database.
         * 
         * @param string $key
         * @param int|string|float $valueMin
         * @param int|string|float $valueMax
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        protected function getByKeyBetween($key, $valueMin, $valueMax, $forceDbFetch = false)
        {
//            if (!(is_int($valueMin) && is_int($valueMax)) && !(is_float($valueMin) && is_float($valueMax)) && !(is_string($valueMin) && is_string($valueMax))) {
//                throw new \InvalidArgumentException("The provided values have invalid types.");
//            }
            
            return $this->getBySelect(
                function (Select $select) use ($key, $valueMin, $valueMax) {
                    $select->where->between($key, $valueMin, $valueMax);
                },
                strval($valueMin) . strval($valueMax),
                "get_between_$key",
                "Could not find row between values of $key, $valueMin -> $valueMax, of table $this->table.",
                $forceDbFetch
            );
        }
        
        /**
         * Fetches all rows greater than the provided key value as stored in cache, 
         * if none are present in cache or $forceDbFetch is true, fetches from 
         * the database.
         * 
         * @param string $key
         * @param int|string|float $value
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        protected function getByKeyGreaterThanOrEqualTo($key, $value, $forceDbFetch = false)
        {
//            if (!is_int($value) && !is_float($value) && !is_string($value)) {
//                throw new \InvalidArgumentException("The provided value has invalid type.");
//            }
            
            return $this->getBySelect(
                function (Select $select) use ($key, $value) {
                    $select->where->greaterThanOrEqualTo($key, $value);
                },
                $value,
                "get_greater_than_or_equal_to_$key",
                "Could not find row with greater or equal value of $key, $value, of table $this->table.",
                $forceDbFetch
            );
        }
        
        /**
         * Fetches all rows less than the provided key value as stored in cache, 
         * if none are present in cache or $forceDbFetch is true, fetches from 
         * the database.
         * 
         * @param string $key
         * @param int|string|float $value
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        protected function getByKeyLessThanOrEqualTo($key, $value, $forceDbFetch = false)
        {
//            if (!is_int($value) && !is_float($value) && !is_string($value)) {
//                throw new \InvalidArgumentException("The provided value has invalid type.");
//            }
            
            return $this->getBySelect(
                function (Select $select) use ($key, $value) {
                    $select->where->lessThanOrEqualTo($key, $value);
                },
                $value,
                "get_less_than_or_equal_to_$key",
                "Could not find row with less or equal value of $key, $value, of table $this->table.",
                $forceDbFetch
            );
        }
        
        /**
         * 
         * @param type $key
         * @param type $valueCollection
         * @param type $forceDbFetch
         * 
         * @return type
         */
        protected function getByKeyInCollection($key, $valueCollection, $forceDbFetch = false)
        {
            return $this->getBySelect(
                function (Select $select) use ($key, $valueCollection) {
                    $select->where->in($key, $valueCollection);
                },
                $valueCollection,
                "get_in_collection_$key",
                "Could not find row with value in the provided collection of $key of table $this->table.",
                $forceDbFetch
            );
        }
        
        /**
         * Gets all entries of a table from cache if existent and if 
         * $forceDbFetch is false, otherwise fetches from the database.
         * 
         * @var boolean
         *
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function fetchAll($forceDbFetch = false)
        {
            $cachedResult = null;
            if (!$forceDbFetch && !Application::forceDbFetch()) {
                $cacheManager = new DataCache;
                $cacheManager->initialise($this->table, null, "fetch_all");

                $cachedResult = $cacheManager->getCachedData();
            }
            
            $result = array();
            if (is_null($cachedResult)) {
                $result =  $this->tableGateway->select();
                $cacheManager->setCachedData($result);
            } else {
                $result = $cachedResult;
            }
            
            return $result;
        }
        
        /**
         * Returns the last insert value of the TableGateway instance.
         * 
         * @return int
         */
        public function lastInsertValue()
        {
            return $this->tableGateway->getLastInsertValue();
        }
    }