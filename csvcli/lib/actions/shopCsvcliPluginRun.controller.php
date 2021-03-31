<?php
class shopCsvcliPluginRunController extends shopCsvProductrunController {

    public function getUser(){
        return new shopCsvcliRights();
    }

    private function download($url, $file, $n = 1){

        $url = self::secureUrl($url);

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

    public static function secureUrl($url){
        $d = parse_url(urldecode($url));
        return ifempty($d['scheme'], 'http') . '://' . $d['host'] . implode('/', array_map('rawurlencode', explode('/', $d['path']))) . (!empty($d['query']) ? '?' . $d['query'] : '');
    }

    public function quietExecute($profile_id = 0){
        $result = null;

        try {
            ob_start();
            $this->_processId = $profile_id;
            $this->cli = true;
            $this->data['profile_id'] = $profile_id;
            $this->data['rights'] = true;
            $this->init();
            $this->data['rights'] = true;
            $is_done = $this->isDone();

            $type_model = new shopTypeModel();

            $this->data['types'] = array_map('intval', array_keys($type_model->select('id')->fetchAll('id')));
            $this->data['type_id'] = waRequest::post('type_id', reset($this->data['types']));
            $this->data['rights'] = true;

            while (!$is_done) {
                @$this->step();
                $is_done = $this->isDone();
            }

            $_POST['cleanup'] = true;
            $this->finish(null);

            $out = ob_get_clean();
            $result = array(
                'success' => $this->exchangeReport(),
            );

        } catch (waException $ex) {
            if ($ex->getCode() == '302') {
                $result = array(
                    'warning' => $ex->getMessage(),
                );
            } else {
                $result = array(
                    'error' => $ex->getMessage(),
                );
            }
        }
        return $result;
    }

    public function exchangeReport(){
        $interval = '—';
        if (!empty($this->data['timestamp'])) {
            $interval = time() - $this->data['timestamp'];
            $interval = sprintf('%02d ч %02d мин %02d с', floor($interval / 3600), floor($interval / 60) % 60, $interval % 60);
        }

        $template = "Импорт из CSV; \nВремя выполнения:\t%s";
        $report = sprintf($template, $interval);
        if (!empty($this->data['memory'])) {
            $memory = $this->data['memory'] / 1048576;
            $report .= sprintf("\nПотребление памяти:\t%0.3f МБ", $memory);
        }

        if ( isset($this->data['count'][self::STAGE_PRODUCT]) ){
            $report .= sprintf("\nТоваров обработано: %s", $this->data['count'][self::STAGE_PRODUCT]);
        }

        if ( isset($this->data['count'][self::STAGE_CATEGORY]) ){
            $report .= sprintf("\nКатегорий обработано: %s", $this->data['count'][self::STAGE_CATEGORY]);
        }

        return $report;
    }

}