<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf1d625195e941dccd0f31d3978bb54ea
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'M' => 
        array (
            'Mythical\\Dash\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'Mythical\\Dash\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf1d625195e941dccd0f31d3978bb54ea::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf1d625195e941dccd0f31d3978bb54ea::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitf1d625195e941dccd0f31d3978bb54ea::$classMap;

        }, null, ClassLoader::class);
    }
}
