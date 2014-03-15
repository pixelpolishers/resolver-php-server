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
use PixelPolishers\Resolver\Adapter\Pdo\Entity\PdoVersion;
use PixelPolishers\Resolver\Entity\Package;
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
            $packageLink = new PdoPackageLink($this);
            $packageLink->setVersionId($row->version_id);
            $packageLink->setPackageVersionId($row->package_version_id);
            $result[] = $packageLink;
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

        $result = new PdoPackage($this);
        $result->setId($obj->id);
        $result->setUserId($obj->user_id);
        $result->setFullname($obj->fullname);
        $result->setDescription($obj->description);
        return $result;
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

        $result = new PdoPackage($this);
        $result->setId($obj->id);
        $result->setUserId($obj->user_id);
        $result->setFullname($obj->fullname);
        $result->setDescription($obj->description);
        return $result;
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
            $entity = new PdoVersion($this);
            $entity->setId($row->id);
            $entity->setCreatedAt($row->created_at);
            $entity->setId($row->id);
            $entity->setLicense($row->license);
            $entity->setPackageId($row->package_id);
            $entity->setReference($row->reference);
            $entity->setReferenceType($row->reference_type);
            $entity->setReferenceUrl($row->reference_url);
            $entity->setUpdatedAt($row->updated_at);
            $entity->setVersion(SemanticVersion::fromString($row->version));

            $versions[] = $entity;
        }

        return $versions;
    }

	public function persistPackage(Package $package)
	{
        if ($package->getId()) {
            $sql = "UPDATE " . $this->getTablePrefix() . "package
                    SET
                        fullname = ?,
                        description = ?
                    WHERE
                        id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(
                $package->getFullname(),
                $package->getDescription(),
                $package->getId(),
            ));
        } else {
            $sql = "INSERT INTO " . $this->getTablePrefix() . "package
                    (fullname, description)
                    VALUES
                    (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array(
                $package->getFullname(),
                $package->getDescription(),
            ));

            $package->setId($this->pdo->lastInsertId());
        }

        foreach ($package->getVersions() as $version) {
            $this->persistVersion($version);
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
}
