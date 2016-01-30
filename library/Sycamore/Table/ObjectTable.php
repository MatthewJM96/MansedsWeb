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
    
    use Sycamore\Table\Table;
    
    use Zend\Db\ResultSet\ResultSet;
    use Zend\Db\Sql\Sql;

    /**
     * Sycamore abstract object table class.
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