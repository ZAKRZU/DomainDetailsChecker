<?php
namespace Zakrzu\DDC\Modules\Dns\Records;

use Zakrzu\DDC\Modules\Dns\Records\Record;

class NsRecord extends Record
{

    public function __construct(string $host, string $value)
    {
        parent::__construct($host, $value, Record::NS);
    }

}
