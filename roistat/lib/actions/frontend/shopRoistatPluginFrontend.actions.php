<?php

class shopRoistatPluginFrontendActions extends waActions
{
    public function __construct()
    {
        //Если установлен логин и пароль, передается токен
        $enter_token = waRequest::get('token');

        $username = $this->plugin()->getSettings('username');
        $password = $this->plugin()->getSettings('password');

        if ($enter_token !== md5($username . $password) || (strlen($username) === 0 && strlen($password) === 0)) {
            exit;
        }
    }

    public function exportOrdersAction()
    {
        //Параметр date передает сервер ROIstat'а, чтобы отфильтровать заказы по дате обновления
        $edit_date = waRequest::get('date', time() - 31 * 24 * 60 * 60, 'int');

        $response = array('statuses' => array(), 'orders' => array());

        $model = new waModel();
        $db_result = $model->query("
            SELECT 
				so.id,
				so.contact_id,
				so.create_datetime, 
				so.update_datetime, 
				so.state_id, 
				so.total, 
				so.roistat_visit,
                so.currency,
				soi.order_id,
				SUM(soi.purchase_price * soi.quantity) as cost
            FROM 
                shop_order as so 
			LEFT JOIN 
				shop_order_items as soi ON so.id = soi.order_id
            WHERE 
                so.create_datetime > i:edit_date
			OR 
				so.update_datetime > i:edit_date
			GROUP BY id", array('edit_date' => $edit_date))->fetchAll();

        foreach ($db_result as $row) {
            $date_create = strtotime($row['create_datetime']);
            $date_update = ($row['update_datetime']) ? strtotime($row['update_datetime']) : $date_create;

            $response['orders'][] = array(
                'id'          => $row['id'],
                'date_create' => $date_create,
                'date_update' => $date_update,
                'status'      => $row['state_id'],
                'price'       => $row['total'],
                'cost'        => $row['cost'],
                'visit'       => $row['roistat_visit'],
                'client_id'   => $row['contact_id'],
                'fields'      => array(
                    'currency' => 'RUB',
                ),
            );
        }

        //Получаем имя приложения и находим статусы магазина
        $config = shopWorkflow::getConfig();

        foreach ($config['states'] as $key => $value) {
            $response['statuses'][] = array(
                'id'   => $key,
                'name' => $value['name'],
            );
        }
        echo json_encode($response);
    }

    public function exportClientsAction()
    {
        $edit_date = waRequest::get('date', time() - 31 * 24 * 60 * 60, 'int');

        $model = new waModel();
        $contacts = $model->query("
          SELECT
              id,
              name,
              company,
              birth_day,
              birth_month,
              birth_year,
              roistat_update_datetime
          FROM
              wa_contact
          WHERE
		      roistat_update_datetime > i:edit_date", array('edit_date' => $edit_date))->fetchAll();

        $contacts_ids = array_map(function (array $contact) {
            return $contact['id'];
        }, $contacts);

        $contacts_phones = $model->query("
            SELECT
              id,
              contact_id,
              field,
              value
            FROM
              wa_contact_data
            WHERE
              contact_id IN (s:contacts_ids)
            AND
              field = 'phone';", array('contacts_ids' => $contacts_ids))->fetchAll();
        $phones_by_contact_id = array();
        foreach ($contacts_phones as $contact_phone) {
            $phones_by_contact_id[$contact_phone['contact_id']][] = $contact_phone['value'];
        }

        $contacts_emails = $model->query("
            SELECT
              id,
              contact_id,
              email
            FROM
              wa_contact_emails
            WHERE
              contact_id IN (s:contacts_ids);", array('contacts_ids' => $contacts_ids))->fetchAll();
        $emails_by_contact_id = array();
        foreach ($contacts_emails as $contact_email) {
            $emails_by_contact_id[$contact_email['contact_id']][] = $contact_email['email'];
        }

        $response = array();
        foreach ($contacts as $contact) {
            $contact_data = array(
                'id'    => $contact['id'],
                'name'  => $contact['name'],
                'phone' => null,
                'email' => null,
                'company' => $contact['company'],
                'birth_date' => null,
            );
            if (array_key_exists($contact['id'], $emails_by_contact_id)) {
                $contact_data['email'] = implode(',', $emails_by_contact_id[$contact['id']]);
            }
            if (array_key_exists($contact['id'], $phones_by_contact_id)) {
                $contact_data['phone'] = implode(',', $phones_by_contact_id[$contact['id']]);
            }
            if ($contact['birth_year'] !== null && $contact['birth_month'] !== null && $contact['birth_day'] !== null) {
                $contact_data['birth_date'] = $contact['birth_year'] . '-' . $contact['birth_month']  . '-' . $contact['birth_day'];
            }
            $response[] = $contact_data;
        }
        echo json_encode(array('clients' => $response));
    }


    public function leadAction()
    {
        $model = new waModel();
        $phone1 = waRequest::get('phone');
        $phone2 = substr($phone1, 0, 2) == "+7" ?  "8".substr($phone1, 2, strlen($phone1)) : "+7".substr($phone1, 1, strlen($phone1));
        $contacts_by_phone = $model->query("
            SELECT
                contact_id
            FROM
                wa_contact_data
            WHERE
                value = s:contact_phone OR value = s:contact_phone2
            AND
                field = 'phone';",
            array(
                'contact_phone'  => $phone1,
                'contact_phone2' => $phone2
            ))->fetchAll();

        $contacts_by_email = $model->query("
            SELECT
                contact_id
            FROM
                wa_contact_emails
            WHERE
                email = s:contact_email;",
            array('contact_email' => waRequest::get('email')))->fetchAll();

        // Соеденим массивы
        $arrayOfContacts = array_merge($contacts_by_phone, $contacts_by_email);
        // ID контакта
        $contactId = $arrayOfContacts[0]['contact_id'];
        // Установим время, переданное из ройстат
        date_default_timezone_set("Europe/Moscow");
        $timeRoistat = waRequest::get('created_date');
        if ($timeRoistat == null) $timeRoistat = time();

        if (empty($arrayOfContacts)) {
            // Добавим контакт
            $addContactResult = $model->query("
                INSERT
                INTO
                    wa_contact
                        (name,
                        firstname,
                        roistat_update_datetime,
                        create_datetime)
                VALUES
                    (s:name_contact, s:name_contact, s:roistat_update_datetime, s:datetime)",
                array(
                    'name_contact' => waRequest::get('name'),
                    'roistat_update_datetime' => $timeRoistat,
                    'datetime' => date('Y-m-d H:i:s'),
                ));

            $contactId = $addContactResult->lastInsertId();

            // Добавим телефон
            if (waRequest::get('phone') != '') {
                $addPhoneResult = $model->query("
                INSERT
                INTO
                    wa_contact_data
                        (contact_id,field,value)
                VALUES
                    (i:contact_id, 'phone', s:phone)",
                    array(
                        'contact_id' => $contactId,
                        'phone' => waRequest::get('phone'),
                    ));
            }

            if (waRequest::get('email') != '') {
                // Добавим email
                $addEmailResult = $model->query("
                INSERT
                INTO
                    wa_contact_emails
                        (contact_id,email)
                VALUES
                    (s:contact_id, s:email)",
                    array(
                        'contact_id' => $contactId,
                        'email' => waRequest::get('email'),
                    ));
            }
        }

        // Комментарий
        $comment = "Название: " . waRequest::get('title') . PHP_EOL
            . "Комментарий: " . waRequest::get('text') . PHP_EOL;
        $dataArray = json_decode(waRequest::get('data'));
        $roistat_form = "";
        if (!empty($dataArray)) {
            foreach($dataArray as $k=>$v) {
                $comment .= $k . ": " . $v . PHP_EOL;
                if ($k == "roistat_form"){
                    $roistat_form = $v;
                }
            }
        }

        $createOrder = false;

            $statusesForCheck = array('new', 'processing', 'shipped', 'zamer', 'process'); //Статусы заказа "В работе"
            //Все заказы контакта
            $db_result = $model->query("
                    SELECT
                        id,
                        state_id,
                        comment
                    FROM
                        shop_order
                    WHERE
                        contact_id = s:contact_id
                    ORDER BY update_datetime DESC",
                array('contact_id' => $contactId))->fetchAll();
            if (!empty($db_result)) {
                if (in_array($db_result[0]['state_id'], $statusesForCheck)) {  //Найдем заказы контакта "В работе"
                    $comment = $db_result[0]['comment'] . PHP_EOL . "---" .PHP_EOL .$comment;
                    // Обновим заказ
                    $resultOfUpdateOrder = $model->query("
                        UPDATE
                            shop_order
                        SET
                          comment = s:comment_new,
                          update_datetime = s:datetime
                        WHERE
                            id = i:id",
                        array(
                            'id' => $db_result[0]['id'],
                            'datetime' => date('Y-m-d H:i:s'),
                            'comment_new' => $comment,
                        ));
                } else {
                    $createOrder = true;
                }
            } else {
                $createOrder = true;
            }


        if ($createOrder) {
            // Создадим заказ
            $db_result = $model->query("
                INSERT
                INTO
                shop_order
                    (contact_id,create_datetime,update_datetime,state_id,total,comment,roistat_visit,currency)
                VALUES
                    (s:contact_id, s:datetime, s:datetime, 'new', '0', s:comment, s:roistat_visit,s:currency)",
                array(
                    'contact_id' => $contactId,
                    'datetime' => date('Y-m-d H:i:s'),
                    'comment' => $comment,
                    'roistat_visit' => waRequest::get('visit'),
                    'currency' => 'RUB',
                ));
            echo json_encode([
                'status' => 'ok',
                'order_id' => $db_result->lastInsertId()
            ]);
        }
    }

    /**
     * @return shopRoistatPlugin
     */
    private function plugin()
    {
        static $plugin;
        if ( ! $plugin) {
            $plugin = wa()->getPlugin('roistat');
        }
        return $plugin;
    }
}