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
    
    use Sycamore\Utils\TableCache;
    
    use Zend\Mail\Message as ZendMessage;
    use Zend\Mime;
    
    class Message extends ZendMessage
    {
        protected $bodyParts = array ();
        
        protected $attachments = array();
        
        protected $finalised = false;
        
        protected $templateParts = array();
        
        /**
         * Prepares the construction of the body.
         */
        public function prepareBody()
        {
            $this->addHtmlBlock("header");
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
            $body->setParts(array_merge($this->bodyParts, $this->attachments));
            
            // Set body.
            $this->setBody($body);
            
            // Set finalised flag and return.
            $this->finalised = true;
            return $this;
        }
        
        /**
         * Adds an HTML block to the collection of body parts to be assembled.
         * Throws InvalidArgumentException if the block template specified does not exist.
         * 
         * @param string $blockName
         * @param string $content
         * @throws \InvalidArgumentException
         */
        public function addHtmlBlock($blockName, $content = NULL)
        {
            $blockTemplateTable = TableCache::getTableFromCache("EmailBlockTemplate");
            $blockTemplate = $blockTemplateTable->getByName($blockName);
            
            if (!$blockTemplate) {
                throw new \InvalidArgumentException("The provided block name was invalid.");
            }
            
            $block = $blockTemplate->template;
            if (!is_null($content)) {
                $block = str_replace("{CONTENT}", $content, $block);
            }
            
            $bodyPart = new Mime\Part($block);
            $bodyPart->type = "text/html";
            
            $this->bodyParts[] = $bodyPart;
            $this->templateParts[] = array ( $blockName, $content );
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
         * Adds an attachment directly.
         * 
         * @param \Zend\Mime\Part $attachment
         * 
         * @return \Sycamore\Mail\Message
         */
        public function addAttachmentDirect(Mime\Part $attachment) {
            $this->attachments[] = $attachment;
            
            return $this;
        }
        
        /**
         * Returns the constituent template parts of the message.
         * 
         * @return array
         */
        public function getConstituentTemplates()
        {
            return $this->templateParts;
        }
        
        /**
         * Returns the attachments associated with this message.
         * 
         * @return array
         */
        public function getAttachments()
        {
            return $this->attachments;
        }
    }
    