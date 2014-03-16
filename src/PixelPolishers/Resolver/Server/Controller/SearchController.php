<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Server\Controller;

use PixelPolishers\Resolver\Search\SearchProviderInterface;

class SearchController extends AbstractController
{
    private $searchProvider;

    public function __construct(SearchProviderInterface $searchProvider)
    {
        $this->searchProvider = $searchProvider;
    }

    public function getSearchProvider()
    {
        return $this->searchProvider;
    }

    public function execute()
    {
        $result = array();

        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            $result['status'] = 405;
            $result['error'] = 'Method not allowed, only GET is expected.';
            return $result;
        }

        if (!array_key_exists('q', $_GET)) {
            $result['status'] = 400;
            $result['error'] = 'No "q" parameter provided.';
            return $result;
        }

        if (!$this->getSearchProvider()) {
            $result['status'] = 500;
            $result['error'] = 'No search provider configured.';
            return $result;
        }

        $result['status'] = 200;
        $result['query'] = (string)$_GET['q'];
        $result['packages'] = $this->searchPackages((string)$_GET['q']);
        return $result;
    }

    private function searchPackages($query)
    {
        $result = array();

        return $result;
    }
}
