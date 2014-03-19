<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Server\Controller;

class ResolverController extends AbstractController
{
    public function execute()
    {
        $result = array();
        $result['packages'] = array();
        $result['api'] = array();

        $router = $this->getServer()->getRouter();
        foreach ($router->getControllers() as $url => $controller) {
            $class = get_class($controller);

            $index = strrpos($class, '\\') + 1;

            $classPart = substr($class, $index, -strlen('Controller'));

            $result['api'][strtolower($classPart)] = $url;
        }
        
        return $result;
    }
}
