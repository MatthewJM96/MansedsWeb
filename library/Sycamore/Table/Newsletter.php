<?php

/* 
 * Copyright (C) 2016 Matthew
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
    
    use Sycamore\Row\Newsletter;
    use Sycamore\Table\ObjectTable;
    
    class Newsletter extends ObjectTable
    {
        /**
         * Sets up the result set prototype and then created the table gateway.
         */
        public function __construct()
        {
            parent::__construct("newsletters", new Newsletter, null);
        }
        
        /**
         * Gets a newsletter object by their sent status.
         * 
         * @param bool $sent
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getBySent($sent, $forceDbFetch = false)
        {
            return $this->getByKey("sent", $sent, $forceDbFetch);
        }
        
        /**
         * Gets a newsletter object by their cancelled status.
         * 
         * @param bool $cancelled
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getByCancelled($cancelled, $forceDbFetch = false)
        {
            return $this->getByKey("cancelled", $cancelled, $forceDbFetch);
        }
        
        /**
         * Get's newsletters to be sent after a certain time.
         * 
         * @param int $sendTimeMin
         * @param bool $forceDbFetch
         * 
         * @return Zend\Db\ResultSet\ResultSet
         */
        public function getBySendTimeMin($sendTimeMin, $forceDbFetch = false)
        {
            return $this->getByKeyGreaterThanOrEqualTo("sendTime", $sendTimeMin, $forceDbFetch);
        }
        
        /**
         * Get's newsletters to be sent before a certain time.
         * 
         * @param int $sendTimeMax
         * @param bool $forceDbFetch
         * 
         * @return Zend\Db\ResultSet\ResultSet
         */
        public function getBySendTimeMax($sendTimeMax, $forceDbFetch = false)
        {
            return $this->getByKeyLessThanOrEqualTo("sendTime", $sendTimeMax, $forceDbFetch);
        }
        
        /**
         * Get's newsletters to be sent within a time range.
         * 
         * @param int $sendTimeMin
         * @param int $sendTimeMax
         * @param bool $forceDbFetch
         * 
         * @return Zend\Db\ResultSet\ResultSet
         */
        public function getBySendTimeRange($sendTimeMin, $sendTimeMax, $forceDbFetch = false)
        {
            return $this->getByKeyBetween("sendTime", $sendTimeMin, $sendTimeMax, $forceDbFetch);
        }
    }