<?php

try {
    waFiles::delete(wa()->getDataPath('plugins/reviews/', false, 'shop', false));
} catch (waException $e) {
}