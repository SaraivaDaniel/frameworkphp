<?php

namespace Saraiva\Framework\View\Functions;

use Twig_Environment;
use Twig_SimpleFunction;

class Filemtime {

    public static function setTwigFunction(Twig_Environment $twig, $root) {
        $function = new Twig_SimpleFunction('filemtime',
                function($filepath) use ($root) {
            $change_date = @filemtime($root . '/' . $filepath);
            return $filepath . '?' . $change_date;
        });
        $twig->addFunction($function);
    }

}
