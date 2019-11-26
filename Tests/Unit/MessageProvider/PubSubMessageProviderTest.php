<?php

namespace Dekalee\PubSubSwarrot\Tests\Unit\MessageProvider;

use Dekalee\PubSubSwarrot\Driver\MessageCacheInterface;
use Dekalee\PubSubSwarrot\MessageProvider\PubSubMessageProvider;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Subscription;
use Phake;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\Broker\MessageProvider\MessageProviderInterface;
use Google\Cloud\PubSub\Message as GoogleMessage;

/**
 * Class PubSubMessageProviderTest
 */
class PubSubMessageProviderTest extends TestCase
{
    /**
     * @var PubSubMessageProvider
     */
    protected $provider;

    protected $channel;
    protected $cache;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->channel = Phake::mock(PubSubClient::CLASS);
        $this->cache = Phake::mock(MessageCacheInterface::CLASS);

        $this->provider = new PubSubMessageProvider($this->channel, 'foo', $this->cache);
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf(MessageProviderInterface::CLASS, $this->provider);
    }

    /**
     * Test with no cache
     */
    public function testGetWithNoCache()
    {
        $message = Phake::mock(Message::CLASS);

        Phake::when($this->cache)->pop(Phake::anyParameters())
            ->thenReturn(null)
            ->thenReturn($message);

        $subscription = Phake::mock(Subscription::CLASS);
        Phake::when($this->channel)->subscription(Phake::anyParameters())->thenReturn($subscription);
        $googleMessage = Phake::mock(GoogleMessage::CLASS);
        Phake::when($googleMessage)->data()->thenReturn(json_encode(['data']));
        Phake::when($googleMessage)->ackId()->thenReturn(1);

        Phake::when($subscription)->pull(Phake::anyParameters())->thenReturn([
            $googleMessage
        ]);

        $this->assertSame($message, $this->provider->get());

        Phake::verify($this->cache)->push(Phake::anyParameters());
    }

    /**
     * Test with no cache
     */
    public function testGetWithNoResult()
    {
        Phake::when($this->cache)->pop(Phake::anyParameters())->thenReturn(null);

        $subscription = Phake::mock(Subscription::CLASS);
        Phake::when($this->channel)->subscription(Phake::anyParameters())->thenReturn($subscription);

        Phake::when($subscription)->pull(Phake::anyParameters())->thenReturn([]);

        $this->assertNull($this->provider->get());

    }

    /**
     * Test with cache
     */
    public function testGetWithCache()
    {
        $message = Phake::mock(Message::CLASS);

        Phake::when($this->cache)->pop(Phake::anyParameters())
            ->thenReturn($message);

        $this->assertSame($message, $this->provider->get());

        Phake::verify($this->cache, Phake::never())->push(Phake::anyParameters());
        Phake::verify($this->channel, Phake::never())->subscription(Phake::anyParameters());
    }

    /**
     * Test simple ack with empty line
     */
    public function testAck()
    {
        $message = Phake::mock(Message::CLASS);
        Phake::when($message)->getId()->thenReturn(1);

        $subscription = Phake::mock(Subscription::CLASS);
        Phake::when($this->channel)->subscription(Phake::anyParameters())->thenReturn($subscription);

        $this->provider->ack($message);

        Phake::verify($subscription)->acknowledge(Phake::anyParameters());
    }


    /**
     * Test nack, with ack on all the cached elements
     */
    public function testNack()
    {
        $messageToNack = Phake::mock(Message::CLASS);

        $this->provider->nack($messageToNack);

        Phake::verify($this->channel, Phake::never())->deleteMessageBatch(Phake::anyParameters());
    }
}
