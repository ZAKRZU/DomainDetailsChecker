<?php

foreach (glob("src/*.php") as $filename) {
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

foreach (glob("src/legacy/*.php") as $filename) {
    include_once($filename);
}