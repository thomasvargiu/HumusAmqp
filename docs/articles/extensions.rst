.. _extensions:

RabbitMQ Extensions
===================

HumusAmqp supports all `RabbitMQ extensions to AMQP
0.9.1 <http://www.rabbitmq.com/extensions.html>`_ that the PHP AMQP Extension supports, too:

-  `Negative acknowledgements <http://www.rabbitmq.com/nack.html>`_
   (basic.nack)
-  `Exchange-to-Exchange Bindings <http://www.rabbitmq.com/e2e.html>`_
-  `Alternate Exchanges <http://www.rabbitmq.com/ae.html>`_
-  `Per-queue Message
   Time-to-Live <http://www.rabbitmq.com/ttl.html#per-queue-message-ttl>`_
-  `Per-message
   Time-to-Live <http://www.rabbitmq.com/ttl.html#per-message-ttl>`_
-  `Queue Leases <http://www.rabbitmq.com/ttl.html#queue-ttl>`_
-  `Sender-selected
   Distribution <http://www.rabbitmq.com/sender-selected.html>`_
-  `Dead Letter Exchanges <http://www.rabbitmq.com/dlx.html>`_
-  `Publisher confirms <http://www.rabbitmq.com/confirms.html>`_
-  `Validated
   user\_id <http://www.rabbitmq.com/validated-user-id.html>`_


This guide briefly describes how to use these extensions with HumusAmqp.

Enabling RabbitMQ Extensions
----------------------------

You don't need to require any additional files to make HumusAmqp support
RabbitMQ extensions. The support is built into the core.

Per-queue Message Time-to-Live
------------------------------

Per-queue Message Time-to-Live (TTL) is a RabbitMQ extension to AMQP
0.9.1 that allows developers to control how long a message published to
a queue can live before it is discarded. A message that has been in the
queue for longer than the configured TTL is said to be dead. Dead
messages will not be delivered to consumers and cannot be fetched.

.. code-block:: php

    <?php

    return [
        'dependencies' => [
            'factories' => [
                Driver::class => Humus\Amqp\Container\DriverFactory::class,
                'default-amqp-connection' => [Humus\Amqp\Container\ConnectionFactory::class, 'default'],
            ],
        ],
        'humus' => [
            'amqp' => [
                'driver' => 'amqp-extensions',
                'connection' => [
                    'default' => [
                        'host' => 'localhost',
                        'port' => 5672,
                        'login' => 'guest',
                        'password' => 'guest',
                    ],
                ],
                'exchange' => [
                    'demo-exchange' => [
                        'name' => 'demo-exchange',
                        'type' => 'direct',
                        'connection' => 'default-amqp-connection',
                    ],
                ],
                'queue' => [
                    'my-queue' => [
                        'name' => 'my-queue',
                        'exchanges' => [
                            'demo-exchange' => [
                                [
                                    'arguments' => [
                                        'x-message-ttl' => 1000
                                    ],
                                ],
                            ],
                        ],
                        'connection' => 'default-amqp-connection',
                    ],
                ],
            ],
        ],
    ];

When a published message is routed to multiple queues, each of the
queues gets a *copy of the message*. If the message subsequently dies in
one of the queues, it has no effect on copies of the message in other
queues.

Learn More
~~~~~~~~~~

See also rabbitmq.com section on `Per-queue Message
TTL <http://www.rabbitmq.com/ttl.html#per-queue-message-ttl>`_

basic.nack
----------

The AMQP 0.9.1 specification defines the basic.reject method that allows
clients to reject individual, delivered messages, instructing the broker
to either discard them or requeue them. Unfortunately, basic.reject
provides no support for negatively acknowledging messages in bulk.

To solve this, RabbitMQ supports the basic.nack method that provides all
of the functionality of basic.reject whilst also allowing for bulk
processing of messages.

How To Use It With HumusAmqp
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The HumusAmqp makes already use of the nack method to reject a block of messages.
You don't need to take care of that, unless you want to write a consumer yourself.

Learn More
~~~~~~~~~~

See also rabbitmq.com section on
`basic.nack <http://www.rabbitmq.com/nack.html>`_

Alternate Exchanges
-------------------

The Alternate Exchanges RabbitMQ extension to AMQP 0.9.1 allows
developers to define "fallback" exchanges where unroutable messages will
be sent.

.. code-block:: php

    <?php

    return [
        'dependencies' => [
            'factories' => [
                Driver::class => Humus\Amqp\Container\DriverFactory::class,
                'default-amqp-connection' => [Humus\Amqp\Container\ConnectionFactory::class, 'default'],
            ],
        ],
        'humus' => [
            'amqp' => [
                'driver' => 'amqp-extensions',
                'connection' => [
                    'default' => [
                        'host' => 'localhost',
                        'port' => 5672,
                        'login' => 'guest',
                        'password' => 'guest',
                    ],
                ],
                'exchanges' => [
                    'demo-exchange' => [
                        'name' => 'demo-exchange',
                        'type' => 'direct',
                        'arguments' => [
                            'alternate_exchange' => 'alternate-exchange-name'
                        ],
                        'connection' => 'default-amqp-connection',
                    ],
                ],
            ],
        ],
    ];

Learn More
~~~~~~~~~~

See also rabbitmq.com section on `Alternate
Exchanges <http://www.rabbitmq.com/ae.html>`_

Exchange-To-Exchange Bindings
-----------------------------

RabbitMQ supports `exchange-to-exchange
bindings <http://www.rabbitmq.com/e2e.html>`_ to allow even richer
routing topologies as well as a backbone for some other features (e.g.
tracing).

.. code-block:: php

    <?php

    return [
        'dependencies' => [
            'factories' => [
                Driver::class => Humus\Amqp\Container\DriverFactory::class,
                'default-amqp-connection' => [Humus\Amqp\Container\ConnectionFactory::class, 'default'],
            ],
        ],
        'humus' => [
            'amqp' => [
                'driver' => 'amqp-extensions',
                'connection' => [
                    'default' => [
                        'host' => 'localhost',
                        'port' => 5672,
                        'login' => 'guest',
                        'password' => 'guest',
                    ],
                ],
                'exchange' => [
                    'exchange1' => [
                        'name' => 'exchange1',
                        'type' => 'direct',
                        'connection' => 'default-amqp-connection',
                    ],
                    'exchange2' => [
                        'name' => 'exchange2',
                        'type' => 'direct',
                        'connection' => 'default-amqp-connection',
                    ],
                    'demo-exchange' => [
                        'name' => 'demo-exchange',
                        'type' => 'direct',
                        'exchange_bindings' => [
                            'exchange1' => [
                                [
                                    'routing_keys' => [
                                        'routingKey.1',
                                        'routingKey.2'
                                    ],
                                ],
                            ),
                            'exchange2' => [
                                [
                                    'routing_keys' => [
                                        'routingKey.3'
                                    ],
                                ],
                            ],
                        ],
                        'connection' => 'default-amqp-connection',
                    ],
                ],
            ],
        ],
    ];

Learn More
~~~~~~~~~~

See also rabbitmq.com section on `Exchange-to-Exchange
Bindings <http://www.rabbitmq.com/e2e.html>`_

Queue Leases
------------

Queue Leases is a RabbitMQ feature that lets you set for how long a
queue is allowed to be *unused*. After that moment, it will be deleted.
*Unused* here means that the queue

-  has no consumers
-  is not redeclared
-  no message fetches happened

.. code-block:: php

    <?php

    return [
        'dependencies' => [
            'factories' => [
                Driver::class => Humus\Amqp\Container\DriverFactory::class,
                'default-amqp-connection' => [Humus\Amqp\Container\ConnectionFactory::class, 'default'],
            ],
        ],
        'humus' => [
            'amqp' => [
                'driver' => 'amqp-extensions',
                'connection' => [
                    'default' => [
                        'host' => 'localhost',
                        'port' => 5672,
                        'login' => 'guest',
                        'password' => 'guest',
                    ],
                ],
                'exchange' => [
                    'demo-exchange' => [
                        'name' => 'demo-exchange',
                        'type' => 'direct',
                        'arguments' => [
                            'x-expires' => 10000
                        ],
                        'connection' => 'default-amqp-connection',
                    ],
                ],
            ],
        ],
    ];

Learn More
~~~~~~~~~~

See also rabbitmq.com section on `Queue
Leases <http://www.rabbitmq.com/ttl.html#queue-ttl>`_

Per-Message Time-to-Live
------------------------

A TTL can be specified on a per-message basis, by setting the
``:expiration`` property when publishing.


.. code-block:: php

    <?php

    $attribs = new MessageAttributes()
    $attribs->setExpiration(5000);

    $producer->publish('some message', '', $attribs);

Learn More
~~~~~~~~~~

See also rabbitmq.com section on `Per-message
TTL <http://www.rabbitmq.com/ttl.html#per-message-ttl>`_

Sender-Selected Distribution
----------------------------

Generally, the RabbitMQ model assumes that the broker will do the
routing work. At times, however, it is useful for routing to happen in
the publisher application. Sender-Selected Routing is a RabbitMQ feature
that lets clients have extra control over routing.

The values associated with the ``"CC"`` and ``"BCC"`` header keys will
be added to the routing key if they are present. If neither of those
headers is present, this extension has no effect.

.. code-block:: php

    <?php

    $producer->publish('some message', '', Constants::AMQP_NOPARAM, [
        'headers' => [
            'CC' => [
                'two',
                'three'
            ],
        ],
    ]);

Learn More
~~~~~~~~~~

See also rabbitmq.com section on `Sender-Selected
Distribution <http://www.rabbitmq.com/sender-selected.html>`_

Dead Letter Exchange (DLX)
--------------------------

The x-dead-letter-exchange argument to queue.declare controls the
exchange to which messages from that queue are 'dead-lettered'. A
message is dead-lettered when any of the following events occur:

The message is rejected (basic.reject or basic.nack) with requeue=false;
or the TTL for the message expires.

How To Use It With HumusAmqp
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Dead-letter Exchange is a feature that is used by specifying additional
queue arguments:

-  ``"x-dead-letter-exchange"`` specifies the exchange that dead
   lettered messages should be published to by RabbitMQ
-  ``"x-dead-letter-routing-key"`` specifies the routing key that should
   be used (has to be a constant value)

.. code-block:: php

    <?php

    return [
        'dependencies' => [
            'factories' => [
                Driver::class => Humus\Amqp\Container\DriverFactory::class,
                'default-amqp-connection' => [Humus\Amqp\Container\ConnectionFactory::class, 'default'],
            ],
        ],
        'humus' => [
            'amqp' => [
                'driver' => 'amqp-extensions',
                'connection' => [
                    'default' => [
                        'host' => 'localhost',
                        'port' => 5672,
                        'login' => 'guest',
                        'password' => 'guest',
                    ],
                ],
                'queue' => [
                    'foo' => [
                        'name' => 'foo',
                        'exchanges' => [
                            'demo' => [],
                        ],
                        'arguments' => [
                            'x-dead-letter-exchange' => 'demo.error'
                        ],
                        'connection' => 'default-amqp-connection',
                    ],
                ],
            ],
        ],
    ];

Learn More
~~~~~~~~~~

See also rabbitmq.com section on `Dead Letter
Exchange <http://www.rabbitmq.com/dlx.html>`_

Wrapping Up
-----------

RabbitMQ provides a number of useful extensions to the AMQP 0.9.1
specification.

HumusAmqp releases have RabbitMQ extensions support built into
the core. Some features are based on optional arguments for queues,
exchanges or messages, and some are HumusAmqp public API features. Any
future argument-based extensions are likely to be useful with HumusAmqp
immediately, without any library modifications.

What to Read Next
-----------------

The documentation is organized as :ref:`a number of guides <guides>`, covering various topics.

We recommend that you read the following guides first, if possible, in
this order:

-  :ref:`RabbitMQ Extensions to AMQP 0.9.1 <extensions>`
-  :ref:`Error Handling and Recovery <error_handling>`
-  :ref:`Troubleshooting <troubleshooting>`
-  :ref:`Deployment <deployment>`

Tell Us What You Think!
-----------------------

Please take a moment to tell us what you think about this guide: `Send an e-mail <saschaprolic@googlemail.com>`_,
say hello in the `HumusAmqp gitter <https://gitter.im/prolic/HumusAmqp>`_ chat.
or raise an issue on `Github <https://www.github.com/prolic/HumusAmqp/issues>`_.

Let us know what was unclear or what has not been covered. Maybe you
do not like the guide style or grammar or discover spelling
mistakes. Reader feedback is key to making the documentation better.
