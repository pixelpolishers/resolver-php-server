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
use PixelPolishers\Resolver\Entity\Vendor;
use PixelPolishers\Resolver\Entity\Version;
use PixelPolishers\Resolver\SemanticVersion;

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
        $result->setId($obj->id);
        $result->setLicense($obj->license);
        $result->setPackageId($obj->package_id);
        $result->setReference($obj->reference);
        $result->setReferenceType($obj->reference_type);
        $result->setReferenceUrl($obj->reference_url);
        $result->setUpdatedAt($obj->updated_at);
        $result->setVersion(SemanticVersion::fromString($obj->version));
        return $result;
	}

    public function findDependencies($versionId)
    {
        $sql = "SELECT d.*
                FROM " . $this->getTablePrefix() . "dependency AS d
                WHERE d.version_id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $versionId, \PDO::PARAM_INT);
        $stmt->execute();

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
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $obj = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$obj) {
            return null;
        }

		return $this->buildUpPackage($obj);
    }

    public function findPackageByName($name)
    {
        $sql = "SELECT p.*
                FROM " . $this->getTablePrefix() . "package AS p
                WHERE p.fullname = :name";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':name', $name, \PDO::PARAM_STR);
        $stmt->execute();

        $obj = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$obj) {
            return null;
        }

		return $this->buildUpPackage($obj);
    }

    public function findVendorById($id)
    {
        $sql = "SELECT v.*
                FROM " . $this->getTablePrefix() . "vendor AS v
                WHERE v.id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

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
        $stmt->bindParam(':name', $name, \PDO::PARAM_STR);
        $stmt->execute();

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
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $obj = $stmt->fetch(\PDO::FETCH_OBJ);
        if (!$obj) {
            return null;
        }

		return $this->buildUpVersion($obj);
    }

    public function findVersions($name)
    {
        $sql = "SELECT v.*
                FROM " . $this->getTablePrefix() . "version AS v
                LEFT JOIN " . $this->getTablePrefix() . "package AS p
                ON p.id = v.package_id
                WHERE p.fullname = :name";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':name', $name, \PDO::PARAM_STR);
        $stmt->execute();

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
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

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
                        description = ?
                    WHERE
                        id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
        } else {
            $sql = "INSERT INTO " . $this->getTablePrefix() . "package
                    (created_at, updated_at, user_id, vendor_id, name, fullname, description)
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);

            $package->setId($this->pdo->lastInsertId());
        }

        foreach ($package->getVersions() as $version) {
            $this->persistVersion($version);
        }
    }

	public function persistVendor(Vendor $vendor)
	{
        $data = array(
			$vendor->getName(),
        );

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
            $version->getReference(),
            $version->getReferenceType(),
            $version->getReferenceUrl(),
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
                        reference = ?,
                        reference_type = ?,
                        reference_url = ?,
                        license = ?,
                        created_at = ?,
                        updated_at = ?
                    WHERE
                        id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
        } else {
            $sql = "INSERT INTO " . $this->getTablePrefix() . "version
                    (package_id, version, reference, reference_type, reference_url, license, created_at, updated_at)
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);

            $version->setId($this->pdo->lastInsertId());
        }
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
