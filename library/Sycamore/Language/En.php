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

    namespace Sycamore\Language;
    
    use Sycamore\Utils\ArrayObjectAccess;

    /**
     * English language file for Sycamore.
     */
    class En extends ArrayObjectAccess
    {
        protected $data = array (
            "invalid_email_format" => "The email address provided is not a valid format.\nCorrect format: \"john.smith@example.com\".",
            "none_unique_email" => "The email address provided is already used by another account.",
            "error_username_too_short" => "Usernames can be no shorter than {username\minimumLength} characters.",
            "error_username_too_long" => "Usernames can be no longer than {username\maximumLength} characters.",
            "error_username_invalid_character" => "Usernames can only have the characters A-Z, 0-9 and the underscore character.",
            "error_password_too_short" => "Your password must be at least {seurity\password\minimumLength} characters long.",
            "error_password_too_long" => "Your password must be at most {security\password\maximumLength} characters long.",
            "error_password_missing_number" => "Your password must have at least one number.",
            "error_password_missing_letter" => "Your password must have at least one letter.",
            "error_password_missing_capital_letter" => "Your password must have at least one capital letter.",
            "suggestion_password_missing_capital_letter" => "Your password could be made more secure with a capital letter.",
            "error_password_missing_symbol" => "Your password must have at least one symbol.",
            "suggestion_password_missing_symbol" => "Your password could be made more secure with a symbol.",
            "invalid_login_details" => "Your login details were incorrect.",
            "missing_username" => "A username must be provided.",
            "missing_email" => "An email must be provided.",
            "missing_password" => "A password must be provided.",
            "missing_username_or_email" => "A username or email must be provided.",
            "missing_name" => "A name must be provided.",
            "missing_newsletter_subscriber_delete_key" => "A subscriber delete key must be provided.",
            "invalid_newsletter_subscriber_delete_key" => "The subscriber delete key provided was invalid.",
            "invalid_emails_filter_object" => "The emails parameter expects a json encoded array of emails to fetch.",
            "invalid_data_filter_object" => "The data parameters expect json encoded arrays.",
            "invalid_username" => "The username provided was not a valid username.",
            "invalid_user_id" => "The user ID provided was not a valid ID.",
            "missing_user_id" => "No user ID was provided.",
            "permission_missing" => "You are missing the needed permission to complete this action.",
            "missing_banned_id" => "No ID was provided for the user to be banned.",
            "missing_expiry_time" => "No expiry time was provided.",
            "banned_user_non_existent" => "The user to be banned does not exist.",
            "missing_ban_id" => "A ban ID needs to be provided.",
            "invalid_ban_id" => "The ban ID provided was invalid.",
        );
        
        public function __construct()
        {
        }
    }