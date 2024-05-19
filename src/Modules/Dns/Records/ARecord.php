<?php

namespace Zakrzu\DDC\Modules\Dns\Records;

use Zakrzu\DDC\Modules\Dns\Records\Record;

class ARecord extends Record
{

    protected string $reverseDns = "";

    public function __construct(string $host, string $value)
    {
        parent::__construct($host, $value, Record::A);
        $this->reverseDns = gethostbyaddr($value);
    }

    public function getReverseAddr(): string
    {
        return $this->reverseDns;
    }
}
