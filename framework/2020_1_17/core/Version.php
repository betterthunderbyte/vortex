<?php declare(strict_types=1); namespace core;

class Version
{
    private $major;
    private $minor;
    private $patch;

    private function __construct()
    {
        $this->major = 0;
        $this->minor = 0;
        $this->patch = 0;
    }

    public function IsGreaterThan(Version $version) : bool
    {
        $result = false;

        if($this->major >= $version->major AND $this->minor >= $version->minor AND $this->patch >= $version->patch)
        {
            $result = true;
        }

        return $result;
    }

    public static function Create(int $major, int $minor, int $patch) : Version
    {
        $version = new Version();

        $version->major = $major;
        $version->minor = $minor;
        $version->patch = $patch;

        return $version;
    }

    public static function FromString(string $version_string) : Version
    {
        $version = new Version();

        $parts = explode('.', $version_string);
        $parts_count = count($parts);

        if($parts_count >= 1)
        {
            $version->major = intval($parts[0]);
        }

        if($parts_count >= 2)
        {
            $version->minor = intval($parts[1]);
        }

        if($parts_count >= 3)
        {
            $version->patch = intval($parts[2]);
        }

        return $version;
    }

    public function __toString()
    {
        return $this->major . '.' . $this->minor . '.' . $this->patch;
    }
}
