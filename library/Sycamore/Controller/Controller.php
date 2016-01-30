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

    namespace Sycamore\Controller;

    use Sycamore\ErrorManager;
    use Sycamore\Request;
    use Sycamore\Response;
    use Sycamore\Renderer\Renderer;
    use Sycamore\Utils\APIData;
    
    /**
     * Abstract Sycamore controller class. 
     * All controllers must extend this class.
     */
    abstract class Controller
    {
        /**
         * Request object.
         *
         * @var \Sycamore\Request
         */
        protected $request;
        
        /**
         * Response object.
         *
         * @var \Sycamore\Response
         */
        protected $response;
        
        /**
         * Renderer object.
         *
         * @var \Sycamore\Renderer\Renderer
         */
        protected $renderer;
        
        /**
         * Properties array of requested page.
         *
         * @var array
         */
        protected $properties = null;
        
        /**
         * Passes the request for the instanced Controller.
         *
         * @return \Sycamore\Request
         */
        public function getRequest()
        {
            return $this->request;
        }
        
        /**
         * Passes the response for the instanced Controller.
         *
         * @return \Sycamore\Response
         */
        public function getResponse()
        {
            return $this->response;
        }
        
        /**
         * Passes the renderer for the instanced Controller.
         *
         * @return \Sycamore\Renderer\Renderer
         */
        public function getRenderer()
        {
            return $this->response;
        }
        
        /**
         * Prepares request and response objects.
         * 
         * @param \Sycamore\Request
         * @param \Sycamore\Response
         * @param \Sycamore\Renderer\Renderer
         */
        public function __construct(Request &$request, Response &$response, Renderer $renderer)
        {
            $this->request = $request;
            $this->response = $response;
            $this->renderer = $renderer;
        }
        
        /**
         * Constants for combining the data requirements.
         */
        const DP_AND = 0;
        const DP_OR = 1;
        const DP_XOR = 2;
        
        /**
         * Assesses if all the required data points were sent to the server.
         * 
         * @return boolean
         */
        protected function dataProvided($dataRequired, $inputStream, $operator = self::DP_AND)
        {
            $missingNeededData = false;
            $hadEntry = false;
            foreach ($dataRequired as $detail) {
                if (!filter_input($inputStream, $detail["key"], $detail["filter"] ?: FILTER_DEFAULT)) {
                    if ($operator == self::DP_AND) {
                        ErrorManager::addError($detail["errorType"], $detail["errorKey"]);
                    }
                    $missingNeededData = true;
                } else {
                    if ($operator == self::DP_XOR && $hadEntry) {
                        ErrorManager::addError($detail["errorType"], "too_many_data_points");
                        break;
                    }
                    $hadEntry = true;
                }
            }
            return (!$missingNeededData && $hadEntry);
        }
        
        /**
         * If EXIT_REQUEST_ERROR is passed intro prepareExit, content will be drawn from error manager.
         */
        const EXIT_REQUEST_ERROR = -1;
        
        /**
         * Prepares for an early exit from controller. 
         * 
         * @param string|int $renderContent
         */
        protected function prepareExit($renderContent = self::EXIT_REQUEST_ERROR)
        {
            if ($renderContent == self::EXIT_REQUEST_ERROR) {
                $renderContent = APIData::encode(ErrorManager::getErrors(ErrorManager::DELETE_ERRORS));
                $this->response->setResponseCode(400);
            }
            $this->response->send();
            $this->renderer->render($renderContent);
        }
//        
//        /**
//         * Requests and returns the dependencies for the given URI.
//         *
//         * @var array
//         *
//         * @return array
//         */
//        protected function getUriDependencies()
//        {
//            $depModel = $this->getModelFromCache("DependenciesModel");
//            $depsToGet = $depModel->getUriDependencies($this->request->getUri());
//            
//            $dependencies = array();
//            if (isset($depsToGet["templates"]))
//            {
//                $dependencies = array_merge($dependencies, $this->getTemplates($depsToGet["templates"]));
//            }
//            return $dependencies;
//        }
//        
//        /**
//         * Gets the specified templates from the template model.
//         *
//         * @var array
//         *
//         * @return array
//         */
//        protected function getTemplates($templateObjects)
//        {
//            if (!is_array($templateObjects))
//            {
//                return false;
//            }
//            $templateModel = $this->getModelFromCache("TemplateModel");
//            $templates = array();
//            foreach ($templateObjects as $templateObject)
//            {
//                if (!isset($templateObject["name"]) || !isset($templateObject["type"]) || !Template::isValidValue($templateObject["type"]))
//                {
//                    continue;
//                }
//                $templates[$templateObject["type"]][$templateObject["name"]] = $templateModel->getTemplateByName($templateObject["name"], $templateObject["type"]);
//            }
//            return array ( "templates" => $templates );
//        }
//        
//        /**
//         * Gets the head parameters for the page requested.
//         *
//         * @return array
//         */
//        protected function getHeadParameters() {
//            if (!$this->properties) {
//                return $this->_getPageProperties()["head"];
//            } else {
//                return $this->properties["head"];
//            }
//        }
//        
//        /**
//         * Gets the properties for the page requested.
//         *
//         * @return array
//         */        
//        protected function getPageProperties() {
//            if (!$this->properties) {
//                $pagePropsModel = $this->getModelFromCache("PagePropertiesModel");
//                $this->properties = $pagePropsModel->getPropertiesFromUri($this->request->getUri());
//            }
//            return $this->properties;
//        }
    }