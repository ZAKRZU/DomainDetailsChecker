<?php
namespace Zakrzu\DDC\Modules\Dns;

use Zakrzu\DDC\Exceptions\DnsException;
//use Zakrzu\DDC\Exceptions\ModuleException;

use Zakrzu\DDC\Modules\Module;
use Zakrzu\DDC\Modules\ModuleType;

use Zakrzu\DDC\Modules\Dns\DnsParser;
use Zakrzu\DDC\Modules\Dns\DnsZone;


class DnsModule extends Module
{

    protected DnsParser $parser;

    public function __construct()
    {
        parent::__construct(ModuleType::DNS);
        $this->parser = new DnsParser();
        //throw new ModuleException("Cannot initialize current module \"" . $this->getType() . "\"");
    }

    public function dig(string $domain): DnsZone
    {
        if (APP_ENV == "DEV") {
            $response = dns_get_record($domain, DNS_ALL);
        } else {
            $response = @dns_get_record($domain, DNS_ALL);
        }
        if ($response === false) {
            throw new DnsException("System Error: Function failed to load DNS Records for " . $domain);
        }
        if (count($response) < 1) {
            throw new DnsException("No records found for domain " . $domain . " (Domain may not exist)" );
        }
        $dns = $this->parser->parseRecords($response);
        return $dns;
    }

}
