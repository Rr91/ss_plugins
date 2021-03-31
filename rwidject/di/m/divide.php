<?php
class ModelModuleDivide extends Model {

    public function copy($order_id)
    {   
        $new_order_id = $this->copyOrderTable($order_id);
        if(!$new_order_id) return false;
        
        $this->copyOrderProductTable($order_id, $new_order_id);
        $this->copyOrderTotalTable($order_id, $new_order_id);
        
        return $new_order_id;
    }

    public function copyOrderTotalTable($order_id, $new_order_id)
    {   
        $table = 'order_total';        
        $sql = "CREATE TEMPORARY TABLE `tmp_table` SELECT * FROM `" . DB_PREFIX . $table."` WHERE `order_id` = '".$order_id."'";
        $this->db->query($sql);
        
        $sql = "UPDATE tmp_table SET order_total_id=NULL ,order_id='".$new_order_id."'";
        $this->db->query($sql);
        
        $sql = "INSERT INTO `" . DB_PREFIX . $table."` SELECT * FROM tmp_table";
        $this->db->query($sql);
        
        $sql = "DROP TABLE tmp_table";
        $this->db->query($sql);        
    }


    private function copyOrderProductTable($order_id, $new_order_id)
    {        
        $table = 'order_product';
        $sql = "SELECT * FROM `" . DB_PREFIX . $table."` WHERE `order_id` = '".$order_id."'";
        $query = $this->db->query($sql);

        foreach ($query->rows as $row) {
            $order_product_id = $row['order_product_id'];
            $sql = "CREATE TEMPORARY TABLE `tmp_table` SELECT * FROM `" . DB_PREFIX . $table."` WHERE `order_product_id` = '".$order_product_id."'";
            $this->db->query($sql);

            $sql = "UPDATE tmp_table SET order_product_id=NULL, order_id='".$new_order_id."' ";
            $this->db->query($sql);
        
            $sql = "INSERT INTO `" . DB_PREFIX . $table."` SELECT * FROM tmp_table LIMIT 1";
            $this->db->query($sql);
        
            $new_order_product_id = $this->db->getLastId();
            $sql = "DROP TABLE tmp_table";
            $this->db->query($sql);
        
            $this->copyOrderOptionTable($order_id, $new_order_id, $order_product_id, $new_order_product_id);
        }
        

    }

    private function copyOrderOptionTable($order_id, $new_order_id, $order_product_id, $new_order_product_id)
    {
        $table = 'order_option';
        $sql = "SELECT * FROM `" . DB_PREFIX . $table."` WHERE `order_product_id` = '".$order_product_id."'";
        $query = $this->db->query($sql);
        foreach ($query->rows as $row) {
            $order_option_id = $row['order_option_id'];
            $sql = "CREATE TEMPORARY TABLE `tmp_table` SELECT * FROM `" . DB_PREFIX . $table."` WHERE `order_option_id` = '".$order_option_id."'";
            $this->db->query($sql);

            $sql = "UPDATE tmp_table SET order_option_id=NULL, order_product_id = '".$new_order_product_id."' , order_id='".$new_order_id."' ";
            $this->db->query($sql);
        
            $sql = "INSERT INTO `" . DB_PREFIX . $table."` SELECT * FROM tmp_table LIMIT 1";
            $this->db->query($sql);
        
            $new_order_option_id = $this->db->getLastId();

            $sql = "DROP TABLE tmp_table";
            $this->db->query($sql);        
        }
    }

    private function copyOrderTable($order_id)
    {
        $table = 'order';
        $sql = "SELECT MAX(`num`) num FROM `" . DB_PREFIX . $table."` WHERE parent_id = '".$order_id."'";
        $query = $this->db->query($sql);
        $num = intval($query->row['num'])+1;
        
        $sql = "CREATE TEMPORARY TABLE `tmp_table` SELECT * FROM `" . DB_PREFIX . $table."` WHERE `order_id` = '".$order_id."'";
        $this->db->query($sql);
        
        $sql = "UPDATE tmp_table SET order_id=NULL, parent_id = '".$order_id."', num='".$num."'";
        $this->db->query($sql);
        
        $sql = "INSERT INTO `" . DB_PREFIX . $table."` SELECT * FROM tmp_table LIMIT 1";
        $this->db->query($sql);

        $new_order_id = $this->db->getLastId();
        
        $sql = "DROP TABLE tmp_table";
        $this->db->query($sql);

        return $new_order_id;
    }

    


    

   

}

