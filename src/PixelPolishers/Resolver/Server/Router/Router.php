<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Server\Router;

use PixelPolishers\Resolver\Server\Controller\ControllerInterface;

/**
 * The router that find the correct controller for the server.
 */
class Router implements RouterInterface
{
    /**
     * The controller map with the url as key and the controller as value.
     *
     * @var array
     */
    private $controllerMap;

    /**
     * Initializes a new instance of this class.
     */
    public function __construct()
    {
        $this->controllerMap = array();
    }

    /**
     * Gets the controller map.
     *
     * @return array
     */
    public function getControllers()
    {
        return $this->controllerMap;
    }

    /**
     * Sets the controller.
     *
     * @param string $url The url to set.
     * @param ControllerInterface $controller The controller to set.
     */
    public function setController($url, ControllerInterface $controller)
    {
        $this->controllerMap[$url] = $controller;
    }

    /**
     * Finds the controller for the given url.
     *
     * @param string $url The url to parse.
     * @return ControllerInterface
     */
    public function find($url)
    {
        if (!array_key_exists($url, $this->controllerMap)) {
            return null;
        }

        return $this->controllerMap[$url];
    }
}
