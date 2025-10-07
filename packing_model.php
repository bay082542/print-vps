<?php
defined('BASEPATH') or exit('No direct script access allowed');

class packing_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->wk = $this->load->database('workload', true);
        $this->van = $this->load->database('vann', true);
        $this->pack = $this->load->database('pack', true);
        $this->as400 = $this->load->database('as400', true);
        $this->vpis = $this->load->database('vpis', true);
    }

    public function get_detail_issue($issue_no)
    {
        $issue_no = $this->db->escape($issue_no);
        $sql = "SELECT *
            FROM J002MP
            LEFT JOIN (SELECT S01M01 AS S01ORD, S01M04, S01M16, S01M17, S01M08 FROM S010MP) ON J2CUS = S01ORD
            LEFT JOIN PACKING_SHEET ps ON S01ORD = ORDER_NO AND S01M04 = PACKING_NO
            WHERE J2SEQ != '0'
            AND (SUBSTR(J2ODR, 6, 1) = 'U' OR S01M04 LIKE '%88' OR S01M04 LIKE '%89')
            AND (STATUS IS NULL OR STATUS = '1')
            AND J2ODR = $issue_no";

        $query = $this->wk->query($sql);
        return $query->result();
    }

    public function getOrderDetails($order, $packing)
    {
        $order = $this->db->escape($order);
        $packing = $this->db->escape($packing);

        $sql = "SELECT A.*, B.LVAL, S.*, ps.*, SUBSTR(F_CPROD(M8K01), -3) AS SCHEDULE, SUBSTR(F_CPROD(M8K01), -5) AS JUN, M8K02
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

        $query = $this->wk->query($sql);
        return $query->result();
    }

    public function insert_packing_sheet($data)
    {
        $date = date("Y-m-d H:i:s");
        $this->wk->set('DATETIME_CREATE', "TO_DATE('" . $date . "','YYYY-MM-DD HH24:MI:SS')", false);
        $this->wk->insert("PACKING_SHEET", $data);
    }

    public function getPackingSheetByOrderAndPacking($order_no, $packing_no)
    {
        return $this->wk->get_where('PACKING_SHEET', ['ORDER_NO' => $order_no, 'PACKING_NO' => $packing_no]);
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
        return $this->van->get('PRINT_REMARK')->result();
    }

    public function get_packing_sheet()
    {
        return $this->wk->select('ORDER_NO,PACKING_NO')
            ->from('PACKING_SHEET')
            ->where('STATUS', '2')
            ->group_by('ORDER_NO,PACKING_NO')
            ->get()
            ->result();
    }

    public function get_packing($order)
    {
        return $this->wk->select('PACKING_NO,AMOUNT_PRINT')
            ->from('PACKING_SHEET')
            ->where('ORDER_NO', $order)
            ->group_by('PACKING_NO ,AMOUNT_PRINT')
            ->get()
            ->result();
    }

    public function insert_print_log($data)
    {
        $date = date("Y-m-d H:i:s");
        $this->wk->set('PRINTDATE', "TO_DATE('" . $date . "','YYYY-MM-DD HH24:MI:SS')", false);
        $this->wk->insert('PRINT_LOG_VPS', $data);
    }

    public function chk_packorder($order, $packing)
    {
        return $this->pack->get_where('packorddtl', ['orderno' => $order, 'packno' => $packing])->result();
    }

    public function chk_ItemMas($order, $packing)
    {
        return $this->pack->get_where('ItemMas', ['orderno' => $order, 'packno' => $packing])->result();
    }

    public function chk_ItemQty($order, $packing)
    {
        return $this->pack->get_where('ItemQty', ['ordrno' => $order, 'itemno' => $packing])->result();
    }

    public function chk_print($order, $packing)
    {
        return $this->pack->get_where('packorddtl', ['orderno' => $order, 'packno' => $packing, 'printsta' => '1'])->result();
    }

    public function chk_pis($order, $packing)
    {
        return $this->wk->like('S01M01', $order, 'bold')->where('S01M04', $packing)->get('S010MP')->result();
    }

    public function get_packingorder($packing)
    {
        return $this->pack->get_where('packorddtl', ['packno' => $packing])->result();
    }

    public function insert_ora($table, $data)
    {
        if ($table == 'NG_LOG_VPS' || $table == 'PRINT_LOG_VPS_OTHER') {
            $date = date("Y-m-d H:i:s");
            $this->wk->set('DATE_CREATE', "TO_DATE('" . $date . "','YYYY-MM-DD HH24:MI:SS')", false);
        }
        $this->wk->insert($table, $data);
    }

    public function update_ora($table, $data, $where)
    {
        $this->wk->update($table, $data, $where);
    }

    public function delete_ora($table, $where)
    {
        $this->wk->delete($table, $where);
    }

    public function insert_db($table, $data)
    {
        if (!$this->pack->insert($table, $data)) {
            $error = $this->pack->error();
            log_message('error', 'DB Error: ' . $error['message']);
        }
    }

    public function update_db($table, $data, $where)
    {
        $this->pack->update($table, $data, $where);
    }

    public function chk_PISinfo($order, $packing)
    {
        return $this->pack->get_where('PISInfo', ['orderno' => $order, 'item' => $packing])->result();
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
        return $this->pack->get_where('PackingDetail', ['orderno' => $order, 'item' => $item])->result();
    }

    public function get_packingNo($sect)
    {
        $this->wk->distinct()->select('PACKNO')->from('AMECORDERS_PACKNO');
        if ($sect != 'WSD' && $sect != 'SSA') {
            $this->wk->where('SECT', $sect);
        }
        return $this->wk->order_by('PACKNO')->get()->result();
    }

    public function get_packingNo88_89()
    {
        return $this->wk->distinct()
            ->select('PACKNO')
            ->from('AMECORDERS_PACKNO')
            ->like('PACKNO', '88', 'before')
            ->or_like('PACKNO', '89', 'before')
            ->order_by('PACKNO')
            ->get()
            ->result();
    }

    private function get_distinct_column($column, $sect, $order_no = null)
    {
        $sub_query = $this->wk->distinct()
            ->select('PACKNO')
            ->from('AMECORDERS_PACKNO')
            ->where('SECT', $sect)
            ->get_compiled_select();

        $this->pack->distinct()->select($column)->from('PACKORDDTL')->where('PRINTSTA', '1');
        if ($order_no !== null) {
            $this->pack->where('ORDERNO', $order_no);
        }
        if ($sect !== "WSD") {
            $this->wk->where("PACKNO IN ($sub_query)", null, false);
        }

        return $this->pack->get()->result();
    }

    public function get_distinct_order_no($sect, $search = '', $page = 1, $limit = 50)
    {
        $packnoResult = $this->wk->distinct()
            ->select('PACKNO')
            ->from('AMECORDERS_PACKNO')
            ->where('SECT', $sect)
            ->get()
            ->result_array();

        $packnoList = "NULL";
        if (!empty($packnoResult)) {
            $packnoArray = array_map(fn ($item) => "'" . $this->db->escape_str($item) . "'", array_column($packnoResult, 'PACKNO'));
            $packnoList  = implode(',', $packnoArray);
        }

        $sql = "SELECT DISTINCT(ORDERNO) FROM (
                SELECT * FROM PACKORDDTL
                WHERE PRINTSTA = '1'";
        if ($search) {
            $sql .= " AND ORDERNO LIKE '%" . $this->db->escape_like_str($search) . "%'";
        }
        if ($sect !== "WSD") {
            $sql .= " AND PACKNO IN ($packnoList)";
        }
        $sql .= " ORDER BY UPDATEDATE DESC) WHERE ROWNUM <= $limit";

        $query = $this->wk->query($sql);
        $items = array_map(fn ($row) => ['id' => $row->ORDERNO, 'text' => $row->ORDERNO], $query->result());

        return ['items' => $items, 'more' => count($items) >= $limit];
    }

    public function get_distinct_packing_no($order_no, $sect)
    {
        return $this->get_distinct_column('PACKNO', $sect, $order_no);
    }

    public function get_order_other($packing, $schedule = null)
    {
        $where_sche = !empty($schedule) ? "AND MAX_DATE = '$schedule'" : "";
        $sql = "SELECT DISTINCT(M8K03), SUBSTR(F_CPROD(M8K01), -3) AS SCHEDULE, mk.*, sm.*, p.*, sch.max_date, a.AGENT
                FROM M008KP mk
                JOIN S010MP sm ON mk.M8K03 = sm.S01M01
                JOIN PACKORDDTL p ON mk.M8K03 = p.ORDERNO AND sm.S01M04 = p.PACKNO
                LEFT JOIN (SELECT NEXTWORKDAY(max(workid), 1) AS max_date, schdnumber FROM AMECCALENDAR a GROUP BY schdnumber) sch ON sch.schdnumber = sm.S01M09
                LEFT JOIN AMECORDERS a ON sm.S01M01 = a.MFGNO
                WHERE S01M04 = '$packing'
                AND S01M17 IS NULL
                $where_sche
                ORDER BY max_date DESC, M8K02 ASC, M8K04 ASC";
        return $this->wk->query($sql)->result();
    }

    public function get_order_88_89()
    {
        $where_sche = !empty($schedule) ? "AND MAX_DATE = '$schedule'" : "";
        $sql = "SELECT SUBSTR(F_CPROD(M8K01), -3) AS SCHEDULE, mk.*, sm.*, p.*, sch.max_date, a.AGENT
                FROM M008KP mk
                JOIN S010MP sm ON mk.M8K03 = sm.S01M01
                JOIN PACKORDDTL p ON mk.M8K03 = p.ORDERNO AND sm.S01M04 = p.PACKNO
                LEFT JOIN (SELECT NEXTWORKDAY(max(workid), 1) AS max_date, schdnumber FROM AMECCALENDAR a GROUP BY schdnumber) sch ON sch.schdnumber = sm.S01M09
                LEFT JOIN AMECORDERS a ON sm.S01M01 = a.MFGNO
                WHERE SUBSTR(sm.S01M04, -2) IN ('88', '89')
                AND PRINTSTA = '0'
                AND mk.M8K01 >= '20250000'
                AND S01M17 IS NULL
                $where_sche
                ORDER BY max_date DESC";
        return $this->wk->query($sql)->result();
    }

    public function get_order_jobs($packing, $schedule = null)
    {
        $where_sche = !empty($schedule) ? "AND MAX_DATE = '$schedule'" : "";
        $sql = "SELECT DISTINCT(M8K03), SUBSTR(F_CPROD(M8K01), -3) AS SCHEDULE, mk.*, sm.*, p.*, sch.max_date, a.AGENT
                FROM M008KP mk
                JOIN S010MP sm ON mk.M8K03 = sm.S01M01
                JOIN PACKORDDTL p ON mk.M8K03 = p.ORDERNO AND sm.S01M04 = p.PACKNO
                LEFT JOIN (SELECT NEXTWORKDAY(max(workid), 1) AS max_date, schdnumber FROM AMECCALENDAR a GROUP BY schdnumber) sch ON sch.schdnumber = TRIM(sm.S01M09)
                LEFT JOIN AMECORDERS a ON sm.S01M01 = a.MFGNO
                WHERE S01M04 = '$packing'
                $where_sche
                ORDER BY M8K01 DESC";
        return $this->wk->query($sql)->result();
    }

    public function getCalendar($where)
    {
        return $this->wk->order_by('WORKID', 'ASC')->get_where('AMECCALENDAR', $where)->result();
    }

    public function get_report_cause()
    {
        return $this->wk->get_where('REPRINT_CAUSE', ['STATUS' => 1])->result();
    }

    public function get_amecuserall($SECCODE)
    {
        return $this->wk->get_where('AMECUSERALL', ['SSECCODE' => $SECCODE, 'CSTATUS' => '1'])->result();
    }

    public function get_permission($q = NULL)
    {
        $this->wk->from('PRINT_PERMISSION');
        if ($q) {
            $this->wk->where($q);
        }
        return $this->wk->get()->result();
    }

    public function search_idtag($idtag)
    {
        return $this->wk->get_where('PARTLABEL', ['Q16001' => $idtag])->result();
    }

    public function get_order_idtag($idtag)
    {
        return $this->wk->get_where('F003KP', ['F03R01' => $idtag])->result();
    }

    public function get_Q141KP($order, $packing, $dwg)
    {
        $sql = "SELECT A.*, C.Q43K06
                FROM RTNLIBF.S011MP A
                JOIN RTNLIBF.Q141KP B ON A.S11M01 = B.Q41K01 AND A.S11M04 = B.Q41K08
                LEFT JOIN RTNLIBF.Q143KP C ON A.S11M01 = C.Q43K01 AND B.Q41K05 = C.Q43K05 AND B.Q41K04 = C.Q43K04
                WHERE A.S11M01 = '$order'
                AND A.S11M02 = '$packing'
                AND A.S11M07 = '0'
                AND A.S11M04 = '$dwg'";
        return $this->as400->query($sql)->result();
    }

    public function getPURcode($main, $cond)
    {
        $sql = "SELECT TRIM(SUBSTR(PNDATA, 45, 7)) as purcode FROM Q008MP qm WHERE PNRKUB = '0' AND PNZUBA LIKE '$main' AND PNHING = '$cond'";
        return $this->wk->query($sql)->result();
    }

    public function getDataByOrder($order, $packing)
    {
        return $this->wk->from('S011MP')
            ->join('S010MP', 'S011MP.S11M01 = S010MP.S01M01 AND S011MP.S11M02 = S010MP.S01M04', 'left')
            ->where('S11M01', $order)
            ->where('S11M02', $packing)
            ->get()
            ->result();
    }

    public function getMasterPacking($dwg)
    {
        return $this->wk->get_where('MASTER_PACKLIST', ['DWGNO' => $dwg])->result();
    }

    public function getProblemPacking()
    {
        return $this->wk->get_where('PROBLEM_PACKLIST', ['PP_STATUS' => '1'])->result();
    }

    public function get_temp_status($where)
    {
        return $this->wk->get_where("TEMP_STATUS_VPS", $where)->result();
    }

    public function get_agent()
    {
        return $this->wk->distinct()
            ->select('AGENT')
            ->from('AMECORDERS')
            ->where('AGENT IS NOT NULL', null, false)
            ->order_by('AGENT', 'ASC')
            ->get()
            ->result();
    }

    public function chk_print_vpis($order, $packing, $drawing)
    {
        return $this->vpis->get_where('QRASSY_STICKER_DATA', [
            'ORDERNO' => $order,
            'PACKNO' => $packing,
            'DWG' => $drawing,
            'PRINT_STA' => '1'
        ])->result();
    }

    public function getvpcorder($order)
    {
        return $this->wk->like('MFGNO', $order, 'bold')->get('AMECVPCORDER')->result();
    }
}