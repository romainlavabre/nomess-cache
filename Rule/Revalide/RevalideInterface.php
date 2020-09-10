<?php

namespace Nomess\Component\Cache\Rule\Revalide;

interface RevalideInterface
{
    
    public function before( array $content, array $options ): array;
    
    
    public function revalide( array $content ): ?array;
}
