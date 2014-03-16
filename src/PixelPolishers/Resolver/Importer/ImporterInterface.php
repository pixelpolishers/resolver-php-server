<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Importer;

use PixelPolishers\Resolver\Entity\Package;

interface ImporterInterface
{
    public function import($url, Package $package = null);
}
