<?php

$engines = shopAutotags::getEngines();
foreach ($engines as $engine) {
    $engine->event('uninstall');
}