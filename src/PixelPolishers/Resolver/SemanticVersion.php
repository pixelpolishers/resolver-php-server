<?php
/**
 * This file is part of Resolver. An application created by Pixel Polishers.
 *
 * @copyright Copyright (c) 2012-2013 Pixel Polishers. All rights reserved.
 * @license https://github.com/pixelpolishers/resolver-server
 */

namespace PixelPolishers\Resolver;

class SemanticVersion
{
    private $major;
    private $minor;
    private $patch;
    private $preRelease;
    private $metaData;

    public function __construct($major, $minor, $patch, $preRelease = null, $metaData = null)
    {
        $this->major = $major;
        $this->minor = $minor;
        $this->patch = $patch;
        $this->preRelease = $preRelease;
        $this->metaData = $metaData;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public static function fromString($str)
    {
        $result = null;

        $regex = '';
        $regex .= '([0-9]+)\.';
        $regex .= '([0-9]+)\.';
        $regex .= '([0-9]+)';
        $regex .= '(-(([0-9A-Za-z-]+\.)*[0-9A-Za-z-]+))?';
        $regex .= '(\+(([0-9A-Za-z-]+\.)*[0-9A-Za-z-]+))?';

        if (preg_match('/^' . $regex . '$/', $str, $matches)) {
            $result = new SemanticVersion(
                (int)$matches[1],
                (int)$matches[2],
                (int)$matches[3],
                (isset($matches[5]) ? $matches[5] : null),
                (isset($matches[8]) ? $matches[8] : null)
            );
        }
        return $result;
    }

    public function getMajor()
    {
        return $this->major;
    }

    public function getMinor()
    {
        return $this->minor;
    }

    public function getPatch()
    {
        return $this->patch;
    }

    public function getNormalVersion()
    {
        return $this->major . '.' . $this->minor . '.' . $this->patch;
    }

    public function toString()
    {
        $result = $this->getNormalVersion();

        if ($this->preRelease) {
            $result .= '-' . $this->preRelease;
        }

        if ($this->metaData) {
            $result .= '+' . $this->metaData;
        }

        return $result;
    }
}
