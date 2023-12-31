<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6dc1867fd1644509bd4138a43b2979dd
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6dc1867fd1644509bd4138a43b2979dd::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6dc1867fd1644509bd4138a43b2979dd::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit6dc1867fd1644509bd4138a43b2979dd::$classMap;

        }, null, ClassLoader::class);
    }
}
