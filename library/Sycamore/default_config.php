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

    /**
     * Default configuration file for the Sycamore application.
     */
    return array (
        "application_title" => "Example", // Title of the application.
        "domain" => "example.com", // Domain of the application.
        "db" => array ( /* Database Details */
            "driver" => "pdo_mysql", // The driver to use for database interfacing.
            "params" => array (
                "host" => "localhost", // The host of the database.
                "port" => "3306", // The port to connect to the database via.
                "username" => "", // The username to connect to the database with.
                "password" => "", // The password to connect to the database with.
                "dbname" => "" // The name of the database.
            ),
            "tablePrefix" => "",
        ),
        "username" => array ( /* Username Details */
            "minimumLength" => 1,
            "maximumLength" => 32,
        ),
        "security" => array ( /* Security Details */
            "enableClickjackingProtection" => true, // If true, prevents clickjacking attacks by not allowing site to be rendered in frame of another site.
            "simpleHashAlgorithm" => "sha256", // The hashing algorithm to be used for simple hashes - no sensitive data is hashed using this.
            "sessionPrivateKey" => "CHANGE_THIS", // The key used for signing JWTs. Do NOT share.
            "sessionHashAlgorithm" => "HS256", // The hashing algorithm used for the signing of JWTs.
            "sessionLength" => 43200/*12 Hours*/, // How long should log in session last if not extended?
            "sessionLengthExtended" => 2629740,/*~1 Month*/ // How long should an extended log-in session last?
            "password" => array ( /* Password Security Details */
                "strictness" => "normal", // How secure must passwords be? Normal -> >8 characters long, include numbers and letters. High -> Normal + include capital letter. Strict -> High + include symbol. Retroactively checks passwords.
                "hashingStrength" => 11, // How strong should the hash be? Higher is stronger, but requires more CPU time.
                "minimumLength" => 8,
                "maximumLength" => 48,
            ),
        ),
        "language" => "en", // The language of the application.
        "cache" => array ( /* Cache Details */
            "timeToLive" => 1800/*30 Mins*/, // How long does the data live for in cache?
            "namespace" => "oscache" // Namespace in which all application data is cached.
        )
    );