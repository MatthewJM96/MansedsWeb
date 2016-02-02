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

    namespace Sycamore\ACL;
    
    use Sycamore\ACL\ListenerInterface;
    use Sycamore\Utils\TableCache;
    
    class ACL
    {
        /**
         * Holds instance of ACL.
         * 
         * @var \Sycamore\ACL\ACL
         */
        protected $instance;
        
        /**
         * Protected constructor. Use {@link getInstance()} instead.
         */
        protected function __construct()
        {
            $this->initialiseACL();
        }
        
        /**
         * Initialises the ACL manager.
         */
        protected function initialiseACL()
        {
            // Prepare listeners.
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(SYCAMORE_DIRECTORY . "\\ACL\\Listeners"));
            foreach ($iterator as $file) {
                $className = "Sycamore\\ACL\\Listeners\\" . $file->getBasename(".php");
                if (class_exists($className)) {
                    $listener = new $className();
                    if ($listener instanceof ListenerInterface) {
                        $listener->prepare($this);
                    }
                }
            }
        }
        
        /**
         * Gets the collection of maps that map ACL groups to the give route ID.
         * 
         * @param int $routeId
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getACLGroupRouteMapsByRouteId($routeId)
        {
            $aclGroupRouteMapTable = TableCache::getTableFromCache("ACLGroupRouteMapTable");
            return $aclGroupRouteMapTable->getByRouteId($routeId);
        }
        
        /**
         * Gets the collection of maps that map ACL groups to the give action ID.
         * 
         * @param int $actionId
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getACLGroupActionMapsByRouteId($actionId)
        {
            $aclGroupActionMapTable = TableCache::getTableFromCache("ACLGroupActionMapTable");
            return $aclGroupActionMapTable->getByActionId($actionId);
        }
        
        /**
         * Assesses if the given user is a member of the given ACL group.
         * 
         * @param int $userId
         * @param int $groupId
         * 
         * @return bool
         */
        public function userHasACLGroup($userId, $groupId) {
            $aclGroupUserMapTable = TableCache::getTableFromCache("ACLGroupUserMapTable");
            return $aclGroupUserMapTable->areMappedTogether($groupId, $userId);
        }
        
        /**
        * Gets the ACL instance.
        *
        * @return \Sycamore\Autoloader
        */
        public static final function getInstance()
        {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }
    