<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Server\Controller;

use PixelPolishers\Resolver\Server\Server;

abstract class AbstractController implements ControllerInterface
{
    private $adapter;
    private $server;

    public function __construct()
    {
    }

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function setServer(Server $server)
    {
        $this->server = $server;
        $this->adapter = $server->getAdapter();
    }

    public abstract function execute();
}
