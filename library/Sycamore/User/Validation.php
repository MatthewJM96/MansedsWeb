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

    namespace Sycamore\User;
    
    use Sycamore\Application;
    use Sycamore\ErrorManager;
    use Sycamore\Utils\TableCache;
    
    /**
     * Validation holds functions for checking of the validity of user account details.
     */
    class Validation
    {
        /**
         * Ascertains the given username is both correctly formatted and unique.
         * 
         * @param string $username
         * @param string $errorType
         * 
         * @return boolean
         */
        public static function validateUsername($username, $errorType = "username")
        {            
            self::isUsername($username, $errorType);
            self::isUniqueUsername($username, $errorType);
            
            return !ErrorManager::hasError();
        }
        
        /**
         * Ascertains the given email is both correctly formatted and unique.
         * 
         * @param string $email
         * @param string $errorType
         * 
         * @return array
         */
        public static function validateEmail($email, $errorType = "email")
        {
            self::isEmail($email, $errorType);
            self::isUniqueEmail($email, $errorType);
            
            return !ErrorManager::hasError();
        }
        
        /**
         * Ascertains the given password is sufficiently strong.
         * 
         * @param string $password
         * @param string $errorType
         * 
         * @return array
         */
        public static function passwordStrengthCheck($password, $errorType = "password")
        {
            $pw = Application::getConfig()->security->password;
            
            // Check password length.
            if (strlen($password) < $pw->minimumLength) {
                ErrorManager::addError($errorType, "error_password_too_short");
            } else if (strlen($password) > $pw->maximumLength) {
                ErrorManager::addError($errorType, "error_password_too_long");
            }
            
            // Check password has a number.
            if (!preg_match("#[0-9]+#", $password)) {
                ErrorManager::addError($errorType, "error_password_missing_number");
            }
            
            // Check password has a letter, capital or otherwise.
            if (!preg_match("#[a-zA-Z]+#", $password)) {
                ErrorManager::addError($errorType, "error_password_missing_letter");
            }
            
            // Check password has a capital letter.
            if ($pw->strictness == "high" || $pw->strictness == "strict") {
                if (!preg_match("#[A-Z]+#", $password)) {
                    ErrorManager::addError($errorType, "error_password_missing_capital_letter");
                }
            }
            
            // Check password has a symbol.
            if ($pw->strictness == "strict") {
                if (!preg_match("#\W+#", $password)) {
                    ErrorManager::addError($errorType, "error_password_missing_symbol");
                }
            }
            
            return !ErrorManager::hasError();
        }
        
        /**
         * Ascertains the given string is in email format.
         *
         * @param string $email
         * @param string $errorType
         *
         * @return array
         */
        public static function isEmail($email, $errorType = "email")
        {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                ErrorManager::addError($errorType, "invalid_email_format");
                return false;
            }
            return true;
        }
        
        /**
         * Ascertains the given email is not already registered in the database of users.
         * 
         * @param string $email
         * @param string $errorType
         * 
         * @return array
         */
        public static function isUniqueEmail($email, $errorType = "email")
        {
            $userTable = TableCache::getTableFromCache("User");
            if (!$userTable->isEmailUnique($email)) {
                ErrorManager::addError($errorType, "none_unique_email");
                return false;
            }
            return true;
        }
        
        /**
         * Ascertains the given username is correctly formatted.
         * 
         * @param string $username
         * @param string $errorType
         * 
         * @return array
         */
        public static function isUsername($username, $errorType = "username")
        {
            $un = Application::getConfig()->username;
            
            if (strlen($username) > $un->maximumLength) {
                ErrorManager::addError($errorType, "error_username_too_long");
            } else if (strlen($username) < $un->minimumLength) {
                ErrorManager::addError($errorType, "error_username_too_short");
            }
            if (preg_match("#[^a-zA-Z0-9\_]+#", $username)) {
                ErrorManager::addError($errorType, "error_username_invalid_character");
            }
            return !ErrorManager::hasError();
        }
        
        /**
         * Ascertains if the given username is not already registered in the user database.
         * 
         * @param string $username
         * @param string $errorType
         * 
         * @return array
         */
        public static function isUniqueUsername($username, $errorType = "username")
        {
            $userTable = TableCache::getTableFromCache("User");
            if (!$userTable->isUsernameUnique($username)) {
                ErrorManager::addError($errorType, "none_unique_username");
                return false;
            }
            return true;
        }
    }