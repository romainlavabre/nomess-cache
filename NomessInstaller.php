<?php


namespace Nomess\Component\Cache;


use Nomess\Component\Cache\Builder\CacheBuilder;
use Nomess\Component\Cache\Builder\CacheBuilderInterface;
use Nomess\Component\Cache\Cli\ClearCache;
use Nomess\Component\Cli\Executable\ExecutableInterface;
use Nomess\Component\Config\ConfigStoreInterface;

/**
 * @author Romain Lavabre <webmaster@newwebsouth.fr>
 */
class NomessInstaller implements \Nomess\Installer\NomessInstallerInterface
{
    
    public function __construct( ConfigStoreInterface $configStore )
    {
    }
    
    
    /**
     * @inheritDoc
     */
    public function container(): array
    {
        return [
            CacheHandlerInterface::class => CacheHandler::class,
            CacheBuilderInterface::class => CacheBuilder::class
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function controller(): array
    {
        return [];
    }
    
    
    /**
     * @inheritDoc
     */
    public function cli(): array
    {
        return [
            'nomess/cache' => NULL,
            'clear:cache' => [
                ExecutableInterface::CLASSNAME => ClearCache::class,
                ExecutableInterface::COMMENT => 'Clear all caches'
            ]
        ];
    }
    
    
    /**
     * @inheritDoc
     */
    public function exec(): ?string
    {
        return NULL;
    }
}
