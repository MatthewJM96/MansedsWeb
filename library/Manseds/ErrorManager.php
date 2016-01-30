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
    
    use Manseds\Application;

    /**
     * ErrorManager encapsulates error generation for controllers.
     */
    class ErrorManager
    {
        /**
         * Error collection for 
         *
         * @var array
         */
        protected static $errors = array ( "success" => false );
        
        /**
         * Protected constructor. Use {@link getInstance()} instead.
         */
        protected function __construct()
        {
        }
        
        /**
         * Add an error to the collection of errors.
         * 
         * @param string $errorType
         * @param string $errorMessageKey
         */
        public static function addError($errorType, $errorMessageKey)
        {
            // Grab raw error message.
            $errorMessageRaw = Application::getLanguageObject()[$errorMessageKey];
            
            // Grab parameter paths embedded in message.
            $params = preg_split("/{((?:[a-zA-Z]+[\\\/])+[a-zA-Z]+)}/g", $errorMessageRaw);            
            foreach ($params as $param) {
                // Split parameter path up into parts.
                $path = preg_split("/([a-zA-Z]+)/g", $param);
                
                // Set starting value from path and then iterate into final value.
                $value = Application::getConfig()[$path[0]];
                array_shift($path);
                foreach ($path as $pathPart) {
                    $value = $value[$pathPart];
                }
                
                // Replace param path with the value.
                $errorMessage = str_replace("{" . $param . "}", strval($value), $errorMessageRaw);
            }
            
            // Insert the formatted error message into errors array.
            self::$errors["errors"][] = array ( "type" => $errorType, "message" => $errorMessage );
        }
        
        /**
         * Ascertains if one or more errors have been added.
         * 
         * @return boolean
         */
        public static function hasError()
        {
            return (isset(self::$errors["errors"]) && !empty(self::$errors["errors"]));
        }
        
        /**
         * Constants for determining if errors should be cleared on returning them from getErrors().
         */
        const KEEP_ERRORS = 0;
        const DELETE_ERRORS = 1;
        
        /**
         * Returns the collection of errors stored in the ErrorManager.
         * 
         * @param int $clearErrors
         * 
         * @return array
         */
        public static function getErrors($clearErrors = self::KEEP_ERRORS)
        {
            $errors = self::$errors;
            if ($clearErrors == self::DELETE_ERRORS) {
                self::$errors = array ( "success" => false );
            }
            return $errors;            
        }
    }