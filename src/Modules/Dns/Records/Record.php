<?php

namespace Zakrzu\DDC\Modules\Dns\Records;

abstract class Record
{
    const A = "A";
    const CNAME = "CNAME";
    const NS = "NS";
    const TXT = "TXT";

    protected string $host;
    protected string $value;
    protected string $type;

    public function __construct(string $host, string $value, string $type)
    {
        $this->host = $host;
        $this->value = $value;
        $this->type = $type;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getType()
    {
        return $this->type;
    }
}
