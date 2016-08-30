<?php

namespace TomPHP\ConfigServiceProvider\League;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use TomPHP\ConfigServiceProvider\InflectorConfig;
use TomPHP\ConfigServiceProvider\InflectorDefinition;

final class InflectorServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{
    /**
     * @var InflectorConfig
     */
    private $config;

    /**
     * @api
     *
     * @param InflectorConfig $config
     */
    public function __construct(InflectorConfig $config)
    {
        $this->config = $config;
    }

    public function register()
    {
    }

    public function boot()
    {
        foreach ($this->config as $definition) {
            $this->configureInterface($definition);
        }
    }

    /**
     * @param string $interface
     * @param array  $config
     */
    private function configureInterface(InflectorDefinition $definition)
    {
        foreach ($definition->getMethods() as $method => $args) {
            $this->addInflectorMethod(
                $definition->getInterface(),
                $method,
                $args
            );
        }
    }

    /**
     * @param string $interface
     * @param string $method
     * @param array  $args
     */
    private function addInflectorMethod($interface, $method, array $args)
    {
        $this->getContainer()
            ->inflector($interface)
            ->invokeMethod($method, $args);
    }
}
