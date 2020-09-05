<?php


namespace Nomess\Component\Cache;


use Nomess\Component\Cache\Builder\CacheBuilderInterface;
use Nomess\Component\Cache\Exception\InvalidConfigurationException;
use Nomess\Component\Cache\Exception\InvalidSendException;
use Nomess\Component\Cache\Rule\Revalide\RevalideInterface;
use Nomess\Component\Config\ConfigStoreInterface;
use Nomess\Component\Config\Exception\ConfigurationNotFoundException;
use Nomess\Container\ContainerInterface;

class CacheHandler extends AbstractCache implements CacheHandlerInterface
{
    
    private CacheBuilderInterface $cacheBuilder;
    
    
    public function __construct( ConfigStoreInterface $configStore, ContainerInterface $container )
    {
        parent::__construct( $configStore, $container );
    }
    
    
    /**
     * @inheritDoc
     */
    public function get( string $name, string $filename = NULL )
    {
        $this->hasConfiguration( $name );
        $path = $this->getPath( $name );
        
        // If filename is not null, once file is target, revalide and caste the value
        if( $filename !== NULL ) {
            if( file_exists( $file = $path . $filename ) ) {
                return $this->getCastedContent( $name, file_get_contents( $file ) );
            }
        }
        
        // If the configuration has not specific path, throw exception
        if( !$this->hasPath( $name ) ) {
            throw new InvalidSendException( 'Impossible to defined the target files, your configuration not specify path' );
        }
        
        $result = array();
        
        // Travel dir and exec identical process that with once file
        foreach( $this->scanDirectory( $path ) as $file ) {
            $result[] = $this->getCastedContent( $name, file_get_contents( $path . $file ) );
        }
        
        return is_array( $result ) ? $result : NULL;
    }
    
    
    /**
     * @inheritDoc
     */
    public function add( string $name, array $parameters ): CacheHandlerInterface
    {
        $this->cacheBuilder->add( $parameters, $name );
    }
    
    
    /**
     * @inheritDoc
     */
    public function invalid( string $name, string $filename = NULL ): CacheHandlerInterface
    {
        $this->hasConfiguration( $name );
        $path = $this->getPath( $name );
        
        if( $filename !== NULL ) {
            if( file_exists( $file = $path . $filename ) ) {
                unlink( $file );
            }
        }
        
        if( !$this->hasPath( $name ) ) {
            throw new InvalidSendException( 'Impossible to defined the target files, your configuration not specify path' );
        }
        
        foreach( $this->scanDirectory( $path ) as $file ) {
            unlink( $path . $file );
        }
        
        return $this;
    }
    
    
    /**
     * @param string $name
     * @throws ConfigurationNotFoundException
     */
    private function hasConfiguration( string $name ): void
    {
        if( !array_key_exists( $name, $this->configuration['cache'] ) ) {
            throw new ConfigurationNotFoundException( 'The configuration with name "' . $name . '" was not found in cache' );
        }
    }
    
    
    /**
     * Return the good type of value
     *
     * @param string $name
     * @param string $content
     * @return bool|float|mixed|string
     * @throws InvalidConfigurationException
     */
    private function getCastedContent( string $name, string $content )
    {
        $content = $this->revalide( $name, $content = unserialize( $content ) );
        
        if( $content === NULL ) {
            return NULL;
        }
        
        
        $returnType = $this->configuration['cache'][$name]['return'];
        
        if( $returnType === 'array' ) {
            return $content;
        } elseif( $returnType === 'bool' ) {
            return (bool)current( $content );
        } elseif( $returnType === 'string' ) {
            return (string)current( $content );
        } elseif( $returnType === 'float' || $returnType === 'double' ) {
            return (float)current( $content );
        }
        
        throw new InvalidConfigurationException( 'The type "' . $returnType . '" is not supported by cache component' );
    }
    
    
    private function scanDirectory( string $directory ): array
    {
        $content = scandir( $directory );
        
        if( is_array( $content ) ) {
            unset( $content[array_search( '.', $content )], $content[array_search( '..', $content )] );
            
            return $content;
        }
        
        return [];
    }
    
    
    /**
     * Call the revalidations
     *
     * @param string $name
     * @param array $content
     * @return array|null
     */
    private function revalide( string $name, array $content ): ?array
    {
        foreach( $this->configuration['cache'][$name]['revalidation_rules'] as $rule ) {
            if( !array_key_exists( $rule, $this->configuration['rules']['revalidation'] ) ) {
                throw new InvalidConfigurationException( 'The revalidation rule "' . $rule . '" was not found' );
            }
            
            /** @var RevalideInterface $revalide */
            $revalide = $this->container->get( $this->configuration['rules']['revalidation'][$rule] );
            
            $content = $revalide->revalide( $content );
        }
        
        return $content;
    }
}
