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
    
    class ACLGroupRouteMap extends Row
    {
        // Admin token required:
        ///* <0 -> DENY, 0 -> NEITHER, >0 -> ALLOW. ALLOW overridden by DENY.
        public $state;
        // Uneditable in API:
        public $groupId;
        public $routeId;
    }