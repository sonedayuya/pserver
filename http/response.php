<?php
    class HttpResponse
    {
        private $_header = [];
        private $_request = [];
        private $_response = [];
        private $_httpStatusLine = "HTTP/{{httpVersion}} {{httpStatus}} {{httpReasonPhrase}}";

        public function __construct($request)
        {
            $this->_request = $request;
            $this->_response['header']['Content-Type'] = "text/html; charset=utf-8";
            $this->_response['header']['Content-Length'] = 0;

            $this->_setResponseHeader();
        }

        public function getResponseBody()
        {
            return $this->_response['body'];
        }

        public function getResponseHeader()
        {
            return $this->_response['header'];
        }

        private function _setResponseBody($body)
        {
            $this->_response['body'] = $body;
            return;
        }

        private function _setResponseHeader()
        {
            $request = $this->_request;
            $this->_httpStatusLine = str_replace('{{httpVersion}}', $request['httpVersion'], $this->_httpStatusLine);
            $this->_httpStatusLine = str_replace('{{httpStatus}}', $request['httpStatus'], $this->_httpStatusLine);
            $this->_httpStatusLine = str_replace('{{httpReasonPhrase}}', $request['httpReasonPhrase'], $this->_httpStatusLine);
            $responseHeader = $this->_httpStatusLine . "\n\r";

            foreach($this->_response['header'] as $headerName => $headerValue) {
                if ($headerName == 'Content-Length') {
                    $headerValue = strlen($request['body']);
                    $this->_setResponseBody($request['body']);
                }
                $responseHeader .= $headerName . ':' . $headerValue . "\n\r";
            }

            $this->_response['header'] = $responseHeader;
            return;
        }
    }
?>
