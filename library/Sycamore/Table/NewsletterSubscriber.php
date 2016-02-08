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
    
    use Sycamore\Row\NewsletterSubscriber;
    use Sycamore\Table\ObjectTable;
    
    use Zend\Db\ResultSet\ResultSet;
    
    class NewsletterSubscriber extends ObjectTable
    {
        /**
         * Sets up the result set prototype and then created the table gateway.
         */
        public function __construct()
        {
            parent::__construct("newsletter_subscribers", new NewsletterSubscriber);
        }
        
        /**
         * Gets a newsletter subscriber object by their email.
         * 
         * @param string $email
         * @param bool $forceDbFetch
         * 
         * @return \Sycamore\Row\NewsletterSubscriber
         */
        public function getByEmail($email, $forceDbFetch = false)
        {
            return $this->getByUniqueKey("email", $email, $forceDbFetch);
        }
        
        /**
         * Gets the matching newsletter subscribers by their emails.
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
         * Gets a newsletter subscriber object by their subscription delete key.
         * 
         * @param string $deleteKey
         * @param bool $forceDbFetch
         * 
         * @return \Sycamore\Row\NewsletterSubscriber
         */
        public function getByDeleteKey($deleteKey, $forceDbFetch = false)
        {
            return $this->getByUniqueKey("deleteKey", $deleteKey, $forceDbFetch);
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
            $emailStr = (string) $email;
            $row = $this->tableGateway->select(array("email" => $emailStr))->current();
            if (!$row) {
                return true;
            }
            return false;
        }
    }