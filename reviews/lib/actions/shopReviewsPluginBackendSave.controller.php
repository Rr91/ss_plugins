<?php

class shopReviewsPluginBackendSaveController extends waJsonController
{
    public function execute()
    {
        $id = waRequest::get('id', null, waRequest::TYPE_INT);
        $data = waRequest::post();

        $review_model = new shopReviewsModel();
        $old = $review_model->getById($id);

        if ($id && $data) {
            if (isset($data['date'])) {
                if (!empty($data['date2'])) {
                    $data['date'] = $data['date2'];
                } else {
                    $data['date'] = waDateTime::parse('date', $data['date']);
                }
                $data['datetime'] = $data['date'].' '.$data['time'];
            }

            if (!empty($data['response_date'])) {
                if (!empty($data['response_date2'])) {
                    $data['response_date'] = $data['response_date2'];
                } else {
                    $data['response_date'] = waDateTime::parse('date', $data['response_date']);
                }
                $data['response_datetime'] = $data['response_date'].' '.$data['response_time'];
            }

            if (empty($old['response']) && !empty($data['response'])) {
                $data['response_contact_id'] = wa()->getUser()->getId();
                if (empty($data['response_datetime'])) {
                    $data['response_datetime'] = date('Y-m-d H:i:s');
                }
            }

            $image_extensions = array('jpg', 'jpeg', 'png', 'gif');
            // upload image
            $image = waRequest::file('image');
            if ($image->uploaded() && in_array(strtolower($image->extension), $image_extensions)) {
                try {
                    $image->waImage();

                    $data['image'] = '.' . $image->extension;
                    $path = wa()->getDataPath('reviews/', true, 'shop');
                    $image->moveTo($path, $id . $data['image']);
                    $this->response['image'] = wa()->getDataUrl('reviews/', true, 'shop').$id.$data['image'];
                } catch (waException $e) {
                }
            }
            // upload image
            $images = waRequest::file('images');
            $data_images = array();
            foreach ($images as $i => $image) {
                if ($image->uploaded() && in_array(strtolower($image->extension), $image_extensions)) {
                    try {
                        $wa_image = $image->waImage();
                        $wa_image_save = false;
                        $image_path = time() . $i . '.' . $image->extension;
                        $data_images[] = $image_path;
                        $path = wa()->getDataPath('reviews/', true, 'shop');
                        if (in_array(strtolower($image->extension), array('jpg', 'jpeg')) && function_exists('exif_read_data')) {
                            $exif = @exif_read_data($image->tmp_name);
                            if (!empty($exif) && !empty($exif['Orientation'])) {
                                switch($exif['Orientation']) {
                                    case 3:
                                        $wa_image->rotate(180);
                                        $wa_image_save = true;
                                        break;
                                    case 6:
                                        $wa_image->rotate(90);
                                        $wa_image_save = true;
                                        break;
                                    case 8:
                                        $wa_image->rotate(-90);
                                        $wa_image_save = true;
                                        break;
                                }
                            }
                        }
                        if ($wa_image_save) {
                            $wa_image->save($path.$id . '_' . $image_path, 90);
                        } else {
                            $image->moveTo($path, $id . '_' . $image_path);
                        }
                        $this->response['images'][] =
                            wa()->getDataUrl('reviews/', true, 'shop') .
                            $id . '_' . $image_path;
                    } catch (waException $e) {
                    }
                }
            }
            if ($data_images) {
                $data['images'] = implode(';', $data_images);
            }

            $review_model->updateById($id, $data);

            if (!empty($data['response'])) {
                $this->response['response'] = nl2br($data['response']);
                $this->response['response_datetime'] = waDateTime::format('humandatetime', $data['response_datetime']);
                $this->response['response_date'] = waDateTime::format('date', $data['response_datetime']);
                $this->response['response_time'] = waDateTime::format('fulltime', $data['response_datetime']);
            }
            if (!empty($data['name'])) {
                $this->response['name'] = htmlspecialchars($data['name'], ENT_NOQUOTES);
            }
            if (!empty($data['text'])) {
                $this->response['text'] = nl2br(htmlspecialchars($data['text']));
            }
            if (!empty($data['datetime'])) {
                $this->response['datetime'] = waDateTime::format('humandatetime', $data['datetime']);
                $this->response['date'] = waDateTime::format('date', $data['datetime']);
                $this->response['time'] = waDateTime::format('fulltime', $data['datetime']);
            }
            if (isset($data['rating'])) {
                $this->response['rating'] = isset($data['rating']) ? (int)$data['rating'] : 0;
            }
        }
    }
}