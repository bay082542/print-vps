@extends('layout/template2')

@section('content')
    <div id="loading_waiting" class="d-none" data-aos="zoom-out"><img src="{{base_url('assets\images\hourglass.GIF')}}" alt="" class="gif_load" style=""></div>
    <div id="loading-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.7); z-index:9999;">
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%);">
            <img src="{{base_url()}}assets/images/loading_gif.gif" alt="Loading..." width="150">
        </div>
    </div>
    <!-- <div id="loader" class=""></div> -->
    <div class="d-flex justify-content-center d-none" id="loader">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    <!-- <button type="button" class="btn btn-primary" id="openModalButton">เปิดฟอร์มกรอกข้อมูล</button> -->

    <div class="card">
        <div class="containerrr">
            <div class="card-body form-container ">
                <img class="mb-3" src="{{base_url('assets\images\qr-code.PNG')}}" alt="" style="width:300px; height:300px;">
                <textarea type="text" class="form-control input_qr" rows="5" style="width:70%;" placeholder="scan barcode at here.."></textarea>
                <!-- <textarea name="" id="" cols="30" rows="10"></textarea> -->
                <div class="d-inline-block">
                    <button class="btn_submit btn btn-success mt-2">submit</button>
                    <a href="<?php echo site_url('packing/pkc/re_print');?>" class="btn btn-warning mt-2" style="margin-left:1rem; color:black !important;">Re-Print</a>
                    <a href="{{site_url('packing/pkc/print_manual')}}" class="btn_submit btn btn-info mt-2" style="margin-left:1rem;">Print Manual (orther 88 & 89)</a>
                    <div id="show_data" style="width:100% ; min-height:0px;color:white;"></div>
                </div>
                <!-- <button id="btnprint" onclick="sendData();">print</button> -->


            </div>
        </div>
        <div class="card-body d-none div_table">

            <a href="" class="btn btn-primary mb-2">กลับไปหน้าแรก</a>
            <table id="orderTable" class="table table-hover" border="1">
                <thead>
                    <tr>
                        <th>ISSUE No.</th>
                        <th>Order No.</th>
                        <th>PACKING No.</th>
                        <th>PROJECT NAME</th>
                        <!-- <th>Quantity</th> -->
                        <!-- Add more headers as needed -->
                    </tr>
                </thead>
                <tbody>
                    <!-- Table body rows will be dynamically populated -->
                </tbody>
            </table>
        </div>
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">PIS</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table-header" style="">
                            <tr>
                                <th colspan="7">PACKING INSTRUCTION SHEET (FOR PARTS)</th>
                            </tr>
                            <tr>
                                <th style="width:40%;">ORDER NAME</th>
                                <th colspan="2">MANUFACTURE ORDER NO.</th>
                                <th colspan="2">PACKING NO.</th>
                                <th>ITEM</th>
                                <th>SCHEDULE</th>
                            </tr>
                            <tr>
                                <td class="order-name"></td>
                                <td class="order-no" rowspan="2" colspan="2"></td>
                                <td class="packing-no" rowspan="2" colspan="2"></td>
                                <td class="item"></td>
                                <td class="schedule"></td>
                            </tr>
                            <tr>
                                <th>MAIN PARTS NAME</th>
                                <th></th>
                                <th>MODEL</th>
                            </tr>
                            <tr>
                                <td class="main-part-name"></td>
                                <td class="part-name" colspan="5" style="text-align:center;"></td>
                                <td class="model"></td>
                            </tr>
                            <tr>
                                <th>ASSEMBLE</th>
                                <th>FINISH DATE</th>
                                <th>PACKING DATE</th>
                                <th colspan="3"></th>
                                <th rowspan="2" style="font-size:2.5rem;"><label class="priority"></label>
                                    <p class="date-p d-none"></p>
                                </th>
                            </tr>
                            <tr>
                                <td class="assemble">PKC</td>
                                <td class="f-date"></td>
                                <td class="p-date"></td>
                                <td colspan="3" style="text-align:right;">PRIORITY</td>
                            </tr>
                        </table>
                        <table style="line-height:2mm; margin-top:2px;" class="detail-table">
                            <tr>
                                <th style="width:5%;">No.</th>
                                <th>PART NAMES</th>
                                <th>DRAWING NO.</th>
                                <th>QTY</th>
                                <th>Con. QTY</th>
                                <th>REMARK</th>
                            </tr>

                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        <button type="button" class="btn btn-primary save-btn">บันทึก</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="modal" tabindex="-1" id="Qc_Check">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">ตรวจสอบความผิดปกติของชิ้นงาน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1" value="1">
                            <label class="form-check-label" for="flexRadioDefault1">
                                พบความผิดปกติของชิ้นงาน (พบสนิม,รอยแตกหักเสียหาย,บิดงอ)
                            </label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2" value="2">
                            <label class="form-check-label" for="flexRadioDefault2">
                                <b style="color:red;">ไม่</b>พบความผิดปกติของชิ้นงาน (<b style="color:red;">ไม่</b>พบสนิม,รอยแตกหักเสียหาย,บิดงอ)
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="check-item"></div>
                        <button type="button" class="btn btn-primary" id="checkButton">ตกลง</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="modal" tabindex="-1" id="printModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">กรอกจำนวน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="containerQuantity">กรอกจำนวนลังที่ต้องปริ้นสติกเกอร์</label>
                            <input type="number" class="form-control" id="containerQuantity" placeholder="Enter the quantity of containers">
                        </div>
                        <div id="itemsQuantityContainer" class="mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="check-item"></div>
                        <button type="button" class="btn btn-primary" id="submitButton">ตกลง</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="{{$GLOBALS['cdn']}}sweetalert2/js/sweetalert2@11.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/dist/print/BrowserPrint-2.0.0.75.min.js"></script>
    <Script src="<?php echo base_url();?>assets/dist/js/print_label.min.js?ver={{date('YmdHis')}}"></Script>
    <Script src="<?php echo base_url();?>assets/dist/js/print_vps.min.js?ver={{date('YmdHis')}}"></Script>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{$GLOBALS['cdn']}}sweetalert2/css/sweetalert2@11.min.css">
    <style>
        .containerrr {
            /* display: flex; */
            padding-top: 70px;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #loading_waiting {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            /* Semi-transparent black overlay */
            z-index: 3000;
            /* Ensure it's above other content */
            text-align: center;
            color: #fff;
            font-size: 24px;
            padding-top: 20%;
        }

        .gif_load {
            border-radius: 40px;
            width: 100px;
            height: 100px;
        }

        .btn_modal {
            cursor: pointer;
        }

        .modal-body {
            table {
                width: 100%;
                border-collapse: collapse;
                line-height: 1mm;
                background-color: #FFF;
                color: black;
            }

            th,
            td {
                border: 1px solid black;
                padding: 8px;
                text-align: left;
            }
        }

        #loader {
            position: absolute;
            left: 50%;
            top: 50%;
            z-index: 1;
            width: 120px;
            height: 120px;
            margin: -76px 0 0 -76px;

        }

        .static {
            position: absolute;
            background: white;
        }

        .static:hover {
            opacity: 0;
        }
    </style>
@endsection