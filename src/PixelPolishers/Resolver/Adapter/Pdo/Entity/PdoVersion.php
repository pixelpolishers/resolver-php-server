<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Adapter\Pdo\Entity;

use PixelPolishers\Resolver\Entity\Version;
use PixelPolishers\Resolver\Adapter\Pdo\Pdo;

class PdoVersion extends Version
{
    private $pdo;
    private $packageId;
    private $syncedDependencies;

    public function __construct(Pdo $pdo)
    {
        parent::__construct();
        $this->pdo = $pdo;
        $this->syncedDependencies = false;
    }

    public function getPackageId()
    {
        return $this->packageId;
    }

    public function setPackageId($packageId)
    {
        $this->packageId = $packageId;
    }

    public function getPackage()
    {
        $package = parent::getPackage();

        if ($package === null) {
            $package = $this->pdo->findPackageById($this->getPackageId());
            $this->setPackage($package);
        }

        return $package;
    }

    public function getDependencies()
    {
        $result = parent::getDependencies();

        if (!$this->syncedDependencies) {
            $this->syncedDependencies = true;
            $result = $this->pdo->findDependencies($this->getId());
            $this->setDependencies($result);
        }

        return $result;
    }
}
