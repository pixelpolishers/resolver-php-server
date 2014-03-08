<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Adapter\Pdo\Entity;

use PixelPolishers\Resolver\Entity\PackageLink;
use PixelPolishers\Resolver\Adapter\Pdo\Pdo;

class PdoPackageLink extends PackageLink
{
    private $pdo;
    private $versionId;
    private $packageVersionId;

    public function __construct(Pdo $pdo)
    {
        parent::__construct();
        $this->pdo = $pdo;
    }

    public function getVersionId()
    {
        return $this->versionId;
    }

    public function setVersionId($versionId)
    {
        $this->versionId = $versionId;
    }

    public function getPackageVersionId()
    {
        return $this->packageVersionId;
    }

    public function setPackageVersionId($packageVersionId)
    {
        $this->packageVersionId = $packageVersionId;
    }

    public function getVersion()
    {
        $result = parent::getVersion();

        if ($result === null) {
            $result = $this->pdo->findVersionById($this->versionId);
            $this->setVersion($result);
        }

        return $result;
    }

    public function getPackageVersion()
    {
        $result = parent::getPackageVersion();

        if ($result === null) {
            $result = $this->pdo->findVersionById($this->packageVersionId);
            $this->setPackageVersion($result);
        }

        return $result;
    }
}
