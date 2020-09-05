<?php


namespace Nomess\Component\Cache;


use Nomess\Component\Config\ConfigStoreInterface;
use Nomess\Container\ContainerInterface;

class AbstractCache
{
    
    private const CONFIGURATION_NAME = 'cache';
    private ConfigStoreInterface    $configStore;
    protected ContainerInterface    $container;
    protected array                 $configuration;
    protected string                $cache_root;
    
    
    public function __construct(
        ConfigStoreInterface $configStore,
        ContainerInterface $container )
    {
        $this->configStore   = $configStore;
        $this->container     = $container;
        $this->cache_root    = $this->configStore->get( ConfigStoreInterface::DEFAULT_NOMESS )['general']['path']['default_cache'];
        $this->configuration = $this->configStore->get( self::CONFIGURATION_NAME );
    }
    
    
    /**
     * Return the complete path of cache
     *
     * @param string $name
     * @return string
     */
    protected function getPath( string $name ): string
    {
        $confPath = NULL;
        
        if( $this->hasPath( $name ) ) {
            $confPath = $this->configuration['cache'][$name]['path'];
        }
        
        return $this->cache_root . $confPath;
    }
    
    
    /**
     * Return true if a specific path is configured
     *
     * @param string $name
     * @return bool
     */
    protected function hasPath( string $name ): bool
    {
        return array_key_exists( 'path', $this->configuration['cache'][$name] );
    }
}
