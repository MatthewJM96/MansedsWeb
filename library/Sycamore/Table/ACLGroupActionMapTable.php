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
    
    use Sycamore\Row\ACLGroupActionMap;
    use Sycamore\Table\Table;
    
    class ACLGroupActionMapTable extends Table
    {
        /**
         * Sets up the result set prototype and then created the table gateway.
         */
        public function __construct()
        {
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new ACLGroupActionMap);
            parent::__construct("acl_group_action_maps", null, $resultSetPrototype);
        }
        
        /**
         * Gets all mappings with ACL group ID as given.
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
         * Gets all mappings with action key as given.
         * 
         * @param string $key
         * @param bool $forceDbFetch
         * 
         * @return Zend\Db\ResultSet\ResultSet
         */
        public function getByActionKey($key, $forceDbFetch = false)
        {
            return $this->getByKey("actionKey", $key, $forceDbFetch);
        }
        
        /**
         * Gets all mappings with ACL group ID and action key as given.
         * 
         * @param int $groupId
         * @param string $key
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getByACLGroupIdAndActionKey($id, $key, $forceDbFetch = false)
        {
            return $this->getBySelect(
                array ( "groupId" => $id, "actionKey" => $key ),
                strval($id) . $key,
                "get_by_acl_group_id_and_action_key",
                "Could not find row with an ACL group ID of $id and action key of $key, in table $this->table.",
                $forceDbFetch
            );
        }
    }
        