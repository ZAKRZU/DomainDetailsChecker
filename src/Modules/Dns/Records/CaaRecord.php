<?php

namespace Zakrzu\DDC\Modules\Dns\Records;

use Zakrzu\DDC\Modules\Dns\Records\Record;

// CAA record is not supported on Windows platform
// and there is nothing we can do about it
// https://bugs.php.net/bug.php?id=75909
// https://learn.microsoft.com/en-us/windows-server/administration/windows-commands/nslookup-set-type
class CaaRecord extends Record
{

    private string $tag;
    private string $flags;

    public function __construct(string $host, string $flags, string $tag, string $value)
    {
        parent::__construct($host, $value, Record::CAA);
        $this->tag = $tag;
        $this->flags = $flags;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getFlags(): string
    {
        return $this->flags;
    }
}
