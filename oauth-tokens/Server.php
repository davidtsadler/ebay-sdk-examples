<?php
/**
 * Copyright 2017 David T. Sadler
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
class Server
{
    private $host;
    private $port;
    private $server;

    public function __construct($host, $port)
    {
        $context = stream_context_create();

        stream_context_set_option($context, 'ssl', 'local_cert', __DIR__.'/server.pem');
        stream_context_set_option($context, 'ssl', 'passphrase', 'abracadabra');
        stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
        stream_context_set_option($context, 'ssl', 'verify_peer', false);

        $this->server = stream_socket_server(
            "ssl://{$host}:{$port}",
            $errno,
            $errstr,
            STREAM_SERVER_BIND|STREAM_SERVER_LISTEN,
            $context
        );

        if (!$this->server) {
            throw new Exception("$errstr ($errno)");
        }

        $this->host = $host;
        $this->port = $port;
    }

    public function listen($callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Callback passed to listen should be callable.');
        }

        printf(
            "\nListening on %s:%s\n\n",
            $this->host,
            $this->port
        );

        printf(
            "\nPoint your browser to https://%s:%s\n\n",
            $this->host,
            $this->port
        );

        while (true) {
            $request = '';
            $client = stream_socket_accept($this->server);
            if ($client) {
                while ($client && !preg_match('/\r?\n\r?\n/', $request)) {
                    $request .= fread($client, 2046);
                    if (strlen($request) == 0) {
                        break;
                    }
                }
                fwrite($client, $callback($this->buildSlimEnvironment($request)));
                fclose($client);
            }
        }
    }

    private function buildSlimEnvironment($request)
    {
        return \Slim\Http\Environment::mock(
            array_merge(
                $this->parseRequest($request),
                [
                    'SERVER_PORT' => $this->port
                ]
            )
        );
    }

    private function parseRequest($request)
    {
        if (!$request) {
            return [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI'    => '/'
            ];
        }
        $lines = explode("\r\n", $request);
        list($method, $uri) = explode(' ', array_shift($lines));

        $data = [
            'REQUEST_METHOD' => $method,
            'REQUEST_URI'    => $uri
        ];

        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, ': ') !== false) {
                list($key, $value) = explode(': ', $line);
                $data['HTTP_'.strtoupper(str_replace('-', '_', $key))] = $value;
            }
        }

        return $data;
    }
}
