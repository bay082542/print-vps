@extends('layout/template2')
@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/icheck-bootstrap/3.0.1/icheck-bootstrap.min.css">

    <style>
        #exampleModal .modal-body {
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


        #orderTable {
            width: 100%;
            border-collapse: collapse;
        }

        #orderTable th,
        #orderTable td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        #orderTable th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #f2f2f2;
            color: black;
        }

        #orderTable tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        #orderTable tr:hover {
            background-color: #ddd;
        }

        #orderTable th.sorting,
        #orderTable th.sorting_asc,
        #orderTable th.sorting_desc {
            cursor: pointer;
        }

        .dataTables_wrapper .dataTables_paginate {
            float: right;
            text-align: right;
            margin-top: 10px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            display: inline-block;
            padding: 6px 12px;
            margin-left: 2px;
            margin-right: 2px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            color: #333;
            background-color: #fff;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: #f2f2f2;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #007bff !important;
            color: #fff !important;
            border: 1px solid #007bff !important;
        }

        .select2 .select2-selection {
            border: unset !important;
            border: 1px solid #aaa !important;
            border-radius: 4px !important;
            height: 38px !important;
            padding-top: 5px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            padding-top: 35px;
        }

        /* .modal-xl-custom {
            max-width: 1500px;
            height: 50vh;
        } */

        .modal-content {
            border-radius: 24px;
            box-shadow: 0 8px 32px 0 rgba(30, 60, 110, 0.18);
            border: none;
            overflow: hidden;
        }

        .modal-header {
            background: #0b2545;
            color: #fff;
            border: none;
            border-radius: 24px 24px 0 0;
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            justify-content: center;
            padding: 1.25rem 2.25rem 1rem 2.25rem;
            box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.05);
        }

        .btn-close {
            filter: invert(1) grayscale(1) brightness(1.8);
            opacity: 0.6;
            top: 28px;
            right: 34px;
            position: absolute;
            z-index: 1;
            font-size: 1.25rem;
        }

        .left-panel {
            background: #f4f8fb;
            color: #222;
            padding: 22px 22px 16px 22px;
            min-width: 260px;
            font-weight: 500;
            border-radius: 0 0 0 24px;
            box-shadow: 2px 0 12px 0 rgba(11, 37, 69, 0.08);
        }

        .left-panel label {
            font-size: 0.96rem;
            color: #3b466e;
            margin-bottom: 2px;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .left-panel input,
        .left-panel select {
            border-radius: 10px;
            border: 1.5px solid #dde6ed;
            font-weight: 600;
            font-size: 1rem;
            background: #fff !important;
            color: #16233a !important;
            margin-bottom: 12px;
            box-shadow: none;
            padding: 0.5rem 0.8rem;
        }

        .input-group .input-group-text {
            background: #ffe400;
            color: #242424;
            font-weight: 700;
            border-radius: 0 10px 10px 0 !important;
        }

        .img-preview {
            width: 500px;
            height: 500px;
            object-fit: cover;
            border-radius: 5px;
            box-shadow: 0 2px 20px 0 rgba(0, 0, 0, 0.10);
            background: #e5ecf4;
            margin: 0 auto;
            display: block;
        }

        .center-panel {
            background: linear-gradient(135deg, #e0eafd 60%, #ffffff 100%);
            border-radius: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 350px;
            min-height: 360px;
            padding: 10px 0 0 0;
        }

        .qty-panel {
            border-radius: 18px;
            box-shadow: 0 2px 12px 0 rgba(250, 40, 90, 0.07);
            background: #fff;
            min-width: 120px;
            text-align: center;
            margin-bottom: 18px;
        }

        .qty-label {
            background: linear-gradient(90deg, #e40303 70%, #ff4e3b 100%);
            color: #fff;
            padding: 7px 0;
            font-weight: bold;
            font-size: 1.1rem;
            border-radius: 18px 18px 0 0;
            letter-spacing: 0.07em;
        }

        .qty-value {
            font-size: 3.5rem;
            font-weight: 900;
            padding: 14px 0 8px 0;
            color: #28396e;
            letter-spacing: 0.03em;
            background: none;
            border-radius: 0 0 18px 18px;
        }

        .right-panel {
            background: #f4f8fb;
            border-radius: 0 0 24px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 32px 18px 16px 18px;
            min-width: 175px;
            box-shadow: -2px 0 12px 0 rgba(30, 60, 110, 0.07);
        }

        .btn-modern {
            border-radius: 18px;
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 14px;
            width: 100%;
            letter-spacing: 0.04em;
            box-shadow: 0 2px 8px 0 rgba(11, 37, 69, 0.07);
            border: none;
            transition: 0.2s;
        }

        .btn-modern.ok {
            background: linear-gradient(90deg, #20ba68 70%, #21da8d 100%);
            color: #fff;
        }

        .btn-modern.nopic {
            background: linear-gradient(90deg, #bdbdbd 60%, #f4f4f4 100%);
            color: #232323;
            font-size: 1rem;
        }

        .btn-modern.ng {
            background: linear-gradient(90deg, #e40303 60%, #fa6c5c 100%);
            color: #fff;
            font-size: 1.18rem;
        }

        .btn-modern:active,
        .btn-modern:focus {
            transform: scale(0.97);
        }

        .problem-row {
            background: #f4f8fb;
            color: #263254;
            padding: 16px 20px 16px 22px;
            font-weight: 600;
            border-top: 2px solid #dde6ed;
            border-radius: 0 0 24px 24px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .problem-row select {
            background: #fff;
            color: #111;
            font-weight: bold;
            border-radius: 10px;
            padding: 0.36rem 0.7rem;
            border: 1.5px solid #dde6ed;
        }

        .btn-send {
            background: linear-gradient(90deg, #ffe400 70%, #fff38a 100%) !important;
            color: #242424 !important;
            font-weight: bold;
            border-radius: 12px;
            padding: 0.45rem 1.2rem;
            margin-left: auto;
            border: none;
            letter-spacing: 0.02em;
        }

        .btn-ans {
            /* background: linear-gradient(90deg, #ffe400 70%, #fff38a 100%) !important; */
            color: #fff !important;
            font-weight: bold;
            border-radius: 10px;
            padding: 1rem 1.2rem;
            margin-left: auto;
            border: none;
            letter-spacing: 0.02em;
        }

        .btn-take-picture {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(90deg, #36d1c4 0%, #5b86e5 100%);
            color: #fff;
            border: none;
            border-radius: 2rem;
            padding: 0.6rem 1.4rem;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 1px;
            box-shadow: 0 2px 10px 0 rgba(91, 134, 229, 0.09);
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s, transform 0.1s;
        }
    </style>
    <link rel="stylesheet" href="{{ $GLOBALS['cdn'] }}sweetalert2/css/sweetalert2@11.min.css">
@endsection
@section('content')
    <div class="d-flex justify-content-center d-none" id="loader">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    @php
        function formatOrderNo($order)
        {
            $orderno = substr($order, 0, 1) . '-' . substr($order, 1, 2) . '' . substr($order, 3, 5) . '-' . substr($order, 8, 1);
            return $orderno;
        }
    @endphp
    <div class="card">
        <div class="card-body div_table">
            <div class="row mb-3">
                {{-- <div class="col-md-3">
                    <label for="itemSearch" class="form-label">Search Item Packing</label>
                    <select name="" id="itemSearch" class="form-select">
                        <option value="">Select PACKING</option>
                    </select>
                </div> --}}
                <div class="col-md-3">
                    <label for="junSearch" class="form-label">Search JUN</label>
                    <select id="junSearch" class="form-select">
                        <option value="">Select JUN</option>
                        <!-- Add options dynamically here -->
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="pSearch" class="form-label">Search P</label>
                    <select id="pSearch" class="form-select">
                        <option value="">Select P</option>
                        <option value="P1">P1</option>
                        <option value="P2">P2</option>
                        <option value="P3">P3</option>
                        <option value="P4">P4</option>
                        <!-- Add options dynamically here -->
                    </select>
                </div>
            </div>
            <table id="orderTable" class="table table-hover" border="1" style="width:100%;"></table>
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

        <div class="modal fade" id="Qc_Check" tabindex="-1">
            <div class="modal-dialog modal-fullscreen modal-xl-custom modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header position-relative">
                        <span class="w-100 text-center">PICTURE</span>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="d-flex flex-row flex-nowrap" style="min-height:70vh;">

                            <!-- LEFT PANEL -->
                            <div class="left-panel d-flex flex-column" style="max-width:320px; flex:0 0 320px;">
                                <div>
                                    <label>PUR / STK CODE</label>
                                    <input type="text" class="form-control" id="purCode" value="" readonly>
                                    <label>DRAWING</label>
                                    <input type="text" class="form-control" id="dwgQc" value="" readonly>
                                    <label>DESCRIPTION</label>
                                    <input type="text" class="form-control" id="DiscQc" value="" readonly>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label>ITEM</label>
                                            <input type="text" class="form-control" id="itemQc" value="" readonly>
                                        </div>
                                        <div class="col-6">
                                            <label>PRODUCTION</label>
                                            <input type="text" class="form-control" id="prodQc" value="" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="qty-panel w-100 mb-3">
                                    <div class="qty-label">QTY</div>
                                    <div class="qty-value"></div>
                                </div>
                            </div>


                            <!-- CENTER PANEL (IMAGE) -->
                            <div class="center-panel flex-fill d-flex align-items-center justify-content-center">
                                <img src="https://i.ibb.co/j4T5Yj2/vbelt.png" class="img-preview" id="imgQc" alt="vbelt" loading="lazy">
                                <video id="videoQc" style="display:none;width:500px;height:500px;border:1px solid #ccc;" autoplay></video>
                                <canvas id="canvasQc" width="500" height="500" style="display:none;"></canvas>
                                <div class="d-flex gap-2">
                                    <!-- ปุ่มสแน็ป -->
                                    <button id="btnSnap" class="btn btn-success mt-3" style="display:none; border-radius:10px; background-color:#FF6347">
                                        ถ่ายรูป
                                    </button>
                                    <!-- ปุ่ม Retake -->
                                    <button id="btnRetake" class="btn btn-warning mt-3" style="display:none; border-radius:10px;">
                                        ถ่ายใหม่
                                    </button>
                                    <!-- ปุ่ม Upload (สมมติว่ามีแล้ว) -->
                                </div>
                            </div>



                            <!-- RIGHT PANEL -->
                            <div class="right-panel p-3 border-start bg-white" style="width: 260px;">
                                <h5 class="text-center mb-3 text-dark fw-bold">Master Packing Checklist</h5>

                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-start">รายการ</th>
                                            <th>สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-start">ถุงใส</td>
                                            <td class="text-center" id="clr_bag"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">แอร์แคป</td>
                                            <td class="text-center" id="aircap"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">กันชื้น</td>
                                            <td class="text-center" id="mois_proof"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">กันไฟฟ้า</td>
                                            <td class="text-center" id="anti_static"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-start">โฟม</td>
                                            <td class="text-center" id="foam"></td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="mt-4 text-center">
                                    <label class="form-label fw-semibold">Q'TY / BOX</label>
                                    <div class="form-control fw-bold bg-light text-dark" id="qty_box" style="font-size: 1.2rem;"></div>
                                </div>
                                <div class="mt-4 text-center">
                                    <label class="form-label fw-semibold">BOX NO.</label>
                                    <div class="form-control fw-bold bg-light text-dark" id="box_no" style="font-size: 1.2rem;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-center" style="min-height: 100px;">
                            <div class="row gap-2 w-80 justify-content-center align-items-center h-100">
                                <div class="col-3 d-flex align-items-center justify-content-center h-100">
                                    <div class="icheck-primary d-inline">
                                        <input type="radio" name="qcStatus" id="qcOk" value="ok" checked>
                                        <label for="qcOk">
                                            <i class="bi bi-check-circle me-2"></i>OK
                                        </label>
                                    </div>
                                </div>
                                <div class="col-3 d-flex align-items-center justify-content-center h-100">
                                    <div class="icheck-danger d-inline-flex align-items-center">
                                        <input type="radio" name="qcStatus" id="qcNg" value="ng" class="me-2">
                                        <label for="qcNg" class="me-2 mb-0">
                                            <i class="bi bi-x-octagon me-1"></i>NG
                                        </label>
                                        <select class="form-select" style="max-width:210px; height: calc(1.5em + 0.75rem + 2px);">
                                            <option>Please select</option>
                                            @foreach($problem as $p)
                                                <option value="{{ $p->PP_ID }}">{{ $p->PP_NAME }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <!-- AT Js -->
                                <div class="col-3 d-flex align-items-center justify-content-center h-100" id="qc-action"></div>

                            </div>
                        </div>

                    </div>
                    <!-- PROBLEM ROW -->
                    <div class="problem-row mt-3 text-center">
                        <button class="btn btn-send" id="qc-ok-btn">NEXT</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--<div class="container mt-5">
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
    </div>--}}

    <!-- Modal -->
    {{--
    <div class="modal fade" id="Qc_Check" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered h-50">
            <div class="modal-content h-100 d-flex flex-column p-2">

                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title w-100 text-center">Modal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Form -->
                <form id="carouselForm" class="d-flex flex-column flex-grow-1">

                    <!-- Body -->
                    <div class="modal-body flex-grow-1 overflow-auto">
                        <div class="d-flex align-items-center gap-2 h-100">

                            <!-- Prev Button -->
                            <button type="button" id="prevBtn" class="btn btn-secondary" style="font-size: 2rem;" disabled>
                                &lsaquo;
                            </button>

                            <!-- Carousel Content Wrapper -->
                            <div class="flex-grow-1">

                                <!-- Page 1 -->
                                <div class="carousel-page h-100" id="carousel-1">
                                    <div class="mb-2">
                                        <label class="form-label">Part Name</label>
                                        <input type="text" class="form-control" placeholder="Part 1 Name">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Part Type</label>
                                        <select class="form-select">
                                            <option>Type A</option>
                                            <option>Type B</option>
                                        </select>
                                    </div>
                                    <div class="mb-2 d-flex align-items-center gap-2">
                                        <label class="form-label mb-0">PIC</label>
                                        <input type="text" class="form-control w-auto" placeholder="PIC">
                                        <img src="https://placehold.co/48" class="rounded border" alt="part pic">
                                    </div>
                                </div>

                                <!-- Page 2 -->
                                <div class="carousel-page h-100 d-none" id="carousel-2">
                                    <div class="mb-2">
                                        <label class="form-label">Part Name</label>
                                        <input type="text" class="form-control" placeholder="Part 2 Name">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Part Type</label>
                                        <select class="form-select">
                                            <option>Type A</option>
                                            <option>Type B</option>
                                        </select>
                                    </div>
                                    <div class="mb-2 d-flex align-items-center gap-2">
                                        <label class="form-label mb-0">PIC</label>
                                        <input type="text" class="form-control w-auto" placeholder="PIC">
                                        <img src="https://placehold.co/48?text=2" class="rounded border" alt="part pic">
                                    </div>
                                </div>

                                <!-- Page 3 -->
                                <div class="carousel-page h-100 d-none" id="carousel-3">
                                    <div class="mb-2">
                                        <label class="form-label">Part Name</label>
                                        <input type="text" class="form-control" placeholder="Part 3 Name">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Part Type</label>
                                        <select class="form-select">
                                            <option>Type A</option>
                                            <option>Type B</option>
                                        </select>
                                    </div>
                                    <div class="mb-2 d-flex align-items-center gap-2">
                                        <label class="form-label mb-0">PIC</label>
                                        <input type="text" class="form-control w-auto" placeholder="PIC">
                                        <img src="https://placehold.co/48?text=3" class="rounded border" alt="part pic">
                                    </div>
                                </div>

                            </div>

                            <!-- Next Button -->
                            <button type="button" id="nextBtn" class="btn btn-secondary" style="font-size: 2rem;">
                                &rsaquo;
                            </button>

                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success w-100">Submit</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    --}}


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
    </div>
@endsection

@section('scripts')
    <script src="<?php echo base_url(); ?>assets/dist/print/BrowserPrint-2.0.0.75.min.js"></script>
    <Script src="<?php echo base_url(); ?>assets/script/issue_kanban/print_label_other.js?ver={{ date('YmdHis') }}"></Script>
    <script src="{{ $GLOBALS['cdn'] }}sweetalert2/js/sweetalert2@11.min.js"></script>
    <script src="{{ base_url('assets/dist/js/print_vps.min.js') }}?ver={{ date('YmdHis') }}"></script>
    <!-- <script src="{{ base_url('assets/script/print_vps/print_vps_refactor.js') }}?ver={{ date('YmdHis') }}"></script> -->
    <script>
        $(document).ready(function () {
            const $selectPackno = $("#itemSearch").select2();
            const $junSearch = $("#junSearch").select2();
            const $pSearch = $("#pSearch").prop('disabled', true);
            const $orderTable = $('#orderTable');

            initPage();

            async function initPage() {
                setupEvents();
                await listOrder();
            }

            function setupEvents() {
                $selectPackno.on('change', function () {
                    listOrder($(this).val());
                });

                $junSearch.on('change', function () {
                    filterTable();
                });

                $pSearch.on('change', function () {
                    filterTable();
                });
            }

            async function listOrder(packing = "") {
                try {
                    const response = await fetchOrderData(packing);
                    populateJunOptions(response);
                    renderTable(response);
                    $orderTable.data('fullData', response); // เก็บข้อมูลเต็มไว้สำหรับ filter
                } catch (error) {
                    console.error("Error listing orders:", error);
                }
            }

            function fetchOrderData(packing) {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        type: "POST",
                        url: host + "packing/pkc/get_order_88_89",
                        data: { packing },
                        dataType: "json",
                        beforeSend: () => {
                            Swal.fire({
                                title: 'Loading...',
                                text: 'Please wait while we fetch the data.',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });
                        },
                        success: (response) => {
                            Swal.close();
                            resolve(response);
                        },
                        error: (xhr, status, error) => {
                            Swal.close();
                            reject(error);
                        }
                    });
                });
            }

            function populateJunOptions(data) {
                const uniqueJun = [...new Map(data.map(item => [item.M8K01, item])).values()]
                    .sort((a, b) => a.M8K01.localeCompare(b.M8K01));

                $junSearch.empty().append(new Option('Select JUN', ''));
                uniqueJun.forEach(item => {
                    const text = item.M8K01.slice(0, 4) + item.SCHEDULE;
                    $junSearch.append(new Option(text, item.M8K01));
                });
            }

            function filterTable() {
                const fullData = $orderTable.data('fullData') || [];
                const jun = $junSearch.val();
                const p = $pSearch.val();

                if (!jun) {
                    $orderTable.DataTable().clear().rows.add(fullData).draw();
                    $pSearch.prop('disabled', true).val('');
                    return;
                }

                let filtered = fullData.filter(item => item.M8K01 === jun);
                if (p) {
                    filtered = filtered.filter(item => item.M8K02 === p);
                }

                $pSearch.prop('disabled', false);
                $orderTable.DataTable().clear().rows.add(filtered).draw();
            }

            function renderTable(data) {
                if ($.fn.DataTable.isDataTable($orderTable)) {
                    $orderTable.DataTable().destroy();
                }

                $orderTable.empty();
                $orderTable.DataTable({
                    data: data,
                    columns: [
                        {
                            data: null,
                            title: 'No',
                            width: '5%',
                            className: 'text-center',
                            render: (data, type, row, meta) => meta.row + 1
                        },
                        { data: 'M8K03', title: 'ORDER' },
                        { data: 'S01M04', title: 'PACKING' },
                        {
                            data: null,
                            title: 'JUN',
                            render: row => row.M8K01.slice(0, 4) + row.SCHEDULE
                        },
                        {
                            data: 'M8K02',
                            title: 'P'
                        },
                        { data: 'S01M08', title: 'PARTNAME' },
                        { data: 'S01M07', title: 'MODEL' },
                        {
                            data: null,
                            title: 'Action',
                            width: '10%',
                            className: 'text-center',
                            render: row => `<button class="btn btn-primary btn_modal" data_order="${row.S01M01}" data_packing="${row.S01M04}">Print</button>`
                        }
                    ],
                    dom: '<"aa"f>tpi',
                    initComplete: function () {
                        $('.aa').append('<a href="search_order" class="btn btn-primary mb-2">กลับไปหน้าแรก</a>');
                    }
                });
            }
        });
    </script>
    <script>
        let page = 1;
        const maxPage = 3;
        function showPage(n) {
            for (let i = 1; i <= maxPage; i++) {
                document.getElementById('carousel-' + i).style.display = (i === n) ? 'block' : 'none';
            }
            document.getElementById('prevBtn').disabled = n === 1;
            document.getElementById('nextBtn').disabled = n === maxPage;
        }
        document.getElementById('prevBtn').onclick = function () {
            if (page > 1) { page--; showPage(page); }
        };
        document.getElementById('nextBtn').onclick = function () {
            if (page < maxPage) { page++; showPage(page); }
        };
        document.getElementById('carouselForm').onsubmit = function (e) {
            e.preventDefault();
            alert('ส่งฟอร์มสำเร็จ!');
            // รีเซ็ต
            page = 1;
            showPage(page);
            // ปิด modal
            var modalEl = document.getElementById('formModal');
            var modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();
        };
        document.getElementById('formModal').addEventListener('show.bs.modal', function () {
            page = 1;
            showPage(page);
        });
        showPage(page);
    </script>

@endsection