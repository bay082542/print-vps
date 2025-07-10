@extends('layout/template2')
@section('styles')
    <style>
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
            /* float: right;
                    text-align: right;
                    margin-top: 10px; */

            float: right;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;

        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 8px 12px !important;
            margin: 0 2px !important;
            /* border: none !important; */
            border-radius: 10px !important;
            border: 1px solid #ddd !important;
            background: #fff !important;
            color: #333 !important;
            cursor: pointer !important;
            transition: background 0.3s, color 0.3s, transform 0.2s !important;
            font-size: 14px !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: rgb(48, 102, 250) !important;
            color: white !important;
            transform: translateY(-2px) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: rgb(48, 102, 250) !important;
            border: none !important;
            color: white !important;
            font-weight: bold !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            background: #f1f1f1 !important;
            color: #ccc !important;
            cursor: not-allowed !important;
            transform: none !important;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            justify-content: center;
        }

        .print-btn {
            width: 200px;
            padding: 15px 30px;
            background: blue;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
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
    <input type="hidden" id="sempno" value="{{ $_SESSION['user']->SEMPNO }}">
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
                <div class="col-md-3">
                    <label for="agent" class="form-label">J / V</label>
                    <select name="" id="agent" class="form-select">

                    </select>
                </div>


                <!-- <div id="printModal" class="modal">
                            <div class="modal-content">
                                <h2>เลือกประเภทการพิมพ์</h2>
                                <div class="button-group">
                                    <button class="print-btn" data-type="1">แบบที่ 1</button>
                                    <button class="print-btn" data-type="2">แบบที่ 2</button>
                                    <button class="print-btn" data-type="3">แบบที่ 3</button>
                                </div>
                            </div>
                        </div> -->


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
                        <div class="data-pis" data-order="" data-item=""></div>
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
                                <!-- <th>Con. QTY</th> -->
                                <th>REMARK</th>
                            </tr>

                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        <button type="button" class="btn btn-primary save-btn">พิมพ์</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="button_modal" tabindex="-1" aria-labelledby="button_modalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="button-group">
                            <button class="print-btn" data-type="1">ปริ้นตามจำนวน QTY</button>
                            <button class="print-btn" data-type="2">ปริ้น 1 ใบ</button>
                            <button class="print-btn" data-type="3">ปริ้นกรอกจำนวนเอง</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="stickerModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">กำหนดจำนวนสติ๊กเกอร์</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <label for="stickerCount" class="form-label fs-5">จำนวนสติ๊กเกอร์</label>
                        <div class="d-flex justify-content-center align-items-center mb-3">
                            <button class="btn btn-danger btn-lg" id="decreaseBtn">-</button>
                            <input type="hidden" id="order-input">
                            <input type="hidden" id="packing-input">
                            <input type="hidden" id="stickerDwg">
                            <input type="hidden" id="totalQty">
                            <input type="hidden" id="jun">
                            <input type="hidden" id="p">
                            <input type="number" id="stickerCount" class="form-control text-center mx-2" style="width: 80px; font-size: 1.5rem;" min="1" value="1">
                            <button class="btn btn-success btn-lg" id="increaseBtn">+</button>
                        </div>
                        <div id="stickerInputs"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        <button type="button" class="btn btn-primary btn-lg" id="submit_vpis">ยืนยัน</button>
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
    </div>
@endsection

@section('scripts')
    <script src="<?php echo base_url(); ?>assets/dist/print/BrowserPrint-2.0.0.75.min.js"></script>
    <Script src="<?php echo base_url(); ?>assets/script/issue_kanban/print_label_other.js?ver={{ date('YmdHis') }}"></Script>
    <script src="{{ $GLOBALS['cdn'] }}sweetalert2/js/sweetalert2@11.min.js"></script>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <script src="{{ base_url('assets/dist/js/print_vps_other.min.js') }}?ver={{ date('YmdHis') }}"></script>
    <script>
        $(document).ready(function () {
            list_order();

            $("#pSearch").prop('disabled', true);

            // โหลดข้อมูล JUN
            // $.ajax({
            //     type: "POST",
            //     url: host + "packing/pkc/get_calendar",
            //     dataType: "json",
            //     success: function (response) {
            //         const jun = $("#junSearch").empty();
            //         jun.append(`<option value="">Select JUN</option>`);
            //         [...new Set(response.map(item => item.SCHDMFG + '-' + item.SCHDNUMBER))]
            //             .forEach((item) => {
            //                 const [schdMfg, schdNumber] = item.split('-');
            //                 jun.append(`<option value="${schdNumber}">${schdMfg}</option>`);
            //             });
            //     },
            //     error: function (jqXHR, textStatus, errorThrown) {
            //         console.log(jqXHR, textStatus, errorThrown);
            //     }
            // });

            // โหลดข้อมูล AGENT
            $.ajax({
                type: "GET",
                url: host + "packing/pkc/get_agent",
                dataType: "json",
                success: function (response) {
                    const agent = $("#agent").empty();
                    agent.append(`<option value="">Select -Agent-</option>`);
                    response.forEach((item) => {
                        if (item.AGENT.trim() !== '') {
                            agent.append(`<option value="${item.AGENT}">${item.AGENT}</option>`);
                        }
                    });
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR, textStatus, errorThrown);
                }
            });

            $(document).on("click", ".print_vpis", function () {
                $("#exampleModal").modal("hide");
                $("#button_modal").modal("show");

                $("#jun").val($(this).data('jun'));
                $("#stickerDwg").val($(this).data('dwg'));
                $("#totalQty").val($(this).data('qty'));
                $("#p").val($(this).data('p'));
                $("#order-input").val($(this).data('order'));
                $("#packing-input").val($(this).data('packing'));
            });

            $(document).on("click", ".print-btn", function () {
                let printType = $(this).data('type');
                console.log('เลือกประเภทการพิมพ์:', printType);
                // สามารถเพิ่มโค้ดส่งค่าไปยังระบบพิม์ที่ต้องการได้ที่นี่
                $('#button_modal').modal('hide');
                if (printType == 3) {
                    $('#stickerModal').modal('show');
                } else {
                    // print_vps_other(printType);
                }
            });

            $("#submit_vpis").click(function () {
                var stickerCount = parseInt($("#stickerCount").val());
                var stickerInputs = $("#stickerInputs input");
                var isFormValid = true;
                var totalStickerAmount = 0; // ตัวแปรสำหรับเก็บผลรวมของสติ๊กเกอร์
                const maxAllowedAmount = parseInt($("#totalQty").val());

                if (stickerInputs.length < stickerCount) {
                    alert("กรุณากรอกข้อมูลให้ครบทุกช่อง");
                    return;
                }

                stickerInputs.each(function () {
                    var inputValue = $(this).val().trim();
                    if (inputValue === "") {
                        isFormValid = false;
                    } else {
                        totalStickerAmount += parseFloat(inputValue); // นับผลรวมของสติ๊กเกอร์ที่กรอก
                    }
                });

                if (!isFormValid) {
                    alert("กรุณากรอกข้อมูลให้ครบทุกช่อง");
                    return;
                }

                if (totalStickerAmount > maxAllowedAmount) {
                    alert("ผลรวมของสติ๊กเกอร์เกินกว่าจำนวนที่กำหนด");
                    return; // หยุดการทำงาน
                }

                console.log("ข้อมูลครบ");
                console.log("ผลรวมของสติ๊กเกอร์: " + totalStickerAmount); // แสดงผลรวมของสติ๊กเกอร์

                let order = $("#order-input").val();
                let packing = $("#packing-input").val();
                let stickerQty = $("#stickerCount").val();
                let jun = $("#jun").val();
                let p = $("#p").val();
                let dwg = $("#stickerDwg").val();

                console.log(order, packing, jun, stickerQty);

                print_vpis(order, packing, jun, p, dwg, stickerCount);

                // $('#stickerModal').modal('hide');
            });

            function updateStickerInputs(count) {
                let container = $('#stickerInputs');
                container.empty();
                for (let i = 1; i <= count; i++) {
                    container.append(`<input type="text" class="form-control mb-2 fs-5" placeholder="QTY สติ๊กเกอร์ที่ ${i}">`);
                }
            }

            function adjustCount(amount) {
                let input = $('#stickerCount');
                let count = parseInt(input.val()) + amount;
                if (count < 1) count = 1;
                input.val(count);
                updateStickerInputs(count);
            }

            $('#increaseBtn').click(function () {
                adjustCount(1);
            });

            $('#decreaseBtn').click(function () {
                adjustCount(-1);
            });

            $('#stickerCount').on('input', function () {
                let count = parseInt($(this).val());
                if (isNaN(count) || count < 1) count = 1;
                updateStickerInputs(count);
            });

            updateStickerInputs(1);

        });

        // ตัวแปรเก็บข้อมูลทั้งหมด
        let orderData = [];

        async function list_order() {
            const packing = localStorage.getItem('selectedPackingNo');

            const getdata = () => {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        type: "POST",
                        url: host + "packing/pkc/get_order_other",
                        data: {
                            packing: packing
                        },
                        dataType: "json",
                        beforeSend: function () {
                            Swal.fire({
                                title: 'Loading...',
                                text: 'Please wait while we fetch the data.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                        },
                        success: function (response) {
                            Swal.close();
                            orderData = response;
                            populateJunOptions(response);
                            resolve(response);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            Swal.close();
                            reject(errorThrown);
                        }
                    });
                });
            };

            const data = await getdata();

            $('#orderTable').DataTable({
                data: data,
                ordering: true, // (ยังเปิดใช้งานได้ เพื่อให้บางคอลัมน์ sort ได้อยู่)
                order: [], // <<<<<< บอกว่า "ไม่ต้อง sort อัตโนมัติ"
                columnDefs: [{
                    orderable: false,
                    targets: [0]
                }],
                columns: [
                    // {
                    //     data: null,
                    //     title: 'No',
                    //     width: '5%',
                    //     className: 'text-center',
                    //     render: function (data, type, row, meta) {
                    //         return meta.row + 1;
                    //     }
                    // },
                    {
                        data: 'M8K04',
                        title: 'REQ',
                        className: 'text-center'
                    },
                    {
                        data: 'M8K03',
                        title: 'ORDER'
                    },
                    {
                        data: 'S01M04',
                        title: 'PACKING'
                    },
                    {
                        data: null,
                        title: 'JUN',
                        render: function (data, type, row) {
                            return row.M8K01.slice(0, 4) + row.SCHEDULE;
                        }
                    },
                    {
                        data: 'M8K02',
                        title: 'P'
                    },
                    {
                        data: 'S01M08',
                        title: 'PARTNAME'
                    },
                    {
                        data: 'S01M07',
                        title: 'MODEL'
                    },
                    {
                        data: null,
                        title: 'Action',
                        width: '10%',
                        className: 'text-center',
                        render: function (data, type, row) {
                            if (data.PRINTSTA == '0') {
                                return `<button class="btn btn-primary btn-print" id="${row.S01M01}" data-order="${row.S01M01}" data-packing="${row.S01M04}">Print</button>`;
                            } else {
                                return `<button class="btn btn-success btn-view" id="${row.S01M01}" data-order="${row.S01M01}" data-packing="${row.S01M04}"">view</button>`;
                            }
                        }
                    }
                ],
                dom: '<"aa"f>tpi',
                initComplete: function () {
                    $('.aa').append('<a href="search_order" class="btn btn-primary mb-2">กลับไปหน้าแรก</a>');
                },
            });

            // เรียก filterData() เมื่อมีการเปลี่ยนค่า
            $("#junSearch, #pSearch, #agent").change(filterData);
        }

        function filterData() {
            const jun = $("#junSearch").val();
            const p = $("#pSearch").val();
            const agent = $("#agent").val();

            let filteredData = orderData;

            if (jun) {
                filteredData = filteredData.filter(item => item.M8K01 === jun);
            }
            if (p) {
                filteredData = filteredData.filter(item => item.M8K02 === p);
            }
            if (agent) {
                filteredData = filteredData.filter(item => item.AGENT === agent);
            }

            $('#orderTable').DataTable().clear().rows.add(filteredData).draw();
        }

        function populateJunOptions(data) {
            const $junSearch = $("#junSearch")
            const uniqueJun = [...new Map(data.map(item => [item.M8K01, item])).values()]
                .sort((a, b) => a.M8K01.localeCompare(b.M8K01));

            $junSearch.empty().append(new Option('Select JUN', ''));
            uniqueJun.forEach(item => {
                const text = item.M8K01.slice(0, 4) + item.SCHEDULE;
                $junSearch.append(new Option(text, item.M8K01));
            });
        }

        $("#junSearch").change(function () {
            const jun = $(this).val();
            if (jun) {
                $("#pSearch").prop('disabled', false);
            } else {
                $("#pSearch").prop('disabled', true).val('');
            }
            filterData();
        });
    </script>
@endsection