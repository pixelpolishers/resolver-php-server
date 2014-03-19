<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Server\Controller;

class LookupController extends AbstractController
{
    public function execute()
    {
        $result = array();

        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            $result['status'] = 405;
            $result['error'] = 'Method not allowed, only GET is allowed.';
            return $result;
        }

        if (!array_key_exists('q', $_GET)) {
            $result['status'] = 400;
            $result['error'] = 'No "q" parameter provided.';
            return $result;
        }

        $result['status'] = 200;
        $result['packages'] = array();
        foreach ((array)$_GET['q'] as $q) {
            $result['packages'][] = $this->lookupPackage($q);
        }
        return $result;
    }

    private function lookupPackage($query)
    {
        $options = explode('@', $query, 2);

        $pckName = $options[0];
        $pckVersion = isset($options[1]) ? $options[1] : '*';

        $data = array(
            'status' => 404,
            'query' => $query,
            'package' => $pckName,
            'version' => $pckVersion,
        );

        $version = $this->lookupVersion($pckName, $pckVersion);
        if ($version) {
            $package = $version->getPackage();

            $data['status'] = 200;
            $data['version'] = (string)$version->getVersion();
            $data['source'] = array(
                'name' => $version->getReferenceName(),
                'hash' => $version->getReferenceHash(),
            );
            $data['package'] = array(
                'vendor' => $package->getVendor()->getName(),
                'name' => $package->getName(),
                'fullname' => $package->getFullname(),
                'description' => $package->getDescription(),
                'repository' => array(
                    'url' => $package->getRepositoryUrl(),
                    'type' => $package->getRepositoryType(),
                ),
            );

            $data['dependencies'] = array();
            foreach ($version->getDependencies() as $requiredPackage) {
                $reqVersion = $requiredPackage->getPackageVersion();

                $data['dependencies'][] = $reqVersion->getPackage()->getFullname()
                        . '@' . $reqVersion->getVersion();
            }
        }

        return $data;
    }

    private function lookupVersion($pckName, $pckVersion)
    {
        $versions = $this->getAdapter()->findVersions($pckName);
        if (!$versions) {
            return null;
        }

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
            if ($version->getVersion()->getNormalVersion() == $pckVersion) {
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
