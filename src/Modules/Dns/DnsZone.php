<?php
namespace Zakrzu\DDC\Modules\Dns;

class DnsZone implements \JsonSerializable
{

    protected $data;
    
    protected $dataDefault = [
        "A" => [],
        "NS" => [],
        "CNAME" => [],
        "TXT" => [],
    ];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function __get($key): mixed
    {
        $default = $this->dataDefault[$key] ?? null;
        return $this->get($key, $default);
    }

    public function hasTXT(string $text): bool
    {
        foreach ($this->__get("TXT") as $record) {
            if (strcmp($text, $record->getValue() == 0))
                return true;
        }
        return false;
    }

    public function get($key, $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function toArray(): array
    {
        $data = [];
        foreach($this->dataDefault as $key => $default) {
            $data[$key] = $this->__get($key);
        }
        return $data;
    }

    public function JsonSerialize(): mixed
    {
        return $this->toArray();
    }

}
