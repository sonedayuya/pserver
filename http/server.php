<?php
    require_once './http/response.php';

    class HttpServer
    {
        private $_host = 'tcp://0.0.0.0';
        private $_port = '8080';

        public function __construct($host = null, $port = null) {
            if (empty($host) === false) $this->_host = $host;
            if (empty($port) === false) $this->_port = $port;
        }

        public function start()
        {
            $socket = stream_socket_server($this->_host . ':' . $this->_port , $errno, $errstr);
            if (!$socket) {
                die("$errstr ($errno)\n");
            }

            while ($connection = @stream_socket_accept($socket, -1)) {

                if(stream_socket_get_name($connection, true)!==false){
                    echo stream_socket_get_name($connection, true) . PHP_EOL;
                }

                $request = $this->_parseRequest($connection);
                $response = $this->_getResponse($request);

                fwrite($connection,  $response['header'] . "\n\r" . $response['body']);
                fclose($connection);
            }
        }

        private function _parseRequest($connection)
        {
            $request = [];

            $requestLine = fread($connection, 2046);
            preg_match('/^(\S+)\s+(\S++)(?:\s+HTTP\/(\d+\.\d+))?\r?\n/', $requestLine, $match);
            $request['method'] = $match[1];
            $request['uri'] = $match[2];
            $request['httpVersion'] = $match[3];

            return $request;
        }

        private function _getResponse($request)
        {
            $response = [];
            $request['httpStatus'] = "200";
            $request['httpReasonPhrase'] = "OK";
            $request['body'] = "";

            if (file_exists('.' . $request['uri']) === true) {
                $request['body'] = file_get_contents('.' . $request['uri']);
            } else {
                $request['httpStatus'] = "400";
                $request['httpReasonPhrase'] = "NOT FOUND";
            }

            $httpResponse = new HttpResponse($request);
            $response['header'] = $httpResponse->getResponseHeader();
            $response['body'] = $httpResponse->getResponseBody();

            return $response;
        }
    }
?>
