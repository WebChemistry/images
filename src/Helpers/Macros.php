<?php

namespace WebChemistry\Images\Helpers;

use Latte;

class Macros extends Latte\Macros\MacroSet {
    
    public static function install(Latte\Compiler $compiler) {
        $me = new static($compiler);
        
        $me->addMacro('img', [$me, 'beginImg'], NULL, [$me, 'attrImg']);
        $me->addMacro('imgRes', [$me, 'beginImgRes']);
    }
    
    public function beginImgRes(Latte\MacroNode $node, Latte\PhpWriter $writer) {
        return $writer->write('echo $_image->setBasePath($template->basePath)->createResponsiveLinks(%node.args)');
    }
    
    public function beginImg(Latte\MacroNode $node, Latte\PhpWriter $writer) {
        return $writer->write('echo $template->basePath . $_image->createLink(%node.args)');
    }
    
    public function attrImg(Latte\MacroNode $node, Latte\PhpWriter $writer) {
        if ($node->htmlNode->name === 'a') {
            $attr = 'href=';
        } else {
            $attr = 'src=';
        }
        
        return $writer->write('echo \' ' . $attr . '"\' . ($template->basePath) . $_image->createLink(%node.args) . \'"\'');
    }
}
