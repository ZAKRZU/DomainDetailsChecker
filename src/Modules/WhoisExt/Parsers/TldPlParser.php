<?php

namespace Zakrzu\DDC\Modules\WhoisExt\Parsers;

use Iodev\Whois\Modules\Tld\TldResponse;
use Iodev\Whois\Modules\Tld\TldInfo;

class TldPlParser implements IParser
{
    private string $name = "TldPLParser";

    public function parseInfo(TldInfo $tldInfo): TldInfo
    {
        $response = $tldInfo->getResponse();
        $data = $tldInfo->getData();
        $options = $tldInfo->getExtra();

        $ns = $this->findNameServers($options["groups"]);
        $registrar = $this->findRegistrar($options["groups"]);

        $data["nameServers"] = $ns;
        $data["registrar"] = $registrar;

        return $this->createDomainInfo($response, $data, $options);
    }

    public function findNameServers(array $groups): array
    {
        $ns = [];
        $found = false;
        foreach($groups as $group) {
            if (isset($group["nameservers"])) {
                $found = true;
                $ns[] = $group["nameservers"];
                continue;
            }
            if ($found) {
                if (isset($group[0]))
                    $ns[] = $group[0];
                else
                    $found = false;
            }
        }
        return $ns;
    }

    public function findRegistrar(array $groups): string
    {
        $registrar = "";
        $found = false;
        foreach($groups as $group) {
            if (isset($group["REGISTRAR"])) {
                $found = true;
                //$registrar .= $group["REGISTRAR"][0];
                continue;
            }
            if (isset($group["tel"])) {
                $found = true;
                $registrar .= $group["tel"];
                $registrar .= "<br>";
                continue;
            }
            if (isset($group["email"])) {
                $found = true;
                $registrar .= $group["email"];
                $registrar .= "<br>";
                continue;
            }
            if ($found) {
                if (isset($group[0])) {
                    $registrar .= $group[0];
                    $registrar .= "<br>";
                } else
                    $found = false;
            }
        }
        return $registrar;
    }

    public function createDomainInfo(TldResponse $response, array $data, $options = []): TldInfo
    {
        return new TldInfo($response, $data, $options);
    }

    public function getParserName(): string
    {
        return $this->name;
    }
}
