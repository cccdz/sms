<?php

namespace Cccdz\Sms\Connectors;

use Cccdz\Sms\Connections\MktConnection;
use Cccdz\Sms\Contracts\Connection;
use Cccdz\Sms\Contracts\Connector;
use GuzzleHttp\Client;

class MktConnector implements Connector
{
    /**
     * @param array $config
     * @return Connection
     */
    public function connect(array $config): Connection
    {
        $client = new Client([
            'base_uri'  => 'https://smsapi.mitake.com.tw/',
            'timeout' => 10.0
        ]);

        return new MktConnection($client, $config);
    }
}