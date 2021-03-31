<?php

return array(
    'sign' => array(
        'description' => '<h3>Данные о продажах будут экспортироваться на сервер RoiStat для подсчета статистики и вычисления точного ROI.</h3><div><a target="_blank" href="http://roistat.com/">Зарегистрируйтесь</a> на сайте и получите номер проекта, укажите его в форме ниже. В целях безопасности вы можете установить логин и пароль на экспорт данных.<br> (Не забудьте обновить логин и пароль в настройках проекта)</div>',
        'control_type' => waHtmlControl::HIDDEN,
    ),
    'notice' => array(
        'description' => "<h3>Обратите внимание!</h3><div>Если вы используете модуль «Сайт» вам необходимо установить код счётчика вручную!<br>Код счётчика можно получить в cloud.roistat.com -> Настройки -> Код для сайта<br> После вставить в модуль Сайт -> Настройки -> Дополнительный JavaScript-код</div><br>",
        'control_type' => waHtmlControl::HIDDEN,
    ),
    'username' => array(
        'title'        => 'Имя пользователя',
        'description'  => '',
        'control_type' => waHtmlControl::INPUT,
    ),
    'password' => array(
        'title'        => 'Пароль',
        'description'  => '',
        'control_type' => waHtmlControl::INPUT,
    ),
    'project_id' => array(
        'title'        => 'ID проекта',
        'description'  => '',
        'control_type' => waHtmlControl::INPUT,
    ),
);