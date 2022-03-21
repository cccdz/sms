<?php

namespace Cccdz\Sms\Contracts;

interface Connector
{
    /**
     * Create a connection to an SMSManager.
     *
     * @param array $config
     * @return mixed
     */
    public function connect(array $config);
}