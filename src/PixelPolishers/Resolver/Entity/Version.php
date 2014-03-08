<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Entity;

class Version
{
    private $id;
    private $package;
    private $version;
    private $reference;
    private $referenceType;
    private $referenceUrl;
    private $license;
    private $createdAt;
    private $updatedAt;
    private $dependencies;

    public function __construct()
    {
        $this->dependencies = array();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getPackage()
    {
        return $this->package;
    }

    public function setPackage($package)
    {
        $this->package = $package;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    public function getReferenceType()
    {
        return $this->referenceType;
    }

    public function setReferenceType($referenceType)
    {
        $this->referenceType = $referenceType;
    }

    public function getReferenceUrl()
    {
        return $this->referenceUrl;
    }

    public function setReferenceUrl($referenceUrl)
    {
        $this->referenceUrl = $referenceUrl;
    }

    public function getLicense()
    {
        return $this->license;
    }

    public function setLicense($license)
    {
        $this->license = $license;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        if (!$createdAt instanceof \DateTime) {
            $createdAt = new \DateTime($createdAt);
        }
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        if (!$updatedAt instanceof \DateTime) {
            $updatedAt = new \DateTime($updatedAt);
        }
        $this->updatedAt = $updatedAt;
    }

    public function addDependency(PackageLink $package)
    {
        $this->dependencies[] = $package;
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    public function setDependencies($requiredPackages)
    {
        $this->dependencies = array();
        foreach ($requiredPackages as $requiredPackage) {
            $this->addDependency($requiredPackage);
        }
    }
}
