<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Entity;

use PixelPolishers\Resolver\Entity\Version;

class PackageLink
{
    private $version;
    private $packageVersion;

    public function __construct()
    {
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion(Version $version)
    {
        $this->version = $version;
    }

    public function getPackageVersion()
    {
        return $this->packageVersion;
    }

    public function setPackageVersion(Version $packageVersion)
    {
        $this->packageVersion = $packageVersion;
    }
}
