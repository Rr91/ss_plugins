<?php
class shopCsvcliPluginProfileDeleteController extends waJsonController {
    public function execute(){

        $profile_id = waRequest::post('profile_id', 0, waRequest::TYPE_INT);

        if ( $profile_id ){
            $profiler = new shopImportexportHelper('csvcli');
            $profiler->deleteConfig($profile_id);

            $path = wa()->getDataPath('plugins/csvcli/' . $profile_id . '/', false, 'shop', false);
            if ( file_exists($path) ){
                waFiles::delete($path, true);
            }
        }

    }
}