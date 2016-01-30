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
    
    use Zend\Db\ResultSet\ResultSet;
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
        public function __construct($table, $features = null, ResultSet $resultSetPrototype = null, Sql $sql = null)
        {
            $this->table = $table;
            $this->tableGateway = new TableGateway($table, Application::getDbAdapter(), $features, $resultSetPrototype, $sql);
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
    }