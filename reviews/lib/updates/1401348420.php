<?php

$file = wa()->getAppPath('plugins/reviews/lib/actions/shopReviewsPluginSettingsSave.controller.php', 'shop');
if (file_exists($file)) {
    try {
        waFiles::delete($file);
    } catch (waException $e) {
    }
}