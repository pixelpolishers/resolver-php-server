<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Generator;

use PixelPolishers\Resolver\Entity\Package;
use PixelPolishers\Resolver\Entity\Version;

class Generator
{
    private $api;
    private $packages;

    public function __construct()
    {
        $this->packages = array();
    }

    public function setApi(Api $api)
    {
        $this->api = $api;
    }

    public function addPackage(Package $package)
    {
        $this->packages[] = $package;
    }

    public function generate()
    {
        $data = array();

        if ($this->api !== null) {
            $data['api'] = array(
                'search' => $this->api->getSearchUrl(),
                'resolver' => $this->api->getResolverUrl(),
            );
        }

        if ($this->packages) {
            $data['packages'] = array();

            foreach ($this->packages as $package) {
                $data['packages'][] = $this->generatePackage($package);
            }
        }

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    private function generateVersion(Version $version)
    {
        return array(
            'createdAt' => array(
                'date' => $version->getCreatedAt()->format('Y-m-d H:i:s'),
                'timezone' => $version->getCreatedAt()->getTimezone()->getName(),
            ),
            'reference' => $version->getReference(),
            'referenceType' => $version->getReferenceType(),
            'referenceUrl' => $version->getReferenceUrl(),
            'license' => $version->getLicense(),
            'updatedAt' => array(
                'date' => $version->getUpdatedAt()->format('Y-m-d H:i:s'),
                'timezone' => $version->getUpdatedAt()->getTimezone()->getName(),
            ),
            'version' => $version->getVersion(),
        );
    }

    private function generatePackage(Package $package)
    {
        $versions = array();
        foreach ($package->getVersions() as $version) {
            $versions[] = $this->generateVersion($version);
        }

        return array(
            'name' => $package->getFullname(),
            'description' => $package->getDescription(),
            'versions' => $versions,
        );
    }
}
