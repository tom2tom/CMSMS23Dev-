<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit761e0cd0b509946f5f906763270c2fa4
{
    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'StupidPass\\' => 11,
            'splitbrain\\PHPArchive\\' => 22,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'StupidPass\\' => 
        array (
            0 => __DIR__ . '/..' . '/northox/stupid-password/src',
        ),
        'splitbrain\\PHPArchive\\' =>
        array (
            0 => __DIR__ . '/..' . '/splitbrain/php-archive/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit761e0cd0b509946f5f906763270c2fa4::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit761e0cd0b509946f5f906763270c2fa4::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}