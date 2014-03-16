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
    private $userId;
    private $fullname;
    private $description;
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

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getName()
    {
		list($vendor, $name) = explode('/', $this->getFullname(), 2);
		
		return $name;
    }

    public function getVendor()
    {
		list($vendor, $name) = explode('/', $this->getFullname(), 2);
		
		return $vendor;
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

    public function addVersion(Version $version)
    {
        $this->versions[] = $version;
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
