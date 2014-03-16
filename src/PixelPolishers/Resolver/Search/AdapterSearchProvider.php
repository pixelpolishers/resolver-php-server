<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Search;

use PixelPolishers\Resolver\Adapter\AdapterInterface;

class AdapterSearchProvider implements SearchProviderInterface
{
    private $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function search($query)
    {
        return $this->adapter->searchPackages($query);
    }
}
