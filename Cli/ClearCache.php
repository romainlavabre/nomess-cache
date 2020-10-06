<?php

namespace Nomess\Component\Cache\Cli;

use Nomess\Component\Cli\Interactive\InteractiveInterface;
use Nomess\Component\Config\ConfigStoreInterface;
use Nomess\Internal\Scanner;
use Nomess\Component\Cli\Executable\ExecutableInterface;

class ClearCache implements ExecutableInterface
{
    use Scanner;
    private const CONFIG_NAME       = 'cache';
    private const GLOBAL_PATH_CACHE = 'var/cache/';
    private ConfigStoreInterface $configStore;
    private InteractiveInterface $interactive;
    
    
    public function __construct(
        ConfigStoreInterface $configStore,
        InteractiveInterface $interactive )
    {
        $this->configStore = $configStore;
        $this->interactive = $interactive;
    }
    
    
    public function exec( array $command ): void
    {
        $cache = $this->configStore->get( self::CONFIG_NAME );
        
        foreach( $cache['cache'] as $name => $array ) {
            if( $array['removed_by_cli'] ) {
                $this->interactive->writeColorBlue( 'Clearing ' . $name );
                $this->remove($array);
            }
        }
    
        $this->removeTwig($twig = ROOT . self::GLOBAL_PATH_CACHE . 'twig');
    
        $this->interactive->writeColorBlue('Clearing twig');
        mkdir($twig);
        $this->setAccesible( $twig);
        $this->interactive->writeColorGreen('All caches cleared');
    }
    
    
    private function remove( array $config ): void
    {
        if( array_key_exists( 'path', $config ) ) {
            $directory = ROOT . self::GLOBAL_PATH_CACHE . $config['path'];
            
            if(is_dir($directory)) {
                foreach( scandir( $directory ) as $file ) {
                    if( $file !== '.' && $file !== '..' ) {
                        $filename = $directory . $file;
            
                        $this->setAccesible( $filename );
                        unlink( $filename );
                    }
                }
    
                $this->setAccesible( $directory );
                rmdir( $directory );
            }
        } elseif( array_key_exists( 'filename', $config['parameters'] )
                  && array_key_exists( 'default', $config['parameters']['filename'] ) ) {
            
            $filename = ROOT . self::GLOBAL_PATH_CACHE . $config['parameters']['filename']['default'];
            
            if(file_exists($filename)) {
    
                if(is_dir($filename)) {
                    $directory = $filename;
                    foreach( scandir( $directory ) as $file ) {
                        if( $file !== '.' && $file !== '..' ) {
                            $filename = $directory . $file;
                
                            $this->setAccesible( $filename );
                            unlink( $filename );
                        }
                    }
        
                    $this->setAccesible( $directory );
                    rmdir( $directory );
                }else {
    
                    $this->setAccesible( $filename );
    
                    unlink( $filename );
                }
            }
        }
    }
    
    
    private function setAccesible( string $path ): void
    {
        chown( $path, 'www-data' );
    }
    
    private function removeTwig(string $dir): void
    {
    
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    
                    if (filetype($dir."/".$object) == "dir") {
                        $this->removeTwig($dir."/".$object);
                    } else{
                        $this->setAccesible( $dir."/".$object);
                        unlink($dir."/".$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}
