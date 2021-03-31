<?php
class shopCsvcliRunCli extends waCliController {
    public function execute(){
        $profile_id = waRequest::param('profile', 0, waRequest::TYPE_INT);
        $path       = wa()->getDataPath('plugins/csvcli/' . $profile_id . '/', true, 'shop', true);
        $data_path  = $path . 'sets.php';

        $data = file_exists($data_path) ? include($data_path) : array();

        if ( empty($data) ){
            return false;
        }

        $file_path = $path . 'csvcli.csv';

        if ( !empty($data['profile_link']) ){
            $this->download($data['profile_link'], $file_path);
        }

        if ( !file_exists($file_path) || empty($data['file']) ){
            return false;
        }

        if ( empty($data['csv_map']) ){
            die('Не установлены соответствия столбцов');
        }

        $_map = array();
        foreach ( $data['csv_map'] as $k => $v ){
            if ( $v != '-1' ){
                $_map[$k] = $v;
            }
        }

        $data['csv_map'] = $_map;

        if ( empty($data['csv_map']) ){
            die('Не установлены соответствия столбцов');
        }

        if ( strpos($data['file'], '.csv') === false ){
            $data['file'] .= '.csv';
        }
        
        $temp_path = wa()->getTempPath('csv/upload/'. $data['file']);
        waFiles::copy($file_path, $temp_path);

        unset($data['emulate']);
        $data['direction'] = 'import';

        foreach ($data as $key => $val ){
            $_POST[$key] = $val;
        }

        $runner = new shopCsvcliPluginRunController();
        $result = $runner->quietExecute($profile_id);
        if (!empty($result['success'])) {
            print($result['success']);
        } elseif (!empty($result['error'])) {
            print($result['error']);
        } elseif (!empty($result['warning'])) {
            print($result['warning']);
        }
        print "\n";
    }

    private function download($url, $file, $n = 1){
        $url = shopCsvcliPluginRunController::secureUrl($url);

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        if ( file_exists($file) ){
            waFiles::delete($file);
        }

        $headers = array();
        $headers[] = "Connection: keep-alive";
        $headers[] = "Pragma: no-cache";
        $headers[] = "Cache-Control: no-cache";
        $headers[] = "Upgrade-Insecure-Requests: 1";
        $headers[] = "Dnt: 1";
        $headers[] = "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.62 Safari/537.36";
        $headers[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8";
        $headers[] = "Accept-Encoding: gzip, deflate";
        $headers[] = "Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7,ro;q=0.6";
        $headers[] = "Cookie: PHPSESSID=6v79gnbrun4rpe3l1shqkunkn7; f_referer=https%3A%2F%2Fwww.google.ru%2F; _ym_uid=1528219457130625104; _ga=GA1.2.1582923881.1528219458; _gid=GA1.2.1346112713.1528219458; _ym_isad=1";

        $follow = false;
        if ((version_compare(PHP_VERSION, '5.4', '>=') || !ini_get('safe_mode')) && !ini_get('open_basedir')) {
            curl_setopt($ch,CURLOPT_FOLLOWLOCATION, true);
            $follow = true;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_FILE, fopen($file, 'w'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_exec($ch);

        if ( !$follow && ($n < 3) && ($redirectURL = curl_getinfo($ch, CURLINFO_REDIRECT_URL)) ){
            curl_close($ch);
            $this->download($redirectURL, $file, $n + 1);
        } else {
            curl_close($ch);
        }
    }
}
