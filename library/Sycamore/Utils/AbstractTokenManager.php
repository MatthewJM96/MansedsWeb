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

    namespace Sycamore\Utils;
    
    use Firebase\JWT\BeforeValidException;
    use Firebase\JWT\ExpiredException;
    use Firebase\JWT\JWT;
    use Firebase\JWT\SignatureInvalidException;
    
    abstract class AbstractTokenManager
    {
        /**
         * Constructs a JWT token.
         * 
         * @param mixed $applicationPayload
         * @param int $tokenLifetime
         * @param string $prn
         * @param string $key
         * 
         * @return string
         */
        protected static function constructToken($applicationPayload, $tokenLifetime, $prn, $key = null)
        {
            if (!is_array($applicationPayload)) {
                $applicationPayload = (array) $applicationPayload;
            }
            
            $time = time();
            $payload = array (
                "iss" => Application::getConfig()->domain,
                "aud" => Application::getConfig()->domain,
                "iat" => $time,
                "exp" => $time + $tokenLifetime,
                "nbf" => $time,
                "prn" => "$prn",
                "jti" => Random::randomString(12),
                Application::getConfig()->domain => $applicationPayload
            );
            
            $token = JWT::encode($payload, $key ?: Application::getConfig()->security->tokenPrivateKey, Application::getConfig()->security->tokenHashAlgorithm);
            
            return $token;
        }
        
        /**
         * Verifies the given token.
         *
         * @param string $token
         * @param string $key
         * 
         * @return int|array - The token private claim contents on success, else:
         *                      -1 for invalid JWT.
         *                      -2 for invalid JWT due to bad signature.
         *                      -3 for JWT used before nbf or iat.
         *                      -4 for JWT used after exp.
         */
        protected static function verifyToken($token, $key = null)
        {
            try {
                $tokenDecoded = (array) JWT::decode($token, $key ?: Application::getConfig()->security->tokenPrivateKey, array ( Application::getConfig()->security->tokenHashAlgorithm ));
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
            
            return $tokenDecoded[Application::getConfig()->domain];
        }
    }