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

    namespace Sycamore\Mail;
    
    use Sycamore\Application;
    use Sycamore\Mail\Message;
    
    use Zend\Mail\Transport\Factory as TransportFactory;
    
    /**
     * Singleton Mailer.
     */
    class Mailer
    {
        /**
         * Singleton insance of Mailer.
         *
         * @var \Sycamore\Mail\Mailer
         */
        protected static $instance;
        
        /**
         * Transport used for sending emails.
         * 
         * @var \Zend\Mail\Transport\TransportInterface 
         */
        protected $transport;
        
        /**
         * Sends a message via the mailer's transport.
         * 
         * @param \Sycamore\Mail\Message $message
         */
        public function sendMessage(Message $message)
        {
            $this->transport->send($message);
        }
        
        /**
         * Protected constructor. Use {@link getInstance()} instead.
         */
        protected function __construct()
        {
            $this->prepareMailer();
        }
        
        /**
         * Construct the transport for the mailer.
         */
        protected function prepareMailer()
        {
            $cacheManager = new DataCache();
            $cacheManager->initialise("mailer", "transport");

            $cachedResult = $cacheManager->getCachedData();
			
            if (!$cachedResult) {
                $emailConf = Application::getConfig()->email;

                $spec = array();
                $spec["type"] = strtolower($emailConf->transport);
                if ($spec["type"] == "smtp" || $spec["type"] == "file") {
                    $optionsConf = $emailConf->options;
                    $connConf = $optionsConf->connection;

                    $spec["options"] = array();
                    $spec["options"]["name"] = $optionsConf->name;
                    $spec["options"]["host"] = $optionsConf->host;
                    $spec["options"]["port"] = $optionsConf->port;
                    $spec["options"]["connection_class"] = $connConf->class;

                    $spec["options"]["connection_config"] = array();
                    $spec["options"]["connection_config"]["username"] = $connConf->username;
                    $spec["options"]["connection_config"]["password"] = $connConf->password;
                    if (!empty($connConf->ssl)) {
                        $spec["options"]["connection_config"]["ssl"] = $connConf->ssl;
                    }
                }

                $this->transport = TransportFactory::create($spec);

                $cacheManager->setCachedData($this->transport);
            } else {
                $this->transport = $cachedResult;
            }
        }
        
        /**
        * Gets the mailer instance.
        *
        * @return \Sycamore\Mail\Mailer
        */
        public static final function getInstance()
        {
            if (!self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }