<?php
/**
 * Copyright (c) 2016. Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 *  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 *  "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 *  LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 *  A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 *  OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 *  SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 *  LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 *  DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 *  THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 *  OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *  This software consists of voluntary contributions made by many individuals
 *  and is licensed under the MIT license.
 */

declare (strict_types=1);

namespace Humus\Amqp\Driver\PhpAmqpLib;

use Humus\Amqp\Constants;
use Humus\Amqp\AmqpChannel as AmqpChannelInterface;
use Humus\Amqp\AmqpConnection as AmqpConnectionInterface;
use Humus\Amqp\AmqpExchange as AmqpExchangeInterface;
use Humus\Amqp\Exception\AmqpExchangeException;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AmqpExchange
 * @package Humus\Amqp\Driver\AmqpExtension
 */
class AmqpExchange implements AmqpExchangeInterface
{
    /**
     * @var AmqpChannel
     */
    private $channel;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $flags = Constants::AMQP_NOPARAM;

    /**
     * @var array
     */
    private $arguments = [];

    /**
     * Create an instance of AMQPExchange.
     *
     * Returns a new instance of an AMQPExchange object, associated with the
     * given AmqpChannel object.
     *
     * @param AmqpChannel $amqpChannel A valid AmqpChannel object, connected
     *                                 to a broker.
     */
    public function __construct(AmqpChannel $amqpChannel)
    {
        $this->channel = $amqpChannel;
    }

    /**
     * @inheritdoc
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName(string $exchangeName)
    {
        $this->name = $exchangeName;
    }

    /**
     * @inheritdoc
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType(string $exchangeType)
    {
        $this->type = $exchangeType;
    }

    /**
     * @inheritdoc
     */
    public function getFlags() : int
    {
        return $this->flags;
    }

    /**
     * @inheritdoc
     */
    public function setFlags(int $flags)
    {
        $this->flags = (int) $flags;
    }

    /**
     * @inheritdoc
     */
    public function getArgument(string $key)
    {
        return $this->arguments[$key] ?? false;
    }

    /**
     * @inheritdoc
     */
    public function getArguments() : array
    {
        return $this->arguments;
    }

    /**
     * @inheritdoc
     */
    public function setArgument(string $key, $value)
    {
        $this->arguments[$key] = $value;
    }

    /**
     * @inheritdoc
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @inheritdoc
     */
    public function declareExchange() : bool
    {
        try {
            $this->channel->getPhpAmqpLibChannel()->exchange_declare(
                $this->name,
                $this->type,
                (bool) ($this->flags & Constants::AMQP_PASSIVE),
                (bool) ($this->flags & Constants::AMQP_DURABLE),
                (bool) ($this->flags & Constants::AMQP_AUTODELETE),
                (bool) ($this->flags & Constants::AMQP_INTERNAL),
                (bool) ($this->flags & Constants::AMQP_NOWAIT),
                $this->arguments,
                null
            );
        } catch (\Exception $e) {
            throw AmqpExchangeException::fromAmqpExtension($e);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete(string $exchangeName = null, int $flags = Constants::AMQP_NOPARAM) : bool
    {
        if (null === $exchangeName) {
            $exchangeName = $this->name;
        }

        try {
            $this->channel->getPhpAmqpLibChannel()->exchange_delete($exchangeName, $flags);
        } catch (\Exception $e) {
            throw AmqpExchangeException::fromAmqpExtension($e);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function bind(string $exchangeName, string $routingKey = '', array $arguments = []) : bool
    {
        try {
            $this->channel->getPhpAmqpLibChannel()->exchange_bind(
                $exchangeName,
                $this->name,
                $routingKey,
                false,
                $arguments,
                null
            );
        } catch (\Exception $e) {
            throw AmqpExchangeException::fromAmqpExtension($e);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function unbind(string $exchangeName, string $routingKey = '', array $arguments = []) : bool
    {
        try {
            $this->channel->getPhpAmqpLibChannel()->exchange_unbind(
                $exchangeName,
                $this->name,
                $routingKey,
                $arguments,
                null
            );
        } catch (\Exception $e) {
            throw AmqpExchangeException::fromAmqpExtension($e);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function publish(
        string $message,
        string $routingKey = null,
        int $flags = Constants::AMQP_NOPARAM, array $attributes = []
    ) : bool {
        $message = new AMQPMessage($message, $attributes);

        if (null === $routingKey) {
            $routingKey = '';
        }

        try {
            $this->channel->getPhpAmqpLibChannel()->basic_publish(
                $message,
                $this->name,
                $routingKey,
                (bool) ($this->flags & Constants::AMQP_MANDATORY),
                (bool) ($this->flags & Constants::AMQP_IMMEDIATE),
                null
            );
        } catch (\Exception $e) {
            throw AmqpExchangeException::fromAmqpExtension($e);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getChannel() : AmqpChannelInterface
    {
        return $this->channel;
    }

    /**
     * @inheritdoc
     */
    public function getConnection() : AmqpConnectionInterface
    {
        return $this->channel->getConnection();
    }
}
