<?php

namespace Dekalee\PubSubSwarrot\Driver;

use Swarrot\Broker\Message;


/**
 * Class PrefetchMessageCache
 */
interface MessageCacheInterface
{
    /**
     * Pushes a $message to the end of the cache.
     *
     * @param string  $queueName
     * @param Message $message
     */
    public function push($queueName, Message $message);

    /**
     * Get the next message in line. Or nothing if there is no more
     * in the cache.
     *
     * @param string $queueName
     *
     * @return Message|null
     */
    public function pop($queueName);

    /**
     * Get count of the message line
     *
     * @param string $queueName
     *
     * @return int
     */
    public function count($queueName);
}
