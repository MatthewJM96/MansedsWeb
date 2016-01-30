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
    
    class Timer
    {
        /**
         * The unix timestamp at the start of the Timer in microseconds.
         *
         * @var float
         */
        protected $startTime = 0;
        
        /**
         * The duration the timer ran for in microseconds.
         *
         * @var float
         */
        protected $duration = 0;
        
        public function __construct()
        {
        }
        
        public function start()
        {
            $this->startTime = microtime(true);
        }
        
        public function stop()
        {
            $this->duration += microtime(true) - $this->startTime;
        }
        
        public function getDuration()
        {
            return $this->duration;
        }
    }