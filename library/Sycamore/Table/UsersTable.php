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
    
    use Sycamore\Row\User;
    use Sycamore\Table\ObjectTable;
    
    use Zend\Db\ResultSet\ResultSet;

    class UsersModel extends ObjectTable
    {
        /**
         * Sets up the result set prototype and then created the table gateway.
         */
        public function __construct()
        {
            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new User);
            parent::__construct("users", null, $resultSetPrototype);
        }
        
        /**
         * Gets a user by their username.
         * 
         * @param string $username
         * @param bool $forceDbFetch
         * 
         * @return \Sycamore\Row\User
         */
        public function getByUsername($username, $forceDbFetch = false)
        {
            return $this->getByUniqueKey("username", $username, $forceDbFetch);
        }
        
        /**
         * Gets a collection of users by their usernames.
         * 
         * @param array $usernames
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getByUsernames($usernames, $forceDbFetch = false)
        {
            return $this->getByKeyInCollection("username", $usernames, $forceDbFetch);
        }
        
        /**
         * Gets a user by their email.
         * 
         * @param string $email
         * @param bool $forceDbFetch
         * 
         * @return \Sycamore\Row\User
         */
        public function getByEmail($email, $forceDbFetch = false)
        {
            return $this->getByUniqueKey("email", $email, $forceDbFetch);
        }
        
        /**
         * Gets a collection of users by their emails.
         * 
         * @param array $emails
         * @param bool $forceDbFetch
         * 
         * @return \Zend\Db\ResultSet\ResultSet
         */
        public function getByEmails($emails, $forceDbFetch = false)
        {
            return $this->getByKeyInCollection("email", $emails, $forceDbFetch);
        }
        
        /**
         * Checks if the given username is unique.
         * 
         * @param string $username
         * 
         * @return boolean True if unique, false otherwise.
         */
        public function isUsernameUnique($username)
        {
//            $usernameStr = (string) $username;
            return !$this->tableGateway->select(array("username" => $usernameStr))->current();
        }
        
        /**
         * Checks if the given email is unique.
         * 
         * @param string $email
         * 
         * @return boolean True if unique, false otherwise.
         */
        public function isEmailUnique($email)
        {
//            $emailStr = (string) $email;
            return !$this->tableGateway->select(array("email" => $emailStr))->current();
        }
    }