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

    namespace Sycamore\Row;
    
    use Sycamore\Row\Row;
    
    class User extends Row
    {
        // Admin/THIS User Token Required:
        public $name;
        public $preferredName;
        public $dob;
        /// Password Required in case of THIS User Token:
        public $password;
        /// Verification Required (via JWT) in case of THIS User Token:
        public $email;
        public $verified;
        /// Admin Action Required (DISALLOW THIS User Token - unless Admin):
        public $username;
        public $banned;
        // Uneditable in API:
        public $id;
        public $lastOnline;
        ///* Superusers can do anything and have complete rights (i.e. cannot be superseded by any ACL group).
        public $superUser;
        
        // TODO(Matthew): Add functions to get data about user?
    }