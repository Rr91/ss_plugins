<?php

class shopAutotagsPluginSettingsSaveController extends waJsonController {

    public function execute() {
        $post = waRequest::post();
        if (!empty($post['shop_autotags'])) {
            wa()->getPlugin('autotags')->saveSettings($post['shop_autotags']);
        }

        $engines = shopAutotags::getEngines();

        foreach ($engines as $engine) {
            $engine->saveAutotags($post);
        }
    }

}
