<?php
defined('BASEPATH') or exit('No direct script access allowed');

class packing_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->wk = $this->load->database('workload', true);
        // $this->wkts = $this->load->database('workloadts', true);
        $this->van   = $this->load->database('vann', true);
        $this->pack  = $this->load->database('pack', true);
        $this->as400 = $this->load->database('as400', true);
        $this->vpis  = $this->load->database('vpis', true);
    }

    public function get_detail_issue($issue_no)
    {
        $issue_no = $this->db->escape($issue_no);
        $sql      = "SELECT *
            FROM J002MP 
            LEFT JOIN (SELECT  S01M01 AS S01ORD, S01M04, S01M16, S01M17, S01M08 FROM S010MP)ON J2CUS = S01ORD
            LEFT JOIN PACKING_SHEET ps ON S01ORD = ORDER_NO AND S01M04 = PACKING_NO
            WHERE J2SEQ != '0'
            AND (SUBSTR(J2ODR,6,1) = 'U' OR S01M04 LIKE '%88' OR S01M04 LIKE '%89')
            AND (STATUS IS NULL OR STATUS = '1')
            AND J2ODR = $issue_no";

        $query  = $this->wk->query($sql);
        $result = $query->result();

        // Optionally log the last executed query for debugging
        // echo $this->db->last_query();

        return $result;
    }

    public function get_order_detail($order, $packing)
    {
        // Sanitize inputs to prevent SQL injection (assuming $order and $packing are safe to use directly)
        $order   = $this->db->escape($order);
        $packing = $this->db->escape($packing);

        $sql = "SELECT A.*, B.LVAL, S.*, ps.*, SUBSTR(F_CPROD(M8K01), -3) AS SCHEDULE,SUBSTR(F_CPROD(M8K01), -5) AS JUN, M8K02
            FROM S011MP A
            LEFT JOIN (
                SELECT S11M01, S11M08, S11M02, S11M03, S11M04, LISTAGG(SUBSTR(S11M06, -3), '') WITHIN GROUP (ORDER BY S11M06) AS LVAL 
                FROM S011MP 
                WHERE S11M07 = 1
                GROUP BY S11M01, S11M08, S11M02, S11M03, S11M04, S11M09
            ) B ON A.S11M01 = B.S11M01 AND A.S11M02 = B.S11M02 AND A.S11M04 = B.S11M04
            JOIN S010MP S ON A.S11M01 = S.S01M01 AND A.S11M02 = S.S01M04
            JOIN M008KP M ON A.S11M01 = M.M8K03
            LEFT JOIN PACKING_SHEET ps ON A.S11M01 = ps.ORDER_NO AND A.S11M02 = ps.PACKING_NO AND ps.DW_NO = A.S11M04 AND (ps.PART_NO = A.S11M06 OR ps.PART_NO IS NULL)
            WHERE A.S11M01 = $order 
            AND A.S11M02 = $packing 
            AND A.S11M07 = 0
            ORDER BY A.S11M04 ASC";

        $query  = $this->wk->query($sql); // Assuming $this->wk->query() is the correct method to execute queries in your framework
        $result = $query->result();

        return $result;
    }

    public function insert_packing_sheet($data)
    {
        $date = date("Y-m-d H:i:s");
        $this->wk->set('DATETIME_CREATE', "TO_DATE('" . $date . "','YYYY-MM-DD HH24:MI:SS')", false);
        $this->wk->insert("PACKING_SHEET", $data);
    }

    public function chk_data($order_no, $packing_no)
    {
        $this->wk->select('*');
        $this->wk->from('PACKING_SHEET');
        $this->wk->where('ORDER_NO', $order_no);
        $this->wk->where('PACKING_NO', $packing_no);
        $query = $this->wk->get();
        return $query;
    }

    public function update_packing_sheet($order_no, $packing_no, $data, $part_no = NULL)
    {
        $this->wk->where('PACKING_NO', $packing_no);
        $this->wk->where('ORDER_NO', $order_no);
        if (!empty($part_no)) {
            $this->wk->where('PART_NO', $part_no);
        }
        $this->wk->update('PACKING_SHEET', $data);
    }

    public function get_remark_reprint()
    {
        $this->van->select('*');
        $this->van->from('PRINT_REMARK');
        $query = $this->van->get();
        return $query->result();
    }

    public function get_packing_sheet()
    {
        $this->wk->select('ORDER_NO,PACKING_NO');
        $this->wk->from('PACKING_SHEET');
        $this->wk->where('STATUS', '2');
        $this->wk->group_by('ORDER_NO,PACKING_NO');
        $query = $this->wk->get();
        return $query->result();
    }

    public function get_packing($order)
    {
        $this->wk->select('PACKING_NO,AMOUNT_PRINT');
        $this->wk->from('PACKING_SHEET');
        $this->wk->where('ORDER_NO', $order);
        $this->wk->group_by('PACKING_NO ,AMOUNT_PRINT');
        $query = $this->wk->get();
        return $query->result();
    }

    public function insert_print_log($data)
    {
        $date = date("Y-m-d H:i:s");
        $this->wk->set('PRINTDATE', "TO_DATE('" . $date . "','YYYY-MM-DD HH24:MI:SS')", false);
        $this->wk->insert('PRINT_LOG_VPS', $data);
    }

    public function chk_packorder($order, $packing)
    {
        // SELECT * FROM packorddtl WHERE orderno = 'ET900L393' AND packno = '36689' AND  (packno LIKE '%88' OR  packno LIKE '%89')
        $this->pack->select('*');
        $this->pack->from('packorddtl');
        $this->pack->where('orderno', $order);
        $this->pack->where('packno', $packing);
        // $this->pack->group_start();
        // $this->pack->like('packno', '88', 'before');
        // $this->pack->or_like('packno', '89', 'before');
        // $this->pack->group_end();
        $query = $this->pack->get();
        return $query->result();

    }

    public function chk_ItemMas($order, $packing)
    {
        // select * from packingsys.dbo.ItemMas im where orderno = 'SY8751J31'  and (packno like '%88' or  packno like '%89')
        $this->pack->select('*');
        $this->pack->from('ItemMas');
        $this->pack->where('orderno', $order);
        $this->pack->where('packno', $packing);
        // $this->pack->group_start();
        // $this->pack->like('packno', '88', 'before');
        // $this->pack->or_like('packno', '89', 'before');
        // $this->pack->group_end();
        $query = $this->pack->get();
        return $query->result();
    }

    public function chk_ItemQty($order, $packing)
    {
        // select * from packingsys.dbo.ItemQty iq where (itemno like '%88' or itemno like '%89')
        $this->pack->select('*');
        $this->pack->from('ItemQty');
        $this->pack->where('ordrno', $order);
        $this->pack->where('itemno', $packing);
        // $this->pack->group_start();
        // $this->pack->like('itemno', '88', 'before');
        // $this->pack->or_like('itemno', '89', 'before');
        // $this->pack->group_end();
        $query = $this->pack->get();
        return $query->result();
    }

    public function chk_print($order, $packing)
    {
        $this->pack->select('*');
        $this->pack->from('packorddtl');
        $this->pack->where('orderno', $order);
        $this->pack->where('packno', $packing);
        $this->pack->where('printsta', '1');
        // $this->pack->group_start();
        // $this->pack->like('packno', '88', 'before');
        // $this->pack->or_like('packno', '89', 'before');
        // $this->pack->group_end();
        $query = $this->pack->get();
        return $query->result();
    }

    // public function chk_print($order, $packing){
    //     $this->wk->select('*');
    //     $this->wk->from('PACKORDDTL');
    //     $this->wk->where('ORDERNO', $order);
    //     $this->wk->where('PACKNO', $packing);
    //     $this->wk->where('PRINTSTA', '1');
    //     $query = $this->wk->get();
    //     return $query->result();
    // }

    public function chk_pis($order, $packing)
    {
        $this->wk->select('*');
        $this->wk->from('S010MP');
        $this->wk->like('S01M01', $order, 'bold');
        $this->wk->where('S01M04', $packing);
        $query = $this->wk->get();
        return $query->result();
    }

    public function get_packingorder($packing)
    {
        $this->pack->select('*');
        $this->pack->from('packorddtl');
        $this->pack->where('packno', $packing);
        $query = $this->pack->get();
        return $query->result();
    }

    public function insert_ora($table, $data)
    {
        if ($table == 'NG_LOG_VPS') {
            $date = date("Y-m-d H:i:s");
            $this->wk->set('DATE_CREATE', "TO_DATE('" . $date . "','YYYY-MM-DD HH24:MI:SS')", false);
        }

        if ($table == 'PRINT_LOG_VPS_OTHER') {
            $date = date("Y-m-d H:i:s");
            $this->wk->set('PRINTDATE', "TO_DATE('" . $date . "','YYYY-MM-DD HH24:MI:SS')", false);
        }
        $this->wk->insert($table, $data);
    }

    public function update_ora($table, $data, $where)
    {
        $this->wk->where($where);
        $this->wk->update($table, $data);
    }

    public function delete_ora($table, $where)
    {
        $this->wk->where($where);
        $this->wk->delete($table);
    }

    public function insert_db($table, $data)
    {
        if ($this->pack->insert($table, $data)) {
            echo "Data successfully inserted into table: $table\n";
        } else {
            $error = $this->pack->error(); // Get error information
            echo "Error inserting data into table: $table<br>";
            echo "Error: " . $error['message'] . "<br>";
            echo "Query: " . $error['query'] . "<br>";
        }
    }

    public function update_db($table, $data, $where)
    {
        $this->pack->update($table, $data, $where);
    }

    public function chk_PISinfo($order, $packing)
    {
        $this->pack->select('*');
        $this->pack->from('PISInfo');
        $this->pack->where('orderno', $order);
        $this->pack->where('item', $packing);
        // $this->pack->group_start();
        // $this->pack->like('item', '88', 'before');
        // $this->pack->or_like('item', '89', 'before');
        // $this->pack->group_end();
        $query = $this->pack->get();
        return $query->result();
    }

    public function InsPackorddtlByManual($order, $packing)
    {
        $this->pack->query("EXEC InsPackorddtlByManual ?, ?", array($order, $packing));
    }

    public function delete_packing_db($table_name, $data)
    {
        $this->pack->delete($table_name, $data);
    }

    public function chk_packing_detail($order, $item)
    {
        $this->pack->select('*');
        $this->pack->from('PackingDetail');
        $this->pack->where('orderno', $order);
        $this->pack->where('item', $item);
        $query = $this->pack->get();
        return $query->result();
    }

    public function get_packingNo($sect)
    {
        $this->wk->distinct();
        $this->wk->select('PACKNO');
        $this->wk->from('AMECORDERS_PACKNO');
        if ($sect != 'WSD' && $sect != 'SSA') {
            $this->wk->where('SECT', $sect);
        }

        // if ($sect = 'CEC') {
        //     $this->wk->where_in('PACKNO', ['22101', '22102', '14101']);
        // }
        $this->wk->order_by('PACKNO');
        // Execute the query and return the result
        $query = $this->wk->get();

        return $query->result();
    }

    public function get_packingNo88_89()
    {
        $this->wk->distinct();
        $this->wk->select('PACKNO');
        $this->wk->from('AMECORDERS_PACKNO');
        $this->wk->like('PACKNO', '88', 'before');
        $this->wk->or_like('PACKNO', '89', 'before');
        $this->wk->order_by('PACKNO');
        $query = $this->wk->get();
        return $query->result();
    }

    private function get_distinct_column($column, $sect, $order_no = null)
    {
        $sub_query = $this->wk->distinct()
            ->select('PACKNO')
            ->from('AMECORDERS_PACKNO')
            ->where('SECT', $sect)
            ->get_compiled_select();

        $this->pack->distinct();
        $this->pack->select($column);
        $this->pack->from('PACKORDDTL');
        $this->pack->where('PRINTSTA', '1');
        if ($order_no !== null) {
            $this->pack->where('ORDERNO', $order_no);
        }
        if ($sect !== "WSD") {
            $this->wk->where("PACKNO IN ($sub_query)", null, false);
        }

        $query = $this->pack->get();
        // echo $this->wk->last_query();
        return $query->result();
    }

    public function get_distinct_order_no($sect, $search = '', $page = 1, $limit = 50)
    {
        $offset = ($page - 1) * $limit;

        $packnoResult = $this->wk->distinct()
            ->select('PACKNO')
            ->from('AMECORDERS_PACKNO')
            ->where('SECT', $sect)
            ->get()
            ->result_array();

        $packnoArray = array_column($packnoResult, 'PACKNO');

        if (!empty($packnoArray)) {
            $packnoArray = array_map(function ($item) {
                return "'" . $this->db->escape_str($item) . "'";
            }, $packnoArray);
            $packnoList  = implode(',', $packnoArray);
        } else {
            $packnoList = "NULL";
        }

        $sql = "SELECT DISTINCT(ORDERNO) FROM (
                SELECT * FROM PACKORDDTL 
                WHERE PRINTSTA = '1'";

        if ($search) {
            $escapedSearch = $this->db->escape_like_str($search);
            $sql .= " AND ORDERNO LIKE '%" . $escapedSearch . "%'";
        }

        if ($sect !== "WSD") {
            $sql .= " AND PACKNO IN ($packnoList)";
        }

        $sql .= " ORDER BY UPDATEDATE DESC
            ) WHERE ROWNUM <= $limit";

        $query = $this->wk->query($sql);

        $items = [];
        foreach ($query->result() as $row) {
            $items[] = [
                'id'   => $row->ORDERNO,
                'text' => $row->ORDERNO
            ];
        }

        return [
            'items' => $items,
            'more'  => count($items) >= $limit
        ];
    }


    // public function get_distinct_order_no($sect)
    // {
    //     return $this->get_distinct_column('ORDERNO', $sect);
    // }

    public function get_distinct_packing_no($order_no, $sect)
    {
        return $this->get_distinct_column('PACKNO', $sect, $order_no);
    }

    // public function get_order_other($p, $year, $packing, $schedule)
    // {
    //     //     $sql = "SELECT DISTINCT(M8K03),SUBSTR(F_CPROD(M8K01), -3) AS SCHEDULE,mk.*,sm.* FROM M008KP mk 
    //     //     JOIN S010MP sm ON mk.M8K03 = sm.S01M01 
    //     //     WHERE M8K02 = '$p'
    //     //     AND M8K01 LIKE '$year%'
    //     //     AND S01M04 = '$packing'
    //     //     AND SUBSTR(F_CPROD(M8K01), -3) = '$schedule'";

    //     $sql = "SELECT 
    //                 DISTINCT(M8K03),
    //                 SUBSTR(F_CPROD(M8K01), -3) AS SCHEDULE,
    //                 mk.*,
    //                 sm.*
    //                 -- sm2.*
    //             FROM
    //                 M008KP mk
    //             JOIN S010MP sm ON
    //                 mk.M8K03 = sm.S01M01
    //             -- JOIN S011MP sm2 ON M8K03 = S11M01
    //             WHERE M8K02 = '$p'
    //             AND M8K01 LIKE '$year%'
    //             AND S01M04 = '$packing'
    //             AND SUBSTR(F_CPROD(M8K01), -3) = '$schedule'";

    //     $query = $this->wk->query($sql);

    //     return $query->result();
    // }

    public function get_order_other($packing, $schedule = null)
    {
        // SELECT DISTINCT(M8K03),SUBSTR(F_CPROD(M8K01), -3) AS SCHEDULE,mk.*,sm.*,p.* FROM M008KP mk 
        // JOIN S010MP sm ON mk.M8K03 = sm.S01M01 
        // JOIN PACKORDDTL p ON mk.M8K03 = p.ORDERNO AND sm.S01M04 = p.PACKNO
        // LEFT JOIN (SELECT NEXTWORKDAY(max(workid) , 1) AS max_date,schdnumber FROM AMECCALENDAR a group by schdnumber) sch ON sch.schdnumber = sm.S01M09
        // WHERE S01M04 = '14101'
        // AND PRINTSTA = '0'


        // $this->wk->select('DISTINCT(M8K03), SUBSTR(F_CPROD(M8K01), -3) AS SCHEDULE, mk.*, sm.*, p.*, sch.max_date');
        // $this->wk->from('M008KP mk');
        // $this->wk->join('S010MP sm', 'mk.M8K03 = sm.S01M01');
        // $this->wk->join('PACKORDDTL p', 'mk.M8K03 = p.ORDERNO AND sm.S01M04 = p.PACKNO');
        // $this->wk->join('(SELECT NEXTWORKDAY(max(workid), 1) AS max_date, schdnumber FROM AMECCALENDAR a GROUP BY schdnumber) sch', 'sch.schdnumber = sm.S01M09', 'left');
        // $this->wk->where('S01M04', $packing);
        // $this->wk->where('PRINTSTA', '0');
        // $query = $this->wk->get();
        if (!empty($schedule)) {
            $where_sche = "AND MAX_DATE = '$schedule'";
        } else {
            $where_sche = "";
        }

        $sql   = "SELECT DISTINCT(M8K03),SUBSTR(F_CPROD(M8K01), -3) AS SCHEDULE,mk.*,sm.*,p.*,sch.max_date,a.AGENT FROM M008KP mk 
                JOIN S010MP sm ON mk.M8K03 = sm.S01M01 
                JOIN PACKORDDTL p ON mk.M8K03 = p.ORDERNO AND sm.S01M04 = p.PACKNO
                LEFT JOIN (SELECT NEXTWORKDAY(max(workid) , 1) AS max_date,schdnumber FROM AMECCALENDAR a group by schdnumber) sch ON sch.schdnumber = sm.S01M09
                LEFT JOIN AMECORDERS a ON sm.S01M01 = a.MFGNO
                WHERE S01M04 = '$packing'
                --AND PRINTSTA = '0'
                AND S01M17 IS NULL
                $where_sche
                ORDER BY max_date DESC , M8K02 ASC , M8K04 ASC";
        $query = $this->wk->query($sql);
        return $query->result();
    }

    public function get_order_88_89()
    {

        if (!empty($schedule)) {
            $where_sche = "AND MAX_DATE = '$schedule'";
        } else {
            $where_sche = "";
        }

        $sql   = "SELECT SUBSTR(F_CPROD(M8K01), -3) AS SCHEDULE,mk.*,sm.*,p.*,sch.max_date,a.AGENT FROM M008KP mk 
                JOIN S010MP sm ON mk.M8K03 = sm.S01M01 
                JOIN PACKORDDTL p ON mk.M8K03 = p.ORDERNO AND sm.S01M04 = p.PACKNO
                LEFT JOIN (SELECT NEXTWORKDAY(max(workid) , 1) AS max_date,schdnumber FROM AMECCALENDAR a group by schdnumber) sch ON sch.schdnumber = sm.S01M09
                LEFT JOIN AMECORDERS a ON sm.S01M01 = a.MFGNO
                WHERE SUBSTR(sm.S01M04,-2) IN ('88','89')
                AND PRINTSTA = '0'
                AND mk.M8K01 >= '20250000'
                AND S01M17 IS NULL
                -- $where_sche
                ORDER BY max_date DESC";
        $query = $this->wk->query($sql);
        return $query->result();
    }

    public function get_order_jobs($packing, $schedule = null)
    {
        if (!empty($schedule)) {
            $where_sche = "AND MAX_DATE = '$schedule'";
        } else {
            $where_sche = "";
        }

        $sql   = "SELECT DISTINCT(M8K03),SUBSTR(F_CPROD(M8K01), -3) AS SCHEDULE,mk.*,sm.*,p.*,sch.max_date,a.AGENT FROM M008KP mk 
                JOIN S010MP sm ON mk.M8K03 = sm.S01M01 
                JOIN PACKORDDTL p ON mk.M8K03 = p.ORDERNO AND sm.S01M04 = p.PACKNO
                LEFT JOIN (SELECT NEXTWORKDAY(max(workid) , 1) AS max_date,schdnumber FROM AMECCALENDAR a group by schdnumber) sch ON sch.schdnumber = TRIM(sm.S01M09)
                LEFT JOIN AMECORDERS a ON sm.S01M01 = a.MFGNO
                WHERE S01M04 = '$packing'
                $where_sche
                ORDER BY M8K01 DESC";
        $query = $this->wk->query($sql);
        return $query->result();
    }

    public function getCalendar($where)
    {
        $this->wk->select('*');
        $this->wk->from('AMECCALENDAR');
        if ($where) {
            $this->wk->where($where);
        }
        $this->wk->order_by('WORKID', 'ASC');
        $query = $this->wk->get();
        return $query->result();
    }

    public function get_report_cause()
    {
        $this->wk->select('*');
        $this->wk->from('REPRINT_CAUSE');
        $this->wk->where('STATUS', 1);
        $query = $this->wk->get();
        return $query->result();
    }

    public function get_amecuserall($SECCODE)
    {
        $this->wk->select('*');
        $this->wk->from('AMECUSERALL');
        $this->wk->where('SSECCODE', $SECCODE);
        $this->wk->where('CSTATUS', '1');
        $query = $this->wk->get();
        return $query->result();
    }


    public function get_permission($q = NULL)
    {
        $this->wk->select('*');
        $this->wk->from('PRINT_PERMISSION');
        if ($q) {
            $this->wk->where($q);
        }
        $query = $this->wk->get();
        return $query->result();
    }

    public function search_idtag($idtag)
    {
        $this->wk->select('*');
        $this->wk->from('PARTLABEL');
        $this->wk->where('Q16001', $idtag);
        $query = $this->wk->get();

        return $query->result();
    }

    public function get_order_idtag($idtag)
    {
        $this->wk->select('*');
        $this->wk->from('F003KP');
        $this->wk->where('F03R01', $idtag);
        $query = $this->wk->get();

        return $query->result();

    }

    public function get_Q141KP($order, $packing, $dwg)
    {
        // $this->wk->select('*');
        // $this->wk->from('S011MP a');
        // $this->wk->join('Q141KP b', 'a.S11M01 = b.Q41K01 and substr(a.S11M02,0,3) = b.Q41K03 and a.S11M04 = b.Q41K08');
        // $this->wk->where('a.S11M01', $order);
        // $this->wk->where('a.S11M02', $packing);

        // $this->wk->select('*');
        // $this->wk->from('Q141KP');
        // $this->wk->where('ROWNUM <= 2');
        $sql   = "SELECT A.*,C.Q43K06 FROM RTNLIBF.S011MP A
                JOIN RTNLIBF.Q141KP B ON A.S11M01 = B.Q41K01 AND A.S11M04 = B.Q41K08 
                LEFT JOIN RTNLIBF.Q143KP C ON A.S11M01 = C.Q43K01 AND B.Q41K05 = C.Q43K05 AND B.Q41K04 = C.Q43K04
                WHERE A.S11M01 = '$order' 
                AND A.S11M02 = '$packing'
                AND A.S11M07 = '0'
                AND A.S11M04 = '$dwg'";
        $query = $this->as400->query($sql);
        // echo $this->wk->last_query();
        return $query->result();
    }

    public function getPURcode($main, $cond)
    {
        // $sql   = "SELECT * FROM J002MP WHERE J2CUS = '$order' AND J2SEQ != 0 AND J2DRAW = '$dwg'";
        $sql   = "SELECT TRIM(SUBSTR(PNDATA, 45,7)) as purcode FROM Q008MP qm WHERE PNRKUB = '0' AND PNZUBA LIKE '$main' AND PNHING = '$cond' ";
        $query = $this->wk->query($sql);
        return $query->result();
    }

    public function getDataByOrder($order, $packing)
    {
        $this->wk->select('*');
        $this->wk->from('S011MP');
        $this->wk->join('S010MP', 'S011MP.S11M01 = S010MP.S01M01 AND S011MP.S11M02 = S010MP.S01M04', 'left');
        $this->wk->where('S11M01', $order);
        $this->wk->where('S11M02', $packing);
        $query = $this->wk->get();
        return $query->result();
    }

    public function getMasterPacking($dwg)
    {
        $this->wk->select('*')
            ->from('MASTER_PACKLIST')
            ->where('DWGNO', $dwg);
        return $this->wk->get()->result();
    }

    public function getProblemPacking()
    {
        $this->wk->select('*')
            ->from('PROBLEM_PACKLIST')
            ->where('PP_STATUS', '1');
        return $this->wk->get()->result();
    }

    public function get_temp_status($where)
    {
        $this->wk->select("*");
        $this->wk->from("TEMP_STATUS_VPS");
        $this->wk->where($where);
        $query = $this->wk->get();
        return $query->result();
    }

    public function get_agent()
    {
        $this->wk->distinct();
        $this->wk->select('AGENT');
        $this->wk->from('AMECORDERS');
        $this->wk->where('AGENT IS NOT NULL', null, false);
        $this->wk->order_by('AGENT', 'ASC');
        $query = $this->wk->get();
        return $query->result();
    }

    public function chk_print_vpis($order, $packing, $drawing)
    {
        $this->vpis->select('*');
        $this->vpis->from('QRASSY_STICKER_DATA');
        $this->vpis->where('ORDERNO', $order);
        $this->vpis->where('PACKNO', $packing);
        $this->vpis->where('DWG', $drawing);
        $this->vpis->where('PRINT_STA', '1');
        $query = $this->vpis->get();
        return $query->result();
    }

    public function getvpcorder($order)
    {
        $this->wk->select('*');
        $this->wk->from('AMECVPCORDER');
        $this->wk->like('MFGNO', $order, 'bold');
        $query = $this->wk->get();
        return $query->result();
    }






}