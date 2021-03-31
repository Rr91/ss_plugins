<?php
class ControllerSaleDpd extends Controller {
	protected $null_array = array();

	public function index() {

		ini_set('error_reporting', E_ALL);
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);

        $orders = array();

		if (isset($this->request->post['selected'])) {
			$orders = $this->request->post['selected'];
		} elseif (isset($this->request->get['order_id'])) {
			$orders[] = $this->request->get['order_id'];
        }

		$this->load->model('sale/order');
		foreach ($orders as $k=>$oid) {
        	// если у заказа есть дочерние удаляем его из списка
        	if($this->model_sale_order->dpdCheckOrder($oid)){
        		unset($orders[$k]);
        	}
		}


        if(!$orders) {
            echo 'No orders selected!'; 

        }

		$this->load->model('catalog/product');

		$cwd = getcwd();
               
        chdir(DIR_SYSTEM.'PHPExcel');
        require_once('PHPExcel.php');
        chdir($cwd);

		set_time_limit(0);

		$workbook = new PHPExcel();

		$workbook->getDefaultStyle()->getFont()->setName('Calibri');
		$workbook->getDefaultStyle()->getFont()->setSize(11);
		$workbook->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$workbook->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$workbook->getDefaultStyle()->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_GENERAL);

		$box_format = array(
			'fill' => array(
				'type'      => PHPExcel_Style_Fill::FILL_SOLID,
				'color'     => array( 'rgb' => 'F0F0F0')
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			)
		);

		$worksheet_index = 0;

        $workbook->setActiveSheetIndex($worksheet_index++);
        $worksheet = $workbook->getActiveSheet();
                   
		$worksheet->setTitle('Manifest');
                 
		$j = 0;
		$worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('MAWB Number')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('HAWB Number')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Expected arrival date')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Total Amount')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Currency')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Total Weight KG')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Shipper Name')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Consignee Family Name')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Consignee name')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Consignee Middle Name')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Consignee Passport Serial')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Consignee Passport Number')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Passport Issue date')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Consignee full address')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Consignee City')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Consignee state')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Consignee Zip code')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Consignee Mobile/phone number')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Consignee e-mail address')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('ITEM DESCRIPTION')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Link to item description  on e-tailer web-site')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('NUMBER OF ITEM PIECES')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Item price')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Item weight')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('COD amount')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('COD currency')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('DPD Pickup Point Code')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('DPD service code')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('VAT')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('ISSUED BY')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('CONSIGNEE BIRTHDAY')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('HS code')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('Additional number')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('CHECK RESULT CODE')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('CHECK RESULT MESSAGE')+5);
		 $worksheet->getColumnDimensionByColumn($j++)->setWidth(mb_strlen('ITEM DESCRIPTION ENG')+5);

		$styles = array();
		$data = array();
		$i = 1;
		$j = 0;
		$data[$j++] = 'MAWB Number'; 
		$data[$j++] = 'HAWB Number'; 
		$data[$j++] = 'Expected arrival date';
		$data[$j++] = 'Total Amount';
		$data[$j++] = 'Currency';
		$data[$j++] = 'Total Weight KG';
		$data[$j++] = 'Shipper Name';
		$data[$j++] = 'Consignee Family Name';
		$data[$j++] = 'Consignee name';
		$data[$j++] = 'Consignee Middle Name';
		$data[$j++] = 'Consignee Passport Serial';
		$data[$j++] = 'Consignee Passport Number';
		$data[$j++] = 'Passport Issue date';
		$data[$j++] = 'Consignee full address';
		$data[$j++] = 'Consignee City';
		$data[$j++] = 'Consignee state';
		$data[$j++] = 'Consignee Zip code';
		$data[$j++] = 'Consignee Mobile/phone number';
		$data[$j++] = 'Consignee e-mail address';
		$data[$j++] = 'ITEM DESCRIPTION';
		$data[$j++] = 'Link to item description  on e-tailer web-site';
		$data[$j++] = 'NUMBER OF ITEM PIECES';
		$data[$j++] = 'Item price';
		$data[$j++] = 'Item weight';
		$data[$j++] = 'COD amount';
		$data[$j++] = 'COD currency';
		$data[$j++] = 'DPD Pickup Point Code';
		$data[$j++] = 'DPD service code';
		$data[$j++] = 'VAT';
		$data[$j++] = 'ISSUED BY';
		$data[$j++] = 'CONSIGNEE BIRTHDAY';
		$data[$j++] = 'HS code';
		$data[$j++] = 'Additional number';
		$data[$j++] = 'CHECK RESULT CODE';
		$data[$j++] = 'CHECK RESULT MESSAGE';
		$data[$j++] = 'ITEM DESCRIPTION ENG';
	

		$worksheet->getRowDimension($i)->setRowHeight(30);
		$this->setCellRow($worksheet,$i,$data,$box_format);


		$i += 1;
        $j = 0;

		foreach ($orders as $order_id) {

			$order_info = $this->model_sale_order->getOrder($order_id);

			if ($order_info) {

				

				$products = $this->model_sale_order->getOrderProducts($order_id);

				if($products) {

					$amount = 0;
					$weight = 0;

					foreach ($products as $row) {
						
						$product_info = $this->model_catalog_product->getProduct($row['product_id']);

						
						$row_weight = 0;

						if(!empty($product_info['weight'])) {
							
							$row_weight = round($this->weight->convert($product_info['weight'],$product_info['weight_class_id'],$this->config->get('config_weight_class_id')),2);
							
						}
						

						$row_price = round($this->currency->convert($row['price'],$this->config->get('config_currency_code'),'EUR'),2);
						$row_total = round($this->currency->convert($row['total'],$this->config->get('config_currency_code'),'EUR'),2);

							if($row['quantity']==1) {

								if($row_total<1) {
									$price = 1.15;
									$total = $price;
								} else {
									$price = $row_price;
									$total = $price;
								}

								$virtual = false;
							} else {
								$virtual = true;
							}

							if($virtual) {

								if($row['quantity']>1 && $row_total < 1) {
									$total = 1.15;
									$price = $total;
									
								} elseif($row['quantity']>1 && ($row_total/$row['quantity']) < 1) {
									$price = $row_total;
									$total = $row_total;
									
								} else {
									$price = $row_price;
									$total = round($price,2) * $row['quantity'];
									
								}

							} 

							$row_weight = round($row_weight,2)*$row['quantity'];
							
							$weight += $row_weight;
							$amount += $total;
					}

					foreach ($products as $row) {

						$options = $this->model_sale_order->getOrderOptions($order_id, $row['order_product_id']);
						$product_info = $this->model_catalog_product->getProduct($row['product_id']);

						$description = '';
						$row_weight = 0;

						if($product_info) {
							$description = $product_info['description'];
							$row_weight = round($this->weight->convert($product_info['weight'],$product_info['weight_class_id'],$this->config->get('config_weight_class_id')),2);
						}

						$product_description = $this->model_catalog_product->getProductDescriptions($row['product_id']);

						if(!empty($product_description[1]['name'])) {
							$name_eng = $product_description[1]['name'];
						} else {
							$name_eng = '';
						}

						$url = HTTPS_CATALOG . "index.php?route=product/product&product_id=".$row['product_id'] ."&lng=ru#0x";

						$row_price = round($this->currency->convert($row['price'],$this->config->get('config_currency_code'),'EUR'),2);
						$row_total = round($this->currency->convert($row['total'],$this->config->get('config_currency_code'),'EUR'),2);
						$row_quantity = $row['quantity'];

						if($row['quantity']==1) {

							if($row_total<1) {
								$price = 1.15;
								$total = $price;
							} else {
								$price = $row_price;
								$total = $price;
							}

							$virtual = false;
						} else {
							$virtual = true;
						}

						$name = $row['name'];
						$n = false;

						if($virtual) {

							if($row['quantity']>1 && $row_total < 1) {
								$total = 1.15;
								$price = $total;
								$row_weight =  round($row_weight,2) * $row['quantity'];
								$name = $row['name']. " (набор ".$row['quantity']." шт.)";
								$name_eng = $name_eng . " (set of ".$row['quantity']." piece)";
								$row_quantity = 1;
								$n = true;
							} elseif($row['quantity']>1 && ($row_total/$row['quantity']) < 1) {
								$price = $row_total;
								$total = $row_total;
								$row_weight = round($row_weight,2) * $row['quantity'];
								$name = $row['name']. " (набор ".$row['quantity']." шт.)";
								$name_eng = $name_eng . " (set of ".$row['quantity']." piece)";
								$row_quantity = 1;
								$n = true;
							} else {
								$price = $row_price;
								$total = $row_price;
							}

						} 
						
						if($price == $total && $total == 1) {
							$price = 1.15;
							$total = $price;
						}

						$price = number_format(round($price, 2), 2,".","");
						$total = number_format(round($total, 2), 2,".","");

						$exp = explode('.',$total);

						if(!empty($exp[1])) {
							$z = "";
							if($exp[1][0] == "0") {
								$z = '0';
							}
							$url .= dechex((int)$exp[0]).'P'.$z.dechex((int)$exp[1]); 
						} else {
							$url .= dechex((int)$total);
						}

						if($n) {
							$url .= "@sht=".$row['quantity'];
						}

						$amount = number_format(round($amount, 2), 2,".","");
					
						$worksheet->getRowDimension($i)->setRowHeight(26);
						$data = array();
						
                        $data[$j++] = $order_id;
                        
                        if($order_info['parent_id'] && $order_info['num'])
							$data[$j++] = $order_info['parent_id'].".".$order_info['num'];
						else  $data[$j++] = '';

						$data[$j++] = '';
						$data[$j++] = $amount;
						$data[$j++] = 'EUR';
						$data[$j++] = $weight;
						$data[$j++] = $order_info['store_name'];
						$data[$j++] = ($order_info['lastname']?$order_info['lastname']:'');
						$data[$j++] = ($order_info['firstname']?$order_info['firstname']:'');
						$data[$j++] = (!empty($order_info['custom_field'][1])?$order_info['custom_field'][1]:'');
						$data[$j++] = "";
						$data[$j++] = "";
						$data[$j++] = "";
						$data[$j++] = $order_info['shipping_address_1'].','.$order_info['shipping_address_2'];
						$data[$j++] = $order_info['shipping_city'];
						$data[$j++] = $order_info['shipping_country'];
						$data[$j++] = $order_info['shipping_postcode'];
						$data[$j++] = $order_info['telephone'];
						$data[$j++] = $order_info['email'];
						$data[$j++] = $name;
						$data[$j++] = $url;
						$data[$j++] = $row_quantity;
						$data[$j++] = $price;
						$data[$j++] = number_format(round($row_weight, 2), 2,".","");
						$data[$j++] = "";
						$data[$j++] = "";
						$data[$j++] = "";
						$data[$j++] = "";
						$data[$j++] = "";
						$data[$j++] = "";
						$data[$j++] = (!empty($order_info['custom_field'][15])?date('d.m.Y', strtotime($order_info['custom_field'][15])):'');
						$data[$j++] = "";
						$data[$j++] = "";
						$data[$j++] = "";
						$data[$j++] = "";
						$data[$j++] = $name_eng;

                        $this->setCellRow( $worksheet, $i, $data, $this->null_array, $styles );
                        $i += 1;
                        $j = 0;
						
						

					}
				}	
			}
		}

		$worksheet->freezePaneByColumnAndRow( 1, 2 );

		$workbook->setActiveSheetIndex(0);

		$datetime = date('Y-m-d');
		
		$filename = 'Manifest-'.$datetime;
		$filename .= '.xlsx';
				

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$filename.'"');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($workbook, 'Excel2007');
		$objWriter->setPreCalculateFormulas(false);
		$objWriter->save('php://output');

		$files = glob(DIR_CACHE . 'Spreadsheet_Excel_Writer' . '*');

		if ($files) {
			foreach ($files as $file) {
				if (file_exists($file)) {
					@unlink($file);
					clearstatcache();
				}
			}
		}

		exit;

		// foreach ($orders as $order_id) {
		// 	$order_info = $this->model_sale_order->getOrder($order_id);

		// 	// Make sure there is a shipping method
		// 	if ($order_info && $order_info['shipping_code']) {
		// 		$store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

		// 		if ($store_info) {
		// 			$store_address = $store_info['config_address'];
		// 			$store_email = $store_info['config_email'];
		// 			$store_telephone = $store_info['config_telephone'];
		// 			$store_fax = $store_info['config_fax'];
		// 		} else {
		// 			$store_address = $this->config->get('config_address');
		// 			$store_email = $this->config->get('config_email');
		// 			$store_telephone = $this->config->get('config_telephone');
		// 			$store_fax = $this->config->get('config_fax');
		// 		}

		// 		if ($order_info['invoice_no']) {
		// 			$invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
		// 		} else {
		// 			$invoice_no = '';
		// 		}

		// 		if ($order_info['shipping_address_format']) {
		// 			$format = $order_info['shipping_address_format'];
		// 		} else {
		// 			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		// 		}

		// 		$find = array(
		// 			'{firstname}',
		// 			'{lastname}',
		// 			'{company}',
		// 			'{address_1}',
		// 			'{address_2}',
		// 			'{city}',
		// 			'{postcode}',
		// 			'{zone}',
		// 			'{zone_code}',
		// 			'{country}'
		// 		);

		// 		$replace = array(
		// 			'firstname' => $order_info['shipping_firstname'],
		// 			'lastname'  => $order_info['shipping_lastname'],
		// 			'company'   => $order_info['shipping_company'],
		// 			'address_1' => $order_info['shipping_address_1'],
		// 			'address_2' => $order_info['shipping_address_2'],
		// 			'city'      => $order_info['shipping_city'],
		// 			'postcode'  => $order_info['shipping_postcode'],
		// 			'zone'      => $order_info['shipping_zone'],
		// 			'zone_code' => $order_info['shipping_zone_code'],
		// 			'country'   => $order_info['shipping_country']
		// 		);

		// 		$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		// 		$this->load->model('tool/upload');

		// 		$product_data = array();

		// 		$products = $this->model_sale_order->getOrderProducts($order_id);

		// 		foreach ($products as $product) {
		// 			$option_weight = '';

		// 			$product_info = $this->model_catalog_product->getProduct($product['product_id']);

		// 			if ($product_info) {
		// 				$option_data = array();

		// 				$options = $this->model_sale_order->getOrderOptions($order_id, $product['order_product_id']);

		// 				foreach ($options as $option) {
		// 					$option_value_info = $this->model_catalog_product->getProductOptionValue($order_id, $product['order_product_id']);

		// 					if ($option['type'] != 'file') {
		// 						$value = $option['value'];
		// 					} else {
		// 						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

		// 						if ($upload_info) {
		// 							$value = $upload_info['name'];
		// 						} else {
		// 							$value = '';
		// 						}
		// 					}

		// 					$option_data[] = array(
		// 						'name'  => $option['name'],
		// 						'value' => $value
		// 					);

		// 					$product_option_value_info = $this->model_catalog_product->getProductOptionValue($product['product_id'], $option['product_option_value_id']);

		// 					if ($product_option_value_info) {
		// 						if ($product_option_value_info['weight_prefix'] == '+') {
		// 							$option_weight += $product_option_value_info['weight'];
		// 						} elseif ($product_option_value_info['weight_prefix'] == '-') {
		// 							$option_weight -= $product_option_value_info['weight'];
		// 						}
		// 					}
		// 				}

		// 				$product_data[] = array(
		// 					'name'     => $product_info['name'],
		// 					'model'    => $product_info['model'],
		// 					'option'   => $option_data,
		// 					'quantity' => $product['quantity'],
		// 					'location' => $product_info['location'],
		// 					'sku'      => $product_info['sku'],
		// 					'upc'      => $product_info['upc'],
		// 					'ean'      => $product_info['ean'],
		// 					'jan'      => $product_info['jan'],
		// 					'isbn'     => $product_info['isbn'],
		// 					'mpn'      => $product_info['mpn'],
		// 					'weight'   => $this->weight->format(($product_info['weight'] + $option_weight) * $product['quantity'], $product_info['weight_class_id'], $this->language->get('decimal_point'), $this->language->get('thousand_point'))
		// 				);
		// 			}
		// 		}

		// 		$data['orders'][] = array(
		// 			'order_id'	       => $order_id,
		// 			'invoice_no'       => $invoice_no,
		// 			'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
		// 			'store_name'       => $order_info['store_name'],
		// 			'store_url'        => rtrim($order_info['store_url'], '/'),
		// 			'store_address'    => nl2br($store_address),
		// 			'store_email'      => $store_email,
		// 			'store_telephone'  => $store_telephone,
		// 			'store_fax'        => $store_fax,
		// 			'email'            => $order_info['email'],
		// 			'telephone'        => $order_info['telephone'],
		// 			'shipping_address' => $shipping_address,
		// 			'shipping_method'  => $order_info['shipping_method'],
		// 			'product'          => $product_data,
		// 			'comment'          => nl2br($order_info['comment'])
		// 		);
		// 	}
		// }

        // $this->response->setOutput($this->load->view('sale/order_shipping.tpl', $data));
        
	}


	protected function setCellRow( $worksheet, $row, $data, &$default_style=null, &$styles=null ) {
		if (!empty($default_style)) {
			$worksheet->getStyle( "$row:$row" )->applyFromArray( $default_style, false );
		}
		if (!empty($styles)) {
			foreach ($styles as $col=>$style) {
				$worksheet->getStyleByColumnAndRow($col,$row)->applyFromArray($style,false);
			}
		}
		$worksheet->fromArray( $data, null, 'A'.$row, true );
	}


}