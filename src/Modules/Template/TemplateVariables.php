<?php
namespace Zakrzu\DDC\Modules\Template;

class TemplateVariables {

    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function __get($key): mixed
    {
        return $this->data[$key] ?? null;
    }

}
