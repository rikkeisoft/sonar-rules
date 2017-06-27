<?php

namespace App;

use GuzzleHttp\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Twig_Environment;
use Twig_Filter;
use Twig_Loader_Filesystem;

class AppServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple['http-client'] = function () {
            return new Client();
        };

        $pimple['renderer'] = function () {
            $loader = new Twig_Loader_Filesystem(__DIR__ . '/../templates');
            $twig = new Twig_Environment($loader);

            // Extend Twig
            $twig->addFilter(new Twig_Filter('idformat', function ($string) {
                return preg_replace('/\:/', '-', $string);
            }));

            return $twig;
        };
    }
}
