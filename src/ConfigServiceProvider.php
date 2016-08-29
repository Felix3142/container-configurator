<?php

namespace TomPHP\ConfigServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use League\Container\ServiceProvider\ServiceProviderInterface;
use TomPHP\ConfigServiceProvider\Exception\EntryDoesNotExistException;

final class ConfigServiceProvider extends AbstractServiceProvider implements
    BootableServiceProviderInterface
{
    const DEFAULT_PREFIX         = 'config';
    const DEFAULT_SEPARATOR      = '.';
    const DEFAULT_INFLECTORS_KEY = 'inflectors';
    const DEFAULT_DI_KEY         = 'di';

    const SETTING_PREFIX    = 'prefix';
    const SETTING_SEPARATOR = 'separator';

    /**
     * @var array
     */
    private $config;

    /**
     * @var ServiceProviderInterface[]
     */
    private $subProviders;

    /**
     * @api
     *
     * @param array|ApplicationConfig $config
     * @param array                   $settings
     *
     * @return ConfigServiceProvider
     */
    public static function fromConfig($config, array $settings = [])
    {
        return new self(
            $config,
            self::getSettingOrDefault(self::SETTING_PREFIX, $settings, self::DEFAULT_PREFIX),
            self::getSettingOrDefault(self::SETTING_SEPARATOR, $settings, self::DEFAULT_SEPARATOR),
            [
                self::DEFAULT_INFLECTORS_KEY => new InflectorConfigServiceProvider(new InflectorConfig([])),
                self::DEFAULT_DI_KEY         => new DIConfigServiceProvider(new ServiceConfig([])),
            ]
        );
    }

    /**
     * @api
     *
     * @param string[] $patterns
     * @param array    $settings
     *
     * @return ConfigServiceProvider
     */
    public static function fromFiles(array $patterns, array $settings = [])
    {
        $separator = self::getSettingOrDefault(self::SETTING_SEPARATOR, $settings, self::DEFAULT_SEPARATOR);

        return self::fromConfig(ApplicationConfig::fromFiles($patterns, $separator), $settings);
    }

    /**
     * @api
     *
     * @param array|ApplicationConfig    $config
     * @param string                     $prefix
     * @param string                     $separator
     * @param ServiceProviderInterface[] $subProviders
     */
    public function __construct(
        $config,
        $prefix = self::DEFAULT_PREFIX,
        $separator = self::DEFAULT_SEPARATOR,
        array $subProviders = []
    ) {
        $this->config = [];

        $config = ($config instanceof ApplicationConfig) ? $config : new ApplicationConfig($config, $separator);

        $configurator = new League\Configurator();
        $configurator->addConfig($config, $prefix);

        $this->subProviders = [__CLASS__ => $configurator->getServiceProvider()];

        foreach ($subProviders as $key => $provider) {
            if ($provider instanceof DIConfigServiceProvider && isset($config[$key])) {
                try {
                    $this->subProviders[$key] = new DIConfigServiceProvider(new ServiceConfig($config[$key]));
                } catch (EntryDoesNotExistException $e) {
                    // no op
                }
            } elseif ($provider instanceof InflectorConfigServiceProvider && isset($config[$key])) {
                $this->subProviders[$key] = new InflectorConfigServiceProvider(new InflectorConfig($config[$key]));
            } else {
                $this->subProviders[$key] = $provider;
            }
        }

        foreach ($this->subProviders as $key => $provider) {
            $this->provides = array_merge($this->provides, $provider->provides());
        }
    }

    public function register()
    {
        foreach ($this->subProviders as $provider) {
            $provider->setContainer($this->getContainer());
            $provider->register();
        }
    }

    public function boot()
    {
        foreach ($this->subProviders as $provider) {
            if (!$provider instanceof BootableServiceProviderInterface) {
                continue;
            }

            $provider->setContainer($this->getContainer());
            $provider->boot();
        }
    }

    /**
     * @param string $name
     * @param array  $settings
     * @param mixed  $default
     *
     * @return mixed
     */
    private static function getSettingOrDefault($name, array $settings, $default)
    {
        return isset($settings[$name]) ? $settings[$name] : $default;
    }
}
