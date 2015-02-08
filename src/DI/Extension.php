<?php

namespace WebChemistry\Images\DI;

use Nette;

class Extension extends Nette\DI\CompilerExtension {
    
    protected $defaults = array(
        'noimage' => 'noimage/noimage.png',
        'assetsDir' => 'assets',
        'wwwDir' => '%wwwDir%',
        'settings' => array(
            'upload' => array(
                'label' => 'Delete this image?'
            )
        )
    );
    
    public function loadConfiguration() {
        $builder = $this->getContainerBuilder();
        
        if (method_exists($this, 'validateConfig')) {
            $config = $this->validateConfig($this->defaults, $this->config);
            
            $config['wwwDir'] = Nette\DI\Helpers::expand($config['wwwDir'], $builder->parameters);
        } else {
            $config = $this->getConfig($this->defaults); // deprecated
        }
        
        $builder->addDefinition($this->prefix('storage'))
                    ->setClass('WebChemistry\Images\Storage', array($config['wwwDir'] . '/' . $config['assetsDir'], $config['assetsDir'],$config['noimage'], $config['settings']));
    }
    
    public function beforeCompile() {
        $builder = $this->getContainerBuilder();
        
        $builder->getDefinition('nette.latteFactory')
                    ->addSetup('WebChemistry\Images\Macros\Macros::install(?->getCompiler())', array('@self'));
    }
}
