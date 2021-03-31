<?php
class shopCsvcliPluginSaveController extends waJsonController {
    public function execute(){
        $p = waRequest::post();

        if ( empty($p) || !is_array($p) ){
            $p = array();
        }

        $profile_id = ifempty($p['profile_id'], 0);
        unset($p['profile_id']);

        $p['ignore_category'] = ifempty($p['ignore_category'],0);

        if ( $p ){
            $file        = !empty($p['file']) ? $p['file'] : false;

            $plugin_path = wa()->getDataPath('plugins/csvcli/' . $profile_id . '/', true, 'shop', true);

            $copy_path = $plugin_path . 'csvcli.csv';

            if ( empty($p['profile_link']) ){
                if ( $file ){
                    $file_path = wa()->getTempPath('csv/upload/' . $file);

                    if ( file_exists($file_path) ){
                        waFiles::delete($copy_path);
                        waFiles::copy($file_path, $copy_path);
                    }
                }
            }

            $path = $plugin_path . 'sets.php';
            waUtils::varExportToFile($p,$path);

            if ( $profile_id && !empty($p['profile_name']) ){
                $importexport_model = new shopImportexportModel();
                $data = array('name' => $p['profile_name']);

                $search_by = array(
                    'id'     => $profile_id,
                    'plugin' => 'csvcli'
                );

                $importexport_model->updateByField($search_by, $data );
            }
        }
    }
}