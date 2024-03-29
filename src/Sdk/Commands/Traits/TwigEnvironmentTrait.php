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
        $localTemplatesFolder = $_SERVER['HOME'] . '/.cscart-sdk/templates';
        $loader = new FilesystemLoader();
        if (file_exists($localTemplatesFolder)) {
            $loader->addPath($localTemplatesFolder);
        }
        $loader->addPath($this->getSdkTemplatesDir());

        return new Environment($loader );
    }

    abstract protected function getSdkTemplatesDir() : string;
}
