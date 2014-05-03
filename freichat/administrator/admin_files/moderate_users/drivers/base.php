<?php

class Moderation {

    public $db;
    public $db_prefix;
    public $row_username;
    public $row_userid;
    public $usertable;
    public $no_of_ret_users = 0;

    public function get_search_str($id, $username, $show_what) {
        $search_str = '';

        if ($id != '') {
            $search_str = " WHERE u." . $this->row_userid . " = " . $id;
        } else if ($username != '') {
            $search_str = " WHERE u." . $this->row_username . " LIKE  \"%$username%\" ";
        }

        if ($show_what != 'show_everyone') {

            if ($search_str == '') {
                $pre_text = " WHERE ";
            } else {
                $pre_text = " AND ";
            }

            if ($show_what == 'show_banned') {
                $search_str .= $pre_text . " x.user_id IS NOT NULL";
            } else {
                $search_str .= $pre_text . " x.user_id IS NULL";
            }
        }

        return $search_str;
    }

    public function get_users($from, $no_of_records, $search = '', $id = '', $show_what = 'show_everyone') {
        //echo $id;


        $search_str = $this->get_search_str($id, $search, $show_what);


        if ($this->pdo_driver == "sqlsrv") {

            $query = '
                    SELECT  * FROM 
                    (
                        SELECT ROW_NUMBER() OVER ( ORDER BY u."' . $this->row_username . '" ) AS RowNum ,
                            u."' . $this->row_username . '" AS username, u."' . $this->row_userid . '" AS id, f.no_of_messages, x."user_id"
                        FROM ' . $this->db_prefix . $this->usertable . ' AS u

                        LEFT JOIN (

                        SELECT v."from", COUNT( * ) no_of_messages
                        FROM frei_chat AS v
                        GROUP BY v."from"
                        ) AS f ON f."from" = u."' . $this->row_userid . '"

                        LEFT JOIN (

                        SELECT w."user_id"
                        FROM frei_banned_users AS w
                        GROUP BY w."user_id"
                        ) AS x ON x."user_id" = u."' . $this->row_userid . '"
                        ' . $search_str . '
                    ) AS RowConstrainedResult
                    WHERE   RowNum >= ' . $from . ' 
                        AND RowNum < ' . $no_of_records . ' 
                    ORDER BY RowNum';
        } else {

            $query = 'SELECT u.' . $this->row_username . ' AS username, u.' . $this->row_userid . ' AS id, f.no_of_messages, x.user_id
                    FROM ' . $this->db_prefix . $this->usertable . ' AS u
                    
                    LEFT JOIN (

                    SELECT v.from, COUNT( * ) no_of_messages
                    FROM frei_chat AS v
                    GROUP BY v.from
                    ) AS f ON f.from = u.' . $this->row_userid . '
                    
                    LEFT JOIN (

                    SELECT w.user_id
                    FROM frei_banned_users AS w
                    GROUP BY w.user_id
                    ) AS x ON x.user_id = u.' . $this->row_userid . '
                    ' . $search_str . '
                    ORDER BY u.' . $this->row_userid . '   
                    LIMIT ' . $from . ',' . $no_of_records;
        }
        $obj = $this->db->query($query);
        //echo $query;
        $result = $obj->fetchAll();
        $this->no_of_ret_users = count($result);

        return $result;
    }

    public function get_no_of_users($search, $id = '', $show_what = '') {
        if ($id != '') {
            //obviously, userid is unique 
            return 1;
        }

        $search_str = $this->get_search_str($id, $search, 'show_everyone');


        if ($search_str != '') {
            $pre_text = ' AND ';
        } else {
            $pre_text = ' WHERE ';
        }

        if ($show_what != 'show_everyone') {
            if ($show_what == 'show_banned')
                $search_str = " LEFT JOIN frei_banned_users AS x ON x.user_id = u." . $this->row_userid . $search_str . $pre_text . " x.user_id IS NOT NULL";
            else {
                $search_str = " LEFT JOIN frei_banned_users AS x ON x.user_id = u." . $this->row_userid . $search_str . $pre_text . " x.user_id IS NULL";
            }
        }


        $query = "SELECT DISTINCT COUNT( * ) FROM " . $this->db_prefix . $this->usertable . " AS u " . $search_str;
        //echo $query;
        $count = $this->db->query($query)->fetchAll();
        return $count[0][0];
    }

    public function set_db_data() {
        return false;
    }

}