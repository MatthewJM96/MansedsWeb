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
    
    namespace Manseds;
    
    /**
     * Manseds autoloader class. This must be loaded first as
     * loading classes depends on its registration.
     */
    class Autoloader
    {
        /**
         * Singleton insance of Autoloader.
         *
         * @var Manseds\Autoloader
         */
        protected static $instance;
    
        /**
         * Stores whether the autoloader has been setup yet.
         * 
         * @var boolean
         */
        protected $setup = false;    
    
        /**
         * Protected constructor. Use {@link getInstance()} instead.
         */
        protected function __construct()
        {
        }
        
        /**
         * Setup the autoloader.
         */
        public function setupAutoloader()
        {
            if ($this->setup) {
                return;
            }
            
            $this->prepareAutoloader();
            
            $this->setup = true;
        }
        
        /**
         * Protected autoloader setup function. See {@link setupAutoloader()}
         * for the public function.
         */
        protected function prepareAutoloader()
        {
            // Necessary as servers with open_basedir can have incorrect include_path.
            if (@ini_get('open_basedir')) {
                set_include_path(LIBRARY_DIRECTORY . PATH_SEPARATOR . '.');
            }
            else {
                set_include_path(LIBRARY_DIRECTORY . PATH_SEPARATOR . '.' . PATH_SEPARATOR . get_include_path());
            }

            spl_autoload_register('Manseds\\Autoloader::autoload');
        }
        
        /**
         * Autoloads the specified class.
         * 
         * @param string $class Name of class to autoload.
         *
         * @return boolean
         */
        public function autoload($class)
        {
            if (class_exists($class, false) || interface_exists($class, false)) {
                return true;
            }
            
            $filename = $this->getClassFilename($class);
            if (!$filename) {
                return false;
            }
            
            if (file_exists($filename)) {
                include($filename);
                return (class_exists($class, false) || interface_exists($class, false)); 
            }
            
            return false;
        }
        
        /**
         * Gets the filepath for the given class.
         *
         * @param string Name of the class.
         *
         * @return string|false Returns false if the class contains invalid characters.
         */
        public function getClassFilename($class)
        {
            if (preg_match('#[^a-zA-Z0-9_\\\\]#', $class)) {
                return false;
            }
            return LIBRARY_DIRECTORY . '/' . str_replace('\\', '/', $class) . '.php';
        }
        
        /**
         * Gets the autoloader's root directory.
         *
         * @return string
         */
        public function getRootDir()
        {
            return $this->rootDir;
        }
        
        /**
        * Gets the autoloader instance.
        *
        * @return Manseds\Autoloader
        */
        public static final function getInstance()
        {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }