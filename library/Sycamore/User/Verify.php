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
    
    /**
     * Verify has functions related to creation and checking of verification tokens.
     */
    class Verify extends AbstractTokenManager
    {
        /**
         * Constructs a verification token.
         * 
         * @param int $userId
         * @param mixed $itemToVerify
         * 
         * @return string
         */
        public static function create($userId, $itemToVerify)
        {
            return self::constructToken(array (
                "id" => $userId,
                "itemToVerify" => $itemToVerify
            ), Application::getConfig()->security->verifyTokenLifetime, "verify");
        }
        
        /**
         * Verifies the verification token.
         *
         * @param string $token
         * 
         * @return int|array - The token private claim contents on success, else:
         *                      -1 for invalid JWT.
         *                      -2 for invalid JWT due to bad signature.
         *                      -3 for JWT used before nbf or iat.
         *                      -4 for JWT used after exp.
         */
        public static function verify($token, $expected)
        {
            $result = self::verifyToken($token);
            if ($result) {
                return $result["itemToVerify"] == $expected;
            }
            return $result;
        }
    }