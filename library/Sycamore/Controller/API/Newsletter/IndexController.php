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
    
    use Sycamore\Visitor;
    use Sycamore\Controller\Controller;
    use Sycamore\Row\Newsletter;
    
    /**
     * Controller for handling newsletters.
     */
    class IndexController extends Controller
    {
        /**
         * Executes the process of acquiring a desired newsletter.
         */
        public function getAction()
        {
            // Assess if permissions needed are held by the user.
            if (!$this->eventManager->trigger("preExecuteGet", $this)) {
                if (!Visitor::getInstance()->isLoggedIn) {
                    return ActionState::DENIED_NOT_LOGGED_IN;
                } else {
                    ErrorManager::addError("permission", "permission_missing");
                    $this->prepareExit();
                    return ActionState::DENIED;
                }
            }
            
            // Attempt to acquire the provided data.
            $dataJson = filter_input(INPUT_GET, "data");
            
            // Grab the newsletter table.
            $newsletterTable = TableCache::getTableFromCache("Newsletter");
            
            // Fetch newsletters with given values, or all newsletters if no values provided. 
            $result = null;
            $validDataPoint = true;
            if (!$dataJson) {
                $result = $newsletterTable->fetchAll();
            } else {
                // Fetch only users matching given data.
                $data = APIData::decode($dataJson);
                $ids          = (isset($data["ids"])         ? $data["ids"]         : NULL);
                $cancelled    = (isset($data["cancelled"])   ? $data["cancelled"]   : NULL);
                $sent         = (isset($data["sent"])        ? $data["sent"]        : NULL);
                $sendTimeMin  = (isset($data["sendTimeMin"]) ? $data["sendTimeMin"] : NULL);
                $sendTimeMax  = (isset($data["sendTimeMax"]) ? $data["sendTimeMax"] : NULL);
                                
                // Fetch matching users, storing with ID as key for simple overwrite to avoid duplicates.
                $result = array();
                if (!is_null($ids)) {
                    $validDataPoint = $newsletterTable->getByDataPoint($ids, "getByIds", $result);
                }
                if (!is_null($cancelled)) {
                    $validDataPoint = $newsletterTable->getByDataPoint($cancelled, "getByCancelled", $result);
                }
                if (!is_null($sent)) {
                    $validDataPoint = $newsletterTable->getByDataPoint($sent, "getBySent", $result);
                }
                if ($sendTimeMin > 0 && $sendTimeMax > 0) {
                    $validDataPoint = $newsletterTable->getByDataPointRange($sendTimeMin, $sendTimeMax, "getBySendTimeRange", $result);
                } else if ($sendTimeMin > 0) {
                    $validDataPoint = $newsletterTable->getByDataPoint($sendTimeMin, "getBySendTimeMin", $result);
                } else if ($sendTimeMax > 0) {
                    $validDataPoint = $newsletterTable->getByDataPoint($sendTimeMax, "getBySendTimeMax", $result);
                }
            }
            
            // If result is bad, input must have been bad.
            if (is_null($result) || !$validDataPoint) {
                ErrorManager::addError("data", "invalid_data_filter_object");
                $this->prepareExit();
                return ActionState::DENIED;
            }

            // Send the client the fetched newsletters.
            $this->response->setResponseCode(200)->send();
            $this->renderer->render(APIData::encode(array("data" => $result)));
            return ActionState::SUCCESS;
        }
        
        /**
         * Executes the creation process for creating a new newsletter entry.
         * 
         * @return boolean
         */
        public function postAction()
        {
            // Prepare data holder.
            $data = array();
            
            // Ensure all data needed is posted to the server.
            $dataProvided = array (
                array ( "key" => "subject", "errorType" => "subject", "errorKey" => "missing_subject" ),
                array ( "key" => "body", "errorType" => "body", "errorKey" => "missing_body" ),
                array ( "key" => "sendTime", "errorType" => "send_time", "errorKey" => "missing_send_time" ),
                array ( "key" => "recipientGroup", "errorType" => "recipient_group", "errorKey" => "missing_recipient_group" ),
            );
            if (!$this->fetchData($dataProvided, INPUT_POST, $data)) {
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            // Assess if permissions needed are held by the user.
            if (!$this->eventManager->trigger("preExecutePost", $this)) {
                if (!Visitor::getInstance()->isLoggedIn) {
                    return ActionState::DENIED_NOT_LOGGED_IN;
                } else {
                    ErrorManager::addError("permission", "permission_missing");
                    $this->prepareExit();
                    return ActionState::DENIED;
                }
            }
            
            // Grab the newsletter table.
            $newsletterTable = TableCache::getTableFromCache("Newsletter");
            
            // Construct newsletter entry.
            $time = time();
            $newsletter = new Newsletter;
            $newsletter->creatorId = Visitor::getInstance()->id;
            $newsletter->creationTime = $time;
            $newsletter->lastUpdatorId = Visitor::getInstance()->id;
            $newsletter->lastUpdateTime = $time;
            $newsletter->subject = $data["subject"];
            $newsletter->body = $data["body"];
            $newsletter->cancelled = 0;
            $newsletter->sent = 0;
            $newsletter->recipientGroup = $data["recipientGroup"];
            $newsletter->sendTime = $data["sendTime"];
            
            // Save new ban.
            $newsletterTable->save($newsletter);
            
            // Let client know newsletter subscription creation was successful.
            $this->response->setResponseCode(200)->send();
            return ActionState::SUCCESS;
        }
        
        /**
         * Executes the process of deleting the desired newsletter.
         */
        public function deleteAction()
        {
            // Prepare data holder.
            $data = array();
            
            // Ensure data needed is sent to the server.
            $dataProvided = array (
                array ( "key" => "id", "errorType" => "newsletter_id", "errorKey" => "missing_newsletter_id" )
            );
            if (!$this->fetchData($dataProvided, INPUT_GET, $data)) {
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            // Assess if permissions needed are held by the user.
            if (!$this->eventManager->trigger("preExecuteDelete", $this)) {
                if (!Visitor::getInstance()->isLoggedIn) {
                    return ActionState::DENIED_NOT_LOGGED_IN;
                } else {
                    ErrorManager::addError("permission", "permission_missing");
                    $this->prepareExit();
                    return ActionState::DENIED;
                }
            }
            
            // Get newsletter with provided ID.
            $newsletterTable = TableCache::getTableFromCache("Newsletter");
            $newsletter = $newsletterTable->getById($data["id"]);
            
            // Error out if no subscriber was found to have the ID.
            if (!$newsletter) {
                ErrorManager::addError("newsletter_id", "invalid_newsletter_id");
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            // Delete newsletter.
            $newsletterTable->deleteById($newsletter->id);
            
            // Let client know newsletter deletion was successful.
            $this->response->setResponseCode(200)->send();
            return ActionState::SUCCESS;
        }
        
        /**
         * Executes the process of updating a newsletter.
         * Only handles the following data points of a newsletter:
         *  - Subject
         *  - Body
         *  - Cancelled State
         *  - Send Time
         *  - Recipient Group
         */
        public function putAction()
        {
            // Prepare data holder.
            $data = array();
            
            // Ensure ID has been provided of the newsletter object to be updated.
            $dataProvided = array (
                array ( "key" => "id", "errorType" => "newsletter_id", "errorKey" => "missing_newsletter_id" )
            );
            if (!$this->fetchData($dataProvided, INPUT_GET, $data)) {
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            // Assess if permissions needed are held by the user.
            if (!$this->eventManager->trigger("preExecutePut", $this)) {
                // If not logged in, or not the same user as to be edited, fail due to missing permissions.
                if (!Visitor::getInstance()->isLoggedIn) {
                    return ActionState::DENIED_NOT_LOGGED_IN;
                } else if (Visitor::getInstance()->id != $id) {
                    ErrorManager::addError("permission", "permission_missing");
                    $this->prepareExit();
                    return ActionState::DENIED;
                }
            }
            
            // Get newsletter with provided ID.
            $newsletterTable = TableCache::getTableFromCache("Newsletter");
            $newsletter = $newsletterTable->getById($data["id"]);
            
            // Handle invalid newsletter IDs.
            if (!$newsletter) {
                ErrorManager::addError("newsletter_id", "invalid_newsletter_id");
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            // Update newsletter details.
            $newsletter->subject        = isset($data["subject"])        ? $data["subject"]        : $newsletter->subject;
            $newsletter->body           = isset($data["body"])           ? $data["body"]           : $newsletter->body;
            $newsletter->cancelled      = isset($data["cancelled"])      ? $data["cancelled"]      : $newsletter->cancelled;
            $newsletter->sendTime       = isset($data["sendTime"])       ? $data["sendTime"]       : $newsletter->sendTime;
            $newsletter->recipientGroup = isset($data["recipientGroup"]) ? $data["recipientGroup"] : $newsletter->recipientGroup;
            
            // Commit changes.
            $newsletterTable->save($newsletter, $newsletter->id);
            
            // Let client know newsletter update was successful.
            $this->response->setResponseCode(200)->send();
            return ActionState::SUCCESS;
        }
    }