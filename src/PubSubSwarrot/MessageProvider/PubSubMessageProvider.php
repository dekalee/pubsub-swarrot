<?php

namespace Dekalee\PubSubSwarrot\MessageProvider;

use Dekalee\PubSubSwarrot\Driver\MessageCacheInterface;
use Dekalee\PubSubSwarrot\Driver\PrefetchMessageCache;
use Google\Cloud\PubSub\PubSubClient;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessageProvider\MessageProviderInterface;
use Google\Cloud\PubSub\Message as GoogleMessage;

/**
 * Class PubSubMessageProvider
 */
class PubSubMessageProvider implements MessageProviderInterface
{
    protected $cache;
    protected $channel;
    protected $prefetch;
    protected $queueName;

    /**
     * @param PubSubClient               $channel
     * @param string                     $queueName
     * @param MessageCacheInterface|null $cache
     */
    public function __construct(
        PubSubClient $channel,
        $queueName,
        MessageCacheInterface $cache = null
    ) {
        $this->channel = $channel;
        $this->queueName = $queueName;
        $this->cache = $cache?:new PrefetchMessageCache();
        $this->prefetch = 20;
    }

    /**
     * get.
     *
     * @return Message|null
     */
    public function get()
    {
        if ($message = $this->cache->pop($this->getQueueName())) {
            return $message;
        }

        $subscription = $this->channel->subscription($this->getQueueName());
        $result = $subscription->pull([
            'returnImmediately' => true,
            'maxMessages' => $this->prefetch,
        ]);

        if (!$result || 0 === count($result)) {
            return null;
        }

        foreach ($result as $message) {
            $swarrotMessage = new Message($message->data(), [], $message->ackId());
            $this->cache->push($this->getQueueName(), $swarrotMessage);
        }

        return $this->cache->pop($this->getQueueName());
    }

    /**
     * ack.
     *
     * @param Message $message
     */
    public function ack(Message $message)
    {
        $ackMessage = new GoogleMessage([], ['ackId' => $message->getId()]);
        $subscription = $this->channel->subscription($this->getQueueName());
        $subscription->acknowledge($ackMessage);
    }

    /**
     * nack.
     *
     * @param Message $message The message to NACK
     * @param bool    $requeue Requeue the message in the queue ?
     */
    public function nack(Message $message, $requeue = false)
    {
    }

    /**
     * getQueueName.
     *
     * @return string
     */
    public function getQueueName()
    {
        return $this->queueName;
    }
}
