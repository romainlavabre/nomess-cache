<?php

namespace Nomess\Component\Cache\Builder;

use Nomess\Component\Cache\Exception\InvalidConfigurationException;

interface CacheBuilderInterface
{
    
    /**
     * @param array $parameters
     * @param string $name
     * @return void
     * @throws InvalidConfigurationException
     */
    public function add( array $parameters, string $name ): void;
}
