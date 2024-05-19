<?php

/**
 * Global function for printing debug information
 * @param mixed $object
 * @return void
 */
function ddc_debug(mixed $object): void
{
    print_r("<pre>");
    var_dump($object);
    print_r("</pre>");
}
