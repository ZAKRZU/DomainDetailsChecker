<?php

namespace Zakrzu\DDC\Modules\WhoisExt\Parsers;

use Iodev\Whois\Modules\Tld\TldInfo;
use Iodev\Whois\Modules\Tld\TldResponse;

interface IParser
{
    public function parseInfo(TldInfo $tldInfo): TldInfo;
    public function createDomainInfo(TldResponse $tldResponse, array $data, $options = []): TldInfo;
    public function getParserName(): string;
}
