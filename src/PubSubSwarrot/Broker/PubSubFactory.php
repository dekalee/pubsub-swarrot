<?php

namespace Dekalee\PubSubSwarrot\Broker;

use Dekalee\PubSubSwarrot\MessageProvider\PubSubMessageProvider;
use Google\Cloud\PubSub\PubSubClient;
use Swarrot\Broker\MessageProvider\MessageProviderInterface;
use Swarrot\Broker\MessagePublisher\MessagePublisherInterface;
use Swarrot\SwarrotBundle\Broker\FactoryInterface;

/**
 * Class PubSubFactory
 */
class PubSubFactory implements FactoryInterface
{
    protected $keyFilePath;
    protected $connections = array();
    protected $messageProviders = array();
    protected $messagePublishers = array();

    /**
     * @param string $keyFilePath
     */
    public function __construct($keyFilePath)
    {
        $this->keyFilePath = $keyFilePath;
    }

    /**
     * {@inheritDoc}
     */
    public function addConnection($name, array $connection)
    {
        $this->connections[$name] = $connection;
    }

    /**
     * getMessageProvider.
     *
     * @param string $name       The name of the queue where the MessageProviderInterface will found messages
     * @param string $connection The name of the connection to use
     *
     * @return MessageProviderInterface
     */
    public function getMessageProvider($name, $connection)
    {
        if (!isset($this->messageProviders[$connection][$name])) {
            if (!isset($this->messageProviders[$connection])) {
                $this->messageProviders[$connection] = array();
            }

            $channel = $this->getChannel($connection);

            $this->messageProviders[$connection][$name] = new PubSubMessageProvider($channel, $name);
        }

        return $this->messageProviders[$connection][$name];
    }

    /**
     * getMessagePublisher.
     *
     * @param string $name       The name of the exchange where the MessagePublisher will publish
     * @param string $connection The name of the connection to use
     *
     * @return MessagePublisherInterface
     */
    public function getMessagePublisher($name, $connection)
    {
        throw new \Exception('Implement method getMessagePublisher');
    }

    /**
     * getChannel.
     *
     * @param string $connection
     *
     * @return PubSubClient
     */
    public function getChannel($connection)
    {
        return new PubSubClient([
            'keyFilePath' => $this->keyFilePath,
        ]);
    }
}
