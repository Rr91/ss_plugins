<?php
class shopCsvcliPluginUploadController extends waJsonController {
    public function execute(){
        $url        = waRequest::post('url');
        $profile_id = waRequest::post('profile_id', 0, waRequest::TYPE_INT);

        if ( $url ){
            $path = wa()->getDataPath('plugins/csvcli/' . $profile_id . '/', true, 'shop', true) . 'csvcli.csv';

            $this->download($url, $path);

            if ( file_exists($path) ){
                $sets_path = $plugin_path = wa()->getDataPath('plugins/csvcli/' . $profile_id . '/', true, 'shop', true) . 'sets.php';

                $sets = array();
                if ( file_exists($sets_path) ){
                    $sets = include($sets_path);
                }

                $sets['file'] = pathinfo($url, PATHINFO_BASENAME);
                waUtils::varExportToFile($sets, $sets_path);

                $this->response['uploaded'] = 1;
            } else {
                $this->response['uploaded'] = 0;
            }
        } else {
            $this->response['uploaded'] = 0;
        }
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