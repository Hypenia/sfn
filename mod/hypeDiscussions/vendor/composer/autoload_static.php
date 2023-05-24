<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit8a32e2e941e8ee4ac4c3d0af13a98757
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Flintstone\\' => 11,
        ),
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Flintstone\\' => 
        array (
            0 => __DIR__ . '/..' . '/fire015/flintstone/src',
        ),
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
    );

    public static $fallbackDirsPsr0 = array (
        0 => __DIR__ . '/../..' . '/classes',
        1 => __DIR__ . '/../..' . '/mod/hypeInteractions/classes',
        2 => __DIR__ . '/../..' . '/mod/hypeLists/classes',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit8a32e2e941e8ee4ac4c3d0af13a98757::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit8a32e2e941e8ee4ac4c3d0af13a98757::$prefixDirsPsr4;
            $loader->fallbackDirsPsr0 = ComposerStaticInit8a32e2e941e8ee4ac4c3d0af13a98757::$fallbackDirsPsr0;

        }, null, ClassLoader::class);
    }
}