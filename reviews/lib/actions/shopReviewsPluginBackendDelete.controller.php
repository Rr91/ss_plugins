<?php

class shopReviewsPluginBackendDeleteController extends waJsonController
{
    public function execute()
    {
        $id = waRequest::post('id');
        if ($id) {
            $review_model = new shopReviewsModel();
            $review = $review_model->getById($id);
            if ($review) {
                $review_model->deleteById($id);

                $path = wa()->getDataPath('reviews/', true, 'shop');
                // remove files
                if (!empty($review['image'])) {
                    waFiles::delete($path . $review['_id'] . $review['image'], true);
                }
                if (!empty($review['images'])) {
                    $review['images'] = explode(';', $review['images']);
                    foreach ($review['images'] as $img) {
                        waFiles::delete($path . $review['_id'] . '_' . $img, true);
                    }
                }
            }
        }
    }
}