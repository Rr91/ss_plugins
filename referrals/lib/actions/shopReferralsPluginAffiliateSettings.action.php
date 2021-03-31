<?php

class shopReferralsPluginAffiliateSettingsAction extends waViewAction
{
    public function execute()
    {
        $settings = wa()->getPlugin('referrals')->getSettings();
        $this->view->assign('settings', $settings);
        $this->view->assign('enabled', ifset($settings['enabled']));
        $this->view->assign('affiliate_enabled', shopAffiliate::isEnabled());

        $c = waCurrency::getInfo(wa()->getConfig()->getCurrency());
        $this->view->assign('currency', ifset($c['sign'], wa()->getConfig()->getCurrency()));
    }
}