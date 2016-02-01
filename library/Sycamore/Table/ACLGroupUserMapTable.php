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
    
    use Sycamore\Row\ACLGroupUserMap;
    use Sycamore\Table\Table;
    
    class ACLGroupUserMapTable extends Table
    {
        /**
         * Sets up the result set prototype and then created the table gateway.
         */
        public function __construct()
        {
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new ACLGroupUserMap);
            parent::__construct("acl_group_user_maps", null, $resultSetPrototype);
        }
        
        /**
         * Gets all mappings with ACL group ID.
         * 
         * @param int $id
         * @param bool $forceDbFetch
         * 
         * @return Zend\Db\ResultSet\ResultSet
         */
        public function getByACLGroupId($id, $forceDbFetch = false)
        {
            return $this->getByKey("groupId", $id, $forceDbFetch);
        }
        
        /**
         * Gets all mappings with user ID.
         * 
         * @param int $id
         * @param bool $forceDbFetch
         * 
         * @return Zend\Db\ResultSet\ResultSet
         */
        public function getByUserId($id, $forceDbFetch = false)
        {
            return $this->getByKey("userId", $id, $forceDbFetch);
        }
        
        /**
         * Determines if an ACL group and user are mapped together.
         * 
         * @param int $groupId
         * @param int $userId
         * @param bool $forceDbFetch
         * 
         * @return bool
         */
        public function areMappedTogether($groupId, $userId, $forceDbFetch = false)
        {
            return ($this->getBySelect(
                array ( "groupId" => $groupId, "userId" => $userId ),
                strval($groupId) . "-" . strval($userId),
                "get_by_acl_group_id_and_route_id",
                "Could not find row with an ACL group ID of $groupId and user ID of $userId, in table $this->table.",
                $forceDbFetch
            )->current() instanceof ACLGroupUserMap);
        }
    }
