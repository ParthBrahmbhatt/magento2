<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Bulk;

/**
 * Factory class for @see \Magento\Framework\MessageQueue\ExchangeInterface
 */
class ExchangeFactory implements ExchangeFactoryInterface
{
    /**
     * @var ExchangeFactoryInterface[]
     */
    private $exchangeFactories;

    /**
     * @var \Magento\Framework\MessageQueue\ConnectionTypeResolver
     */
    private $connectionTypeResolver;

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\MessageQueue\ConnectionTypeResolver $connectionTypeResolver
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ExchangeFactoryInterface[] $exchangeFactories
     */
    public function __construct(
        \Magento\Framework\MessageQueue\ConnectionTypeResolver $connectionTypeResolver,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $exchangeFactories = []
    ) {
        $this->objectManager = $objectManager;
        $this->exchangeFactories = $exchangeFactories;
        $this->connectionTypeResolver = $connectionTypeResolver;
    }

    /**
     * @inheritdoc
     */
    public function create($connectionName, array $data = [])
    {
        $connectionType = $this->connectionTypeResolver->getConnectionType($connectionName);

        if (!isset($this->exchangeFactories[$connectionType])) {
            throw new \LogicException("Not found exchange for connection name '{$connectionName}' in config");
        }

        $factory = $this->exchangeFactories[$connectionType];
        $exchange = $factory->create($connectionName, $data);

        if (!$exchange instanceof ExchangeInterface) {
            $exchangeInterface = \Magento\Framework\MessageQueue\Bulk\ExchangeInterface::class;
            throw new \LogicException(
                "Exchange for connection name '{$connectionName}' " .
                "does not implement interface '{$exchangeInterface}'"
            );
        }
        return $exchange;
    }
}
