@extends('layout/template')

@section('content')
    <div id="loading_waiting" class="d-none" data-aos="zoom-out"><img src="{{base_url('assets\images\hourglass.GIF')}}" alt="" class="gif_load" style=""></div>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center bg-secondary text-white">
                        Re Print
                    </div>
                    <div id="show_data" class="d-none" style="width:100% ; min-height:0px;color:white"></div>
                    <div class="card-body">
                        <!-- <form> -->
                        <div class="form-group">
                            <label for="orderNo">Type Re-print</label><br>
                            <input type="radio" id="print_all" name="type_print" value="1" class="mt-3 mb-3 type_print">
                            <label for="print_all">ปริ้นทั้งหมด</label>
                            <input type="radio" id="some_print" name="type_print" value="2" class="mt-3 mb-3 type_print" style="margin-left:3rem;">
                            <label for="some_print">เลือกใบปริ้น</label>
                        </div>
                        <div class="form-group">
                            <label for="orderNo">Order No</label>
                            <select class="form-control" id="orderNo" name="orderNo" required>
                                <option value="">-----order no-----</option>
                                @foreach ($packing as $pack)
                                    <option value="{{ $pack->ORDER_NO }}">{{ $pack->ORDER_NO }}</option>
                                @endforeach
                            </select>
                            <div class="alert_order d-none text-center" style="color:red;">***Please select an Order No.***</div>
                        </div>
                        <div class="form-group mt-3">
                            <label for="packingNo">Packing No</label>
                            <select class="form-control" id="packingNo">
                                <option value="">-----packing no-----</option>
                            </select>
                            <div class="alert_packing d-none text-center" style="color:red;">***กรุณาเลือก Packing No.***</div>
                        </div>
                        <div class="form-group mt-3">
                            <label for="remark">Remark</label>
                            <select class="form-control" id="remark">
                                <option value="">-----Remark-----</option>
                                @foreach ($remark as $rem)
                                    <option value="{{$rem->PRIORITY}}" data-name="{{$rem->REMARK}}">{{$rem->REMARK}}</option>
                                @endforeach
                                <!-- Add options here -->
                            </select>
                            <div class="alert_remark d-none text-center" style="color:red;">***กรุณาเลือก Remark***</div>
                            <input type="text" class="other_remark form-control d-none mt-2" placeholder="กรอกเหตุผล.....">
                            <div class="alert_other d-none text-center" style="color:red;">***กรุณากรอกเหตุผล***</div>
                        </div>
                        <input type="hidden" class="amount_print">
                        <button id="printButton" class="btn btn-primary btn-block mt-3 w-100  d-none">Print</button>
                        <button id="printButton_some" class="btn btn-primary btn-block mt-3 w-100  d-none">Print</button>
                        <!-- </form> -->
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
                        <button type="button" class="btn btn-primary" id="submitButton">ตกลง</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="modal" tabindex="-1" id="printModal_some">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">กรอกจำนวน</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="containerQuantity">เลือกลำดับใบที่ต้องการปริ้น</label>
                            <select name="" id="selecet_vps" class="form-control">
                                <option value="">---กรุณาเลือกลำดับที่ต้องการปริ้น---</option>
                            </select>
                        </div>
                        <div id="itemsQuantityContainer" class="mt-3">
                            <label for="containerQuantity">กรอกจำนวนชิ้น</label>
                            <input type="text" class="form-control qty_some">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="submitButton_some">ตกลง</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ $GLOBALS['cdn'] }}select2/js/select2.min.js"></script>
    <script src="{{ $GLOBALS['cdn'] }}sweetalert2/js/sweetalert2@11.min.js"></script>
    <script src="{{ base_url('assets/dist/print/BrowserPrint-2.0.0.75.min.js') }}"></script>
    <script src="{{ base_url('assets/script/issue_kanban/print_label.js?ver=' . date('YmdHis')) }}"></script>



    <script>

        $(function () {
            $("#orderNo").select2();
            $("#packingNo").select2();
        })

        $("#orderNo").change(function () {
            $("#packingNo").empty();
            var val = $(this).val();
            if (val === '') {
                $('.alert_order').removeClass('d-none');
            } else {
                $('.alert_order').addClass('d-none');
            }
            $.ajax({
                url: host + 'packing/PKC/get_packing_no',
                type: 'POST',
                data: { order: val },
                success: function (res) {
                    var data = JSON.parse(res);
                    $.each(data.data, function (index, packing) {
                        $("#packingNo").append(`
                            <option>${packing.PACKING_NO}</option>
                            `);
                    });
                    console.log(data);
                    // $("#packingNo").append();
                }
            })
        })

        $("#packingNo").change(function () {
            $(".alert_packing").addClass("d-none");
            var order = $("#orderNo").val();
            var packing = $(this).val();
            $.ajax({
                url: host + 'packing/PKC/get_amount_print',
                type: 'POST',
                data: {
                    order: order,
                    packing: packing
                },
                success: function (res) {
                    $(".amount_print").val(res);
                }
            })

        })

        $("#remark").change(function () {
            $(".alert_remark").addClass("d-none");
            $(".alert_other").addClass("d-none");
            const val = $(this).val();
            if (val === '8') {
                $(".other_remark").removeClass('d-none');
            } else {
                $(".other_remark").addClass('d-none');
            }

        })

        $('.type_print').change(function () {
            if ($(this).val() == '1') {
                $("#printButton").removeClass('d-none');
                $("#printButton_some").addClass('d-none');
            } else {
                $("#printButton").addClass('d-none');
                $("#printButton_some").removeClass('d-none');
            }


        })

        $("#printButton_some").click(function (e) {
            e.preventDefault();
            var order = $("#orderNo").val();
            var packing = $("#packingNo").val();
            var remark = $("#remark").val();
            var other_remark = $(".other_remark").val();

            $(".alert_order, .alert_packing, .alert_remark, .alert_other").addClass("d-none");

            if (!order) {
                $(".alert_order").removeClass("d-none");
            } else if (!packing) {
                $(".alert_packing").removeClass("d-none");
            } else if (!remark) {
                $(".alert_remark").removeClass("d-none");
            } else if (remark === '8' && !other_remark) {
                $(".alert_other").removeClass("d-none");
            } else {
                $.ajax({
                    url: host + 'packing/pkc/get_amount_print',
                    type: 'post',
                    data: { order: order, packing: packing },
                    success: function (res) {
                        console.log(res);
                        for (let index = 0; index < res; index++) {
                            $("#selecet_vps").append(`<option value="${index + 1}/${res}">${index + 1}/${res}</option>`)
                        }
                        $('#printModal_some').modal('show');
                    },
                    error: function (err) {
                        console.error("Error getting amount to print:", err);
                    }
                });

                $('#submitButton_some').off().on('click', function () {
                    $.ajax({
                        url: host + 'packing/PKC/get_order_detail',
                        type: 'POST',
                        data: {
                            order: order,
                            packing: packing,
                        },
                        beforeSend: function () {
                            $("#loading_waiting").removeClass('d-none');
                        },
                        success: function (res) {
                            var data = JSON.parse(res).data[0];

                            var packing_no = data.S01M04.slice(0, 3) + "-" + data.S01M04.slice(3, 5);
                            var project = data.S01M08;
                            var order_no = formatorderno(data.S01M01);
                            var date = data.S11M08.slice(2, 4);
                            var prod = date + "/" + data.SCHEDULE;
                            var part = data.S11M05;
                            var arr_dw = '';
                            const dw_print = data.S11M06;
                            var priority = data.M8K02;
                            var input = $(".qty_some").val();
                            var page = $("#selecet_vps").val();

                            var IP = re_printbar(packing_no, project, order_no, prod, part, dw_print, priority, input, page);
                            $("#loading_waiting").addClass('d-none');

                        },
                        error: function (err) {
                            console.error("Error getting order detail:", err);
                        }
                    });
                });
            }
        });


        $("#printButton").click(function (e) {
            e.preventDefault();
            var order = $("#orderNo").val();
            var packing = $("#packingNo").val();
            var remark = $("#remark").val();
            var other_remark = $(".other_remark").val();
            if (!order) {
                $(".alert_order").removeClass("d-none");
            } else if (!packing) {
                $(".alert_packing").removeClass("d-none");
            } else if (!remark) {
                $(".alert_remark").removeClass("d-none");
            } else if (remark === '8' && !other_remark) {
                $(".alert_other").removeClass("d-none");
            } else {
                $('#printModal').modal('show');
                $('#containerQuantity').on('keyup', function () {
                    var containerQuantity = $(this).val();
                    var itemsQuantityHtml = '';
                    for (var i = 0; i < containerQuantity; i++) {
                        itemsQuantityHtml += `
                                <div class="form-group mt-3">
                                <label for="itemQuantity${i}">กรอกจำนวนชิ้นสำหรับใบที่ ${i + 1}</label>
                                <input type="number" class="form-control" id="itemQuantity${i}" placeholder="Enter the quantity of items for label ${i + 1}">
                                </div>
                            `;
                    }
                    $('#itemsQuantityContainer').html(itemsQuantityHtml);
                });
                $('#submitButton').off().on('click', function () {
                    const containerQuantity = $('#containerQuantity').val().trim();

                    if (containerQuantity <= 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'ข้อผิดพลาด',
                            text: 'กรุณากรอกจำนวนลังที่ถูกต้อง'
                        });
                        return;
                    }

                    const itemsQuantities = getItemsQuantities(containerQuantity);
                    console.log(itemsQuantities);
                    if (!itemsQuantities.valid) {
                        Swal.fire({
                            icon: 'error',
                            title: 'ข้อผิดพลาด',
                            text: 'กรุณากรอกจำนวนชิ้นที่ถูกต้องสำหรับแต่ละใบ'
                        });
                        return;
                    }
                    $.ajax({
                        url: host + 'packing/PKC/get_order_detail',
                        type: 'POST',
                        data: {
                            order: order,
                            packing: packing,
                        },
                        beforeSend: function () {
                            $("#loading_waiting").removeClass('d-none');
                        },
                        success: function (res) {
                            var data = JSON.parse(res).data[0];

                            var packing_no = data.S01M04.slice(0, 3) + "-" + data.S01M04.slice(3, 5);
                            var project = data.S01M08;
                            var order_no = formatorderno(data.S01M01);
                            var date = data.S11M08.slice(2, 4);
                            var prod = date + "/" + data.SCHEDULE;
                            var part = data.S11M05;
                            var arr_dw = '';
                            const dw_print = data.S11M06;
                            var priority = data.M8K02;
                            var input = $(".amount_print").val();

                            // const IP = printbar(packing_no, project, order_no, prod, part, dw_print, priority, qty_sticker, itemsQuantities);
                            save_print_vps(data.S01M01, data.S01M04, containerQuantity);
                            var IP = printbar(packing_no, project, order_no, prod, part, dw_print, priority, containerQuantity, itemsQuantities.values);
                            $.ajax({
                                url: host + 'packing/PKC/insert_print_log',
                                type: 'POST',
                                data: {
                                    order_no: data.S01M01,
                                    packing_no: data.S01M04,
                                    printer: IP,
                                    qty: containerQuantity,
                                    qty_item: itemsQuantities.values,
                                    ptype: $("#remark").val(),
                                    remark: $("#remark option:selected").data('name'), // ดึงค่า data-name จาก option ที่ถูกเลือก
                                    other_remark: other_remark
                                },
                                success: function (res) {
                                    // console.log(data.S01M01);
                                    // console.log(data.S01M04);
                                    // console.log(IP);
                                    // console.log(itemsQuantities.values);
                                    // alert('Print Success!!!');
                                    $('#printModal').modal('hide');
                                    // location.reload();
                                    console.log(IP);
                                },
                                error: function (err) {
                                    console.log(err);
                                }
                            })

                        },
                        complete: function () {
                            $("#loading_waiting").addClass('d-none');
                        }
                    })
                });
            }
        });

        function save_print_vps(order, packing, qty_print) {
            $.ajax({
                url: host + 'packing/PKC/get_order_detail',
                type: 'POST',
                data: {
                    order: order,
                    packing: packing,
                },
                success: function (res) {
                    var data = JSON.parse(res).data[0];
                    console.log(data);
                    var production = data.S11M08;
                    var p = data.M8K02;
                    var order_no = order;
                    var item = data.S01M06;
                    var partname = data.S01M05;
                    var project = data.S11M06;
                    var sche = data.SCHEDULE + data.S11M08.slice(2, 4);
                    var packing_no = packing;
                    var piscode = order.substring(1, 8) + packing;


                    $.ajax({
                        url: host + 'packing/PKC/reprint_packingorder',
                        type: 'POST',
                        data: {
                            order: order_no,
                            packing: packing_no,
                            production: production,
                            p: p,
                            item: item,
                            partname: partname,
                            project: project,
                            sche: sche,
                            piscode: piscode,
                            qty_print: qty_print
                        },
                        success: function (res) {
                            console.log(res);
                        }
                    })
                }
            });
        }

        function formatorderno(order) {
            let orderno = order.slice(0, 1) + '-' + order.slice(1, 3) + ' ' + order.slice(3, 8) + '-' + order.slice(8, 9);
            return orderno;
        }

        function getItemsQuantities(containerQuantity) {
            const itemsQuantities = [];
            let valid = true;
            for (let i = 0; i < containerQuantity; i++) {
                const itemQuantity = $(`#itemQuantity${i}`).val().trim();
                if (itemQuantity <= 0) {
                    valid = false;
                    break;
                }
                itemsQuantities.push(itemQuantity);
            }
            return { valid, values: itemsQuantities };
        }
    </script>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{$GLOBALS['cdn']}}select2/css/select2.min.css" />
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
            background-color: #C0C0C0;
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
    </style>
@endsection