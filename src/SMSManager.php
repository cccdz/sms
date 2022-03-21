<?php

namespace Cccdz\Sms;

use Cccdz\Sms\Connectors\MktConnector;
use Cccdz\Sms\Contracts\Connection;
use Cccdz\Sms\Contracts\Connector;
use Cccdz\Sms\Exceptions\SMSException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;

class SMSManager
{

    /**
     * The application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * The name of the default driver.
     *
     * @var string
     */
    protected $driver;

    /**
     * The Redis server configurations.
     *
     * @var array
     */
    protected $config;

    /**
     * The SMS connections.
     *
     * @var mixed
     */
    protected $connections;

    /**
     * Create a new SMS manager instance.
     *
     * @param Application $app
     * @param string $driver
     * @param array $config
     * @return void
     */
    public function __construct(Application $app, string $driver, array $config)
    {
        $this->app = $app;
        $this->driver = $driver;
        $this->config = $config;
    }

    /**
     * Get an SMS connection by name.
     *
     * @param string|null $name
     * @return Connection
     * @throws SMSException
     */
    public function connection(string $name = null): Connection
    {
        $name = $name ?: $this->getDefaultDriver();

        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        return $this->connections[$name] = $this->resolve($name);
    }

    /**
     * Resolve the given connection by name.
     *
     * @param $name
     * @return Connection|mixed
     * @throws SMSException
     */
    public function resolve($name)
    {
        if (isset($this->config[$name])) {
            return $this->connector()->connect($this->config[$name]);
        }

        throw new SMSException("SMS connection [{$name}] not configured.");
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->app['config']['sms.default'];
    }


    /**
     * Get the connector instance for the current driver.
     *
     * @return Connector
     */
    protected function connector()
    {
        return new MktConnector;
    }
}