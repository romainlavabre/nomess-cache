<?php


namespace Nomess\Component\Cache\Builder;


use InvalidArgumentException;
use Nomess\Component\Cache\AbstractCache;
use Nomess\Component\Cache\Exception\InvalidConfigurationException;
use Nomess\Component\Cache\Rule\Revalide\RevalideInterface;

class CacheBuilder extends AbstractCache implements CacheBuilderInterface
{
    
    private const TYPE_MAPPER = [
        'array'  => 'is_array',
        'bool'   => 'is_bool',
        'string' => 'is_string',
        'float'  => 'is_float',
        'double' => 'is_float'
    ];
    
    
    /**
     * @inheritDoc
     */
    public function add( array $parameters, string $name ): void
    {
        $parameters = $this->validParameters( $parameters, $name );
        $content    = $this->before( $name, $parameters );
        $this->write( $content, $parameters, $name );
    }
    
    
    /**
     * Control that parameters is valid
     *
     * @param array $parameters
     * @param string $name
     * @return array
     * @throws InvalidArgumentException
     */
    private function validParameters( array $parameters, string $name ): array
    {
        if( !array_key_exists( 'value', $parameters ) ) {
            throw new InvalidArgumentException( 'The parameter "value" is required for cache' );
        }
        
        foreach( $this->configuration['cache'][$name]['parameters'] as $parameter => $credentials ) {
            if( !array_key_exists( 'default', $credentials ) && !array_key_exists( $parameter, $parameters ) ) {
                throw new InvalidArgumentException( 'The parameter "' . $parameter . '" is required for cache' );
            } else {
                $parameters[$parameter] = $credentials['default'];
            }
            
            if( array_key_exists( 'contraint', $credentials ) ) {
                if( call_user_func_array(
                        $this->configuration['rules']['constraint'][$credentials['constraint']],
                        [ $parameters[$parameter] ] ) !== $this->configuration['rules']['constraint'][$credentials['constraint']['expected']] ) {
                    
                    throw new InvalidArgumentException( 'The parameter "' . $parameter . '" does not respect constraint ' . $credentials['contraint'] );
                }
            }
            
            if( array_key_exists( 'type', $credentials ) ) {
                if( !call_user_func_array(
                        self::TYPE_MAPPER[$credentials['type']],
                        [ $parameters[$parameter] ] ) && $parameters[$parameter] !== NULL ) {
                    
                    throw new InvalidArgumentException( 'The parameter "' . $parameter . '" does not respect the specified type' );
                }
            }
        }
        
        return $parameters;
    }
    
    
    /**
     * Call the before method for revalidations
     *
     * @param string $name
     * @param array $content
     * @param array $parameters
     * @return array|null
     * @throws InvalidConfigurationException
     */
    private function before( string $name, array $parameters ): ?array
    {
        $content = $parameters['value'];
        
        foreach( $this->configuration['cache'][$name]['revalidation_rules'] as $rule ) {
            if( !array_key_exists( $rule, $this->configuration['rules']['revalidation'] ) ) {
                throw new InvalidConfigurationException( 'The revalidation rule "' . $rule . '" was not found' );
            }
            
            /** @var RevalideInterface $revalide */
            $revalide = $this->container->get( $this->configuration['rules']['revalidation'][$rule] );
            $content  = $revalide->before( $content, $parameters );
        }
        
        return $content;
    }
    
    
    private function write( array $content, array $parameters, string $name ): void
    {
        if( !array_key_exists( 'filename', $parameters ) ) {
            throw new InvalidConfigurationException( 'A filename is required for cache component,
             please, add default value for filename or add it in parameters' );
        }
        
        if( $this->hasPath( $name ) ) {
            $path = $this->configuration['cache'][$name]['path'];
            
            if( is_dir( $this->cache_root . $path ) ) {
                mkdir( $this->cache_root . $path );
            }
        }
        
        file_put_contents( $this->getPath( $name ) . $parameters['filename'], serialize( $content ) );
    }
}
