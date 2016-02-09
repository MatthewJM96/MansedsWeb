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
    
    use Sycamore\Application;
    use Sycamore\ErrorManager;
    use Sycamore\Visitor;
    use Sycamore\Controller\Controller;
    use Sycamore\Enums\ActionState;
    use Sycamore\Mail\Mailer;
    use Sycamore\Mail\Message;
    use Sycamore\Utils\ObjectData;
    
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
            
            // Grab the mail message table.
            $mailMessageTable = TableCache::getTableFromCache("MailMessage");
            
            // Fetch newsletters with given values, or all newsletters if no values provided. 
            $result = null;
            $validDataPoint = true;
            if (!$dataJson) {
                $result = $mailMessageTable->getByPurpose("newsletter");
            } else {
                // Fetch only users matching given data.
                $data = APIData::decode($dataJson);
                $ids          = (isset($data["ids"])         ? $data["ids"]         : NULL);
                $cancelled    = (isset($data["cancelled"])   ? $data["cancelled"]   : NULL);
                $sent         = (isset($data["sent"])        ? $data["sent"]        : NULL);
                $sendTimeMin  = (isset($data["sendTimeMin"]) ? $data["sendTimeMin"] : NULL);
                $sendTimeMax  = (isset($data["sendTimeMax"]) ? $data["sendTimeMax"] : NULL);
                
                // TODO(Matthew): Faster with one specialised getBySelect?
                // Fetch matching users, storing with ID as key for simple overwrite to avoid duplicates.
                $result = array();
                $intermediateResult = array();
                if (!is_null($ids)) {
                    $validDataPoint = $mailMessageTable->getByDataPoint($ids, "getByIds", $intermediateResult);
                }
                if (!is_null($cancelled)) {
                    $validDataPoint = $mailMessageTable->getByDataPoint($cancelled, "getByCancelled", $intermediateResult);
                }
                if (!is_null($sent)) {
                    $validDataPoint = $mailMessageTable->getByDataPoint($sent, "getBySent", $intermediateResult);
                }
                if ($sendTimeMin > 0 && $sendTimeMax > 0) {
                    $validDataPoint = $mailMessageTable->getByDataPointRange($sendTimeMin, $sendTimeMax, "getBySendTimeRange", $intermediateResult);
                } else if ($sendTimeMin > 0) {
                    $validDataPoint = $mailMessageTable->getByDataPoint($sendTimeMin, "getBySendTimeMin", $intermediateResult);
                } else if ($sendTimeMax > 0) {
                    $validDataPoint = $mailMessageTable->getByDataPoint($sendTimeMax, "getBySendTimeMax", $intermediateResult);
                }
                foreach($intermediateResult as $mailMessage) {
                    if ($mailMessage->purpose == "newsletter") {
                        $result[] = $mailMessage;
                    }
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
                array ( "key" => "bodyBlocks", "errorType" => "body", "errorKey" => "missing_body_blocks" ),
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
            
            // Fail if invalid data type.
            if (!is_string($data["subject"])) {
                ErrorManager::addError("subject", "invalid_subject");
            }
            if (isset($data["sendTime"]) && !is_string($data["sendTime"])) {
                ErrorManager::addError("send_time", "invalid_send_time");
            }
            if (!is_array($data["bodyBlocks"])) {
                ErrorManager::addError("body", "invalid_body_blocks");
            }
            if (isset($data["attachments"]) && !is_array($data["attachments"])) {
                ErrorManager::addError("attachments", "invalid_attachments");
            }
            if (ErrorManager::hasError()) {
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            // Attempt to construct message.
            $message = new Message();
            $message->prepareBody();
            foreach ($data["bodyBlocks"] as $bodyBlock) {
                if (!is_array($bodyBlock) || count($bodyBlock) != 2) {
                    ErrorManager::addError("body", "invalid_body_block");
                    $this->prepareExit();
                    return ActionState::DENIED;
                }
                $message->addHtmlBlock($bodyBlock[0], $bodyBlock[1]);
            }
            if (isset($data["attachments"])) {
                foreach ($data["attachments"] as $attachment) {
                    if (!is_array($attachment) || count($attachment) != 2) {
                        ErrorManager::addError("attachments", "invalid_attachment");
                        $this->prepareExit();
                        return ActionState::DENIED;
                    }
                    $file = fopen(Application::getConfig()->newsletter->attachmentDirectory . $attachment[1]);
                    $message->addAttachment($attachment[0], $file, $attachment[1]);
                }
            }
            $message->finaliseBody();
                        
            // TODO(Matthew): Add subject, recipients and sender.
            
            // Schedule message if sendTime provided, otherwise send now.
            if (isset($data["sendTime"])) {
                Mailer::getInstance()->sendMessage($message, $data["sendTime"], "newsletter", time());
            } else {
                Mailer::getInstance()->sendMessage($message);
            }
            
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
            $mailMessageTable = TableCache::getTableFromCache("MailMessage");
            $newsletter = $mailMessageTable->getById($data["id"]);
            
            // Error out if no subscriber was found to have the ID.
            if (!$newsletter) {
                ErrorManager::addError("newsletter_id", "invalid_newsletter_id");
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            // Stop a message sending.
            Mailer::getInstance()->stopMessageSend($newsletter, true);
            
            // Delete newsletter.
            $mailMessageTable->deleteById($newsletter->id);
            
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
            $mailMessageTable = TableCache::getTableFromCache("MailMessage");
            $newsletter = $mailMessageTable->getById($data["id"]);
            
            // Handle invalid newsletter IDs.
            if (!$newsletter) {
                ErrorManager::addError("newsletter_id", "invalid_newsletter_id");
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            if (isset($data["cancelled"])) {
                if ($data["cancelled"] === 1) {
                    Mailer::getInstance()->stopMessageSend($newsletter);
                } else if ($data["cancelled"] === 0) {
                    if (!isset($data["sendTime"])) {
                        ErrorManager::addError("cancelled", "cannot_uncancel_newsletter_without_new_send_time");
                        $this->prepareExit();
                        return ActionState::DENIED;
                    }
                }
                $newsletter->cancelled = $data["cancelled"];
            }
            
            if (isset($data["bodyBlocks"]) || isset($data["attachments"])) {
                // TODO(Matthew): Need to use old message due to sender and recipient information.
//                // Grab old message.
//                $message = ObjectData::decode($newsletter->serialisedMessage);
//
//                // Prepare new message.
//                $newMessage = new Message();
//                $newMessage->prepareBody();
//                
//                // Handle HTML blocks.
//                $bodyBlocks = array();
//                if (isset($data["bodyBlocks"])) {
//                    $bodyBlocks = $data["bodyBlocks"];
//                } else {
//                    $bodyBlocks = $message->getConsituentTemplates();
//                }
//                foreach ($bodyBlocks as $bodyBlock) {
//                    if (!is_array($bodyBlock) || count($bodyBlock) != 2) {
//                        ErrorManager::addError("body", "invalid_body_block");
//                        $this->prepareExit();
//                        return ActionState::DENIED;
//                    }
//                    $newMessage->addHtmlBlock($bodyBlock[0], $bodyBlock[1]);
//                }
//                
//                // Handle attachments.
//                if (isset($data["attachments"])) {
//                    foreach ($data["attachments"] as $attachment) {
//                        if (!is_array($attachment) || count($attachment) != 2) {
//                            ErrorManager::addError("attachments", "invalid_attachment");
//                            $this->prepareExit();
//                            return ActionState::DENIED;
//                        }
//                        $file = fopen(Application::getConfig()->newsletter->attachmentDirectory . $attachment[1]);
//                        $newMessage->addAttachment($attachment[0], $file, $attachment[1]);
//                    }
//                } else {
//                    foreach ($message->getAttachments() as $attachment) {
//                        $newMessage->addAttachmentDirect($attachment);
//                    }
//                }
//                
//                // Finalise body of message.
//                $newMessage->finaliseBody();
//                
//                // Set new message into newsletter entry.
//                $newsletter->serialisedMessage = ObjectData::encode($newMessage);
            }
            
            if (isset($data["sendTime"])) {
                try {
                    Mailer::getInstance()->updateMessageSendTime($newsletter, $data["sendTime"]);
                } catch (Exception $ex) {
                    ErrorManager::addError("send_time", "invalid_send_time");
                    $this->prepareExit();
                    return ActionState::DENIED;
                }
            }
            
            // Enter update information.
            $newsletter->lastUpdateTime = time();
            $newsletter->lastUpdatorId = Visitor::getInstance()->id;
            
            // Commit changes.
            $mailMessageTable->save($newsletter, $newsletter->id);
            
            // Let client know newsletter update was successful.
            $this->response->setResponseCode(200)->send();
            return ActionState::SUCCESS;
        }
    }