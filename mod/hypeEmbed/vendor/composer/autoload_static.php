<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitad63f6c2dc5e5f137fe21eae6a317489
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
    );

    public static $fallbackDirsPsr0 = array (
        0 => __DIR__ . '/../..' . '/classes',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitad63f6c2dc5e5f137fe21eae6a317489::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitad63f6c2dc5e5f137fe21eae6a317489::$prefixDirsPsr4;
            $loader->fallbackDirsPsr0 = ComposerStaticInitad63f6c2dc5e5f137fe21eae6a317489::$fallbackDirsPsr0;

        }, null, ClassLoader::class);
    }
}
