<?php

namespace Zakrzu\DDC\Modules;

abstract class Module
{

    protected string $type;

    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = strval($type);
    }

    public function getType(): string
    {
        return $this->type;
    }
}
