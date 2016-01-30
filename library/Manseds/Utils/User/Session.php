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

    namespace Manseds\Utils\User;

    use Manseds\Application;
    use Manseds\Utils\Random;
    use Manseds\Utils\TableCache;
    
    use Firebase\JWT\BeforeValidException;
    use Firebase\JWT\ExpiredException;
    use Firebase\JWT\JWT;
    use Firebase\JWT\SignatureInvalidException;
    
    /**
     * Session has functions related to creation and checking of user sessions.
     */
    class Session
    {
        /**
         * Creates a new user session.
         *
         * @var string - The username of the session's user.
         * 
         * @return boolean
         */
        public static function create($usernameOrEmail, $extendedSession = false)
        {
            $userTable = TableCache::getTableFromCache("UserTable");
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
            
            $time = time();
            $sessionPayload = array (
                "iss" => Application::getConfig()->domain,
                "aud" => Application::getConfig()->domain,
                "iat" => $time,
                "exp" => $time + $sessionLength,
                "nbf" => $time,
                "prn" => "user",
                "jti" => Random::randomString(12),
                Application::getConfig()->domain => array (
                    "id" => $user->id,
                    "name" => $user->username,
                    "email" => $user->email
                )
            );
            
            $token = JWT::encode($sessionPayload, Application::getConfig()->security->sessionPrivateKey, Application::getConfig()->security->sessionHashAlgorithm);
                        
            setcookie("MLIS", "$token", time() + $sessionLength, "/", Application::getConfig()->domain, Application::isSecure()); // MLIS -> Manseds Logged In Session
        }
        
        /**
         * Acquires a user session, if it exists.
         *
         * @var string - The session cookie to validate.
         *
         * @return int|array - The token private claim contents on success, else:
         *                      -1 for invalid JWT.
         *                      -2 for invalid JWT due to bad signature.
         *                      -3 for JWT used before nbf or iat.
         *                      -4 for JWT used after exp.
         */
        public static function acquire()
        {
            $mlis = filter_input(INPUT_COOKIE, "MLIS");
            if (!$mlis) {
                return 0;
            }
            
            try {
                $token = (array) JWT::decode($mlis, Application::getConfig()->security->sessionPrivateKey, array ( Application::getConfig()->security->sessionHashAlgoirthm ));
            } catch (\DomainException $ex) {
                logCriticalError($ex);
                exit();
            } catch (\UnexpectedValueException $ex) {
                return -1;
            } catch (SignatureInvalidException $ex) {
                return -2;
            } catch (BeforeValidException $ex) {
                return -3;
            } catch (ExpiredException $ex) {
                return -4;
            }
            
            return $token[Application::getConfig()->domain];
        }
    }