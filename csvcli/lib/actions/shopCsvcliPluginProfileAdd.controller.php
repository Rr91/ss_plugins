<?php
class shopCsvcliPluginProfileAddController extends waJsonController {
    public function execute(){
        $profiles = new shopImportexportHelper('csvcli');
        $config   = array();

        $name = 'Без названия';

        $this->response['profile_id'] = $profiles->addConfig($name, null, $config);
        $this->response['name']       = $name;
    }
}