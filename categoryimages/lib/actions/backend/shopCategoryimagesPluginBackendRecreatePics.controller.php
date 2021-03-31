<?php

/**
 * Класс пересоздания эскизов для категорий
 */
class shopCategoryimagesPluginBackendRecreatePicsController extends waLongActionController
{
    /**
     * Инициализация, задаем настройки, тип эскиза, ширина, высота, лимит для шага пересоздания
     */
    protected function init() {
        $catimModel = new shopCategoryimagesModel();
        $picType = waRequest::post('picType');

        if (isset($picType) && ($picType == 'big' || $picType == 'middle' || $picType == 'little')) {

            $this->data['picType'] = $picType;
            $width = waRequest::post('picWidth');
            $height = waRequest::post('picHeight');

            if (isset($height) && !empty($height) &&
                isset($width) && !empty($width) &&
                is_numeric($width) && is_numeric($width) ) {

                $this->data['picWidth'] = $width;
                $this->data['picHeight'] = $height;

            } else {
                return;
            }

        } else {
            return;
        }

        $this->data['limit'] = 5;
        $this->data['timestamp'] = time();
        $this->data['total_count'] = $catimModel->countAll();
        $this->data['offset'] = 0;
    }

    /**
     * Шаг пересоздания эскизов
     */
    protected function step() {
        $catimModel = new shopCategoryimagesModel();
        $picsList = $catimModel->select('*')->limit($this->data['offset'] .",". $this->data['limit'])->fetchAll('id');
        $i = 0;
        
        if (!empty($picsList)) {
            foreach ($picsList as $pic) {
                $path = wa()->getDataPath("categories/{$pic['category_id']}/", true, 'shop');
                $picPath = $path  . $pic['id'] . "." . $pic['ext'];
                $newPicPath = $path . $pic['id'] . "_" . $this->data['picType'] . "." . $pic['ext'];

                if (file_exists($picPath)) {
                    $image = waImage::factory($picPath);
                    $image->resize($this->data['picWidth'], $this->data['picHeight']);
                    $image->save($newPicPath);
                }
                $i++;
            }
            $this->data['offset'] += $i;    
        }
    }

    /**
     * Информация о ходе выполнения
     */
    protected function info()
    {
        $interval = 0;
        if (!empty($this->data['timestamp'])) {
            $interval = time() - $this->data['timestamp'];
        }
        $response = array(
            'time'       => sprintf('%d:%02d:%02d', floor($interval / 3600), floor($interval / 60) % 60, $interval % 60),
            'processId'  => $this->processId,
            'progress'   => 0.0,
            'ready'      => $this->isDone(),
            'offset'     => $this->data['offset'],
        );
        $response['progress'] = ($this->data['offset'] / $this->data['total_count']) * 100;
        $response['progress'] = sprintf('%0.3f%%', $response['progress']);

        echo json_encode($response);
    }

    protected function isDone()
    {
        return $this->data['offset'] >= $this->data['total_count'];
    }

    protected function finish($filename)
    {
        $this->info();

        if($this->isDone()) {
            return true;
        } else {
            return false;
        }
    }
}