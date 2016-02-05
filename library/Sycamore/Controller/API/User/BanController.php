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
    use Sycamore\Controller\Controller;
    use Sycamore\Enums\ActionState;
    use Sycamore\Row\Ban;
    use Sycamore\Row\User;
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
                    ErrorManager::addError("permission_error", "permission_missing");
                    $this->prepareExit();
                    return ActionState::DENIED;
                }
            }
            
            // Attempt to acquire the provided data.
            $dataJson = filter_input(INPUT_GET, "data");
            
            // Grab the ban table.
            $banTable = TableCache::getTableFromCache("BanTable");
            
            // Fetch bans with given values, or all bans if no values provided.
            $result = null;
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
//                    ErrorManager::addError("data_error", "invalid_data_filter_object");
//                    $this->prepareExit();
//                    return ActionState::DENIED;
//                }
                
                // Fetch matching bans, storing with ID as key for simple overwrite to avoid duplicates.
                $result = array();
                if (!is_null($state)) {
                    $bansByState = $banTable->getByState($state);
                    foreach ($bansByState as $ban) {
                        $result[$ban->id] = $ban;
                    }
                }
                if (!is_null($banIds)) {
                    $bansByBanIds = $banTable->getByIds($banIds);
                    foreach ($bansByBanIds as $ban) {
                        $result[$ban->id] = $ban;
                    }
                }
                if (!is_null($creatorIds)) {
                    $bansByCreatorIds = $banTable->getByCreators($creatorIds);
                    foreach ($bansByCreatorIds as $ban) {
                        $result[$ban->id] = $ban;
                    }
                }
                if (!is_null($bannedIds)) {
                    $bansByBannedIds = $banTable->getByState($bannedIds);
                    foreach ($bansByBannedIds as $ban) {
                        $result[$ban->id] = $ban;
                    }
                }
                $bansByCreationTime = array();
                if ($creationTimeMin > 0 && $creationTimeMax > 0) {
                    $bansByCreationTime = $banTable->getByCreationTimeRange($creationTimeMin, $creationTimeMax);
                } else if ($creationTimeMin > 0) {
                    $bansByCreationTime = $banTable->getByCreationTimeMin($creationTimeMin);
                } else if ($creationTimeMax > 0) {
                    $bansByCreationTime = $banTable->getByCreationTimeMax($creationTimeMax);
                }
                foreach ($bansByCreationTime as $ban) {
                    $result[$ban->id] = $ban;
                }
                $bansByExpiryTime = array();
                if ($expiryTimeMin > 0 && $expiryTimeMax > 0) {
                    $bansByExpiryTime = $banTable->getByExpiryTimeRange($expiryTimeMin, $expiryTimeMax);
                } else if ($expiryTimeMin > 0) {
                    $bansByExpiryTime = $banTable->getByExpiryTimeMin($expiryTimeMin);
                } else if ($expiryTimeMax > 0) {
                    $bansByExpiryTime = $banTable->getByExpiryTimeMax($expiryTimeMax);
                }
                foreach ($bansByExpiryTime as $ban) {
                    $result[$ban->id] = $ban;
                }
                
                // Send the client the fetched bans.
                $this->response->setResponseCode(200)->send();
                $this->renderer->render(APIData::encode(array("data" => $result)));
                return ActionState::SUCCESS;
            }
        }
    }