<?php

class shopReviewsModel extends waModel
{
    protected $table = 'shop_reviews';

    public function getReviews($offset, $limit)
    {
        $sql = "SELECT r.*, c.name contact_name, c.firstname contact_firstname, c.lastname contact_lastname,
                c.photo contact_photo, cd.value contact_city
                FROM ".$this->table." r
                LEFT JOIN wa_contact c ON r.contact_id = c.id
                LEFT JOIN wa_contact_data cd ON r.contact_id = cd.contact_id AND cd.field = 'address:city' AND cd.sort = 0";
        if (wa()->getEnv() == 'frontend') {
            $sql .= " WHERE r.status = 1";
            if ($reviews = wa()->getStorage()->get('shop/reviews')) {
                $reviews = explode(',', $reviews);
                foreach ($reviews as &$r) {
                    $r = (int)$r;
                }
                unset($r);
                $sql .= " OR r.id IN ('".implode("','", $reviews)."')";
            }
        }
        $sql .= " ORDER BY r.datetime DESC
        LIMIT ".(int)$offset.", ".(int)$limit;

        $rows = $this->query($sql)->fetchAll();
        $response_contact_ids = array();
        foreach ($rows as $i => $row) {
            if (!empty($row['response_contact_id'])) {
                $response_contact_ids[] = $row['response_contact_id'];
            }
            if (!empty($row['images'])) {
                $rows[$i]['images'] = explode(';', $row['images']);
            }
        }
        if ($response_contact_ids) {
            $response_contact_ids = array_unique($response_contact_ids);
            $contact_model = new waContactModel();
            $contacts = $contact_model->getById($response_contact_ids);
            foreach ($rows as &$row) {
                if (!empty($row['response_contact_id']) && !empty($contacts[$row['response_contact_id']])) {
                    $row['response_contact'] = $contacts[$row['response_contact_id']];
                }
            }
            unset($row);
        }

        return $rows;
    }

    public function countReviews()
    {
        $sql = "SELECT count(*) FROM ".$this->table;
        if (wa()->getEnv() == 'frontend') {
            $sql .= " WHERE status = 1";
            if ($reviews = wa()->getStorage()->get('shop/reviews')) {
                $reviews = explode(',', $reviews);
                foreach ($reviews as &$r) {
                    $r = (int)$r;
                }
                unset($r);
                $sql .= " OR id IN ('".implode("','", $reviews)."')";
            }
        }
        return $this->query($sql)->fetchField();
    }

    public function getRatings()
    {
        $where = "status = 1";
        if ($reviews = wa()->getStorage()->get('shop/reviews')) {
            $reviews = explode(',', $reviews);
            foreach ($reviews as &$r) {
                $r = (int)$r;
            }
            unset($r);
            $where .= " OR id IN ('".implode("','", $reviews)."')";
        }

        $sql = 'SELECT rating, count(*) FROM shop_reviews WHERE rating > 0 AND ('.$where.') GROUP BY rating';
        return $this->query($sql)->fetchAll('rating', true);
    }
}