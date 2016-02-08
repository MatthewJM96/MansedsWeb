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

    namespace Sycamore\Cron;
    
    class Job
    {
        const LOCAL = "local";
        const UTC = "utc";
        
        /**
         * Stores when to execute the cron job.
         *
         * @var string
         */
        protected $when;
        
        /**
         * Stores the task of the cron job.
         * 
         * @var string
         */
        protected $task;
        
        /**
         * Stores the timezone to construct the cron job from.
         *
         * @var string
         */
        protected $tz;
        
        /**
         * Empty constructor.
         */
        public function __construct()
        {
        }
        
        /**
         * Get the cron job.
         * 
         * @return string
         */
        public function getJob()
        {
            return $this->when . " " . $this->task;
        }
        
        /**
         * Sets the task of the cron job.
         * 
         * @param string $task
         * 
         * @return \Sycamore\Cron\Job
         * @throws \InvalidArgumentException
         */
        public function setTask($task)
        {
            if (!is_string($task)) {
                throw new \InvalidArgumentException("Expected string for task.");
            }
            
            $this->task = $task;
            
            return $this;
        }
        
        /**
         * Sets when the cron job should be executed.
         * 
         * @param string $when
         * 
         * @return \Sycamore\Cron\Job
         * @throws \InvalidArgumentException
         */
        public function setWhen($when)
        {
            if (!is_string($utcWhen)) {
                throw new \InvalidArgumentException("Expected string for task.");
            }
            
            $this->when = $when;
            
            return $this;
        }
        
        /**
         * Sets when the cron job should be excuted.
         * Providing a UTC date/time of valid format for PHP's strtotime.
         * 
         * @param string $utcWhen
         * 
         * @return \Sycamore\Cron\Job
         * @throws \InvalidArgumentException
         */
        public function setWhenUtc($utcWhen)
        {
            if (!is_string($utcWhen)) {
                throw new \InvalidArgumentException("Expected string for task.");
            }
            
            $timestamp = strtotime($utcWhen);
            if (!$timestamp) {
                throw new \InvalidArgumentException("The provided date/time string was an invalid format.");
            }
            
            $localDateTime = localtime($timestamp, true);
            
            $localWhen = $localDateTime["tm_min"] . " " . $localDateTime["tm_hour"] . " " . $localDateTime["tm_mday"] . " " . $localDateTime["mon"] + 1 . " *";
            
            $this->when = $localWhen;
            
            return $this;
        }
    }
