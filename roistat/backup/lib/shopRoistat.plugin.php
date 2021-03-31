<?php

/**
 * Class shopRoistatPlugin
 */
class shopRoistatPlugin extends shopPlugin
{
    /**
     * Обновление поля roistat_visit в таблице shop_order
     * @param string $order_id ID заказа
     * @param string $value Новое значение
     * @return void
     */
    public function updateRoistatVisit($order_id, $value)
    {
        $model = new waModel();
        $update = array(
            'roistat_visit' => $value,
            'id'            => $order_id,
        );
		
        $model->query("UPDATE shop_order SET roistat_visit = s:roistat_visit WHERE id = i:id;", $update);
    }

    /**
     * Хук backend_order
     * @param  array $param Информация о заказе
     * @return string
     */
    public function backendOrderEdit(array $param)
    {
        $static_url = $this->getPluginStaticUrl();
        $backend_url = wa()->getConfig()->getBackendUrl(true);
		
        $return['info_section'] = <<<HTML
<div>
Roistat
<input type="text" name="visit_value" value="{$param['roistat_visit']}" />
<input type="hidden" name="visit_order_id" value="{$param['id']}" />
<input type="submit" class="button blue" onclick="updateRoistatVisit('{$backend_url}');" value="Сохранить" />
<span class="roistatus"></span>
<script type="text/javascript" src="{$static_url}js/form.js"></script>
</div>
HTML;
        
		return $return;
    }
	
    /**
     * Хук order_action.create
     * @param array $param Информация о новом заказе
     */
    public function frontendSetVisit(array $param)
    {
        // ID менеджера не сохраняем
        if (wa()->getEnv() === 'backend') {
            return;
        }
        $roistat_visit = waRequest::cookie('roistat_visit');
        $model = new waModel();
		
        $update = array(
            'roistat_visit' => $roistat_visit,
            'id'            => $param['order_id']
        );
		
        $model->query("UPDATE shop_order SET roistat_visit = s:roistat_visit WHERE id = i:id;", $update);
    }
	
    /**
     * Хук frontend_head
     * @return string
     */
    public function frontendCount()
    {
        $project_id = $this->getSettings('project_id');
		
        return $this->js($project_id);
    }
	
    /**
     * JavaScript код счетчика
     * @param  string $project_id ID проекта
     * @return string
     */
    private function js($project_id)
    {
        if (!$project_id) {
            return;
        }
	
        return <<<JS
<script>
(function(w, d, s, h, id) {
    w.roistatProjectId = id; w.roistatHost = h;
    var p = d.location.protocol == "https:" ? "https://" : "http://";
    var u = /^.*roistat_visit=[^;]+(.*)?$/.test(d.cookie) ? "/dist/module.js" : "/api/site/1.0/"+id+"/init";
    var js = d.createElement(s); js.async = 1; js.src = p+h+u; var js2 = d.getElementsByTagName(s)[0]; js2.parentNode.insertBefore(js, js2);
})(window, document, 'script', 'cloud.roistat.com', '{$project_id}');
</script>
JS;
    }
}