<?php

namespace Dekalee\PubSubSwarrot\Tests\Unit\Driver;

use Dekalee\PubSubSwarrot\Driver\MessageCacheInterface;
use Dekalee\PubSubSwarrot\Driver\PrefetchMessageCache;
use Phake;
use PHPUnit\Framework\TestCase;
use Swarrot\Broker\Message;

/**
 * Class PrefetchMessageCacheTest
 */
class PrefetchMessageCacheTest extends TestCase
{
    /**
     * @var PrefetchMessageCache
     */
    protected $driver;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->driver = new PrefetchMessageCache();
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf(MessageCacheInterface::CLASS, $this->driver);
    }

    /**
     * Test with one element
     */
    public function testPushPop()
    {
        $message = Phake::mock(Message::CLASS);

        $this->driver->push('foo', $message);

        $this->assertSame($message, $this->driver->pop('foo'));
    }

    /**
     * Test with multiple element
     */
    public function testMultiplePushPop()
    {
        $message1 = Phake::mock(Message::CLASS);
        $message2 = Phake::mock(Message::CLASS);
        $message3 = Phake::mock(Message::CLASS);

        $this->driver->push('foo', $message1);
        $this->driver->push('foo', $message2);
        $this->driver->push('foo', $message3);

        $this->assertSame($message1, $this->driver->pop('foo'));
        $this->assertSame($message2, $this->driver->pop('foo'));
        $this->assertSame($message3, $this->driver->pop('foo'));
    }
}
