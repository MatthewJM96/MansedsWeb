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

    namespace Sycamore\Controller\API\Newsletter;
    
    use Sycamore\ErrorManager;
    use Sycamore\Controller\Controller;
    use Sycamore\Enums\ActionState;
    use Sycamore\Model\NewsletterSubscriber;
    use Sycamore\Utils\APIData;
    use Sycamore\Utils\TableCache;
    
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
            // Assess if permissions needed are held by the user.
            if (!$this->eventManager->trigger("preExecuteGet", $this)) {
                if (!Visitor::getInstance()->isLoggedIn) {
                    return ActionState::DENIED_NOT_LOGGED_IN;
                } else {
                    ErrorManager::addError("permission_error", "permission_missing");
                    $this->prepareExit();
                    return ActionState::DENIED;
                }
            }
            
            // Attempt to acquire the provided data.
            $dataJson = filter_input(INPUT_GET, "data");
            
            $newsletterSubscriberTable = TableCache::getTableFromCache("NewsletterSubscriberTable");
            $result = null;
            $validDataPoint = true;
            if (!$dataJson) {
                // Fetch all subscribers as no filter provided.
                $result = $newsletterSubscriberTable->fetchAll();
            } else {
                // Fetch only subscribers matching given emails.
                $data = APIData::decode($dataJson);
                $emails = (isset($data["emails"]) ? $data["emails"] : NULL);
                
                // If emails weren't provided as an array, fail.
//                if (!is_array($emails)) {
//                    ErrorManager::addError("emails_error", "invalid_emails_filter_object");
//                    $this->prepareExit();
//                    return ActionState::DENIED;
//                }
                
                // Ascertain each email is valid in type and format.
                $result = array();
                if (!is_null($emails)) {
                    $validDataPoint = $newsletterSubscriberTable->getByDataPoint($emails, "getByEmails", $result);
                }
            }
            
            // TODO(Matthew): Check that bad contents of data point does result in null result. Extends for all controllers.
            // If result is bad, input must have been bad.
            if (is_null($result) || !$validDataPoint) {
                ErrorManager::addError("data_error", "invalid_data_filter_object");
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            // Send the client the fetched newsletter subscribers.
            $this->response->setResponseCode(200)->send();
            $this->renderer->render(APIData::encode(array("data" => $result)));
            return ActionState::SUCCESS;
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
                return ActionState::DENIED;
            }
            
            // Assess if permissions needed are held by the user.
            if (!$this->eventManager->trigger("preExecutePost", $this)) {
                // TODO(Matthew): Should we be treating non-logged in people differently?
                //                Perhaps separate admin create and public create?
                if (!Visitor::getInstance()->isLoggedIn) {
                    return ActionState::DENIED_NOT_LOGGED_IN;
                } else {
                    ErrorManager::addError("permission_error", "permission_missing");
                    $this->prepareExit();
                    return ActionState::DENIED;
                }
            }
            
            // Acquire the sent data, sanitised appropriately.
            $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
            $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING);
            
            // Ensure the email has valid formatting.
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                ErrorManager::addError("email_error", "invalid_email_format");
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            // Grab the newsletter subscriber table.
            $newsletterSubscriberTable = TableCache::getTableFromCache("NewsletterSubscriberTable");
            
            // Ensure the email is unique.
            if (!$newsletterSubscriberTable->isEmailUnique($email)) {
                ErrorManager::addError("email_error", "email_already_subscribed_to_newsletter");
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            // Construct new newsletter subscriber.
            $newsletterSubscriber = new NewsletterSubscriber;
            $newsletterSubscriber->name = $name;
            $newsletterSubscriber->email = $email;
            
            // Insert new newsletter subscriber into database.
            $newsletterSubscriberTable->save($newsletterSubscriber);
            
            // Let client know newsletter subscription creation was successful.
            $this->response->setResponseCode(200)->send();
            return ActionState::SUCCESS;
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
                return ActionState::DENIED;
            }
            
            // Asses if permissions needed are held by the user.
            if (!$this->eventManager->trigger("preExecuteDelete", $this)) {
                if (!Visitor::getInstance()->isLoggedIn) {
                    return ActionState::DENIED_NOT_LOGGED_IN;
                } else {
                    // TODO(Matthew): How to delete own account?
                    ErrorManager::addError("permission_error", "permission_missing");
                    $this->prepareExit();
                    return ActionState::DENIED;
                }
            }
                        
            // Get newsletter subscriber with provided delete key.
            $newsletterSubscriberTable = TableCache::getTableFromCache("NewsletterSubscriberTable");
            $newsletterSubscriber = $newsletterSubscriberTable->getByDeleteKey($deleteKey);
            
            // Error out if no subscriber was found to have the delete key.
            if (!$newsletterSubscriber) {
                ErrorManager::addError("newsletter_subscriber_delete_key_error", "invalid_newsletter_subscriber_delete_key");
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            // Delete subscriber.
            $newsletterSubscriberTable->deleteById($newsletterSubscriber->id);
            
            // Let client know newsletter subscription deletion was successful.
            $this->response->setResponseCode(200)->send();
            return ActionState::SUCCESS;
        }
    }