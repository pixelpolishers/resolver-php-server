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
 * The interface that defines a router.
 */
interface RouterInterface
{
    /**
     * Gets the controller map.
     *
     * @return array
     */
    public function getControllers();
    
    /**
     * Finds the controller for the given url.
     *
     * @param string $url The url to parse.
     * @return ControllerInterface
     */
    public function find($url);
}
