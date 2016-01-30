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

    namespace Manseds\Table;
    
    use Manseds\Table\Table;
    
    use Zend\Db\ResultSet\ResultSet;
    use Zend\Db\Sql\Select;
    use Zend\Db\Sql\Sql;

    /**
     * Manseds abstract object table class.
     * Assumes id is possessed.
     */
    abstract class ObjectTable extends Table
    {
        /**
         * Passes straight through to Table constructor.
         */
        public function __construct($table, $features = null, ResultSet $resultSetPrototype = null, Sql $sql = null)
        {
            parent::__construct($table, $features, $resultSetPrototype, $sql);
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
         * Gets a row object by their ID.
         * 
         * @var int
         * @var boolean
         *
         * @return \Manseds\Row\Row
         */
        public function getById($id, $forceDbFetch = false)
        {
            return $this->getByUniqueKey("id", $id, $forceDbFetch);
        }
        
        /**
         * Gets the matching users by their IDs.
         * 
         * @param array $ids
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getByIds($ids, $forceDbFetch = false)
        {
            return $this->getByKeyInCollection("id", $ids, $forceDbFetch);
        }
        
        /**
         * Deletes the entry with the given id.
         * 
         * @param int $id
         */
        public function deleteById($id) {
            $this->tableGateway->delete(array("id" => (int) $id));
        }
        
        /**
         * Saves the given row object, as an update if an id is provided, 
         * as an insertion otherwise.
         * 
         * @param array $row
         * @param int $id
         * @throws \Exception If id points to invalid row.
         */
        public function save(\Manseds\Row\Row $row, $id = 0) {
            if ($id == 0) {
                $this->tableGateway->insert($row->toArray());
            } else {
                if ($this->getById($id)) {
                    $this->tableGateway->update($row->toArray(), array("id" => $id));
                } else {
                    throw new \OutOfBoundsException("Record of id $id does not exist.");
                }
            }
        }
    }