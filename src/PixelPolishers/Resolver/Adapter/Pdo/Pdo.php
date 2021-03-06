<?php

/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Adapter\Pdo;

use PixelPolishers\Resolver\Adapter\AdapterInterface;
use PixelPolishers\Resolver\Adapter\Pdo\Entity\PdoPackage;
use PixelPolishers\Resolver\Adapter\Pdo\Entity\PdoPackageLink;
use PixelPolishers\Resolver\Adapter\Pdo\Entity\PdoVendor;
use PixelPolishers\Resolver\Adapter\Pdo\Entity\PdoVersion;
use PixelPolishers\Resolver\Entity\Package;
use PixelPolishers\Resolver\Entity\PackageLink;
use PixelPolishers\Resolver\Entity\Vendor;
use PixelPolishers\Resolver\Entity\Version;

class Pdo implements AdapterInterface
{
    private $pdo;
    private $tablePrefix;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;
    }

    private function buildUpPackage($obj)
    {
        $result = new PdoPackage($this);
        $result->setId($obj->id);
        $result->setCreatedAt($obj->created_at);
        $result->setUpdatedAt($obj->updated_at);
        $result->setUserId($obj->user_id);
        $result->setVendorId($obj->vendor_id);
        $result->setName($obj->name);
        $result->setFullname($obj->fullname);
        $result->setDescription($obj->description);
        $result->setRepositoryUrl($obj->repository_url);
        $result->setRepositoryType($obj->repository_type);
        return $result;
    }

    private function buildUpPackageLink($obj)
    {
        $result = new PdoPackageLink($this);
        $result->setVersionId($obj->version_id);
        $result->setPackageVersionId($obj->package_version_id);
        return $result;
    }

    private function buildUpVendor($obj)
    {
        $result = new PdoVendor($this);
        $result->setId($obj->id);
        $result->setName($obj->name);
        return $result;
    }

    private function buildUpVersion($obj)
    {
        $result = new PdoVersion($this);
        $result->setId($obj->id);
        $result->setCreatedAt($obj->created_at);
        $result->setUpdatedAt($obj->updated_at);
        $result->setVersion($obj->version);
        $result->setLicense($obj->license);
        $result->setPackageId($obj->package_id);
        $result->setReferenceName($obj->reference_name);
        $result->setReferenceHash($obj->reference_hash);
        return $result;
    }

    public function findDependencies($versionId)
    {
        $sql = "SELECT d.*
                FROM " . $this->getTablePrefix() . "dependency AS d
                WHERE d.version_id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('id' => $versionId));

        $result = array();
        foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $row) {
            $result[] = $this->buildUpPackageLink($row);
        }
        return $result;
    }

    public function findPackageById($id)
    {
        $sql = "SELECT p.*
                FROM " . $this->getTablePrefix() . "package AS p
                WHERE p.id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('id' => $id));

        $obj = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$obj) {
            return null;
        }

        return $this->buildUpPackage($obj);
    }

    public function findPackageByFullname($fullName)
    {
        $sql = "SELECT p.*
                FROM " . $this->getTablePrefix() . "package AS p
                WHERE p.fullname = :fullname";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('fullname' => $fullName));

        $obj = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$obj) {
            return null;
        }

        return $this->buildUpPackage($obj);
    }

    public function findPackageByVendor(Vendor $vendor)
    {
        $sql = "SELECT p.*
                FROM " . $this->getTablePrefix() . "package AS p
                WHERE p.vendor_id = :vendor";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('vendor' => $vendor->getId()));

        $result = array();
        foreach ($stmt->fetchAll(\PDO::FETCH_CLASS) as $row) {
            $result[] = $this->buildUpPackage($row);
        }
        return $result;
    }

    public function findVendorById($id)
    {
        $sql = "SELECT v.*
                FROM " . $this->getTablePrefix() . "vendor AS v
                WHERE v.id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('id' => $id));

        $obj = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$obj) {
            return null;
        }

        return $this->buildUpVendor($obj);
    }

    public function findVendorByName($name)
    {
        $sql = "SELECT v.*
                FROM " . $this->getTablePrefix() . "vendor AS v
                WHERE v.name = :name";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('name' => $name));

        $obj = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$obj) {
            return null;
        }

        return $this->buildUpVendor($obj);
    }

    public function findVersionById($id)
    {
        $sql = "SELECT v.*
                FROM " . $this->getTablePrefix() . "version AS v
                WHERE v.id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('id' => $id));

        $obj = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$obj) {
            return null;
        }

        return $this->buildUpVersion($obj);
    }

    public function findVersions($packageFullName)
    {
        $sql = "SELECT v.*
                FROM " . $this->getTablePrefix() . "version AS v
                LEFT JOIN " . $this->getTablePrefix() . "package AS p
                ON p.id = v.package_id
                WHERE p.fullname = :fullname";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('fullname' => $packageFullName));

        $versions = array();
        foreach ($stmt->fetchAll(\PDO::FETCH_CLASS) as $row) {
            $versions[] = $this->buildUpVersion($row);
        }

        return $versions;
    }

    public function findVersionsByPackageId($id)
    {
        $sql = "SELECT v.*
                FROM " . $this->getTablePrefix() . "version AS v
				WHERE v.package_id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('id' => $id));

        $versions = array();
        foreach ($stmt->fetchAll(\PDO::FETCH_CLASS) as $row) {
            $versions[] = $this->buildUpVersion($row);
        }

        return $versions;
    }

    public function searchPackages($query)
    {
        $sql = "SELECT p.*
                FROM " . $this->getTablePrefix() . "package AS p
				WHERE p.fullname LIKE :query
                OR p.description LIKE :query";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array('query' => '%' . $query . '%'));

        $result = array();
        foreach ($stmt->fetchAll(\PDO::FETCH_CLASS) as $row) {
            $result[] = $this->buildUpPackage($row);
        }
        return $result;
    }

    public function persistPackage(Package $package)
    {
        $this->persistVendor($package->getVendor());

        $data = array(
            $package->getCreatedAt()->format('Y-m-d H:i:s'),
            $package->getUpdatedAt()->format('Y-m-d H:i:s'),
            $package->getUserId(),
            $package->getVendor()->getId(),
            $package->getName(),
            $package->getFullname(),
            $package->getDescription(),
            $package->getRepositoryUrl(),
            $package->getRepositoryType(),
        );

        if ($package->getId()) {
            $data[] = $package->getId();

            $sql = "UPDATE " . $this->getTablePrefix() . "package
                    SET
						created_at = ?,
						updated_at = ?,
						user_id = ?,
						vendor_id = ?,
						name = ?,
                        fullname = ?,
                        description = ?,
                        repository_url = ?,
                        repository_type = ?
                    WHERE
                        id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
        } else {
            $sql = "INSERT INTO " . $this->getTablePrefix() . "package
                    (created_at, updated_at, user_id, vendor_id, name, fullname, description, repository_url, repository_type)
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);

            $package->setId($this->pdo->lastInsertId());
        }

        // Persist the versions:
        $persistedVersions = array();
        foreach ($package->getVersions() as $version) {
            $this->persistVersion($version);
            $persistedVersions[] = $version->getId();
        }

        // Remove all versions that were not persisted:
        $sql = "DELETE FROM " . $this->getTablePrefix() . "version WHERE package_id = ? AND id NOT IN (" . implode(', ', $persistedVersions) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($package->getId()));
    }

    public function persistVendor(Vendor $vendor)
    {
        $data = array(
            $vendor->getName(),
        );
        
        if (!$vendor->getId()) {
            $existingVendor = $this->findVendorByName($vendor->getName());
            
            if ($existingVendor !== null) {
                $vendor->setId($existingVendor->getId());
                return;
            }
        }

        if ($vendor->getId()) {
            $data[] = $vendor->getId();

            $sql = "UPDATE " . $this->getTablePrefix() . "vendor
                    SET
                        name = ?
                    WHERE
                        id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
        } else {
            $sql = "INSERT INTO " . $this->getTablePrefix() . "vendor
                    (name)
                    VALUES
                    (?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);

            $vendor->setId($this->pdo->lastInsertId());
        }
    }

    public function persistVersion(Version $version)
    {
        $data = array(
            $version->getPackage()->getId(),
            $version->getVersion(),
            $version->getReferenceHash(),
            $version->getReferenceName(),
            $version->getLicense(),
            $version->getCreatedAt()->format('Y-m-d H:i:s'),
            $version->getUpdatedAt()->format('Y-m-d H:i:s'),
        );
        
        if ($version->getId()) {
            $data[] = $version->getId();

            $sql = "UPDATE " . $this->getTablePrefix() . "version
                    SET
                        package_id = ?,
                        version = ?,
                        reference_hash = ?,
                        reference_name = ?,
                        license = ?,
                        created_at = ?,
                        updated_at = ?
                    WHERE
                        id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
        } else {
            $sql = "INSERT INTO " . $this->getTablePrefix() . "version
                    (package_id, version, reference_hash, reference_name, license, created_at, updated_at)
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);

            $version->setId((int)$this->pdo->lastInsertId());
        }
        
        $this->persistDependencies($version, $version->getDependencies());
    }

    private function persistDependencies(Version $version, array $dependencies)
    {
        $sql = "DELETE FROM " . $this->getTablePrefix() . "dependency WHERE version_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($version->getId()));

        foreach ($dependencies as $dependency) {
            $this->persistDependency($dependency);
        }
    }
    
    private function persistDependency(PackageLink $packageLink)
    {
        $data = array(
            $packageLink->getVersion()->getId(),
            $packageLink->getPackageVersion()->getId(),
        );
        
        $sql = "INSERT INTO " . $this->getTablePrefix() . "dependency
                (version_id, package_version_id)
                VALUES
                (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
    }

    public function removePackage(Package $package)
    {
        $sql = "DELETE FROM " . $this->getTablePrefix() . "package WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($package->getId()));
    }

    public function removeVendor(Vendor $vendor)
    {
        $sql = "DELETE FROM " . $this->getTablePrefix() . "vendor WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($vendor->getId()));
    }

    public function removeVersion(Version $version)
    {
        $sql = "DELETE FROM " . $this->getTablePrefix() . "version WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array($version->getId()));
    }
}
