<?php

namespace WebChemistry\Images\DI;

use Nette;

class Extension extends Nette\DI\CompilerExtension {
    
    protected $defaults = array(
        'noimage' => 'noimage/noimage.png',
        'registration' => array(
            'texy' => FALSE,
            'upload' => TRUE,
            'multiUpload' => FALSE,
            'presenter' => TRUE
        ),
        'assetsDir' => 'assets',
        'wwwDir' => '%wwwDir%',
        'settings' => array(
            'upload' => array(
                'label' => 'Delete this image?'
            )
        ),
        'router' => array(
            'mask' => 'show-image/<name>[/<size>[/<flag>[/<noimage>]]]',
            'resize' => FALSE,
            'flag' => 0,
            'disable' => FALSE
        )
    );
    
    public function loadConfiguration() {
        $builder = $this->getContainerBuilder();
        
        $config = $this->getSettings();
        
        $builder->addDefinition($this->prefix('storage'))
                    ->setClass('WebChemistry\Images\Storage', array($config['wwwDir'] . '/' . $config['assetsDir'], $config['assetsDir'],$config['noimage'], $config['settings']));
        
        if ($config['router']['disable'] === FALSE) {
            $builder->addDefinition($this->prefix('routerFactory'))
                        ->setFactory('WebChemistry\Images\Router\Factory::createRouter')
                        ->setClass('Nette\Application\IRouter')
                        ->setArguments(array($config['router'], '@WebChemistry\Images\Storage'))
                        ->setAutowired(FALSE);

            if ($config['registration']['presenter']) {
                $builder->addDefinition($this->prefix('presenter'))
                            ->setClass('WebChemistry\Images\Addons\GeneratePresenter', array($config['router']['resize']));
            }
        }
    }
    
    public function beforeCompile() {
        $builder = $this->getContainerBuilder();
        
        $config = $this->getSettings();
        
        $builder->getDefinition('nette.latteFactory')
                    ->addSetup('WebChemistry\Images\Macros\Macros::install(?->getCompiler())', array('@self'));
        
        if ($config['router']['disable'] === FALSE) {
            $builder->getDefinition('router')
                        ->addSetup('WebChemistry\Images\Router\Factory::prepend($service, ?)', array($this->prefix('@routerFactory')));

            $builder->getDefinition('nette.presenterFactory')
                        ->addSetup('setMapping', array(
                            array('ImageStorage' => 'WebChemistry\Images\Addons\*Presenter')
                        ));
        }
    }
    
    public function getSettings() {
        if (method_exists($this, 'validateConfig')) {
            $config = $this->validateConfig($this->defaults, $this->config);
            
            $config['wwwDir'] = Nette\DI\Helpers::expand($config['wwwDir'], $this->getContainerBuilder()->parameters);
        } else {
            $config = $this->getConfig($this->defaults); // deprecated
        }
        
        return $config;
    }
    
    public function afterCompile(Nette\PhpGenerator\ClassType $class) {
        $methods = $class->getMethods();
        $init = $methods['initialize'];
        
        $config = $this->getSettings();
        
        if ($config['registration']['texy']) {
            $init->addBody('WebChemistry\Images\Texy::register($this->getByType(?), $this->getService(?), $this->getByType(?)->getUrl()->getBaseUrl());', array('Texy', $this->prefix('storage'), 'Nette\Http\IRequest'));
        }
        
        if ($config['registration']['upload']) {
            $init->addBody('WebChemistry\Images\Addons\UploadControl::register();');
        }
        
        if ($config['registration']['multiUpload']) {
            $init->addBody('WebChemistry\Images\Addons\MultiUploadControl::register();');
        }
    }
}
