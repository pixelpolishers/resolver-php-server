<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Server;

use PixelPolishers\Resolver\Adapter\AdapterInterface;
use PixelPolishers\Resolver\Server\Router\RouterInterface;
use PixelPolishers\Resolver\Server\Controller\ControllerInterface;

/**
 * A webserver listener that acts on HTTP requests.
 */
class Server
{
    /**
     * The router that is used to find controllers.
     *
     * @var RouterInterface
     */
    private $router;

    /**
     * The adapter that is used to locate the packages.
     *
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * Initializes a new instance of this class.
     *
     * @param RouterInterface $router The router used to match URL's.
     * @param AdapterInterface $adapter The adapter used to locate the packages.
     */
    public function __construct(RouterInterface $router, AdapterInterface $adapter)
    {
        $this->router = $router;
        $this->adapter = $adapter;
    }

    /**
     * Gets the adapter.
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Gets the router.
     *
     * @return RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Runs the listener.
     *
     * @throws \RuntimeException
     */
    public function run()
    {
        $url = parse_url($_SERVER['REQUEST_URI']);

        $controller = $this->router->find($url['path']);
        if (!$controller instanceof ControllerInterface) {
            throw new \RuntimeException('No valid controller found.');
        }

        $controller->setServer($this);

        $result = $controller->execute();

        $data = json_encode($result);

        if (array_key_exists('callback', $_GET)) {
            $data = $_GET['callback'] . '(' . $data . ')';
        }
        
        return $data;
    }
}
