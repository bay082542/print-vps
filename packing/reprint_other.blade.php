@extends('layout/template2')
@section('styles')
<style>
    .header {
        background: #1A237E;
        color: white;
        padding: 15px;
        text-align: center;
        font-size: 28px;
        font-weight: bold;
        border-radius: 8px 8px 0 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Form Container Styling */
    .form-container {
        background-color: #ffffff;
        border-radius: 0 0 8px 8px;
        padding: 30px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Form Label */
    .form-label {
        font-weight: 500;
        color: #555;
    }

    /* Custom Input Group */
    .input-group-text {
        background-color: #f1f1f1;
        border: none;
    }

    .container {
        width: 50%;
        margin-top: 50px;
    }

    .btn-primary {
        background-color: #1A237E;
        border-color: #1A237E;
    }

    .btn-primary:hover {
        background-color: #3949AB;
        border-color: #3949AB;
    }

    .remind-box {
        position: relative;
        background-color: #ffcc00;
        padding: 20px;
        border-radius: 8px;
        color: #333;
        font-size: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        /* max-width: 400px; */
        margin-bottom: 20px;
    }

    .remind-box button {
        background-color: transparent;
        border: none;
        font-size: 20px;
        color: #333;
        cursor: pointer;
    }
</style>
@endsection
@section('content')

<div class="container">
    <div class="header">
        Reprint
    </div>
    <div class="form-container">

        <div class="remind-box">
            <span><i class="icofont-warning" style="font-size:16pt;"></i> <strong>ห้ามสแกน PIS!</strong> ในหน้านี้ กรุณาเลือก Order และ Item Packing</span>
            <!-- <button onclick="this.parentElement.style.display='none';">&times;</button> -->
        </div>
        <div class="form-group mb-4">
            <label for="order_id" class="form-label">Order</label>
            <!-- <input type="text" class="form-control" id="order_id" name="order_id" placeholder="Enter Order ID" required> -->
            <select id="order_id" name="order_id" class="form-select">

            </select>
        </div>
        <div class="form-group mb-4">
            <label for="packing_id" class="form-label">Item Packing</label>
            <!-- <input type="text" class="form-control" id="packing_id" name="packing_id" placeholder="Enter Packing ID" required> -->
            <select class="form-select" id="packing_id" name="packing_id" disabled>

            </select>
        </div>
        <div class="form-group mb-4">
            <label for="reason" class="form-label">Reason</label>
            <select class="form-select" id="reason" name="reason" required>

            </select>
            <input type="text" class="form-control mt-3 d-none" id="other_reason" name="other_reason" placeholder="Please specify the reason">
        </div>
        <div class="text-center">
            <button class="btn btn-primary" id="btn-submit" type="submit">Search</button>
        </div>
    </div>
</div>
<div class="container mt-5">
    <div class="modal" tabindex="-1" id="printModal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">กรอกจำนวน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="containerQuantityManual">Enter the number of containers to print stickers</label>
                        <input type="number" class="form-control" id="containerQuantityManual" placeholder="Enter the quantity of containers">
                    </div>
                    <div id="itemsQuantityContainer" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <div class="check-item"></div>
                    <button type="button" class="btn btn-primary" id="submitButtonManual">Submit</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- <div class="container mt-5" id="order_form_container">
    <div class="header">
        VPS Sticker
    </div>
    <div class="form-container">
        <div class="mb-4">
            <label for="order" class="form-label">Order :</label>
            <input type="text" class="form-control" id="order" placeholder="e.g. EOBU89023">
        </div>
        <div class="mb-4">
            <label for="packing" class="form-label">Item-Packing:</label>
            <input type="text" class="form-control" id="packing" placeholder="e.g. 29502">
        </div>
        <div class="text-center">
            <button class="btn btn-success" id="btn-submit-order">ยืนยัน</button>
        </div>
    </div>
</div> -->

@section('scripts')
<script src="{{ base_url('assets/dist/print/BrowserPrint-2.0.0.75.min.js') }}"></script>
<script src="{{ base_url('assets/script/issue_kanban/print_label_other.js') }}?ver={{ date('YmdHis') }}"></script>
<script src="{{ $GLOBALS['cdn'] }}sweetalert2/js/sweetalert2@11.min.js"></script>
<script src="{{ base_url('assets/dist/js/print_vps_other.min.js') }}?ver={{ date('YmdHis') }}"></script>
<script>
    $(document).ready(function () {
        // $("#order_id").select2();
        $("#packing_id").select2();
        $("#order_id").select2({
            placeholder: "Select Order ID",
            minimumInputLength: 0,
            ajax: {
                url: host + "packing/pkc/fetchDistinctOrderNo",
                type: "POST",
                dataType: "json",
                // delay: 250, // ลดโหลดเซิร์ฟเวอร์
                data: function (params) {
                    return {
                        search: params.term, // คีย์เวิร์ดที่ผู้ใช้พิมพ์
                        page: params.page || 1 // รองรับ pagination
                    };
                },
                processResults: function (data) {
                    console.log(data);
                    return {
                        results: data.items, // รายการที่ส่งกลับ
                        pagination: { more: data.more } // เช็คว่ามีหน้าต่อไปหรือไม่
                    };
                },
                cache: true
            }
            // เริ่มค้นหาหลังจากพิมพ์ 2 ตัวอักษร
        });

        $('#order_id').change(function () {
            const order = $(this).val();
            $.ajax({
                type: "POST",
                url: host + "packing/pkc/fetchDistinctPackingNo",
                data: { order_no: order },
                dataType: "JSON",
                beforeSend: function () {
                    $("#packing_id").prop("disabled", true);
                    $("#packing_id").append('<option value="" disabled selected>กำลังโหลดข้อมูล รอสักครู่...</option>');
                },
                success: function (res) {
                    console.log(res);
                    $("#packing_id").prop("disabled", false);
                    $("#packing_id").empty();
                    $("#packing_id").append('<option value="" disabled selected>Select Item Packing</option>');
                    res.forEach(function (item) {
                        $("#packing_id").append(`<option value="${item.PACKNO}">${item.PACKNO}</option>`);
                    });
                }
            });
        });


        $.ajax({
            type: "GET",
            url: host + "packing/pkc/get_report_cause",
            dataType: "JSON",
            success: function (res) {
                console.log(res);
                $("#reason").empty();
                $("#reason").append('<option value="" disabled selected>Select Reason</option>');
                res.forEach(function (item) {
                    $("#reason").append(`<option value="${item.RC_ID}">${item.CAUSE_NAME}</option>`);
                });
            }
        });

        $('#reason').change(function () {
            if ($(this).val() === '5') {
                $('#other_reason').removeClass('d-none').attr('required', true);
            } else {
                $('#other_reason').addClass('d-none').removeAttr('required').val('');
            }
        });

        $("#btn-submit").click(function () {
            const order = $("#order_id").val();
            const packing = $("#packing_id").val();
            const reason = $("#reason").val();
            const otherReason = $("#other_reason").val();

            if (!order) {
                $("#order_id").focus();
                return Swal.fire({
                    icon: 'warning',
                    title: 'ข้อมูลไม่ครบ',
                    text: 'กรุณาเลือก Order ID',
                });
            }

            if (!packing) {
                $("#packing_id").focus();
                return Swal.fire({
                    icon: 'warning',
                    title: 'ข้อมูลไม่ครบ',
                    text: 'กรุณาเลือก Packing ID',
                });
            }

            if (!reason) {
                $("#reason").focus();
                return Swal.fire({
                    icon: 'warning',
                    title: 'ข้อมูลไม่ครบ',
                    text: 'กรุณาเลือก Reason',
                });
            }

            if (reason === '5' && !otherReason) {
                $("#other_reason").focus();
                return Swal.fire({
                    icon: 'warning',
                    title: 'ข้อมูลไม่ครบ',
                    text: 'กรุณากรอก Reason',
                });
            }
            $.ajax({
                url: host + "packing/pkc/get_order_detail",
                type: "POST",
                data: { order: order, packing: packing },
                dataType: "json",
                beforeSend: function () {
                    Swal.fire({
                        title: 'กำลังโหลด...',
                        text: 'กรุณารอสักครู่',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function (res) {
                    Swal.close();
                    if (!res.data || res.data.length === 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'ไม่พบข้อมูล',
                            text: 'ไม่พบข้อมูล กรุณาตรวจสอบข้อมูลอีกครั้ง',
                        });
                    } else {

                        const data = res.data[0];
                        $.ajax({
                            url: host + "packing/pkc/chk_print",
                            type: "POST",
                            data: { order: order, packing: packing },
                            success: function (ress) {
                                if (ress === '2') {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'ไม่พบประวัติการปริ้น',
                                        text: 'ไม่พบประวัติการปริ้น VPS ORDER นี้ กรุณาปริ้น VPS ก่อน หรือ ติดต่อแผนก IS Tel:2033',
                                    });
                                    return;
                                } else {
                                    $("#containerQuantityManual").val('');
                                    $("#printModal").modal('show');
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error("Error in chk_print AJAX call:", status, error);
                                Swal.fire({
                                    icon: "error",
                                    title: "เกิดข้อผิดพลาด",
                                    text: "เกิดข้อผิดพลาดในการตรวจสอบการปริ้น กรุณาติดต่อแผนก IS Tel:2033",
                                });
                            }
                        });

                        $(document).on('click', '#submitButtonManual', function () {
                            const containerQuantity = $("#containerQuantityManual").val().trim();
                            const item = data.S01M04.slice(0, 3) + "-" + data.S01M04.slice(3, 5);
                            const project = data.S01M08;
                            const order_format = formatorderno(data.S01M01);
                            const partname = data.S01M05;
                            const dwg = data.S11M04;
                            const date = data.S11M08.slice(2, 4);
                            const prod = data.SCHEDULE + date;
                            const priority = data.M8K02;
                            const item_qty = '';

                            if (isNaN(containerQuantity) || containerQuantity <= 0) {
                                Swal.fire({
                                    icon: "error",
                                    title: "ข้อผิดพลาด",
                                    text: "กรุณากรอกจำนวนลังที่ถูกต้อง",
                                });
                                return;
                            }
                            // save_reprint_vps(order, packing, containerQuantity);
                            printbar(item, project, order_format, prod, partname, dwg, priority, containerQuantity, item_qty, function (error, result) {
                                if (error) {
                                    console.log(error);
                                    Swal.fire({
                                        icon: "error",
                                        title: "Error",
                                        text: "An error occurred while printing. Please try again.",
                                    });
                                } else {
                                    $("#printModal").modal('hide');
                                    insert_log_other(order, packing, containerQuantity, reason, otherReason);

                                    save_reprint_vps(order, packing, containerQuantity);


                                }
                            });
                        });
                    }
                }
            });
        });

    });

    function save_reprint_vps(order, packing, qty_print) {
        console.log(order, packing, qty_print);
        $.ajax({
            url: host + 'packing/PKC/get_order_detail',
            type: 'POST',
            data: {
                order: order,
                packing: packing,
            },
            beforeSend: function () {
                Swal.fire({
                    title: 'กำลังค้นหาข้อมูล...',
                    text: 'กรุณารอสักครู่',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function (res) {
                Swal.close();
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
                    beforeSend: function () {
                        Swal.fire({
                            title: 'กำลังบันทึกข้อมูล...',
                            text: 'กรุณารอสักครู่',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
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
                        Swal.close();
                        location.reload();
                    }
                })
            }
        });
    }
</script>
@endsection