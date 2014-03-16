<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Adapter;

use PixelPolishers\Resolver\Entity\Package;
use PixelPolishers\Resolver\Entity\Vendor;
use PixelPolishers\Resolver\Entity\Version;

interface AdapterInterface
{
    public function findDependencies($versionId);

	public function findPackageById($id);
    public function findPackageByName($name);

	public function findVendorById($id);
	public function findVendorByName($name);

    public function findVersionById($id);
    public function findVersions($name);
	public function findVersionsByPackageId($id);

    public function persistPackage(Package $package);
	public function persistVersion(Version $version);
	public function persistVendor(Vendor $vendor);

	public function removePackage(Package $package);
	public function removeVersion(Version $version);
	public function removeVendor(Vendor $vendor);
}
