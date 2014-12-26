<?php

namespace WebChemistry\Images;

use Nette;

class Extension extends Nette\DI\CompilerExtension {
    
    private $config = [
        'wwwDir' => NULL,
        'imageDir' => 'assets',
        'original' => 'original',
        'noimage' => 'noimage.png',
        'models' => [
            'bootstrap' => [
                '768' => '',
                '992' => '(min-width:768px)',
                '1200' => '(min-width:992px)',
                NULL => '(min-wudth:1200)' // Original file
            ]
        ],
        'addons' => [
            'upload' => [
                'label' => 'Delete image ?'
            ]
        ]
    ];
    
    public function loadConfiguration() {
        $builder = $this->getContainerBuilder();
        
        $config = $this->getConfig($this->config);
        
        $builder->addDefinition($this->prefix('root'))
                    ->setClass('WebChemistry\Images\Root', array($config['wwwDir'], $config['addons']))
                    ->addSetup('setImageDir', array($config['imageDir']))
                    ->addSetup('setOriginal', array($config['original']))
                    ->addSetup('setNoImage', array($config['noimage']))
                    ->addSetup('setModels', array($config['models']));
        
        $builder->addDefinition($this->prefix('storage'))
                    ->setClass('WebChemistry\Images\Storage', array('@WebChemistry\Images\Root', 'storageService'));
    }
    
    public function beforeCompile() {
        $builder = $this->getContainerBuilder();
        
        $builder->getDefinition('nette.latteFactory')
                    ->addSetup('WebChemistry\Images\Helpers\Macros::install(?->getCompiler())', array('@self'));
    }
    
    public function getConfig(array $defaults = NULL) {
        $config = parent::getConfig($defaults);
        
        if (!$config['wwwDir']) {
            $config['wwwDir'] = $this->getContainerBuilder()->parameters['wwwDir'];
        }
        
        return $config;
    }
}
