<?php


namespace Nomess\Component\Cache;


use Nomess\Component\Cache\Exception\InvalidConfigurationException;
use Nomess\Component\Cache\Exception\InvalidSendException;
use Nomess\Component\Config\Exception\ConfigurationNotFoundException;

interface CacheHandlerInterface
{
    
    /**
     * Return the value of cache
     *
     * @param string $name          name of configuration
     * @param string|null $filename if is null, all cache will be returned, if is not inside specific directory an
     *                              exception is throw
     * @return array|string|null
     * @throws ConfigurationNotFoundException
     * @throws InvalidSendException
     */
    public function get( string $name, string $filename = NULL );
    
    
    /**
     * Add a file in cache
     *
     * @param string $name
     * @param array $parameters if parameter does not respect constraint, an exception is throw
     * @return CacheHandlerInterface
     * @throws InvalidConfigurationException
     */
    public function add( string $name, array $parameters ): CacheHandlerInterface;
    
    
    /**
     * Invalid a file or all file
     *
     * @param string $name
     * @param string|null $filename if is null invalid all file, if cache is not in specific directory, an exception is
     *                              throw
     * @return CacheHandlerInterface
     * @throws ConfigurationNotFoundException
     * @throws InvalidSendException
     */
    public function invalid( string $name, string $filename = NULL ): CacheHandlerInterface;
}
