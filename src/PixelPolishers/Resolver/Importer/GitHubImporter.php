<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Importer;

use PixelPolishers\Resolver\Entity\Package;
use PixelPolishers\Resolver\Entity\Vendor;
use PixelPolishers\Resolver\Entity\Version;

class GitHubImporter implements ImporterInterface
{
    public function import($url, Package $package = null)
    {
        $repositoryName = $this->getRepositoryName($url);
        $repositoryJson = $this->getRepositoryJson($repositoryName);

        // Create the package:
        $packageJson = $this->getResolverJson($repositoryName, $repositoryJson->default_branch);
        $package = $this->parsePackageJson($packageJson, $package);
        $package->setRepositoryType('github');
        $package->setRepositoryUrl($url);

        // Create the versions for each branch:
        $branchesJson = $this->getBranchesJson($repositoryName);
        foreach ($branchesJson as $branchJson) {
            if ($branchJson->name === $repositoryJson->default_branch) {
                $branchResolverJson = $packageJson;
            } else {
                $branchResolverJson = $this->getResolverJson($repositoryName, $branchJson->name);
            }

            $version = $this->parseVersionJson($branchJson, $branchResolverJson);
            $version->setPackage($package);
            $version->setVersion('dev-' . $branchJson->name);
        }

        $tagsJson = $this->getTagsJson($repositoryName);
        foreach ($tagsJson as $tagJson) {
            $tagResolverJson = $this->getResolverJson($repositoryName, $tagJson->name);

            $version = $this->parseVersionJson($tagJson, $tagResolverJson);
            $version->setPackage($package);
        }

        return $package;
    }

    private function parsePackageJson($json, Package $package = null)
    {
        $package = $package == null ? new Package() : $package;
        $package->setCreatedAt(new \DateTime());
        $package->setUpdatedAt(new \DateTime());

        if (isset($json->name)) {
            list($vendorName, $packageName) = explode('/', $json->name, 2);

            $vendor = new Vendor();
            $vendor->setName($vendorName);

            $package->setName($packageName);
            $package->setVendor($vendor);
            $package->setFullname($json->name);
        }

        if (isset($json->description)) {
            $package->setDescription($json->description);
        }

        return $package;
    }

    private function parseVersionJson($versionJson, $resolverJson)
    {
        $version = new Version();
        $version->setCreatedAt(new \DateTime());
        $version->setUpdatedAt(new \DateTime());
        $version->setVersion($versionJson->name);
        $version->setReferenceName($versionJson->name);
        $version->setReferenceHash($versionJson->commit->sha);

        if (isset($resolverJson->license)) {
            $version->setLicense($resolverJson->license);
        }

        return $version;
    }

    private function getHttpContent($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'pixelpolishers.com');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/vnd.github.3.raw'
        ));
        $output = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($output);
        if (isset($json->message)) {
            throw new \RuntimeException($json->message);
        }

        return $json;
    }

    private function getRepositoryName($url)
    {
        $urlInfo = parse_url($url);

        $parts = explode('/', $urlInfo['path']);

        return $parts[1] . '/' . $parts[2];
    }

    private function getRepositoryJson($repositoryName)
    {
        $url = 'https://api.github.com/repos/' . $repositoryName;

        return $this->getHttpContent($url);
    }

    private function getBranchesJson($repositoryName)
    {
        $url = 'https://api.github.com/repos/' . $repositoryName . '/branches';

        return $this->getHttpContent($url);
    }

    private function getTagsJson($repositoryName)
    {
        $url = 'https://api.github.com/repos/' . $repositoryName . '/tags';

        return $this->getHttpContent($url);
    }

    private function getResolverJson($repositoryName, $ref)
    {
        $url = 'https://api.github.com/repos/' . $repositoryName . '/contents/resolver.json?ref=' . $ref;

        return $this->getHttpContent($url);
    }
}
