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

    namespace Sycamore\Controller\API\User;
    
    use Sycamore\ErrorManager;
    use Sycamore\Visitor;
    use Sycamore\Controller\Controller;
    use Sycamore\Enums\ActionState;
    use Sycamore\Row\Ban;
    use Sycamore\Utils\APIData;
    use Sycamore\Utils\TableCache;
    
    /**
     * Controller for handling banning of users.
     */
    class BanController extends Controller
    {
        /**
         * Executes the process of acquiring a collection of banned users.
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
                    ErrorManager::addError("permission", "permission_missing");
                    $this->prepareExit();
                    return ActionState::DENIED;
                }
            }
            
            // Attempt to acquire the provided data.
            $dataJson = filter_input(INPUT_GET, "data");
            
            // Grab the ban table.
            $banTable = TableCache::getTableFromCache("Ban");
            
            // Fetch bans with given values, or all bans if no values provided.
            $result = null;
            $validDataPoint = true;
            if (!$dataJson) {
                $result = $banTable->fetchAll();
            } else {
                // Fetch only bans matching given data.
                $data = APIData::decode($dataJson);
                $state           = (isset($data["state"])           ? $data["state"]           : NULL);
                $banIds          = (isset($data["banIds"])          ? $data["banIds"]          : NULL);
                $creatorIds      = (isset($data["creatorIds"])      ? $data["creatorIds"]      : NULL);
                $bannedIds       = (isset($data["bannedIds"])       ? $data["bannedIds"]       : NULL);
                $creationTimeMin = (isset($data["creationTimeMin"]) ? $data["creationTimeMin"] : NULL);
                $creationTimeMax = (isset($data["creationTimeMax"]) ? $data["creationTimeMax"] : NULL);
                $expiryTimeMin   = (isset($data["expiryTimeMin"])   ? $data["expiryTimeMin"]   : NULL);
                $expiryTimeMax   = (isset($data["expiryTimeMax"])   ? $data["expiryTimeMax"]   : NULL);
                
                // Ensure all data provided is expected types.
//                if (!is_int($state) || !is_array($banIds) || !is_array($creatorIds) || !is_array($bannedIds) ||
//                        !is_int($creationTimeMin) || !is_int($creationTimeMax) || !is_int($expiryTimeMin) || !is_int($expiryTimeMax)) {
//                    ErrorManager::addError("data", "invalid_data_filter_object");
//                    $this->prepareExit();
//                    return ActionState::DENIED;
//                }
                
                // Fetch matching bans, storing with ID as key for simple overwrite to avoid duplicates.
                $result = array();
                if (!is_null($state)) {
                    $validDataPoint = $banTable->getByDataPoint($state, "getByState", $result);
                }
                if (!is_null($banIds)) {
                    $validDataPoint = $banTable->getByDataPoint($banIds, "getByIds", $result);
                }
                if (!is_null($creatorIds)) {
                    $validDataPoint = $banTable->getByDataPoint($creatorIds, "getByCreators", $result);
                }
                if (!is_null($bannedIds)) {
                    $validDataPoint = $banTable->getByDataPoint($bannedIds, "getByBanned", $result);
                }
                if ($creationTimeMin > 0 && $creationTimeMax > 0) {
                    $validDataPoint = $banTable->getByDataPointRange($creationTimeMin, $creationTimeMax, "getByCreationTimeRange", $result);
                } else if ($creationTimeMin > 0) {
                    $validDataPoint = $banTable->getByDataPoint($creationTimeMin, "getByCreationTimeMin", $result);
                } else if ($creationTimeMax > 0) {
                    $validDataPoint = $banTable->getByDataPoint($creationTimeMax, "getByCreationTimeMax", $result);
                }
                if ($expiryTimeMin > 0 && $expiryTimeMax > 0) {
                    $validDataPoint = $banTable->getByDataPointRange($expiryTimeMin, $expiryTimeMax, "getByExpiryTimeRange", $result);
                } else if ($expiryTimeMin > 0) {
                    $validDataPoint = $banTable->getByDataPoint($expiryTimeMin, "getByExpiryTimeMin", $result);
                } else if ($expiryTimeMax > 0) {
                    $validDataPoint = $banTable->getByDataPoint($expiryTimeMax, "getByExpiryTimeMax", $result);
                }
            }
            
            // If result is bad, input must have been bad.
            if (is_null($result) || !$validDataPoint) {
                ErrorManager::addError("data", "invalid_data_filter_object");
                $this->prepareExit();
                return ActionState::DENIED;
            }

            // Send the client the fetched bans.
            $this->response->setResponseCode(200)->send();
            $this->renderer->render(APIData::encode(array("data" => $result)));
            return ActionState::SUCCESS;
        }
        
        /**
         * Executes the creation process for creating a new ban entry.
         * 
         * @return boolean
         */
        public function postAction()
        {
            // Ensure all data needed is posted to the server.
            $dataProvided = array (
                array ( "key" => "bannedId", "errorType" => "banned_id", "errorKey" => "missing_banned_id" ),
                array ( "key" => "expiryTime",  "errorType" => "expiry_time",  "errorKey" => "missing_expiry_time" )
            );
            if (!$this->fetchData($dataProvided, INPUT_POST)) {
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
            
            // Acquire the sent data, sanitised appropriately.
            $bannedId = filter_input(INPUT_POST, "bannedId", FILTER_SANITIZE_NUMBER_INT);
            $expiryTime = filter_input(INPUT_POST, "expiryTime", FILTER_SANITIZE_NUMBER_INT);
            
            // Grab the user and ban tables.
            $userTable = TableCache::getTableFromCache("User");
            $banTable = TableCache::getTableFromCache("Ban");
            
            $bannedUser = $userTable->getById($bannedId);
            if (!$bannedUser) {
                ErrorManager::addError("banned_user", "banned_user_non_existent");
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            // Update and save user state.
            $bannedUser->banned = 1;
            $userTable->save($bannedUser, $bannedUser->id);
            
            // Construct ban entry.
            $ban = new Ban;
            $ban->status = 1;
            $ban->creationTime = time();
            $ban->expiryTime = $expiryTime;
            $ban->bannedId = $bannedId;
            $ban->creatorId = Visitor::getInstance()->id;
            
            // Save new ban.
            $banTable->save($ban);
            
            // Let client know newsletter subscription creation was successful.
            $this->response->setResponseCode(200)->send();
            return ActionState::SUCCESS;
        }
        
        /**
         * Executes the process of updating a ban entry.
         * Only handles the following data points of a ban:
         *  - Status
         */
        public function putAction()
        {
            // Prepare data holder.
            $data = array();
            
            // Ensure ID has been provided of the user object to be updated.
            $dataProvided = array (
                array ( "key" => "id", "errorType" => "ban_id", "errorKey" => "missing_ban_id" )
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
            
            // Grab the needed tables.
            $banTable = TableCache::getTableFromCache("Ban");
            $userTable = TableCache::getTableFromCache("User");
            
            // Get ban with given ban ID.
            $ban = $banTable->getById($data["id"]);
            
            // Handle invalid ban IDs.
            if (!$ban) {
                ErrorManager::addError("ban_id", "invalid_ban_id");
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            // Get banned user.
            $user = $userTable->getById($ban->bannedId);
            
            if ($data["state"] != 0 || $data["state"] != 1) {
                ErrorManager::addError("ban_state", "invalid_ban_state");
                $this->prepareExit();
                return ActionState::DENIED;
            } else {
                $user->banned = $data["state"];
                $ban->state = $data["state"];
                
                // Save user and ban.
                $userTable->save($user, $user->id);
                $banTable->save($ban, $ban->id);
            }
            
            // Let client know user update was successful.
            $this->response->setResponseCode(200)->send();
            return ActionState::SUCCESS;
        }
    }
    