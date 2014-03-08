<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Generator;

class Api
{
    private $searchUrl;
    private $resolverUrl;

    public function getSearchUrl()
    {
        return $this->searchUrl;
    }

    public function setSearchUrl($searchUrl)
    {
        $this->searchUrl = $searchUrl;
    }

    public function getResolverUrl()
    {
        return $this->resolverUrl;
    }

    public function setResolverUrl($resolverUrl)
    {
        $this->resolverUrl = $resolverUrl;
    }
}
