<?php

namespace Zakrzu\DDC\Modules\Dns;

use Zakrzu\DDC\Modules\Dns\Records\Record;
use Zakrzu\DDC\Modules\Dns\Records\ARecord;
use Zakrzu\DDC\Modules\Dns\Records\NsRecord;
use Zakrzu\DDC\Modules\Dns\Records\TxtRecord;
use Zakrzu\DDC\Modules\Dns\Records\CnameRecord;
use Zakrzu\DDC\Modules\Dns\Records\CaaRecord;

use Zakrzu\DDC\Modules\Dns\DnsZone;

class DnsParser
{

    public function parseRecords(array $response): DnsZone
    {
        $parsed = $this->parseResponse($response);
        return new DnsZone($parsed);
    }

    public function parseResponse(array $response): array
    {
        $info = [
            "A" => [],
            "NS" => [],
            "CNAME" => [],
            "TXT" => [],
            "CAA" => [], // unsupported on Windows, check CaaRecord class for more details
        ];
        foreach ($response as $record) {
            if ($record["type"] === Record::A) {
                $info['A'][] = new ARecord($record['host'], $record['ip']);
            }
            if ($record["type"] === Record::NS) {
                $info['NS'][] = new NsRecord($record['host'], $record['target']);
            }
            if ($record["type"] === Record::CNAME) {
                $info['CNAME'][] = new CnameRecord($record['host'], $record['target']);
            }
            if ($record["type"] === Record::TXT) {
                $info['TXT'][] = new TxtRecord($record['host'], $record['txt']);
            }
            if ($record["type"] === Record::CAA) {
                $info['CAA'][] = new CaaRecord($record['host'], $record['flags'], $record['tag'], $record['value']);
            }
        }
        return $info;
    }
}
