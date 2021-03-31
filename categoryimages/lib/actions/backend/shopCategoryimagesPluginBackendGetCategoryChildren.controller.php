<?php

class shopCategoryimagesPluginBackendGetCategoryChildrenController extends waJsonController
{
    /**
     * Отдает html с информацией о изображениях дочерних категории по id родительской
     */
    public function execute()
    {
        $catModel = $categoriesClass = new shopCategoryModel();
        $catPicModel = new shopCategoryimagesModel();
        $categoryId = waRequest::post('catId');

        if (isset($categoryId) && !empty($categoryId)) {
            $categories = $catModel->getTree($categoryId, 1);
            $html = '';

            if(isset($categories) && !empty($categories)) {
                array_shift($categories);
            }

            if(isset($categories) && !empty($categories)) {
                $html .= '<ul class="catim-list-category">';

                foreach ($categories as &$value) {

                    $categoryImage = $catPicModel->getByField('category_id', $value['id']);
                    $path = wa()->getDataUrl("categories/{$value['id']}/", true, 'shop');

                    if ($categoryImage) {
                        $value['catClass'] = 'isPicture';
                        $value['checked'] = ($categoryImage['standard_out']) ? "checked" : "";
                        $value['picPath'] = $path . $categoryImage['id'] . "." .$categoryImage['ext'];
                        $value['featherlight'] = "data-featherlight='{$value['picPath']}'"; 
                    } else {
                        $value['catClass'] = '';
                        $value['checked'] = '';
                        $value['picPath'] = wa()->getAppStaticUrl('shop') . 'plugins/categoryimages/img/no-image-pic.png';
                        $value['featherlight'] = '';
                    }

                    $hasChildren = $catModel->getByField(array('parent_id' => $value['id']));

                    if (isset($hasChildren) && !empty($hasChildren)) {
                        $value['arrow'] = '';
                    } else {
                        $value['arrow'] = 'without-arrow';
                    }
                    
                    $html .= "<li class='catim-list-category'>";
                    $html .= "<div class='catim-folder-box {$value['catClass']}'><!--
                              --><div class='catim-folder-pic-place'>
                                   <img class='catim-cat-pic {$value['catClass']}' {$value['featherlight']}  src='{$value['picPath']}' alt=''/>
                                   <i class='icon16 loading catim-filebrowser-loading'></i>
                                </div><!--
                             --><div class='catim-folder-title-place'>
                                    <span class='catim-cat-folder-title {$value['arrow']}' data-cat-id='{$value['id']}'>{$value['name']}</span>
                                </div><!--
                             --><div class='catim-folder-buttons-block'>
                                    <input class='catim-folder-btn-file {$value['catClass']}' data-cat-id='{$value['id']}' type='file' id='category-image-{$value['id']}' />
                                    <label for='category-image-{$value['id']}' class='catim-folder-btn-file-label {$value['catClass']}'>
                                        ". _wp('Add') ."
                                    </label>
                                    <button class='catim-folder-del-btn {$value['catClass']}' data-cat-id='{$value['id']}'>". _wp('Remove') ."</button>
                                    <label class='ios-switch-box {$value['catClass']} catim-tooltip' title='". _wp('Default output') ."'>
                                        <input data-cat-id='{$value['id']}' {$value['checked']} class='ios-switch green' type='checkbox'>
                                        <div><div></div></div>
                                    </label>
                                </div>
                            </div>";
                    $html .= "</li>";
                }
                unset($value);
                $html .= '</ul>';
            }
            
            $this->response = array (
                'html' => $html,
            );
        }
    }
}