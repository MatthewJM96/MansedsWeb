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
    
    use Sycamore\Row\RowObject;
    use Sycamore\Table\Table;
    
    use Zend\Db\Sql\Sql;

    /**
     * Sycamore abstract object table class.
     */
    abstract class ObjectTable extends Table
    {
        /**
         * Passes straight through to Table constructor.
         */
        public function __construct($table, RowObject $rowObject, $features = null, Sql $sql = null)
        {
            parent::__construct($table, $rowObject, $features, $sql);
        }
        
        /**
         * Gets a row object by their ID.
         * 
         * @var int
         * @var boolean
         *
         * @return \Sycamore\Row\Row
         */
        public function getById($id, $forceDbFetch = false)
        {
            return $this->getByUniqueKey("id", $id, $forceDbFetch);
        }
        
        /**
         * Gets the matching row objects by their IDs.
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
         * Get matching row objects with a creator of the given ID.
         * 
         * @param int $creatorId
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getByCreator($creatorId, $forceDbFetch = false)
        {
            return $this->getByKey($creatorId, "creatorId", $forceDbFetch);
        }
        
        /**
         * Get matching row objects with a creator of the given IDs.
         * 
         * @param array $creatorIds
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getByCreators($creatorIds, $forceDbFetch = false)
        {
            return $this->getByKeyInCollection("creatorId", $creatorIds, $forceDbFetch);
        }
        
        /**
         * Get matching row objects with a minimum creation time matching that given.
         * 
         * @param int $creationTimeMin
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getByCreationTimeAfter($creationTimeMin, $forceDbFetch = false)
        {
            return $this->getByKeyGreaterThanOrEqualTo("creationTime", $creationTimeMin, $forceDbFetch);
        }
        
        /**
         * Get matching row objects with a maximum creation time matching that given.
         * 
         * @param int $creationTimeMax
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getByCreationTimeBefore($creationTimeMax, $forceDbFetch = false)
        {
            return $this->getByKeyLessThanOrEqualTo("creationTime", $creationTimeMax, $forceDbFetch);
        }
        
        /**
         * Get matching row objects with a minimum and maximum creation time matching those given.
         * 
         * @param int $creationTimeMin
         * @param int $creationTimeMax
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getByCreationTimeRange($creationTimeMin, $creationTimeMax, $forceDbFetch = false)
        {
            return $this->getByKeyBetween("creationTime", $creationTimeMin, $creationTimeMax, $forceDbFetch);
        }
        
        /**
         * Get matching row objects with most recent updator with the given ID.
         * 
         * @param int $updatorId
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getByUpdator($updatorId, $forceDbFetch = false)
        {
            return $this->getByKey($updatorId, "updatorId", $forceDbFetch);
        }
        
        /**
         * Get matching row objects with most recent updator with any of the given IDs.
         * 
         * @param array $updatorIds
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getByUpdators($updatorIds, $forceDbFetch = false)
        {
            return $this->getByKeyInCollection("updatorId", $updatorIds, $forceDbFetch);
        }
        
        /**
         * Get matching row objects with a minimum update time matching that given.
         * 
         * @param int $updateTimeMin
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getByUpdateTimeAfter($updateTimeMin, $forceDbFetch = false)
        {
            return $this->getByKeyGreaterThanOrEqualTo("updateTime", $updateTimeMin, $forceDbFetch);
        }
        
        /**
         * Get matching row objects with a maximum update time matching that given.
         * 
         * @param int $updateTimeMax
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getByUpdateTimeBefore($updateTimeMax, $forceDbFetch = false)
        {
            return $this->getByKeyLessThanOrEqualTo("updateTime", $updateTimeMax, $forceDbFetch);
        }
        
        /**
         * Get matching row objects with a minimum and maximum update time matching those given.
         * 
         * @param int $updateTimeMin
         * @param int $updateTimeMax
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getByUpdateTimeRange($updateTimeMin, $updateTimeMax, $forceDbFetch = false)
        {
            return $this->getByKeyBetween("updateTime", $updateTimeMin, $updateTimeMax, $forceDbFetch);
        }
        
        /**
         * Gets matches to data point provided through calling the given function.
         * Populates result array, keyed by object ID.
         * 
         * @param mixed $dataPoint
         * @param string $getFunc
         * @param array $result
         * 
         * @return boolean
         */
        public function getByDataPoint($dataPoint, $getFunc, $result, $forceDbFetch = false) {
            $rawResult = $this->$getFunc($dataPoint, $forceDbFetch);
            if (is_null($rawResult)) {
                return false;
            } else {
                foreach ($rawResult as $rawResultItem) {
                    $result[$rawResultItem->id] = $rawResultItem;
                }
            }
            return true;
        }
        
        /**
         * Gets matches to data point range provided through calling the given function.
         * Populates result array, keyed by object ID.
         * 
         * @param mixed $dataPointMin
         * @param mixed $dataPointMax
         * @param string $getFunc
         * @param array $result
         * 
         * @return boolean
         */
        public function getByDataPointRange($dataPointMin, $dataPointMax, $getFunc, $result, $forceDbFetch = false) {
            $rawResult = $this->$getFunc($dataPointMin, $dataPointMax, $forceDbFetch);
            if (is_null($rawResult)) {
                return false;
            } else {
                foreach ($rawResult as $rawResultItem) {
                    $result[$rawResultItem->id] = $rawResultItem;
                }
            }
            return true;
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
        public function save(\Sycamore\Row\Row $row, $id = 0) {
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