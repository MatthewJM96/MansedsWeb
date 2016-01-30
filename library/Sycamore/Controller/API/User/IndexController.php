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
    use Sycamore\Model\User;
    use Sycamore\Utils\APIData;
    use Sycamore\Utils\TableCache;
    use Sycamore\Utils\User\Validation as UserValidation;
    use Sycamore\Utils\User\Security as UserSecurity;
    
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
            // Attempt to acquire the provided data.
            $idsJson = filter_input(INPUT_GET, "ids");
            $emailsJson = filter_input(INPUT_GET, "emails");
            $usernamesJson = filter_input(INPUT_GET, "usernames");
            
            // Grab user table.
            $userTable = TableCache::getTableFromCache("UserTable");
            
            // Fetch users with given values, or alternatively 
            $result = null;
            if (!$idsJson && !$emailsJson && !$usernamesJson) {
                $result = $userTable->fetchAll();
            } else {
                // Fetch only subscribers matching given data.
                $ids = APIData::decode($idsJson);
                $emails = APIData::decode($emailsJson);
                $usernames = APIData::decode($usernamesJson);
                
                // Ensure all data provided is correctly batched in arrays.
                if (!is_array($ids) || !is_array($emails) || !is_array($usernames)) {
                    ErrorManager::addError("data_error", "invalid_data_filter_object");
                    $this->prepareExit();
                    return false;
                }
                
                // Ascertain each ID, email and username is valid in type and format.
                foreach ($ids as $id) {
                    if (!is_integer($id)) {
                        ErrorManager::addError("ids_error", "invalid_user_id");
                    }
                }
                foreach ($emails as $email) {
                    if (!is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        ErrorManager::addError("emails_error", "invalid_email_format");
                    }
                }
                foreach ($usernames as $username) {
                    if (!is_string($username)) {
                        ErrorManager::addError("usernames_error", "invalid_username");
                    }
                }
                if (ErrorManager::hasError()) {
                    $this->prepareExit();
                    return false;
                }
                
                // Grab the user table.
                $userTable = TableCache::getTableFromCache("UserTable");
                
                // Fetch matching users, storing with ID as key for simple overwrite to avoid duplicates.
                $result = array();
                $usersByIds = $userTable->getByIds($ids);
                if ($usersByIds instanceof \Iterator) {
                    foreach ($usersByIds as $user) {
                        $result[$user->id] = $user;
                    }
                }
                $usersByUsernames = $userTable->getByUsernames($usernames);
                if ($usersByUsernames instanceof \Iterator) {
                    foreach ($usersByUsernames as $user) {
                        $result[$user->id] = $user;
                    }
                }
                $usersByEmails = $userTable->getByEmails($emails);
                if ($usersByEmails instanceof \Iterator) {
                    foreach ($usersByEmails as $user) {
                        $result[$user->id] = $user;
                    }
                }
                
                // Send the client the fetched users.
                $this->response->setResponseCode(200)->send();
                $this->renderer->render(APIData::encode(array("data" => $result)));
                return true;
            }
        }
        
        /**
         * Executes the process of creating a desired user.
         */
        public function postAction()
        {
            // Ensure all data needed is posted to the server.
            $dataProvided = array (
                array ( "key" => "email", "errorType" => "email_error", "errorKey" => "missing_email" ),
                array ( "key" => "username", "errorType" => "username_error", "errorKey" => "missing_username" ),
                array ( "key" => "password", "errorType" => "password_error", "errorKey" => "missing_password" ),
                array ( "key" => "name",  "errorType" => "name_error",  "errorKey" => "missing_name" )
            );
            if (!$this->dataProvided($dataProvided, INPUT_POST)) {
                $this->prepareExit();
                return false;
            }
            
            // Acquire the sent data, sanitised appropriately.
            $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
            $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_STRING);
            $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);
            $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING);
            
            // Validate provided data.
            UserValidation::validateUsername($username);
            UserValidation::validateEmail($email);
            UserValidation::passwordStrengthCheck($password);
            if (ErrorManager::hasError()) {
                $this->prepareExit();
                return false;
            }
            
            // Construct a new user.
            $user = new User;
            $user->username = $username;
            $user->email = $email;
            $user->password = UserSecurity::hashPassword($password);
            $user->name = $name;
            
            // Save the new user to database.
            $userTable = TableCache::getTableFromCache("UserTable");
            $userTable->save($user);
            
            // Let client know user creation was successful.
            $this->response->setResponseCode(200)->send();
            return true;
        }
        
        /**
         * Executes the process of deleting a desired user.
         */
        public function deleteAction()
        {
            // Ensure data needed is sent to the server.
            $dataProvided = array (
                array ( "key" => "id", "filter" => FILTER_SANITIZE_NUMBER_INT, "errorType" => "user_id_error", "errorKey" => "missing_user_id" )
            );
            if (!$this->dataProvided($dataProvided, INPUT_GET)) {
                $this->prepareExit();
                return false;
            }
            
            // Acquire the sent data, sanitised appropriately.
            $id = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
            
            // Get user with provided delete key.
            $userTable = TableCache::getTableFromCache("UserTable");
            $user = $userTable->getById($id);
            
            // Error out if no subscriber was found to have the delete key.
            if (!$user) {
                ErrorManager::addError("user_id_error", "invalid_user_id");
                $this->prepareExit();
                return false;
            }
            
            // Delete subscriber.
            $userTable->deleteById($user->id);
            
            // Let client know user deletion was successful.
            $this->response->setResponseCode(200)->send();
            return true;
        }
        
        /**
         * Executes the process of updating a desired user.
         */
        public function putAction()
        {
            // Ensure ID has been provided of the user object to be updated.
            $dataProvided = array (
                array ( "key" => "id", "filter" => FILTER_SANITIZE_NUMBER_INT, "errorType" => "user_id_error", "errorKey" => "missing_user_id" )
            );
            if (!$this->dataProvided($dataProvided, INPUT_GET)) {
                $this->prepareExit();
                return false;
            }
            
            
            
            // Acquire the ID, sanitised appropriately.
            $id = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
            
            // Get user with provided delete key.
            $userTable = TableCache::getTableFromCache("UserTable");
            $user = $userTable->getById($id);
            
            // Error out if no subscriber was found to have the delete key.
            if (!$user) {
                ErrorManager::addError("user_id_error", "invalid_user_id");
                $this->prepareExit();
                return false;
            }
            
            
        }
    }
