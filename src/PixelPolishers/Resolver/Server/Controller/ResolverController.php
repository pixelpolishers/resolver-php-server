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
        $result['api'] = array(
            'lookup' => $this->getServer()->getRouter()->getLookupUrl(),
            'search' => $this->getServer()->getRouter()->getSearchUrl(),
            'resolver' => $this->getServer()->getRouter()->getResolverUrl(),
        );

        return $result;
    }
}
