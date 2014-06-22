<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Importer;

use PixelPolishers\Resolver\Adapter\AdapterInterface;
use PixelPolishers\Resolver\Entity\Package;
use PixelPolishers\Resolver\Entity\Version;

abstract class AbstractImporter implements ImporterInterface
{
    /**
     * @var AdapterInterface
     */
    private $adapter;
    
    private $user;
    
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }
    
    protected function getAdapter()
    {
        return $this->adapter;
    }
    
    public function getUser()
    {
        return $this->user;
    }
    
    public function setUser($user)
    {
        $this->user = $user;
    }
    
    protected function mergePackages(Package $oldPackage, Package $newPackage)
    {
        $oldPackage->setUpdatedAt(new \DateTime());
        $oldPackage->setDescription($newPackage->getDescription());

        // Remove all versions that do not exist anymore and update the existing versions:
        foreach ($oldPackage->getVersions() as $version) {
            $newVersion = $this->findVersion($newPackage, $version);
            if (!$newVersion) {
                $oldPackage->removeVersion($version);
            } else {
                $version->setReferenceHash($newVersion->getReferenceHash());
                $version->setLicense($newVersion->getLicense());
                $version->setUpdatedAt(new \DateTime());
                $this->mergeDependencies($version, $newVersion);
            }
        }

        // Add the new versions:
        foreach ($newPackage->getVersions() as $version) {
            $oldVersion = $this->findVersion($oldPackage, $version);
            if ($oldVersion === null) {
                $oldPackage->addVersion($version);
            }
        }
    }
    
    private function mergeDependencies(Version $oldVersion, Version $newVersion)
    {
        // Remove all versions that do not exist anymore and update the existing versions:
        foreach ($oldVersion->getDependencies() as $dependency) {
            
            $newDependency = null;
            foreach ($newVersion->getDependencies() as $tmpDep) {
                if ($tmpDep->getPackageVersion()->getId() == 
                    $dependency->getPackageVersion()->getId()) {
                    $newDependency = $tmpDep;
                    break;
                }
            }
            
            if (!$newDependency) {
                $oldVersion->removeDependency($dependency);
            }
        }

        // Add the new versions:
        foreach ($newVersion->getDependencies() as $dependency) {
            
            $oldDependency = null;
            foreach ($oldVersion->getDependencies() as $tmpDep) {
                if ($tmpDep->getPackageVersion()->getId() == 
                    $dependency->getPackageVersion()->getId()) {
                    $oldDependency = $tmpDep;
                    break;
                }
            }
            
            if (!$oldDependency) {
                $oldVersion->addDependency($dependency);
            }
        }
    }
    
    private function findVersion(Package $package, Version $version)
    {
        foreach ($package->getVersions() as $packageVersion) {
            if ($packageVersion->getVersion() == $version->getVersion()) {
                return $packageVersion;
            }
        }
        return null;
    }
    
    protected function findClosestVersion(Package $package, $version)
    {
        $versions = $package->getVersions();
        
        // Sort the versions from high to low:
        usort($versions, function($lft, $rgt) {
            // TODO
            /* $semVer1 = $lft->getVersion();
            $semVer2 = $rgt->getVersion();

            return version_compare($semVer1, $semVer2, '<');*/
            return 0;
        });
        
        // Return the newest:
        if ($version === '*') {
            return $versions[0];
        }
    }
    
    protected function findDependency($name, $version)
    {
        $package = $this->getAdapter()->findPackageByFullname($name);
        
        return $this->lookupVersion($package->getVersions(), $version);
    }

    private function lookupVersion(array $versions, $pckVersion)
    {
        // Sort the versions from high to low:
        usort($versions, function($lft, $rgt) {
            $semVer1 = $lft->getVersion();
            $semVer2 = $rgt->getVersion();

            return version_compare($semVer1, $semVer2, '<');
        });

        // Any version is ok, use the newest:
        if ($pckVersion == '*') {
            return $versions[0];
        }

        // When there are no wildcards, find the version number:
        if (strpos($pckVersion, '*') === false) {
            return $this->lookupDirectVersion($versions, $pckVersion);
        }

        return $this->lookupAdvancedVersion($versions, $pckVersion);
    }

    private function lookupDirectVersion($versions, $pckVersion)
    {
        foreach ($versions as $version) {
            if ($version->getVersion() == $pckVersion) {
                return $version;
            }
        }

        return null;
    }

    private function lookupAdvancedVersion($versions, $pckVersion)
    {
        $parts = explode('.', $pckVersion);

        // In case all three components are provided:
        if (count($parts) == 3) {
            $versions = $this->stripNonWildcards($versions, $parts[0], $parts[1], $parts[2]);
        } else if (count($parts) == 2) {
            if ($parts[0] == '*') {
                $versions = $this->stripNonWildcards($versions, '*', '*', $parts[1]);
            } else if ($parts[1] == '*') {
                $versions = $this->stripNonWildcards($versions, $parts[0], '*', '*');
            }
        } else {
            throw new \Exception('Direct version should have already been parsed.');
        }

        return $versions[0];
    }

    private function stripNonWildcards($versions, $major, $minor, $patch)
    {
        if ($major != '*') {
            for ($i = count($versions) - 1; $i >= 0; --$i) {
                $semVer = $versions[$i]->getVersion();

                if ($semVer->getMajor() != $major) {
                    unset($versions[$i]);
                }
            }

            $versions = array_values($versions);
        }

        if ($minor != '*') {
            for ($i = count($versions) - 1; $i >= 0; --$i) {
                $semVer = $versions[$i]->getVersion();

                if ($semVer->getMinor() != $minor) {
                    unset($versions[$i]);
                }
            }

            $versions = array_values($versions);
        }

        if ($patch != '*') {
            for ($i = count($versions) - 1; $i >= 0; --$i) {
                $semVer = $versions[$i]->getVersion();

                if ($semVer->getPatch() != $patch) {
                    unset($versions[$i]);
                }
            }

            $versions = array_values($versions);
        }

        return $versions;
    }
}
