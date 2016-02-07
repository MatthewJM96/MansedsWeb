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
    use Sycamore\Utils\AbstractTokenManager;
    use Sycamore\Utils\TableCache;
    
    /**
     * Session has functions related to creation and checking of user sessions.
     */
    class Session extends AbstractTokenManager
    {
        /**
         * Creates a new user session.
         *
         * @param string $usernameOrEmail
         * @param bool $extendedSession
         * 
         * @return boolean
         */
        public static function create($usernameOrEmail, $extendedSession = false)
        {
            $userTable = TableCache::getTableFromCache("User");
            if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
                $user = $userTable->getByEmail($usernameOrEmail);
            } else {
                $user = $userTable->getByUsername($usernameOrEmail);
            }
            
            if (!$user) {
                return false;
            }
            
            $sessionLength = Application::getConfig()->security->sessionLengthExtended;
            if (!$extendedSession) {
                $sessionLength = Application::getConfig()->security->sessionLength;
            }
            
            $token = self::constructToken(array (
                "id" => $user->id,
                "name" => $user->username,
                "email" => $user->email,
                "superUser" => $user->superUser
            ), $sessionLength, "user");
            
            return setcookie("SLIS", "$token", time() + $sessionLength, "/", Application::getConfig()->domain, Application::isSecure()); // SLIS -> Sycamore Logged In Session
        }
        
        /**
         * Acquires a user session, if it exists.
         *
         * @return int|array - The token private claim contents on success, else:
         *                       0 for no SLIS set.
         *                      -1 for invalid JWT.
         *                      -2 for invalid JWT due to bad signature.
         *                      -3 for JWT used before nbf or iat.
         *                      -4 for JWT used after exp.
         */
        public static function acquire()
        {
            $slis = filter_input(INPUT_COOKIE, "SLIS");
            if (!$slis) {
                return 0;
            }
            
            return self::verifyToken($slis);
        }
    }