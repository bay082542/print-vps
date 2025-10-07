<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Dompdf\Dompdf;

class PKC extends MY_Controller
{
    private $packing_service;

    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['user'])) {
            redirect('/');
        }
        $this->load->model('packing/packing_model', 'pack');
        require_once 'packing/services/PackingService.php';
        $this->packing_service = new PackingService();
        $this->load->helper('packing/packing');
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
        $data = $this->packing_service->get_detail_issue_batch($issueNos);
        echo json_encode(array('data' => $data));
    }

    public function get_order_detail()
    {
        $order   = $this->input->post('order');
        $packing = $this->input->post('packing');
        $orderDetails = $this->packing_service->get_order_details($order, $packing);
        echo json_encode(['data' => $orderDetails]);
    }

    public function get_order_other()
    {
        $packing = $this->input->post('packing');
        $data = $this->packing_service->get_order_other($packing);
        echo json_encode($data);
    }

    public function get_order_88_89()
    {
        $data = $this->packing_service->get_order_88_89();
        echo json_encode($data);
    }

    public function insert_order_detail()
    {
        $this->packing_service->handle_order_detail($this->input->post(NULL, TRUE));
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
        $amount = $this->packing_service->get_amount_print($order, $packing);
        echo $amount;
    }

    public function get_packing_no()
    {
        $orderNo = $this->input->post('order');
        $packing = $this->packing_service->get_packing_no($orderNo);
        echo json_encode(array('data' => $packing));
    }

    public function insert_print_log()
    {
        $this->packing_service->log_print($this->input->post(NULL, TRUE));
    }

    public function insert_printlog_other()
    {
        $this->packing_service->log_print_other($this->input->post(NULL, TRUE));
    }

    public function insert_packingorder()
    {
        $this->packing_service->create_packing_order($this->input->post(NULL, TRUE));
    }

    public function reprint_packingorder()
    {
        $this->packing_service->reprint_packing_order($this->input->post(NULL, TRUE));
    }


    public function chk_print()
    {
        $order   = $this->input->post('order');
        $packing = $this->input->post('packing');
        $is_printed = $this->packing_service->is_printed($order, $packing);
        echo $is_printed ? "1" : "2";
    }

    public function get_pis()
    {
        $order   = $this->input->post('order');
        $packing = $this->input->post('packing');
        $data = $this->packing_service->get_pis($order, $packing);
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
        $sect           = explode(" ", $_SESSION['user']->SSEC)[0];
        $data['packNo'] = $this->pack->get_packingNo($sect);
        $this->views('packing/search_order', $data);
    }

    public function list_order()
    {
        $this->views('packing/list_order');
    }

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

        $search = strtoupper($this->input->post('search'));
        $page   = (int) $this->input->post('page');
        $limit  = 20;
        $data   = $this->pack->get_distinct_order_no($sect, $search, $page, $limit);
        echo json_encode($data);
    }

    public function fetchDistinctPackingNo()
    {
        $order_no = $this->input->post('order_no');

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
        $dwg     = $this->input->post('dwg');

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
    }

    public function checkCondition($dwg)
    {
        $parts     = preg_split('/\s+/', trim($dwg), 3);
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
            $dwg      = str_replace(' ', '', $item['dwg']);
            $dwg      = preg_replace('/[^A-Za-z0-9_\-]/', '', $item['dwg']);
            $filename = $dwg . '.jpg';

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