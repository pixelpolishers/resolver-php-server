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
use PixelPolishers\Resolver\Entity\PackageLink;
use PixelPolishers\Resolver\Entity\Vendor;
use PixelPolishers\Resolver\Entity\Version;

class GitHubImporter extends AbstractImporter
{
    private $clientId;
    private $clientSecret;
    private $verifySsl;
    private $user;
    
    public function __construct(AdapterInterface $adapter)
    {
        parent::__construct($adapter);
        
        $this->verifySsl = true;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }
    
    public function getVerifySsl()
    {
        return $this->verifySsl;
    }

    public function setVerifySsl($verifySsl)
    {
        $this->verifySsl = $verifySsl;
    }
    
    public function setUser($user)
    {
        $this->user = $user;
    }
    
    public function import($url)
    {
        $repositoryName = $this->getRepositoryName($url);
        $repositoryJson = $this->getRepositoryJson($repositoryName);

        // Create the package:
        $packageJson = $this->getResolverJson($repositoryName, $repositoryJson->default_branch);
        $newPackage = $this->parsePackageJson($packageJson);
        $newPackage->setRepositoryType('github');
        $newPackage->setRepositoryUrl($url);
        
        if ($this->user) {
            $newPackage->setUserId($this->user);
        }

        // Create the versions for each branch and tag:
        $this->parseBranches($newPackage, $repositoryName, $repositoryJson, $packageJson);
        $this->parseTags($newPackage, $repositoryName, $repositoryJson, $packageJson);
        
        // Try to find an existing package:
        $oldPackage = $this->getAdapter()->findPackageByFullname($newPackage->getFullname());
        if ($oldPackage !== null) {
            $this->mergePackages($oldPackage, $newPackage);
            $package = $oldPackage;
        } else {
            $package = $newPackage;
        }
        
        // And save the package:
        $this->getAdapter()->persistPackage($package);
        
        return $package;
    }
    
    private function parseBranches(Package $package, $repositoryName, $repositoryJson, $packageJson)
    {
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
    }
    
    private function parseTags(Package $package, $repositoryName, $repositoryJson, $packageJson)
    {
        $tagsJson = $this->getTagsJson($repositoryName);
        foreach ($tagsJson as $tagJson) {
            $tagResolverJson = $this->getResolverJson($repositoryName, $tagJson->name);

            $version = $this->parseVersionJson($tagJson, $tagResolverJson);
            $version->setPackage($package);
        }

        return $package;
    }

    private function parsePackageJson($json)
    {
        $package = new Package();
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
        
        if (isset($resolverJson->projects) && is_array($resolverJson->projects)) {
            foreach ($resolverJson->projects as $project) {
                if (isset($project->dependencies) && is_array($project->dependencies)) {
                    $this->parseProjectDependencies($version, $project->dependencies);
                }
            }
        }

        return $version;
    }
    
    private function parseProjectDependencies(Version $version, array $dependencies)
    {
        foreach ($dependencies as $dependency) {
            $packageVersion = $dependencyVersion = $this->findDependency($dependency->name, $dependency->version);
            if (!$packageVersion) {
                throw new \Exception('The dependency "' . $dependency->name . '" does not exist.');
            }
            
            $packageLink = new PackageLink();
            $packageLink->setVersion($version);
            $packageLink->setPackageVersion($packageVersion);
            $version->addDependency($packageLink);
        }
    }

    private function getRepositoryName($url)
    {
        $urlInfo = parse_url($url);

        $parts = explode('/', $urlInfo['path']);

        return $parts[1] . '/' . $parts[2];
    }

    private function getHttpContent($url)
    {
        $ch = curl_init();

        if ($this->getClientId() && $this->getClientSecret()) {
            $info = parse_url($url);

            if (array_key_exists('query', $info)) {
                $info['query'] .= '&client_id=' . $this->getClientId()
                        . '&client_secret=' . $this->getClientSecret();
            } else {
                $info['query'] = 'client_id=' . $this->getClientId()
                        . '&client_secret=' . $this->getClientSecret();
            }

            $url = $info['scheme'] . '://'
                    . $info['host']
                    . $info['path'] . '?'
                    . $info['query'];
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->getVerifySsl());
        curl_setopt($ch, CURLOPT_USERAGENT, 'pixelpolishers.com');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/vnd.github.3.raw'
        ));
        
        $output = curl_exec($ch);
        if (!$output) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            
            throw new \RuntimeException($error, $errno);
        }
        
        curl_close($ch);

        $json = json_decode($output);
        if (isset($json->message)) {
            throw new \RuntimeException($json->message);
        }

        return $json;
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
