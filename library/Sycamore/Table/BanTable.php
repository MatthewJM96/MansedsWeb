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
    
    use Sycamore\Row\Ban;
    use Sycamore\Table\ObjectTable;
    
    class BanTable extends ObjectTable
    {
        /**
         * Sets up the result set prototype and then created the table gateway.
         */
        public function __construct()
        {
            parent::__construct("acl_group_user_maps", new Ban, null);
        }
        
        public function getByBanned($id, $forceDbFetch = false)
        {
            return $this->getByUniqueKey("bannedId", $id, $forceDbFetch);
        }
        
        public function getByBanneds($ids, $forceDbFetch = false)
        {
            return $this->getByKeyInCollection("bannedId", $ids, $forceDbFetch);
        }
        
        public function getByState($state, $forceDbFetch = false)
        {
            return $this->getByKey("status", $state, $forceDbFetch);
        }
        
        public function getByExpiryTimeMin($expiryTimeMin, $forceDbFetch = false)
        {
            return $this->getByKeyGreaterThanOrEqualTo("expiryTime", $expiryTimeMin, $forceDbFetch);
        }
        
        public function getByExpiryTimeMax($expiryTimeMax, $forceDbFetch = false)
        {
            return $this->getByKeyLessThanOrEqualTo("expiryTime", $expiryTimeMax, $forceDbFetch);
        }
        
        public function getByExpiryTimeRange($expiryTimeMin, $expiryTimeMax, $forceDbFetch = false)
        {
            return $this->getByKeyBetween("expiryTime", $expiryTimeMin, $expiryTimeMax, $forceDbFetch);
        }
    }
    
