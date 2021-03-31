<?php

class shopRoistatPluginWorkActions extends waActions
{
    public function visitAction()
    {
        if (waRequest::method() == 'post') {
            $visit_value = waRequest::post('visit_value');
            $order_id = waRequest::post('visit_order_id');
            $this->plugin()->updateRoistatVisit($order_id, $visit_value);
            echo 'ok';
        }
    }
	
    /**
     *
     * @return shopRoistatPlugin
     */
    private function plugin()
    {
        static $plugin;
        if (!$plugin) {
            $plugin = wa()->getPlugin('roistat');
        }
        return $plugin;
    }
}