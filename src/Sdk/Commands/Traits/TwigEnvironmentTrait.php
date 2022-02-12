<?php
namespace Tygh\Sdk\Commands\Traits;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

trait TwigEnvironmentTrait
{
    private ?Environment $twig;

    protected function twig(): Environment {
        if (!isset($this->twig)){
            $this->twig = $this->createTwigEnvironment();
        }
        return $this->twig;
    }

    private function createTwigEnvironment(): Environment
    {
        $loader = new FilesystemLoader([
            $_SERVER['HOME'] . '/.cscart-sdk/templates',
            CSCART_SDK_ROOT . '/templates' // SDK default dir
        ]);

        return new Environment($loader );
    }
}