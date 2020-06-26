<?php

spl_autoload_register(
    function($class) {
        static $classes = array(
            'documentchecker' => '/DocumentChecker.inc.php',
        );

        $className = strtolower($class);

        if(isset($classes[$className])){
            require __DIR__ . $classes[$className];
        }
    },
    true,
    false
);