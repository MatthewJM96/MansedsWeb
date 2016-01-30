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

    namespace Sycamore\Utils\User;
    
    use Sycamore\Application;

    /**
     * Security holds functions for ensuring the security of the user experience.
     */
    class Security
    {       
        /**
         * Return hashed password.
         *
         * @var string - The password to hash.
         *
         * @return string - The hashed password.
         */
        public static function hashPassword($password)
        {
            return password_hash($password, PASSWORD_DEFAULT, ["cost" => Application::getConfig()->security->passwordHashingStrength ]);
        }
        
        /**
         * Verifies given password is the same as given hash.
         *
         * @var string - The password to check.
         * @var string - The hash to check against.
         *
         * @return boolean
         */
        public static function verifyPassword($password, $hash)
        {
            return password_verify($password, $hash);
        }
        
        /**
         * Verifies if the given password needs rehashing.
         *
         * @var string - The password to check.
         *
         * @return boolean
         */
        public static function passwordNeedsRehash($password)
        {
            return password_needs_rehash($password, PASSWORD_DEFAULT, [ 'cost' => Application::getConfig()->security->passwordHashingStrength ]);
        }
    }