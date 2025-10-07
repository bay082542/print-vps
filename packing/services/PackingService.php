<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PackingService
{
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('packing/packing_model', 'pack');
        $this->CI->load->helper('packing/packing');
    }

    public function handle_order_detail($input)
    {
        $order_no = $input['order_no'];
        $packing_no = $input['packing_no'];
        $dw = $input['dw'];
        $qty = $input['qty'];
        $con_qty = $input['con_qty'];
        $qty_sticker = $input['qty_sticker'];
        $remark = $input['remark'];
        $checkItem = $input['checkItem'];
        $part_no = $input['part_no'];

        $formatted_order_no = format_order_no($order_no);
        $formatted_packing_no = format_packing($packing_no);

        $existing_data = $this->CI->pack->getPackingSheetByOrderAndPacking($formatted_order_no, $formatted_packing_no)->result();

        if (empty($existing_data)) {
            foreach ($dw as $key => $dw_no) {
                $data = [
                    'ORDER_NO'      => $formatted_order_no,
                    'PACKING_NO'    => $formatted_packing_no,
                    'DW_NO'         => $dw_no,
                    'QTY'           => $qty[$key],
                    'CON_QTY'       => $con_qty[$key],
                    'EMP_CREATE'    => $_SESSION['user']->SEMPNO,
                    'STATUS'        => ($qty[$key] == $con_qty[$key]) ? '2' : '1',
                    'AMOUNT_PRINT'  => $qty_sticker,
                    'REMARK'        => $remark[$key],
                    'QUALITY_CHECK' => !empty($checkItem) ? $checkItem : '',
                    'PART_NO'       => $part_no[$key]
                ];
                $this->CI->pack->insert_packing_sheet($data);
            }
        } else {
            foreach ($dw as $key => $dw_no) {
                $data = [
                    'CON_QTY'      => $con_qty[$key],
                    'STATUS'       => ($qty[$key] == $con_qty[$key]) ? '2' : '1',
                    'AMOUNT_PRINT' => $qty_sticker,
                    'REMARK'       => $remark[$key],
                    'PART_NO'      => $part_no[$key]
                ];
                $this->CI->pack->update_packing_sheet($formatted_order_no, $formatted_packing_no, $data, $part_no[$key]);
            }
        }
    }

    public function create_packing_order($input)
    {
        $order = $input['order'];
        $packing = $input['packing'];
        $production = $input['production'];
        $p = $input['p'];
        $item = $input['item'];
        $partname = $input['partname'];
        $project = $input['project'];
        $sche = $input['sche'];
        $piscode = $input['piscode'];
        $qty_print = $input['qty_print'];
        $user = $input['user'] ? $input['user'] : $_SESSION['user']->SEMPNO;
        $sub_packing = substr($packing, 0, 3) . "-" . substr($packing, 3, 5);

        $this->CI->pack->insert_ora('PRINT_HISTORY', [
            'ORDER_NO'   => $order,
            'PACKING_NO' => $packing,
            'QUANTITY'   => $qty_print,
            'USERS'      => $user
        ]);

        $chk_order = $this->CI->pack->chk_packorder($order, $packing);
        if (empty($chk_order)) {
            $this->CI->pack->InsPackorddtlByManual($order, $packing);
        }

        $this->CI->pack->update_db('packorddtl', ['printsta' => '1'], ['orderno' => $order, 'packno' => $packing]);

        if (empty($this->CI->pack->chk_ItemMas($order, $packing))) {
            $this->CI->pack->insert_db('ItemMas', [
                'production' => $production, 'p' => $p, 'orderno' => $order, 'seq' => '0', 'item' => $item,
                'partname' => $partname, 'packshop' => 'PC', 'projectno' => $project, 'schedl' => $sche,
                'packno' => $packing, 'piscode' => $piscode, 'updteusr' => $user, 'updte' => date("Y-m-d H:i:s"),
            ]);
        }

        if (empty($this->CI->pack->chk_ItemQty($order, $packing))) {
            $this->CI->pack->insert_db('ItemQty', [
                'ordrno' => $order, 'itemno' => $packing, 'packshop' => 'PC', 'qty' => $qty_print, 'ncopy' => '1',
                'printfg' => '1', 'printtype' => '0', 'autoprint' => '0', 'upuser' => $user, 'updte' => date("Y-m-d H:i:s"),
            ]);
        }

        if (empty($this->CI->pack->chk_PISinfo($order, $sub_packing))) {
            for ($i = 0; $i < $qty_print; $i++) {
                $row = str_pad($i + 1, 4, "0", STR_PAD_LEFT);
                $this->CI->pack->insert_db('PISInfo', [
                    'production' => $production, 'p' => $p, 'orderno' => $order, 'seq' => '0', 'item' => $sub_packing,
                    'pis' => $piscode . "-" . $row, 'partname' => $partname, 'packshop' => 'PC', 'projectno' => $project,
                    'schedl' => $sche, 'itemseq' => $i + 1, 'qty' => $qty_print, 'ncopy' => '1', 'printflg' => '0',
                    'rdel' => '0', 'upduser' => $user, 'upddate' => date("Y-m-d H:i:s"), 'trndata' => '0',
                    'itemtype' => '0', 'printtype' => '0'
                ]);
                $this->CI->pack->insert_db('VPSInfo', [
                    'orderno' => $order, 'item' => $packing, 'itemseq' => $i + 1, 'qty' => $qty_print,
                    'pis' => $piscode . "-" . $row, 'ncopy' => '1', 'rdel' => '0', 'itemtype' => '0',
                    'printtype' => '0', 'printdate' => date("Y-m-d H:i:s")
                ]);
            }
        }

        $this->CI->pack->insert_db('ItemQtyHistory', [
            'pis' => $piscode, 'qty' => $qty_print, 'ncopy' => '1', 'currnt' => '1',
            'upuser' => $user, 'updte' => date("Y-m-d H:i:s")
        ]);
    }

    public function reprint_packing_order($input)
    {
        $order = $input['order'];
        $packing = $input['packing'];
        $production = $input['production'];
        $p = $input['p'];
        $partname = $input['partname'];
        $project = $input['project'];
        $sche = $input['sche'];
        $piscode = $input['piscode'];
        $qty_print = $input['qty_print'];
        $sub_packing = substr($packing, 0, 3) . "-" . substr($packing, 3, 5);

        $this->CI->pack->delete_packing_db('PISInfo', ['orderno' => $order, 'item' => $sub_packing]);
        $this->CI->pack->delete_packing_db('VPSInfo', ['orderno' => $order, 'item' => $packing]);

        for ($i = 0; $i < $qty_print; $i++) {
            $row = str_pad($i + 1, 4, "0", STR_PAD_LEFT);
            $this->CI->pack->insert_db('PISInfo', [
                'production' => $production, 'p' => $p, 'orderno' => $order, 'seq' => '0', 'item' => $sub_packing,
                'pis' => $piscode . "-" . $row, 'partname' => $partname, 'packshop' => 'PC', 'projectno' => $project,
                'schedl' => $sche, 'itemseq' => $i + 1, 'qty' => $qty_print, 'ncopy' => '1', 'printflg' => '0',
                'rdel' => '0', 'upduser' => $_SESSION['user']->SEMPNO, 'upddate' => date("Y-m-d H:i:s"),
                'trndata' => '0', 'itemtype' => '0', 'printtype' => '0'
            ]);
            $this->CI->pack->insert_db('VPSInfo', [
                'orderno' => $order, 'item' => $packing, 'itemseq' => $i + 1, 'qty' => $qty_print,
                'pis' => $piscode . "-" . $row, 'ncopy' => '1', 'rdel' => '0', 'itemtype' => '0',
                'printtype' => '0', 'printdate' => date("Y-m-d H:i:s")
            ]);
        }

        $this->CI->pack->update_db('ItemQty', ['qty' => $qty_print], ['ordrno' => $order, 'itemno' => $packing]);
        $this->CI->pack->update_db('ItemQtyHistory', ['currnt' => '0'], ['pis' => $piscode]);
        $this->CI->pack->insert_db('ItemQtyHistory', [
            'pis' => $piscode, 'qty' => $qty_print, 'currnt' => '1',
            'upuser' => $_SESSION['user']->SEMPNO, 'updte' => date('Y-m-d H:i:s'),
        ]);

        $cpd = $this->CI->pack->chk_packing_detail($order, $packing);
        if (!empty($cpd)) {
            $this->CI->pack->delete_packing_db('PackingDetail', ['orderno' => $order, 'item' => $packing]);
            for ($i = 0; $i < $qty_print; $i++) {
                $this->CI->pack->insert_db('PackingDetail', [
                    'orderno' => $cpd[0]->orderno, 'ordernoref' => $cpd[0]->ordernoref, 'block' => $cpd[0]->block,
                    'item' => $cpd[0]->item, 'qty' => $qty_print, 'itemseq' => $i + 1, 'itemtype' => $cpd[0]->itemtype,
                    'shortitem' => $cpd[0]->shortitem, 'rejectId' => $cpd[0]->rejectId, 'inpttype' => $cpd[0]->inpttype,
                    'inptby' => $_SESSION['user']->SEMPNO, 'inptdate' => date("Y-m-d H:i:s"),
                    'inptdesc' => $cpd[0]->inptdesc, 'delflag' => $cpd[0]->delflag, 'completed' => $cpd[0]->completed,
                ]);
            }
        }
    }

    public function log_print($input)
    {
        $order = format_order_no($input['order_no']);
        $packing = format_packing($input['packing_no']);
        $ptype = $input['ptype'];
        $remark = ($ptype == '8') ? $input['other_remark'] : $input['remark'];
        $qty = $input['qty'];
        $qty_item = $input['qty_item'];

        for ($i = 0; $i < $qty; $i++) {
            $this->CI->pack->insert_print_log([
                'PTYPE' => !empty($ptype) ? $ptype : '0',
                'ORDER_NO' => $order,
                'PACKING_NO' => $packing,
                'PRINT_QTY' => $qty,
                'REMARK' => !empty($remark) ? $remark : '',
                'PRINTER' => $_SERVER['REMOTE_ADDR'],
                'USERS' => !empty($_SESSION['user']->SEMPNO) ? $_SESSION['user']->SEMPNO : '',
                'PRINT_SEQ' => $i + 1,
                'QTY_ITEM' => $qty_item[$i],
            ]);
        }
    }

    public function log_print_other($input)
    {
        $order = format_order_no($input['order_no']);
        $packing = format_packing($input['packing_no']);

        $data = [
            'ORDER_NO' => $order,
            'PACKING_NO' => $packing,
            'PRINT_QTY' => $input['print_qty'],
            'REMARK' => $input['remark'],
            'REPRINT_CAUSE' => $input['reprint_cause'],
            'PRINTER' => $_SERVER['REMOTE_ADDR'],
            'USERS' => $_SESSION['user']->SEMPNO
        ];

        $this->CI->pack->insert_ora('PRINT_LOG_VPS_OTHER', $data);
    }

    public function get_order_details($order, $packing)
    {
        $orderDetails = $this->CI->pack->getOrderDetails($order, $packing);
        foreach ($orderDetails as $detail) {
            $remarkDetails = $this->CI->pack->get_Q141KP($detail->S11M01, $detail->S11M02, $detail->S11M04);
            $detail->REMARK = !empty($remarkDetails) ? $remarkDetails[0]->Q43K06 : '';
        }
        return $orderDetails;
    }

    public function get_detail_issue_batch($issue_nos)
    {
        $data = [];
        foreach ($issue_nos as $iss_no) {
            $a = $this->CI->pack->get_detail_issue($iss_no);
            $data = array_merge($data, $a);
        }
        return $data;
    }

    public function get_order_other($packing)
    {
        return $this->CI->pack->get_order_other($packing);
    }

    public function get_order_88_89()
    {
        return $this->CI->pack->get_order_88_89();
    }

    public function is_printed($order, $packing)
    {
        $query = $this->CI->pack->chk_print($order, $packing);
        return !empty($query);
    }

    public function get_pis($order, $packing)
    {
        return $this->CI->pack->chk_pis($order, $packing);
    }

    public function get_amount_print($order, $packing)
    {
        $data = $this->CI->pack->getPackingSheetByOrderAndPacking($order, $packing)->result_array();
        return $data[0]['AMOUNT_PRINT'] ?? null;
    }

    public function get_packing_no($orderNo)
    {
        return $this->CI->pack->get_packing($orderNo);
    }
}