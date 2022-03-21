<?php

namespace Cccdz\Sms\Connections;

use Cccdz\Sms\Contracts\Connection;
use Cccdz\Sms\Exceptions\SMSException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class MktConnection implements Connection
{
    /**
     * The Redis client.
     *
     * @var Client
     */
    protected $client;

    /**
     * The connection configuration array.
     *
     * @var array
     */
    protected $config;

    /**
     * Success status code.
     *
     * @var string[]
     */
    protected $successStatusCodes = ['1', '2', '4'];

    /**
     * @param Client $client
     * @param array $config
     */
    public function __construct(Client $client, array $config = [])
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Single send.
     *
     * @param string $mobile
     * @param string $content
     * @return array
     * @throws SMSException
     * @throws GuzzleException
     */
    public function singleSend(string $mobile, string $content): array
    {
        $response = $this->client->request('POST', 'api/mtk/SmSend', [
            'query' => [
                'CharsetURL' => 'UTF8',
                'username' => $this->config['username'],
                'password' => $this->config['password'],
                'dstaddr' => $mobile,
                'smbody' => $content
            ]
        ]);

        $body = $response->getBody()->getContents();

        $result = $this->handleSendResult($body);

        //preg_match("/statuscode=(.*?)[\\s]/", $body, $match);
        //
        //if (!$match) {
        //    throw new SMSException("regex match failed \n body:" . $body);
        //}

        if ($result['fail_count'] > 0) {
            throw new SMSException("send failed \n body:" . $body);
        }


        return $result['result'][0];
    }

    /**
     * Bulk send.
     *
     * @param string $mobile
     * @param array $contents
     * @return array
     * @throws GuzzleException
     */
    public function bulkSend(string $mobile, array $contents): array
    {
        $response = $this->client->request('POST', 'api/mtk/SmBulkSend', [
            'query' => [
                'CharsetURL' => 'UTF8',
                'username' => $this->config['username'],
                'password' => $this->config['password'],
                'dstaddr' => $mobile,
            ],
            'body' => implode("\r\n", $contents) . "\r\n",
        ]);

        return $this->handleSendResult($response->getBody()->getContents());
    }

    /**
     * handle send results.
     *
     * @param string $body
     * @return array
     */
    public function handleSendResult(string $body): array
    {
        $bodyArr = explode("\n", $body);

        $result = $tmp = [];

        $successCount = $failCount = 0;

        foreach ($bodyArr as $str) {

            if (!preg_match("/^\\[(.*)\\]$/", $str, $match)) {

                list($key, $value) = explode('=', $str);

                $tmp[$key] = $value;

                if ($key == 'statuscode') {

                    if (in_array($value, $this->successStatusCodes)) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                }

                continue;
            }

            $result[] = $tmp;

            $tmp = ['client_id' => $match[1]];
        }

        if ($tmp) {
            $result[] = $tmp;
        }

        return ['result' => $result, 'success_count' => $successCount, 'fail_count' => $failCount];
    }
}