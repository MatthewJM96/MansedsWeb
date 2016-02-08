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
    
    use Zend\Mail\Message as ZendMessage;
    use Zend\Mime;
    
    class Message extends ZendMessage
    {
        protected $bodyParts = array ();
        
        protected $attachments = array();
        
        protected $finalised = false;
        
        public function prepareBody()
        {
            // Get header HTML and general CSS.
        }
        
        public function addBlock($blockName, $content)
        {
            // Get block and inject content.
        }
        
        /**
         * Adds an attachment to the message.
         * 
         * @param string $type
         * @param \resource $file
         * @param string $filename
         * 
         * @return \Sycamore\Mail\Message
         */
        public function addAttachment($type, \resource $file, $filename, $encoding = Mime\Mime::ENCODING_BASE64)
        {
            $attachment = new Mime\Part($file);
            $attachment->type = $type;
            $attachment->filename = $filename;
            $attachment->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;
            $attachment->encoding = $encoding;
            
            $this->attachments[] = $attachment;
            
            return $this;
        }
        
        /**
         * Constructs and sets the body for the message.
         * Throws an exception if the function has already been called on a given instance.
         * 
         * @return \Sycamore\Mail\Message
         * @throws \Exception
         */
        public function finaliseBody()
        {
            if (!$this->finalised) {
                throw new \Exception("Body has already been finalised.");
            }
            
            // Construct body.
            $body = new Mime\Message();
            $body->setParts($this->bodyParts);
            
            // Set body.
            $this->setBody($body);
            
            // Set finalised flag and return.
            $this->finalised = true;
            return $this;
        }
    }
    