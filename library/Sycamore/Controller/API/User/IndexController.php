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
    use Sycamore\Model\User;
    use Sycamore\Utils\APIData;
    use Sycamore\Utils\TableCache;
    use Sycamore\User\Validation as UserValidation;
    use Sycamore\User\Security as UserSecurity;
    use Sycamore\Visitor;
    
    /**
     * Controller for handling newsletters.
     */
    class IndexController extends Controller
    {
        /**
         * Executes the process of acquiring a desired user.
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
            
            // Grab the user table.
            $userTable = TableCache::getTableFromCache("User");
            
            // Fetch users with given values, or all users if no values provided. 
            $result = null;
            $validDataPoint = true;
            if (!$dataJson) {
                $result = $userTable->fetchAll();
            } else {
                // Fetch only users matching given data.
                $data = APIData::decode($dataJson);
                $ids        = (isset($data["ids"])       ? $data["ids"]       : NULL);
                $emails     = (isset($data["emails"])    ? $data["emails"]    : NULL);
                $usernames  = (isset($data["usernames"]) ? $data["usernames"] : NULL);
                
                // Ensure all data provided is correctly batched in arrays.
//                if (!is_array($ids) || !is_array($emails) || !is_array($usernames)) {
//                    ErrorManager::addError("data", "invalid_data_filter_object");
//                    $this->prepareExit();
//                    return ActionState::DENIED;
//                }
                
                // Fetch matching users, storing with ID as key for simple overwrite to avoid duplicates.
                $result = array();
                if (!is_null($ids)) {
                    $validDataPoint = $userTable->getByDataPoint($ids, "getByIds", $result);
                }
                if (!is_null($usernames)) {
                    $validDataPoint = $userTable->getByDataPoint($usernames, "getByUsernames", $result);
                }
                if (!is_null($emails)) {
                    $validDataPoint = $userTable->getByDataPoint($emails, "getByEmails", $result);
                }
            }
            
            // If result is bad, input must have been bad.
            if (is_null($result) || !$validDataPoint) {
                ErrorManager::addError("data", "invalid_data_filter_object");
                $this->prepareExit();
                return ActionState::DENIED;
            }

            // Send the client the fetched users.
            $this->response->setResponseCode(200)->send();
            $this->renderer->render(APIData::encode(array("data" => $result)));
            return ActionState::SUCCESS;
        }
        
        /**
         * Executes the process of creating a desired user.
         */
        public function postAction()
        {
            // Prepare data holder.
            $data = array();
            
            // Ensure all data needed is posted to the server.
            $dataProvided = array (
                array ( "key" => "email", "errorType" => "email", "errorKey" => "missing_email" ),
                array ( "key" => "username", "errorType" => "username", "errorKey" => "missing_username" ),
                array ( "key" => "password", "errorType" => "password", "errorKey" => "missing_password" ),
                array ( "key" => "name",  "errorType" => "name",  "errorKey" => "missing_name" )
            );
            if (!$this->fetchData($dataProvided, INPUT_POST, $data)) {
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
                    ErrorManager::addError("permission", "permission_missing");
                    $this->prepareExit();
                    return ActionState::DENIED;
                }
            }
            
            // Validate provided data.
            UserValidation::validateUsername($data["username"]);
            UserValidation::validateEmail($data["email"]);
            UserValidation::passwordStrengthCheck($data["password"]);
            if (ErrorManager::hasError()) {
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            // Construct a new user.
            $user = new User;
            $user->username = $data["username"];
            $user->email = $data["email"];
            $user->password = UserSecurity::hashPassword($data["password"]);
            $user->name = $data["name"];
            
            // Save the new user to database.
            $userTable = TableCache::getTableFromCache("User");
            $userTable->save($user);
            
            // Let client know user creation was successful.
            $this->response->setResponseCode(200)->send();
            return ActionState::SUCCESS;
        }
        
        /**
         * Executes the process of deleting the desired user.
         */
        public function deleteAction()
        {
            // Prepare data holder.
            $data = array();
            
            // Ensure data needed is sent to the server.
            $dataProvided = array (
                array ( "key" => "id", "errorType" => "user_id", "errorKey" => "missing_user_id" )
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
                    // TODO(Matthew): How to delete own account?
                    ErrorManager::addError("permission", "permission_missing");
                    $this->prepareExit();
                    return ActionState::DENIED;
                }
            }
            
            // Get user with provided ID.
            $userTable = TableCache::getTableFromCache("User");
            $user = $userTable->getById($data["id"]);
            
            // Error out if no subscriber was found to have the ID.
            if (!$user) {
                ErrorManager::addError("user_id", "invalid_user_id");
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            // Delete subscriber.
            $userTable->deleteById($user->id);
            
            // Let client know user deletion was successful.
            $this->response->setResponseCode(200)->send();
            return ActionState::SUCCESS;
        }
        
        /**
         * Executes the process of updating a user.
         * Only handles the following data points of a user:
         *  - Name
         *  - Preferred Name
         *  - Date of Birth
         *  - Password
         */
        public function putAction()
        {
            // Prepare data holder.
            $data = array();
            
            // Ensure ID has been provided of the user object to be updated.
            $dataProvided = array (
                array ( "key" => "id", "errorType" => "user_id", "errorKey" => "missing_user_id" )
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
            
            // Get user with provided user ID.
            $userTable = TableCache::getTableFromCache("User");
            $user = $userTable->getById($data["id"]);
            
            // Handle invalid user IDs.
            if (!$user) {
                ErrorManager::addError("user_id", "invalid_user_id");
                $this->prepareExit();
                return ActionState::DENIED;
            }
            
            // Check new and old passwords are valid if a new password is provided.
            if ($data["newPassword"]) {
                UserValidation::passwordStrengthCheck($data["newPassword"]);
                if (Visitor::getInstance()->id == $id) {
                    if (!$data["password"]) {
                        ErrorManager::addError("password", "old_password_missing");
                    } else if (UserSecurity::verifyPassword($data["password"], $user->password)) {
                        ErrorManager::addError("password", "old_password_incorrect");
                    }
                    if (ErrorManager::hasError()) {
                        $this->prepareExit();
                        return ActionState::DENIED;
                    }
                }
                $user->password = UserSecurity::hashPassword($data["newPassword"]);
            }
            
            // Update user details.
            $user->name = $data["name"];
            $user->preferredName = $data["preferredName"];
            $user->dateOfBirth = $data["dateOfBirth"];
            
            // Commit changes.
            $userTable->save($user, $user->id);
            
            // Let client know user update was successful.
            $this->response->setResponseCode(200)->send();
            return ActionState::SUCCESS;
        }
    }
