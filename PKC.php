<?php
defined('BASEPATH') or exit('No direct script access allowed');

function pre_array($array)
{
    echo "<pre>";
    print_r($array);
    echo "</pre>";
}

use Dompdf\Dompdf;
class PKC extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user'])) {
            redirect('/');
        }
        $this->load->model('packing/packing_model', 'pack');
        $this->upload_path = "//amecnas/FileServer/PP_Dept/WH_sect/Data_wh/Picture/";
        $this->load->library('Amecmail2', 'amecmail2');
    }

    public function index()
    {
        $this->views('packing/index');
    }

    public function index_test()
    {
        $this->views('packing/test_index');
    }

    public function scan_pis()
    {
        $this->views('packing/scan_pis');
    }


    public function manual_order()
    {
        $this->views('packing/manual_order');
    }

    public function reprintPackingOrder()
    {
        $this->views('packing/reprint_other');
    }

    public function scan_idtag()
    {
        $this->views('packing/scan_idtag');
    }

    public function get_detail_issue_batch()
    {
        $issueNos = $this->input->post('issue_nos');
        $data     = array();
        foreach ($issueNos as $iss_no) {
            $a    = $this->pack->get_detail_issue($iss_no);
            $data = array_merge($data, $a);
        }

        echo json_encode(array('data' => $data));
    }

    public function get_order_detail()
    {
        $order   = $this->input->post('order');
        $packing = $this->input->post('packing');

        $orderDetails = $this->pack->get_order_detail($order, $packing);
        foreach ($orderDetails as $detail) {
            $remarkDetails  = $this->pack->get_Q141KP($detail->S11M01, $detail->S11M02, $detail->S11M04);
            $detail->REMARK = !empty($remarkDetails) ? $remarkDetails[0]->Q43K06 : '';
        }

        echo json_encode(['data' => $orderDetails]);
    }

    public function get_order_other()
    {
        $packing = $this->input->post('packing');
        // $packing = '14101';
        $a = $this->pack->get_order_other($packing);
        echo json_encode($a);
        // pre_array($a);
    }

    public function get_order_88_89()
    {
        // $packing = $this->input->post('packing');
        // $packing = '14101';
        $a = $this->pack->get_order_88_89();
        echo json_encode($a);
        // pre_array($a);
    }

    public function insert_order_detail()
    {
        $dw          = $this->input->post('dw');
        $order_no    = $this->input->post('order_no');
        $packing_no  = $this->input->post('packing_no');
        $project     = $this->input->post('project');
        $prod        = $this->input->post('prod');
        $priority    = $this->input->post('priority');
        $part        = $this->input->post('part');
        $part_no     = $this->input->post('part_no');
        $qty         = $this->input->post('qty');
        $con_qty     = $this->input->post('con_qty');
        $remark      = $this->input->post('remark');
        $qty_sticker = $this->input->post('qty_sticker');
        $checkItem   = $this->input->post('checkItem');

        echo "DW: " . pre_array($dw) . "<br>";
        echo "Order No: " . $order_no . "<br>";
        echo "Packing No: " . $packing_no . "<br>";
        echo "Project: " . $project . "<br>";
        echo "Product: " . $prod . "<br>";
        echo "Priority: " . $priority . "<br>";
        echo "Part No: " . pre_array($part_no) . "<br>";
        echo "Quantity: " . pre_array($qty) . "<br>";
        echo "Confirmed Quantity: " . pre_array($con_qty) . "<br>";
        echo "Remark: " . pre_array($remark) . "<br>";
        echo "Quantity Sticker: " . pre_array($qty_sticker) . "<br>";
        echo "Check Item: " . $checkItem . "<br>";
        print_r($qty_sticker);

        $chk_data = $this->pack->chk_data($this->format_order_no($order_no), $this->format_packing($packing_no))->result();
        if (empty($chk_data)) {
            foreach ($dw as $key => $dw_no) {
                $arr = [
                    'ORDER_NO'      => $this->format_order_no($order_no),
                    'PACKING_NO'    => $this->format_packing($packing_no),
                    'DW_NO'         => $dw_no,
                    'QTY'           => $qty[$key],
                    'CON_QTY'       => $con_qty[$key],
                    'EMP_CREATE'    => $_SESSION['user']->SEMPNO,
                    'STATUS'        => $qty[$key] == $con_qty[$key] ? '2' : '1',
                    'AMOUNT_PRINT'  => $qty_sticker,
                    'REMARK'        => $remark[$key],
                    'QUALITY_CHECK' => !empty($checkItem) ? $checkItem : '',
                    'PART_NO'       => $part_no[$key]
                ];


                pre_array($arr);
                // $viewContent = $this->print_vps($packing_no, $project, $order_no, $prod, $part, $priority, $dw_no);
                // echo $viewContent;
                $this->pack->insert_packing_sheet($arr);
            }
        } else {
            foreach ($dw as $key => $dw_no) {
                $arr = array(

                    'CON_QTY'      => $con_qty[$key],
                    'STATUS'       => $qty[$key] == $con_qty[$key] ? '2' : '1',
                    'AMOUNT_PRINT' => $qty_sticker,
                    'REMARK'       => $remark[$key],
                    'PART_NO'      => $part_no[$key]
                );
                pre_array($arr);
                $this->pack->update_packing_sheet($this->format_order_no($order_no), $this->format_packing($packing_no), $arr, $part_no[$key]);
            }
        }
    }

    public function format_order_no($order_no)
    {
        $format1 = str_replace('-', '', $order_no);
        $format2 = str_replace(' ', '', $format1);

        return $format2;
    }

    public function format_packing($packing)
    {
        $format = str_replace('-', '', $packing);
        return $format;
    }

    public function print_vps($item, $project, $order, $prod, $part, $priority, $dw_no)
    {
        $data['item']     = $item;
        $data['project']  = $project;
        $data['order']    = $order;
        $data['prod']     = date("y") . "/" . $prod;
        $data['part']     = $part;
        $data['priority'] = $priority;
        $data['dw']       = $dw_no;
        return $this->views('packing/print', $data, true);
    }

    public function re_print()
    {
        $data['remark']  = $this->pack->get_remark_reprint();
        $data['packing'] = $this->pack->get_packing_sheet();
        return $this->views('packing/print', $data);
    }

    public function get_amount_print()
    {
        $order   = $this->input->post('order');
        $packing = $this->input->post('packing');
        $data    = $this->pack->chk_data($order, $packing)->result_array();
        // print_r($data);
        echo $data[0]['AMOUNT_PRINT'];
    }

    public function get_packing_no()
    {
        $orderNo = $this->input->post('order');
        $packing = $this->pack->get_packing($orderNo);


        echo json_encode(array('data' => $packing));
    }

    public function insert_print_log()
    {
        $order        = $this->format_order_no($this->input->post('order_no'));
        $packing      = $this->format_packing($this->input->post('packing_no'));
        $ip           = $this->input->post('printer');
        $remark       = $this->input->post('remark');
        $ptype        = $this->input->post('ptype');
        $qty          = $this->input->post('qty');
        $qty_item     = $this->input->post('qty_item');
        $other_remark = $this->input->post('other_remark');

        if ($ptype == '8') {
            $remark = $other_remark;
        }



        for ($i = 0; $i < $qty; $i++) {
            $arr = array(
                'PTYPE'      => !empty($ptype) ? $ptype : '0',
                'ORDER_NO'   => $order,
                'PACKING_NO' => $packing,
                'PRINT_QTY'  => $qty,
                'REMARK'     => !empty($remark) ? $remark : '',
                'PRINTER'    => $_SERVER['REMOTE_ADDR'],
                'USERS'      => !empty($_SESSION['user']->SEMPNO) ? $_SESSION['user']->SEMPNO : '',
                'PRINT_SEQ'  => $i + 1,
                'QTY_ITEM'   => $qty_item[$i],
            );

            print_r($arr);
            $this->pack->insert_print_log($arr);
        }
        // print_r($arr);

    }

    public function insert_printlog_other()
    {
        $order         = $this->format_order_no($this->input->post('order_no'));
        $packing       = $this->format_packing($this->input->post('packing_no'));
        $print_qty     = $this->input->post('print_qty');
        $remark        = $this->input->post('remark');
        $reprint_cause = $this->input->post('reprint_cause');
        $printer       = $_SERVER['REMOTE_ADDR'];
        $user          = $_SESSION['user']->SEMPNO;

        $data = [
            'ORDER_NO'      => $order,
            'PACKING_NO'    => $packing,
            'PRINT_QTY'     => $print_qty,
            'REMARK'        => $remark,
            'REPRINT_CAUSE' => $reprint_cause,
            'PRINTER'       => $printer,
            'USERS'         => $user
        ];

        $this->pack->insert_ora('PRINT_LOG_VPS_OTHER', $data);
    }

    public function insert_packingorder()
    {
        // Retrieve input data from POST request
        $order       = $this->input->post('order');
        $packing     = $this->input->post('packing');
        $production  = $this->input->post('production');
        $p           = $this->input->post('p');
        $item        = $this->input->post('item');
        $partname    = $this->input->post('partname');
        $project     = $this->input->post('project');
        $sche        = $this->input->post('sche');
        $piscode     = $this->input->post('piscode');
        $qty_print   = $this->input->post('qty_print');
        $user        = $this->input->post('user') ? $this->input->post('user') : $_SESSION['user']->SEMPNO;
        $sub_packing = substr($packing, 0, 3) . "-" . substr($packing, 3, 5);

        $print_history = [
            'ORDER_NO'   => $order,
            'PACKING_NO' => $packing,
            'QUANTITY'   => $qty_print,
            'USERS'      => $user
        ];

        $this->pack->insert_ora('PRINT_HISTORY', $print_history);

        // Debugging output
        echo "Order: $order\n";
        echo "Packing: $packing\n";
        echo "Production: $production\n";
        echo "P: $p\n";
        echo "Item: $item\n";
        echo "Part Name: $partname\n";
        echo "Project: $project\n";
        echo "Schedule: $sche\n";
        echo "PIS Code: $piscode\n";
        echo "Quantity to Print: $qty_print\n";

        // // Check if required session variable is set
        // if (!isset($_SESSION['user']->SEMPNO)) {
        //     echo "Session user not set.";
        //     return;
        // }

        // // Check database entries
        $chk_order   = $this->pack->chk_packorder($order, $packing);
        $chk_ItemMas = $this->pack->chk_ItemMas($order, $packing);
        $chk_ItemQty = $this->pack->chk_ItemQty($order, $packing);
        $chk_PISinfo = $this->pack->chk_PISinfo($order, $sub_packing);

        // Debugging output for database checks
        echo "chk_order: ";
        print_r($chk_order);
        echo "\nchk_ItemMas: ";
        print_r($chk_ItemMas);
        echo "\nchk_ItemQty: ";
        print_r($chk_ItemQty);
        echo "\nchk_PISinfo: ";
        print_r($chk_PISinfo);
        echo "\n";

        if (empty($chk_order)) {
            $this->pack->InsPackorddtlByManual($order, $packing);
            $chk_order = $this->pack->chk_packorder($order, $packing);
            echo "INSERT ORDER";
        }
        if (!empty($chk_order)) {
            $data  = array(
                'printsta' => '1',
                // 'updatedate' => date("Y-m-d H:i:s")
            );
            $where = array(
                'orderno' => $order,
                'packno'  => $packing,
            );
            // Update the database
            $this->pack->update_db('packorddtl', $data, $where);
            echo 'UPDATE \n';
        }

        if (empty($chk_ItemMas)) {
            $data_Mas = array(
                'production' => $production,
                'p'          => $p,
                'orderno'    => $order,
                'seq'        => '0',
                'item'       => $item,
                'partname'   => $partname,
                'packshop'   => 'PC',
                'projectno'  => $project,
                'schedl'     => $sche,
                'packno'     => $packing,
                'piscode'    => $piscode,
                'updteusr'   => $user,
                'updte'      => date("Y-m-d H:i:s"),
            );
            // echo "chk_ItemMas: ";
            // print_r($data_Mas);
            // echo "\n";
            // Uncomment to insert into the database
            $this->pack->insert_db('ItemMas', $data_Mas);
        }

        if (empty($chk_ItemQty)) {
            $data_QTY = array(
                'ordrno'    => $order,
                'itemno'    => $packing,
                'packshop'  => 'PC',
                'qty'       => $qty_print,
                'ncopy'     => '1',
                'printfg'   => '1',
                'printtype' => '0',
                'autoprint' => '0',
                'upuser'    => $user,
                'updte'     => date("Y-m-d H:i:s"),
            );
            // echo "chk_ItemQty: ";
            // print_r($data_QTY);
            // echo "\n";
            // Uncomment to insert into the database
            $this->pack->insert_db('ItemQty', $data_QTY);
        }

        if (empty($chk_PISinfo)) {
            for ($i = 0; $i < $qty_print; $i++) {
                $row      = str_pad($i + 1, 4, "0", STR_PAD_LEFT);
                $data_pis = array(
                    'production' => $production,
                    'p'          => $p,
                    'orderno'    => $order,
                    'seq'        => '0',
                    'item'       => $sub_packing,
                    'pis'        => $piscode . "-" . $row,
                    'partname'   => $partname,
                    'packshop'   => 'PC',
                    'projectno'  => $project,
                    'schedl'     => $sche,
                    'itemseq'    => $i + 1,
                    'qty'        => $qty_print,
                    'ncopy'      => '1',
                    'printflg'   => '0',
                    'rdel'       => '0',
                    'upduser'    => $user,
                    'upddate'    => date("Y-m-d H:i:s"),
                    'trndata'    => '0',
                    'itemtype'   => '0',
                    'printtype'  => '0'
                );
                // echo "PISInfo entry: ";
                // print_r($data_pis);
                // echo "\n";
                // Uncomment to insert into the database
                $this->pack->insert_db('PISInfo', $data_pis);

                $data_vps = array(
                    'orderno'   => $order,
                    'item'      => $packing,
                    'itemseq'   => $i + 1,
                    'qty'       => $qty_print,
                    'pis'       => $piscode . "-" . $row,
                    'ncopy'     => '1',
                    'rdel'      => '0',
                    'itemtype'  => '0',
                    'printtype' => '0',
                    'printdate' => date("Y-m-d H:i:s")
                );
                // echo "VPSInfo entry: ";
                // print_r($data_vps);
                // echo "\n";
                // Uncomment to insert into the database
                $this->pack->insert_db('VPSInfo', $data_vps);
            }

            echo "PIS\n";
        }

        $data_aa = array(
            // 'hno' => '',
            'pis'    => $piscode,
            'qty'    => $qty_print,
            'ncopy'  => '1',
            'currnt' => '1',
            'upuser' => $user,
            'updte'  => date("Y-m-d H:i:s")
        );

        // echo "ItemQtyHistory entry: ";
        // print_r($data_aa);
        // echo "\n";
        // // Uncomment to insert into the database
        $this->pack->insert_db('ItemQtyHistory', $data_aa);
    }

    public function reprint_packingorder()
    {
        $order       = $this->input->post('order');
        $packing     = $this->input->post('packing');
        $production  = $this->input->post('production');
        $p           = $this->input->post('p');
        $partname    = $this->input->post('partname');
        $project     = $this->input->post('project');
        $sche        = $this->input->post('sche');
        $piscode     = $this->input->post('piscode');
        $qty_print   = $this->input->post('qty_print');
        $sub_packing = substr($packing, 0, 3) . "-" . substr($packing, 3, 5);

        echo "Order: $order\n";
        echo "Packing: $packing\n";
        echo "Production: $production\n";
        echo "P: $p\n";
        echo "Part Name: $partname\n";
        echo "Project: $project\n";
        echo "Schedule: $sche\n";
        echo "PIS Code: $piscode\n";
        echo "Quantity to Print: $qty_print\n";

        $del_pis = array(
            'orderno' => $order,
            'item'    => $sub_packing,
        );
        $this->pack->delete_packing_db('PISInfo', $del_pis);

        $del_vps = array(
            'orderno' => $order,
            'item'    => $packing
        );
        $this->pack->delete_packing_db('VPSInfo', $del_vps);

        for ($i = 0; $i < $qty_print; $i++) {
            $row      = str_pad($i + 1, 4, "0", STR_PAD_LEFT);
            $data_pis = array(
                'production' => $production,
                'p'          => $p,
                'orderno'    => $order,
                'seq'        => '0',
                'item'       => $sub_packing,
                'pis'        => $piscode . "-" . $row,
                'partname'   => $partname,
                'packshop'   => 'PC',
                'projectno'  => $project,
                'schedl'     => $sche,
                'itemseq'    => $i + 1,
                'qty'        => $qty_print,
                'ncopy'      => '1',
                'printflg'   => '0',
                'rdel'       => '0',
                'upduser'    => $_SESSION['user']->SEMPNO,
                'upddate'    => date("Y-m-d H:i:s"),
                'trndata'    => '0',
                'itemtype'   => '0',
                'printtype'  => '0'
            );
            // Uncomment to insert into the database
            $this->pack->insert_db('PISInfo', $data_pis);

            $data_vps = array(
                'orderno'   => $order,
                'item'      => $packing,
                'itemseq'   => $i + 1,
                'qty'       => $qty_print,
                'pis'       => $piscode . "-" . $row,
                'ncopy'     => '1',
                'rdel'      => '0',
                'itemtype'  => '0',
                'printtype' => '0',
                'printdate' => date("Y-m-d H:i:s")
            );
            // Uncomment to insert into the database
            $this->pack->insert_db('VPSInfo', $data_vps);
        }

        $data_ItemQty  = array('qty' => $qty_print);
        $Where_ItemQty = array('ordrno' => $order, 'itemno' => $packing);
        $this->pack->update_db('ItemQty', $data_ItemQty, $Where_ItemQty);

        $data_history  = array('currnt' => '0');
        $Where_history = array('pis' => $piscode);
        $this->pack->update_db('ItemQtyHistory', $data_history, $Where_history);

        $data_history_new = array(
            'pis'    => $piscode,
            'qty'    => $qty_print,
            'currnt' => '1',
            'upuser' => $_SESSION['user']->SEMPNO,
            'updte'  => date('Y-m-d H:i:s'),
        );
        $this->pack->insert_db('ItemQtyHistory', $data_history_new);

        $cpd = $this->pack->chk_packing_detail($order, $packing);
        if (!empty($cpd)) {
            $data = [
                'orderno' => $order,
                'item'    => $packing
            ];
            $this->pack->delete_packing_db('PackingDetail', $data);

            for ($i = 0; $i < $qty_print; $i++) {
                $data = [
                    'orderno'    => $cpd[0]->orderno,
                    'ordernoref' => $cpd[0]->ordernoref,
                    'block'      => $cpd[0]->block,
                    'item'       => $cpd[0]->item,
                    'qty'        => $qty_print,
                    'itemseq'    => $i + 1,
                    'itemtype'   => $cpd[0]->itemtype,
                    'shortitem'  => $cpd[0]->shortitem,
                    'rejectId'   => $cpd[0]->rejectId,
                    'inpttype'   => $cpd[0]->inpttype,
                    'inptby'     => $_SESSION['user']->SEMPNO,
                    'inptdate'   => date("Y-m-d H:i:s"),
                    'inptdesc'   => $cpd[0]->inptdesc,
                    'delflag'    => $cpd[0]->delflag,
                    'completed'  => $cpd[0]->completed,
                ];
                $this->pack->insert_db('PackingDetail', $data);
            }
        }

    }


    public function chk_print()
    {
        $order   = $this->input->post('order');
        $packing = $this->input->post('packing');
        $query   = $this->pack->chk_print($order, $packing);
        if (!empty($query)) {
            echo "1";
        } else {
            echo "2";
        }

        // echo "2";
    }

    public function get_pis()
    {
        $order   = $this->input->post('order');
        $packing = $this->input->post('packing');
        // $order = 'OBU8902';
        // $packing = '29502';
        $data = $this->pack->chk_pis($order, $packing);
        echo json_encode($data);
    }

    public function test_print()
    {
        $data['remark']  = $this->pack->get_remark_reprint();
        $data['packing'] = $this->pack->get_packing_sheet();
        return $this->views('packing/testprint', $data);
    }

    public function print_manual()
    {
        $data['problem'] = $this->pack->getProblemPacking();
        $this->views('packing/print_manual', $data);
    }

    public function insert_ng()
    {
        $orderNo    = $_POST['order_no'];
        $packingNo  = $_POST['packing_no'];
        $checkValue = $_POST['check_value'];

        $data = [
            'ORDER_NO'      => $orderNo,
            'PACKING_NO'    => $packingNo,
            'QUALITY_CHECK' => $checkValue,
            'EMP_CREATE'    => $_SESSION['user']->SEMPNO
        ];

        $this->pack->insert_ora('NG_LOG_VPS', $data);
    }

    public function search_order()
    {
        // print_r($_SESSION);
        $sect           = explode(" ", $_SESSION['user']->SSEC)[0];
        $data['packNo'] = $pack = $this->pack->get_packingNo($sect);
        $this->views('packing/search_order', $data);
    }

    public function list_order()
    {
        $this->views('packing/list_order');
    }

    // public function get_order_other()
    // {
    //     $date_jun = $this->input->post('date_jun');
    //     $packing  = $this->input->post('packing_no');
    //     $priority = $this->input->post('schedule');
    //     $year     = substr($date_jun, 0, 4);
    //     $schedule = substr($date_jun, 4, 6);



    //     $orderr = $this->pack->get_order_other($priority, $year, $packing, $schedule);

    // }

    public function get_calendar()
    {
        $q   = array(
            'WORKID >='           => date('Ymd', strtotime('-5 month')),
            'WORKID <='           => date('Ymd'),
            'SCHDMFG IS NOT NULL' => null
        );
        $res = $this->pack->getCalendar($q);
        echo json_encode($res);
    }

    public function inseret_manual()
    {
        $this->views('packing/insert_manual');
    }

    public function test1()
    {
        $this->views('packing/test1');
    }

    public function set_permission()
    {
        $this->views('packing/set_permission');
    }

    public function get_user()
    {
        $seccode = $_SESSION['user']->SSECCODE;
        $data    = $this->pack->get_amecuserall($seccode);
        echo json_encode($data);
    }

    public function get_pckodr()
    {
        $sec  = explode(" ", $_SESSION['user']->SSEC)[0];
        $data = $this->pack->get_packingNo($sec);
        echo json_encode($data);
    }

    public function get_pckodr88_89()
    {
        $data = $this->pack->get_packingNo88_89();
        echo json_encode($data);
    }

    public function get_report_cause()
    {
        $data = $this->pack->get_report_cause();
        echo json_encode($data);
    }

    public function fetchDistinctOrderNo()
    {
        $arr_emp_all = ['93041', '06127', '09039', '06124'];
        if (in_array($_SESSION['user']->SEMPNO, $arr_emp_all)) {
            $sect = 'WSD';
        } else {
            $sect = explode(" ", $_SESSION['user']->SSEC)[0];
        }

        $search = strtoupper($this->input->post('search')); // คำค้นหา
        $page   = (int) $this->input->post('page'); // หน้าปัจจุบัน
        $limit  = 20; // จำนวนที่โหลดต่อหน้า
        $data   = $this->pack->get_distinct_order_no($sect, $search, $page, $limit);
        echo json_encode($data);
    }

    public function fetchDistinctPackingNo()
    {
        // $sect     = explode(" ", $_SESSION['user']->SSEC)[0];
        $order_no = $this->input->post('order_no');

        // $order_no = 'SX0784E11';

        $arr_emp_all = ['93041', '06127', '09039', '06124'];
        if (in_array($_SESSION['user']->SEMPNO, $arr_emp_all)) {
            $sect = 'WSD';
        } else {
            $sect = explode(" ", $_SESSION['user']->SSEC)[0];
        }
        $data = $this->pack->get_distinct_packing_no($order_no, $sect);
        echo json_encode($data);
    }

    public function search_idtag()
    {
        $idtag = trim($this->input->post('id_tag'));
        $data  = $this->pack->search_idtag($idtag);
        $order = $this->pack->get_order_idtag($idtag);

        $arr = [];
        foreach ($order as $value) {
            $chk_print = $this->pack->chk_print($value->F03R02, $data[0]->Q16027);
            if (empty($chk_print)) {
                $arr[] = [
                    'order'   => $value->F03R02,
                    'packing' => $data[0]->Q16027
                ];
            }
        }

        echo json_encode($arr);
    }

    public function format_order($input)
    {
        // แบ่ง input เป็นตัวอักษรและตัวเลข
        $first  = substr($input, 0, 1); // ตัวแรก
        $second = substr($input, 1, 2); // ตัวถัดมา 2 ตัว
        $third  = substr($input, 3, 5); // ตัวเลข 5 หลัก
        $last   = substr($input, 8, 1); // ตัวเลข 1 หลักสุดท้าย

        // จัดรูปแบบใหม่
        return $first . '-' . $second . ' ' . $third . '-' . $last;
    }

    public function format_packingNo($packing)
    {
        $first  = substr($packing, 0, 3);
        $second = substr($packing, 3, 5);

        return $first . '-' . $second;
    }

    public function format_qrcode($order, $item)
    {
        return substr($order, 1, 7) . substr($item, 0, 5);
    }

    public function check_status_open()
    {
        $order   = $this->input->post('order');
        $packing = $this->input->post('packing');
        $where   = [
            'ORDER'   => $order,
            'PACKING' => $packing,
        ];
        $data    = $this->pack->get_temp_status($where);
        echo json_encode($data);
    }

    public function insert_status_open()
    {
        $order   = $this->input->post('order');
        $packing = $this->input->post('packing');
        $status  = '1';
        $data    = [
            'ORDER'    => $order,
            'PACKING'  => $packing,
            'STATUS'   => $status,
            'EMP_OPEN' => $_SESSION['user']->SEMPNO
        ];
        $this->pack->insert_ora('TEMP_STATUS_VPS', $data);
    }

    public function update_status_open()
    {
        $order   = $this->input->post('order');
        $packing = $this->input->post('packing');
        $status  = '2';
        $data    = [
            'STATUS' => $status,
        ];
        $where   = [
            'ORDER'   => $order,
            'PACKING' => $packing,
        ];

        $this->pack->delete_ora('TEMP_STATUS_VPS', $where);
    }

    public function get_agent()
    {
        $data = $this->pack->get_agent();
        echo json_encode($data);
    }

    public function getPURcode()
    {
        $order   = $this->input->post('order');
        $packing = $this->input->post('packing');
        $dwg     = $this->input->post('dwg');  // ['A','B','C',…]

        $dataOrder = $this->pack->getDataByOrder($order, $packing);
        $data      = [];
        foreach ($dwg as $value) {
            list($main, $cond) = $this->checkCondition($value);
            $rows              = $this->pack->getPURcode($main, $cond);
            $master_packing    = $this->pack->getMasterPacking(str_replace(' ', '', $value));
            if (is_array($rows)) {
                foreach ($rows as &$row) {
                    $row->dataOrder     = $dataOrder;
                    $row->masterPacking = $master_packing;
                }
                $data = array_merge($data, $rows);
            } else {
                $rows->dataOrder    = $dataOrder;
                $row->masterPacking = $master_packing;
                $data[]             = $rows;
            }
        }

        echo json_encode($data);
    }

    public function chk_print_vpis()
    {
        $order   = $this->input->post('order');
        $packing = $this->input->post('packing');
        $drawing = $this->input->post('drawing');
        $data    = $this->pack->chk_print_vpis($order, $packing, $drawing);
        echo json_encode($data);
    }

    public function runfor128()
    {

        $packing      = '12802';
        $filteredData = $this->pack->get_order_other($packing, date("Ymd"));


    }

    public function GetVpcOrder()
    {
        $order = str_replace(['-', ' '], '', $this->input->post('order'));
        // echo $order;     
        // $order = 'ET901U630' ;
        $data = $this->pack->getvpcorder($order);
        echo json_encode($data);
    }

    public function preview($filename)
    {
        $filepath = $this->upload_path . $filename;
        $nopic    = FCPATH . 'assets\images\no-image.png';
        $dwgpic   = FCPATH . "uploads/qc/" . $filename;
        $isPur    = $this->input->get('is_pur');

        if (file_exists($filepath)) {
            $mime = mime_content_type($filepath);
            header("Content-Type: $mime");
            readfile($filepath);
        } else if (file_exists($dwgpic)) {
            $mime = mime_content_type($dwgpic);
            header("Content-Type: $mime");
            readfile($dwgpic);
        } else {
            $mime = mime_content_type($nopic);
            header("Content-Type: $mime");
            readfile($nopic);
        }

        if ($isPur && !file_exists($filepath)) {
            $data['from']    = ['name' => "MFG Monitor", 'mail' => "mfgmonitor@MitsubishiElevatorAsia.co.th"];
            $data['to']      = ['perapatr@MitsubishiElevatorAsia.co.th'];
            $data['subject'] = "Missing Image Detected for PUR Code " . $filename;
            $data['view']    = "mail/email";
            $data['message'] = [
                'SUBJECT' => "Missing Image Detected for PUR Code " . $filename,
                'BODY'    => '
                 <div style="font-family: Arial, sans-serif; font-size: 14px; color: #333;">
                    <p>Dear all,</p>

                     <p>
                         There is <strong>no image found</strong> for <strong>PUR Code: ' . $filename . '</strong>.<br>
                         Please upload the image as soon as possible.
                     </p>

                     <p>Thank you,</p>
                 </div>'

            ];
            $this->amecmail2->sendMail($data);
        }
    }

    public function test_sendmail()
    {

        $filepath = "";

        // $body    = '
        //         <div style="font-family: Arial, sans-serif; font-size: 14px; color: #333;">
        //             <p>Dear all,</p>

        //             <p>
        //                 There is <strong>no image found</strong> for <strong>PUR Code: ' . $filepath . '</strong>.<br>
        //                 Please upload the image as soon as possible.
        //             </p>

        //             <p>Thank you,</p>
        //         </div>';

    }

    public function checkCondition($dwg)
    {
        $parts     = preg_split('/\s+/', trim($dwg), 3); // แยกไม่เกิน 2 ส่วนหลัก ๆ
        $main      = $parts[0];
        $condition = isset($parts[1]) ? $parts[1] : ' ';
        return [$main, $condition];
    }

    public function test_socket()
    {
        $this->views('packing.test_socket');
    }

    public function uploadQcPics()
    {
        // รับข้อมูลที่ส่งมาจาก ajax
        $pics = $this->input->post('pics');
        if (!$pics) {
            echo json_encode(['status' => 'fail', 'msg' => 'no pics']);
            return;
        }

        $picsArr = json_decode($pics, true);
        $results = [];

        foreach ($picsArr as $idx => $item) {
            if (!isset($item['img']) || !isset($item['dwg']))
                continue;

            $imgData = $item['img'];
            // ทำชื่อไฟล์ให้ปลอดภัย (A-Z, a-z, 0-9, -, _)
            $dwg      = str_replace(' ', '', $item['dwg']);
            $dwg      = preg_replace('/[^A-Za-z0-9_\-]/', '', $item['dwg']);
            $filename = $dwg . '.jpg';

            // เอา prefix base64 ออก
            $imgData = preg_replace('/^data:image\/\w+;base64,/', '', $imgData);
            $imgData = str_replace(' ', '+', $imgData);

            $data = base64_decode($imgData);
            if ($data !== false) {
                file_put_contents(FCPATH . 'uploads/qc/' . $filename, $data);
                $results[] = [
                    'file' => $filename,
                    'dwg'  => $item['dwg']
                ];
            }
        }

        echo json_encode([
            'status' => 'ok',
            'files'  => $results
        ]);
    }
}