<?php

foreach (glob("src/*.php") as $filename) {
    if (!str_contains($filename, 'default'))
        include_once($filename);
}

foreach (glob("src/controllers/*.php") as $filename) {
    include_once($filename);
}

foreach (glob("src/components/*.php") as $filename) {
    include_once($filename);
}

foreach (glob("src/entities/*.php") as $filename) {
    include_once($filename);
}

foreach (glob("src/managers/*.php") as $filename) {
    include_once($filename);
}

function loadConfiguration() {
    if (file_exists('src/configuration.php')) {
        include_once('src/configuration.php');
    } else {
        copy('src/configuration.default.php', 'src/configuration.php');
        include_once('src/configuration.php');
    }
}

loadConfiguration();