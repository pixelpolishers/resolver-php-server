<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Adapter\Pdo\Entity;

use PixelPolishers\Resolver\Entity\Package;
use PixelPolishers\Resolver\Adapter\Pdo\Pdo;

class PdoPackage extends Package
{
    private $pdo;

    public function __construct(Pdo $pdo)
    {
        parent::__construct();
        $this->pdo = $pdo;
    }

    public function getVersions()
    {
        $result = parent::getVersions();

        if (count($result) == 0) {
            $result = $this->pdo->findVersionsByPackageId($this->getId());
            $this->setVersions($result);
        }

        return $result;
    }
}
