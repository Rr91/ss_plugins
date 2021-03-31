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
                    'currency' => $row['currency'],
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