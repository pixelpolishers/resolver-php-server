<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Entity;

class Package
{
    private $id;
    private $createdAt;
    private $updatedAt;
    private $userId;
    private $vendor;
    private $name;
    private $fullname;
    private $description;
    private $repositoryUrl;
    private $repositoryType;
    private $versions;

    public function __construct()
    {
        $this->versions = array();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
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

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getVendor()
    {
        return $this->vendor;
    }

    public function setVendor(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }

    public function getName()
    {
		return $this->name;
    }

    public function setName($name)
    {
		$this->name = $name;
    }

    public function getFullname()
    {
        return $this->fullname;
    }

    public function setFullname($fullname)
    {
        $this->fullname = $fullname;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getRepositoryUrl()
    {
        return $this->repositoryUrl;
    }

    public function setRepositoryUrl($repositoryUrl)
    {
        $this->repositoryUrl = $repositoryUrl;
    }

    public function getRepositoryType()
    {
        return $this->repositoryType;
    }

    public function setRepositoryType($repositoryType)
    {
        $this->repositoryType = $repositoryType;
    }
    
    public function addVersion(Version $version)
    {
        if (!in_array($version, $this->versions)) {
            $this->versions[] = $version;

            $version->setPackage($this);
        }
    }

    public function getVersions()
    {
        return $this->versions;
    }

    public function setVersions($versions)
    {
        $this->versions = array();
        foreach ($versions as $version) {
            $this->addVersion($version);
        }
    }
}
