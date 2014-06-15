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
    private $createdAt;
    private $updatedAt;
    private $version;
    private $license;
    private $referenceName;
    private $referenceHash;
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

    public function setPackage(Package $package)
    {
        if ($this->package !== $package) {
            $this->package = $package;

            $package->addVersion($this);
        }
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

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $this->version = $version;
    }

    public function getLicense()
    {
        return $this->license;
    }

    public function setLicense($license)
    {
        $this->license = $license;
    }

    public function getReferenceName()
    {
        return $this->referenceName;
    }

    public function setReferenceName($referenceName)
    {
        $this->referenceName = $referenceName;
    }

    public function getReferenceHash()
    {
        return $this->referenceHash;
    }

    public function setReferenceHash($referenceHash)
    {
        $this->referenceHash = $referenceHash;
    }
    
    public function addDependency(PackageLink $package)
    {
        $package->setVersion($this);
        
        $this->dependencies[] = $package;
    }
    
    public function clearDependencies()
    {
        $this->dependencies = array();
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }
    
    public function removeDependency(PackageLink $package)
    {
        foreach ($this->dependencies as $k => $dependency) {
            if ($dependency === $package) {
                unset($this->dependencies[$k]);
            }
        }
    }

    public function setDependencies($requiredPackages)
    {
        $this->clearDependencies();
        foreach ($requiredPackages as $requiredPackage) {
            $this->addDependency($requiredPackage);
        }
    }
}
