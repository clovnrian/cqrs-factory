<?php

namespace CQRSFactory;

use CQRS\CommandHandling\CommandBusInterface;
use CQRS\CommandHandling\CommandHandlerLocator;
use CQRS\CommandHandling\Exception\InvalidArgumentException;
use CQRS\CommandHandling\SequentialCommandBus;
use CQRS\HandlerResolver\CommandHandlerResolver;
use CQRS\HandlerResolver\ContainerHandlerResolver;
use Interop\Container\ContainerInterface;

class CommandBusFactory extends AbstractFactory
{
    /**
     * @param ContainerInterface $container
     * @param string $configKey
     * @return CommandBusInterface
     * @throws InvalidArgumentException
     */
    protected function createWithConfig(ContainerInterface $container, $configKey)
    {
        $config = $this->retrieveConfig($container, $configKey, 'command_bus');

        return new $config['class'](
            new CommandHandlerLocator(
                $config['handlers'],
                new ContainerHandlerResolver(
                    $container,
                    new CommandHandlerResolver()
                )
            ),
            $this->retrieveDependency(
                $container,
                $config['transaction_manager'],
                'transaction_manager',
                TransactionManagerFactory::class
            ),
            $this->retrieveDependency(
                $container,
                $config['event_publisher'],
                'event_publisher',
                EventPublisherFactory::class
            ),
            is_string($config['logger'])
                ? $container->get($config['logger'])
                : $config['logger']
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'class' => SequentialCommandBus::class,
            'handlers' => [],
            'transaction_manager' => 'cqrs_default',
            'event_publisher' => 'cqrs_default',
            'logger' => null,
        ];
    }
}
