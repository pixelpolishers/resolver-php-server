<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver\Adapter;

interface AdapterInterface
{
    public function findDependencies($versionId);
    public function findPackageById($id);
    public function findVersionById($id);
    public function findVersions($name);
}
