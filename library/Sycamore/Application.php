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

    namespace Sycamore;

    use Sycamore\Language\En;
    
    use Zend\Config\Config;
    use Zend\Db\Adapter\Adapter;
    
    class Application
    {
        /**
         * Current version of application.
         * 
         * @var string
         */
        protected static $version = "0.0.1";
        
        /**
         * Holds configuration settings of the application.
         *
         * @var \Zend\Config\Config
         */
        protected static $config = null;
        
        /**
         * Holds the language class for this application session.
         *
         * @var \Sycamore\Utils\ArrayObjectAccess
         */
        protected static $language = null;
        
        /**
         * Holds initialised state of application.
         * 
         * @var boolean
         */
        protected static $initialised = false;
        
        /**
         * Holds SSL usage state of application.
         *
         * @var boolean
         */
        protected static $secure = false;
        
        /**
         * Force DB Fetch
         *
         * @var boolean
         */
        protected static $forceDbFetch = true;
        
        /**
         * Adapter object for Zend_Db.
         *
         * @var Zend\Db\Adapter\Adapter
         */
        protected static $dbAdapter;
        
        /**
         * The hashing algorithm to be used for simple hashes.
         * Do NOT use for sensitive data.
         *
         * @var string
         */
        protected static $simpleHashAlgo = "sha256";
        
        /**
         * Passes the config directory path.
         *
         * @return string
         */
        public static function getVersion()
        {
            return self::$version;
        }
        
        /**
         * Passes the configuration settings of the application.
         *
         * @return Zend\Config\Config
         */
        public static function getConfig()
        {
            return self::$config;
        }
        
        /**
         * Passes the language object that contains all language strings.
         *
         * @return Sycamore\Utils\ArrayObjectAccess
         */
        public static function getLanguageObject()
        {
            return self::$language;
        }
        
        /**
         * Passes the initialised state of the application.
         *
         * @return boolean
         */
        public static function isInitialised()
        {
            return self::$initialised;
        }
        
        /**
         * Passes the SSL usage state of the application.
         *
         * @return boolean
         */
        public static function isSecure()
        {
            return self::$secure;
        }
        
        /**
         * Passes whether database fetches should be forced.
         *
         * @return boolean
         */
        public static function forceDbFetch()
        {
            return self::$forceDbFetch;
        }
        
        /**
         * Passes the adapter object for the database.
         *
         * @return Zend\Db\Adapter\Adapter
         */
        public static function getDbAdapter()
        {
            return self::$dbAdapter;
        }
        
        /**
         * Passes the session hashing algorithm.
         *
         * @return string
         */
        public static function getSimpleHashAlgo()
        {
            return self::$simpleHashAlgo;
        }
        
        /**
         * Protected constructor.
         */
        protected function __construct() 
        {
        }
        
        /**
         * Initialises the application.
         */
        public static function initialise()
        {
            if (self::$initialised)
            {
                return;
            }
            
            self::$secure = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
            
            self::$config = self::loadConfig();
            
            $languageClass = "Sycamore\\Language\\".ucfirst(self::$config->language);
            self::$language = new En;
            if (class_exists($languageClass)) {
                self::$language = new $languageClass;
            }
            
            self::prepareDb();
            
            self::$initialised = true;
        }
        
        /**
         * Loads default and user-specific configs and merges them, passing the result.
         *
         * @return \Zend\Config\Config
         */
        protected static function loadConfig()
        {
            if (file_exists(CONFIG_DIRECTORY . "/config.php")) {
                $config = new Config(require(MANSEDS_DIRECTORY . "/default_config.php"), true);
                $userSpecificConfig = new Config(require(CONFIG_DIRECTORY . "/config.php"));
                
                $config->merge($userSpecificConfig)->setReadOnly();
                return $config;
            } else {
                echo "Failed to load ". CONFIG_DIRECTORY . "/config.php file.";
                exit;
            }
        }
        
        protected static function prepareDb()
        {
            $dbConfig = self::$config->db;
            $dbConfigParams = $dbConfig->params;
            self::$dbAdapter = new Adapter( array (
                "driver" => $dbConfig->driver,
                "database" => $dbConfigParams->dbname,
                "username" => $dbConfigParams->username,
                "password" => $dbConfigParams->password,
                "hostname" => $dbConfigParams->host,
                "port" => $dbConfigParams->port,
                "charset" => "utf8"
            ) );
            
            self::$dbAdapter->query("SET @@session.sql_mode='STRICT_ALL_TABLES'");
        }
    }