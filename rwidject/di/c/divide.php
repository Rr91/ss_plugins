<?php  

class ControllerModuledivide extends Controller {

 public function index() {
 	if(isset($_REQUEST['order_id']) && $_REQUEST['order_id']) return $this->divideOrder($_REQUEST['order_id']);
	$this->load->language('module/divide'); //подключаем любой языковой файл
	$this->document->setTitle($this->language->get('heading_title'));
 	$data['heading_title'] = $this->language->get('heading_title'); //объявляем переменную heading_title с данными из языкового файла
 

 	$this->load->model('catalog/product'); //подключаем любую модель из OpenCart

 	$data['breadcrumbs'] = array();

	$data['breadcrumbs'][] = array(
		'text' => $this->language->get('text_home'),
		'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
	);

	$data['breadcrumbs'][] = array(
		'text' => $this->language->get('text_module'),
		'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
	);

	if (!isset($this->request->get['module_id'])) {
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('module/divide', 'token=' . $this->session->data['token'], 'SSL')
		);
	} else {
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('module/divide', 'token=' . $this->session->data['token'] . '&module_id=' . $this->request->get['module_id'], 'SSL')
		);
	}

	if (!isset($this->request->get['module_id'])) {
		$data['action'] = $this->url->link('module/divide', 'token=' . $this->session->data['token'], 'SSL');
	} else {
		$data['action'] = $this->url->link('module/divide', 'token=' . $this->session->data['token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
	}

	$data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

	if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
		$module_info = $this->model_extension_module->getModule($this->request->get['module_id']);
	}

	if (isset($this->request->post['name'])) {
		$data['name'] = $this->request->post['name'];
	} elseif (!empty($module_info)) {
		$data['name'] = $module_info['name'];
	} else {
		$data['name'] = '';
	}

	$data['header'] = $this->load->controller('common/header');
	$data['column_left'] = $this->load->controller('common/column_left');
	$data['footer'] = $this->load->controller('common/footer');

 	//стандартная процедура для контроллеров OpenCart, выбираем файл представления модуля для вывода данных
	$this->response->setOutput($this->load->view('module/divide.tpl', $data));
 
 }

 	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/divide')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}


	private function divideOrder($order_id)
	{
		$this->load->model('module/divide');
		$new_order_id = $this->model_module_divide->copy($order_id);

		$url = html_entity_decode($this->url->link('sale/order', 'token=' . $this->session->data['token'], 'SSL'));
		echo json_encode(array("link" => $url));
		exit;
	}


}