<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Server\Router;

use PixelPolishers\Resolver\Server\Controller\ControllerInterface;
use PixelPolishers\Resolver\Server\Controller\FindController;
use PixelPolishers\Resolver\Server\Controller\RepositoryController;

/**
 * The router that find the correct controller for the server.
 */
class Router implements RouterInterface
{
    /**
     * The url that should be accessed for searching.
     *
     * @var string
     */
    private $searchUrl;

    /**
     * The url that should be accessed to retrieve the JSON overview.
     *
     * @var string
     */
    private $resolverUrl;

    /**
     * Initializes a new instance of this class.
     */
    public function __construct()
    {
        $this->searchUrl = '/resolver/search';
        $this->resolverUrl = '/resolver.json';
    }

    /**
     * Gets the search URL.
     *
     * @return string
     */
    public function getSearchUrl()
    {
        return $this->searchUrl;
    }

    /**
     * Sets the search URL.
     *
     * @param string $searchUrl The url to set.
     */
    public function setSearchUrl($searchUrl)
    {
        $this->searchUrl = $searchUrl;
    }

    /**
     * Gets the resolver URL.
     *
     * @return string
     */
    public function getResolverUrl()
    {
        return $this->resolverUrl;
    }

    /**
     * Sets the repository URL.
     *
     * @param string $repositoryUrl The url to set.
     */
    public function setResolverUrl($repositoryUrl)
    {
        $this->resolverUrl = $repositoryUrl;
    }

    /**
     * Finds the controller for the given url.
     *
     * @param string $url The url to parse.
     * @return ControllerInterface
     */
    public function find($url)
    {
        switch ($url) {
            case $this->searchUrl:
                return new FindController();

            case $this->resolverUrl:
                return new RepositoryController();
        }

        return null;
    }
}
