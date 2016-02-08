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
    
    class Shell
    {
        /**
         * Executes a supplied shell command at the supplied directory.
         * 
         * @param string $dir
         * @param string args
         * 
         * @throws \InvalidArgumentException
         * @throws Exception
         */
        public static function execute($dir)
        {
            if (!is_null($dir) && !is_string($dir)) {
                throw new \InvalidArgumentException("Directory supplied was not a string.");
            }
            
            $argCount = func_num_args() - 1;
            if ($argCount <= 0) {
                throw new Exception("No arguments provided; nothing to execute.");
            }
            
            $argVariables = func_get_args();
            array_shift($argVariables);
            
            $command = $argCount > 1 ? implode(" && ", $argVariables) : $argVariables[0];
            
            if (is_string($dir)) {
                chdir($dir);
            }
            shell_exec($command);
        }
    }