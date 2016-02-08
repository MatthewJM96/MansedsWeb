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
    
    use Sycamore\Application;
    use Sycamore\Cron\Job;
    use Sycamore\Utils\Shell;
    
    class Scheduler
    {
        /**
         * Singleton insance of Scheduler.
         *
         * @var \Sycamore\Cron\Scheduler
         */
        protected static $instance;
        
        /**
         * Stores the directory for cron files.
         *
         * @var string 
         */
        protected $cronDir;
        
        /**
         * Acquires the directory for cron files.
         */
        protected function __construct()
        {
            $this->cronDir = Application::getConfig()->cron->directory;
        }
        
        /**
         * Adds the specified cron jobs.
         * 
         * @param string|\Sycamore\Cron\Job|array $cronJobs
         * @param string $cronFile
         * 
         * @return \Sycamore\Cron\Scheduler
         * @throws \InvalidArgumentException
         */
        public function addCronJobs($cronJobs, $cronFile = "cron.txt")
        {
            if ((!is_string($cronJobs) && !($cronJobs instanceof Job) && !is_array($cronJobs)) || !is_string($cronFile)) {
                throw new \InvalidArgumentException("Cron jobs provided were either not in string, \Sycamore\Cron\Job or array form, or provided cron file handle was not in string form.");
            }
            
            // Construct file path.
            $filePath = $this->cronDir . $cronFile;
            
            // Construct command to add cron jobs.
            $addCronJobsCmd = "echo '";
            if ($cronJobs instanceof Job) {
                $addCronJobsCmd .= $cronJobs->getJob() . "\n";
            } else if (is_array($cronJobs)) {
                foreach ($cronJobs as $cronJob) {
                    if (is_string($cronJob)) {
                        $addCronJobsCmd .= $cronJob . "\n";
                    } else if ($cronJob instanceof Job) {
                        $addCronJobsCmd .= $cronJob->getJob() . "\n";
                    }
                }
            } else {
                $addCronJobsCmd .= $cronJobs . "\n";
            }
            $addCronJobsCmd .= "' >> $filePath";
            
            // Construct command to apply cron jobs to crontab.
            $applyCronJobsCmd = "crontab $filePath";
            
            // Prepare temporary file.
            $this->writeCronJobsToFile($filePath);
            
            // Execute addition.
            Shell::execute(NULL, $addCronJobsCmd, $applyCronJobsCmd);
            
            // Remove temporary file.
            $this->removeFile($filePath);
            
            return $this;
        }
        
        /**
         * Removes the specified cron jobs.
         * Cron jobs can be expressed explicitly as a \Sycamore\Cron\Job or as regex string.
         * 
         * @param string|\Sycamore\Cron\Job|array $cronJobs
         * @param string $cronFile
         * 
         * @return \Sycamore\Cron\Scheduler
         */
        public function removeCronJobs($cronJobs, $cronFile = "cron.txt")
        {
            if ((!is_string($cronJobs) && !($cronJobs instanceof Job) && !is_array($cronJobs)) || !is_string($cronFile)) {
                throw new \InvalidArgumentException("Cron jobs provided were either not in string, \Sycamore\Cron\Job or array form, or provided cron file handle was not in string form.");
            }
            
            // Construct file path.
            $filePath = $this->cronDir . $cronFile;
            
            // Prepare temporary file.
            $this->writeCronJobsToFile($filePath);
            
            // Read existing tasks into an array.
            $existingCronJobs = file($filePath, FILE_IGNORE_NEW_LINES);
            
            // If no cron tasks exist, fail.
            if (empty($existingCronJobs)) {
                $this->removeFile($filePath);
                throw new \InvalidArgumentException("No cron jobs exist as provided");
            }
            
            // Get starting count of cron tasks.
            $existingJobCount = count($existingCronJobs);
            
            if ($cronJobs instanceof Job) {
                $existingCronJobs = preg_grep("/" . $cronJobs->getJob() . "/", $existingCronJobs, PREG_GREP_INVERT);
            } else if (is_array($cronJobs)) {
                foreach ($cronJobs as $cronJob) {
                    if ($cronJob instanceof Job) {
                        $existingCronJobs = preg_grep("/" . $cronJobs->getJob() . "/", $existingCronJobs, PREG_GREP_INVERT);
                    } else if (is_string($cronJob)) {
                        $existingCronJobs = preg_grep($cronJobs, $existingCronJobs, PREG_GREP_INVERT);
                    }
                }
            } else {
                $existingCronJobs = preg_grep($cronJobs, $existingCronJobs, PREG_GREP_INVERT);
            }
            
            // Return this, having removed file, and recreated cron tab without removed cron jobs if any were to be removed.
            return ($existingJobCount === count($existingCronJobs)) ? $this->removeFile($filePath) : $this->removeCronTab($filePath)->addCronJobs($existingCronJobs);
        }
        
        /**
         * Writes the cron jobs associated with the file handle to the file.
         * 
         * @param string $filePath
         * 
         * @return \Sycamore\Cron\Scheduler
         */
        protected function writeCronJobsToFile($filePath)
        {
            // Only execute function if cron job file is non-existent.
            if (!$this->cronFileExists($filePath)) {
                $constructCronFileCmd = "crontab -l > $filePath && [ -f $filePath ] || > $filePath";
                Shell::execute(NULL, $constructCronFileCmd);
            }
            return $this;
        }
        
        /**
         * Removes the specified cron file.
         * 
         * @param string $cronFile
         * 
         * @return \Sycamore\Cron\Scheduler
         */
        protected function removeFile($filePath)
        {
            if ($this->cronFileExists($filePath)) {
                Shell::execute(NULL, "rm $filePath");
            }
            return $this;
        }
        
        /**
         * Removes the cront tab.
         * 
         * @param string $cronFile
         * 
         * @return \Sycamore\Cron\Scheduler
         */
        protected  function removeCronTab($filePath)
        {
            $this->removeFile($filePath);
            Shell::execute(NULL, "crontab -r");
            return $this;
        }
        
        /**
         * Determines if the given cron file exists.
         * 
         * @param string $filePath
         * 
         * @return bool
         */
        protected function cronFileExists($filePath)
        {
            return file_exists($filePath);            
        }
        
        /**
        * Gets the scheduler instance.
        *
        * @return \Sycamore\Cron\Scheduler
        */
        public static final function getInstance()
        {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }