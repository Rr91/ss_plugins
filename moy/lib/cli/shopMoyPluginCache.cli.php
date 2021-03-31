<?php

/**
 * Cron job to
 */
class shopMoyPluginCacheCli extends waCliController
{

    public function execute()
    {
        $plugin = wa('shop')->getPlugin('moy');
        $plugin::updateMoyArticle();
    }
}
