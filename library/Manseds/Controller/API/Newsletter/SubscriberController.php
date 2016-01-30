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

    namespace Manseds\Controller\API\Newsletter;
    
    use Manseds\ErrorManager;
    use Manseds\Controller\Controller;
    use Manseds\Model\NewsletterSubscriber;
    use Manseds\Utils\APIData;
    use Manseds\Utils\TableCache;
    
    /**
     * Controller for handling newsletter subscribers.
     */
    class SubscriberController extends Controller
    {
        /**
         * Executes the process of acquiring a desired newsletter email
         * entry.
         * 
         * @return boolean
         */
        public function getAction()
        {
            // Attempt to acquire the provided data.
            $emailsJson = filter_input(INPUT_GET, "emails");
            
            $newsletterSubscriberTable = TableCache::getTableFromCache("NewsletterSubscriberTable");
            $result = null;
            if (!$emailsJson) {
                // Fetch all subscribers as no filter provided.
                $result = $newsletterSubscriberTable->fetchAll();
            } else {
                // Fetch only subscribers matching given emails.
                $emails = APIData::decode($emailsJson);
                
                // If emails weren't provided as an array, fail.
                if (!is_array($emails)) {
                    ErrorManager::addError("emails_error", "invalid_emails_filter_object");
                    $this->prepareExit();
                    return false;
                }
                
                // Ascertain each email is valid in type and format.
                foreach ($emails as $email) {
                    if (!is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        ErrorManager::addError("emails_error", "invalid_email_format");
                        $this->prepareExit();
                        return false;
                    }
                }
                $result = $newsletterSubscriberTable->getByEmails($emails);
            }
            
            // Send the client the fetched newsletter subscribers.
            $this->response->setResponseCode(200)->send();
            $this->renderer->render(APIData::encode(array("data" => $result)));
            return true;
        }
        
        /**
         * Executes the creation process for creating a new newsletter email 
         * entry.
         * 
         * @return boolean
         */
        public function postAction()
        {
            // Ensure all data needed is posted to the server.
            $dataProvided = array (
                array ( "key" => "email", "errorType" => "email_error", "errorKey" => "missing_email" ),
                array ( "key" => "name",  "errorType" => "name_error",  "errorKey" => "missing_name" )
            );
            if (!$this->dataProvided($dataProvided, INPUT_POST)) {
                $this->prepareExit();
                return false;
            }
            
            // Acquire the sent data, sanitised appropriately.
            $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
            $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING);
            
            // Ensure the email has valid formatting.
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                ErrorManager::addError("email_error", "invalid_email_format");
                $this->prepareExit();
                return false;
            }
            
            // Grab the newsletter subscriber table.
            $newsletterSubscriberTable = TableCache::getTableFromCache("NewsletterSubscriberTable");
            
            // Ensure the email is unique.
            if (!$newsletterSubscriberTable->isEmailUnique($email)) {
                ErrorManager::addError("email_error", "email_already_subscribed_to_newsletter");
                $this->prepareExit();
                return false;
            }
            
            // Construct new newsletter subscriber.
            $newsletterSubscriber = new NewsletterSubscriber;
            $newsletterSubscriber->name = $name;
            $newsletterSubscriber->email = $email;
            
            // Insert new newsletter subscriber into database.
            $newsletterSubscriberTable->save($newsletterSubscriber);
            
            // Let client know newsletter subscription creation was successful.
            $this->response->setResponseCode(200)->send();
            $this->renderer->render();
            return true;
        }
        
        /**
         * Executes the deletion process for deleting a newsletter email 
         * entry.
         * 
         * @return boolean
         */
        public function deleteAction()
        {
            // Acquire the sent data, sanitised appropriately.
            $deleteKey = filter_input(INPUT_GET, "deleteKey", FILTER_SANITIZE_STRING);
            
            // If data is not provided, fail.
            if (!$deleteKey) {
                ErrorManager::addError("newsletter_subscriber_delete_key_error", "missing_newsletter_subscriber_delete_key");
                $this->prepareExit();
                return false;
            }
            
            // Get newsletter subscriber with provided delete key.
            $newsletterSubscriberTable = TableCache::getTableFromCache("NewsletterSubscriberTable");
            $newsletterSubscriber = $newsletterSubscriberTable->getByDeleteKey($deleteKey);
            
            // Error out if no subscriber was found to have the delete key.
            if (!$newsletterSubscriber) {
                ErrorManager::addError("newsletter_subscriber_delete_key_error", "invalid_newsletter_subscriber_delete_key");
                $this->prepareExit();
                return false;
            }
            
            // Delete subscriber.
            $newsletterSubscriberTable->deleteById($newsletterSubscriber->id);
            
            // Let client know newsletter subscription deletion was successful.
            $this->response->setResponseCode(200)->send();
            $this->renderer->render();
            return true;
        }
    }