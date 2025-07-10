@extends('layout/template')

@section('content')
<div class="d-flex justify-content-center d-none" id="loader">
    <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center bg-secondary text-white">
                    Print Manual
                </div>
                <div id="show_data" class="d-none" style="width:100% ; min-height:0px;color:white"></div>
                <div class="card-body">
                    <!-- <div class="form-group">
                        <label for="">Production</label>
                        <input type="text" name="prod" class="form-control prod" placeholder="Ex. 08A">
                    </div>
                    <div class="form-group mt-3">
                        <label for="">P</label>
                        <input type="text" name="jun" class="form-control jun" placeholder="Ex. P2">
                    </div> -->
                    <div class="form-group mt-3">
                        <label for="">Item</label>
                        <input type="text" name="item" class="form-control item" placeholder="Ex. 12588">
                    </div>
                    <div class="form-group mt-3">
                        <label for="">Order</label>
                        <input type="text" name="order" class="form-control order" placeholder="Ex. EXIX06105">
                    </div>
                    <button id="submit" class="btn btn-primary btn-block mt-3 w-100">submit</button>
                </div>
            </div>
        </div>
    </div>
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
                        <th style="width:40%:">ORDER NAME</th>
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
<script src="<?php echo base_url(); ?>assets/dist/print/BrowserPrint-2.0.0.75.min.js"></script>
<Script src="<?php echo base_url();?>assets/script/issue_kanban/print_label.js?ver={{date('YmdHis')}}"></Script>
<script>
    $(document).ready(function () {
        $('#openModalButton').on('click', function () {
            $('#printModal').modal('show');
        });

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
            $(".check-item").html(`
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault1" value="1">
                    <label class="form-check-label" for="flexRadioDefault1">
                        พบความผิดปกติของชิ้นงาน (พบสนิม,รอยแตกหักเสียหาย,บิดงอ)
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="flexRadioDefault" id="flexRadioDefault2" value="2">
                    <label class="form-check-label" for="flexRadioDefault2">
                        <b style="color:red;">ไม่</b>พบความผิดปกติของชิ้นงาน (<b style="color:red;">ไม่</b>พบสนิม,รอยแตกหักเสียหาย,บิดงอ)
                    </label>
                </div>
                `);
        });
    });
    $("#submit").click(function () {
        var prod = $(".prod").val();
        var jun = $(".jun").val();
        var item = $(".item").val();
        var order = $(".order").val();

        console.log(prod, jun, item, order);

        $.ajax({
            url: host + 'packing/PKC/get_order_detail',
            type: 'POST',
            data: {
                order: order,
                packing: item,
            },
            beforeSend: function () {
                $("#loader").removeClass('d-none');
            },
            success: function (res) {
                var data = JSON.parse(res).data[0];

                var detail = JSON.parse(res);
                console.log(detail.data[0]);
                console.log(data.S01M08);
                $(".order-name").text(data.S01M08);
                $(".order-no").text(formatorderno(data.S01M01));
                $(".packing-no").text(data.S01M04.slice(0, 3) + "-" + data.S01M04.slice(3, 5));
                $(".item").text(data.S01M06);
                $(".schedule").text(data.SCHEDULE);
                $(".priority").text(data.M8K02);
                $(".main-part-name").text(data.S11M05);
                $('.part-name').text(data.S11M06);
                $(".model").text(data.S01M07);
                $(".f-date").text(formatdate(data.S01M14));
                $(".p-date").text(formatdate(data.S01M15));
                $(".date-p").text(data.S11M08);

                $(".detail-table .order-detail").remove();
                for (let i = 0; i < 15; i++) {
                    let currentDetail = detail.data[i] || {};
                    $(".detail-table").append(`
                        <tr class="order-detail">
                            <td>${i + 1}</td>
                            <td>${currentDetail.S11M05 ? currentDetail.S11M05 : ''}</td>
                            <td>${currentDetail.S11M04 ? currentDetail.S11M04 + '<input type="hidden" class="dw_no" value="' + currentDetail.S11M04 + '">' : ''}</td >
                            <td style="width:8%;">${currentDetail.S11M09 ? currentDetail.S11M09 + '<input type="hidden" class="qty" value="' + currentDetail.S11M09 + '">' : ''}</td>
                            <td style="padding:0;width:8%;">${currentDetail.S11M09 ? '<input class="form-control con_qty" style="height:8mm; color:red; border:1px solid red;" value="' + (currentDetail.CON_QTY ? currentDetail.CON_QTY : '') + '">' : ''}</td>
                            <td style="padding:0;">${currentDetail.S11M09 ? '<input class="form-control remark" style="height:8mm; color:red;" value="' + (currentDetail.REMARK ? currentDetail.REMARK : '') + '">' : ''}</td>
                        </tr >
                    `);
                }

                // console.log(res);
                $('#exampleModal').modal('show');
            },
            complete: function () {
                $("#loader").addClass('d-none');
            }
        })
    });
    $('.save-btn').click(async function () {
        const order_no = $('.order-no').text().trim();
        const packing_no = $(".packing-no").text().trim();
        const project = $(".order-name").text().trim();
        const priority = $(".priority").text().trim();
        const part = $(".main-part-name").text().trim();
        const date = $(".date-p").text().slice(2, 4).trim();
        const prod = $(".schedule").text().trim() + date;
        const dw_print = $('.part-name').text().trim();

        const arr_qty = getValuesFromClass(".qty");
        const arr_dw = getValuesFromClass(".dw_no");
        const arr_con = getValuesFromClass(".con_qty");
        const arr_remark = getValuesFromClass(".remark");

        $('#exampleModal').modal('hide');
        const chk_printtt = await chk_print(order_no.replace(/\s/g, '').replace(/-/g, ''), packing_no.replace(/-/g, ''));
        console.log(chk_printtt);
        if (chk_printtt === 2) {
            if (!validateConQty(arr_con)) {
                alert('กรุณากรอกจำนวน Con. QTY');
                return;
            }

            if (allQuantitiesMatch(arr_qty, arr_con)) {
                showPrintModal(order_no, packing_no, project, prod, priority, part, arr_qty, arr_dw, arr_con, arr_remark, dw_print);
            } else {
                insertOrderDetail(order_no, packing_no, project, prod, priority, part, arr_qty, arr_dw, arr_con, arr_remark, dw_print);
            }
        } else {
            alert('พบมีประวัติการปริ้น VPS ORDER นี้แล้ว กรุณาติดต่อแผนก IS');
        }
    });


    function getValuesFromClass(className) {
        const values = [];
        $(className).each(function () {
            values.push($(this).val().trim());
        });
        return values;
    }

    function validateConQty(arr_con) {
        let isValid = true;
        $.each(arr_con, function (index, val) {
            if (!val) {
                isValid = false;
                return false;  // break out of $.each loop
            }
        });
        return isValid;
    }

    function allQuantitiesMatch(arr_qty, arr_con) {
        let chk_qty = 0;
        $.each(arr_qty, function (index, val) {
            if (val === arr_con[index]) {
                chk_qty++;
            }
        });
        return chk_qty === arr_qty.length;
    }

    function showPrintModal(order_no, packing_no, project, prod, priority, part, arr_qty, arr_dw, arr_con, arr_remark, dw_print) {
        $("#printModal").modal('show');
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
            if (!itemsQuantities.valid) {
                Swal.fire({
                    icon: 'error',
                    title: 'ข้อผิดพลาด',
                    text: 'กรุณากรอกจำนวนชิ้นที่ถูกต้องสำหรับแต่ละใบ'
                });
                return;
            }

            const checkItem = $("input[name='flexRadioDefault']:checked").val();
            if (!checkItem) {
                Swal.fire({
                    icon: 'error',
                    title: 'ข้อผิดพลาด',
                    text: 'กรุณาเลือกความผิดปกติของชิ้นงาน'
                });
                return;
            }

            insertOrderDetail(order_no, packing_no, project, prod, priority, part, arr_qty, arr_dw, arr_con, arr_remark, dw_print, checkItem, containerQuantity, itemsQuantities.values);
            $('#containerQuantity').val('');
            $('#itemsQuantityContainer').empty();
            $('#printModal').modal('hide');
        });
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

    function insertOrderDetail(order_no, packing_no, project, prod, priority, part, arr_qty, arr_dw, arr_con, arr_remark, dw_print, checkItem = null, qty_sticker = null, itemsQuantities = null) {
        const data = {
            order_no: order_no,
            packing_no: packing_no,
            project: project,
            prod: prod,
            priority: priority,
            part: part,
            qty: arr_qty,
            dw: arr_dw,
            con_qty: arr_con,
            remark: arr_remark,
            checkItem: checkItem
        };

        if (qty_sticker !== null) {
            data.qty_sticker = qty_sticker;
        }

        $.ajax({
            url: host + 'packing/PKC/insert_order_detail',
            type: "POST",
            data: data,
            success: function (res) {
                console.log(res);
                // order_table();

                if (qty_sticker !== null) {
                    setTimeout(() => {
                        const IP = printbar(packing_no, project, order_no, prod, part, dw_print, priority, qty_sticker, itemsQuantities);
                        const order = order_no.replace(/\s/g, '').replace(/-/g, '');
                        const packing = packing_no.replace(/-/g, '');
                        insertPrintLog(order, packing, IP, qty_sticker, itemsQuantities);
                        save_print_vps(order, packing, qty_sticker);
                    }, 500);
                }
            },
            error: function (error) {
                console.log(error);
            }
        });
    }

    function insertPrintLog(order, packing, IP, qty, itemsQuantities) {
        $.ajax({
            url: host + 'packing/PKC/insert_print_log',
            type: 'POST',
            data: {
                order_no: order,
                packing_no: packing,
                printer: IP,
                qty: qty,
                qty_item: itemsQuantities,
            },
            success: function (res) {
                console.log('success');
            }
        });
    }

    function order_table() {
        var text = $(".input_qr").val();
        var txt_arr = text.split('|');
        var issueNos = [];

        $.each(txt_arr, function (key, txt) {
            issueNos.push(txt.substring(0, 10));
        });

        console.log(issueNos);

        // Send all issue numbers in one request
        $.ajax({
            url: host + 'packing/PKC/get_detail_issue_batch',
            type: 'post',
            data: { issue_nos: issueNos },
            beforeSend: function () {
                $("#loading_waiting").removeClass('d-none');
            },
            success: function (res) {
                $(".div_table").removeClass('d-none');
                $(".containerrr").addClass('d-none');
                // console.log(res);
                var data = JSON.parse(res);
                console.log(data);
                $('#orderTable tbody').empty();
                // Assuming 'data' is an array of order details
                $.each(data.data, function (index, orderDetails) {
                    // var orderDetails = orderDetailsArray[index];

                    // Append each order detail to the table
                    $('#orderTable tbody').append(
                        '<tr>' +
                        '<td>' + orderDetails.J2ODR + '</td>' +
                        '<td><a class="btn_modal" data_order="' + orderDetails.S01ORD + '" data_packing="' + orderDetails.S01M04 + '">' + formatorderno(orderDetails.S01ORD) + '</a></td>' +
                        '<td>' + orderDetails.S01M04.slice(0, 3) + "-" + orderDetails.S01M04.slice(3, 5) + '</td>' +
                        '<td>' + orderDetails.S01M08 + '</td>' +
                        // '<td>' + orderDetails.J2RQTY + '</td>' +
                        '</tr>');
                });
            },
            complete: function () {
                $("#loading_waiting").addClass('d-none');
                // location.reload();
            }
        });
    }

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
                    url: host + 'packing/PKC/insert_packingorder',
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
                    },
                    error: function (err) {
                        console.log(err);
                    }
                })
            }
        });
    }

    function chk_print(order, packing) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: host + "packing/pkc/chk_print",
                type: 'post',
                data: { order: order, packing: packing },
                success: function (res) {
                    console.log('res' + res);
                    if (res == 1) {
                        resolve(1);
                    } else {
                        resolve(2);
                    }
                },
                error: function (err) {
                    reject(err);
                }
            });
        });
    }


    function formatorderno(order) {
        var orderno = order.slice(0, 1) + '-' + order.slice(1, 3) + ' ' + order.slice(3, 8) + '-' + order.slice(8, 9);
        return orderno;
    }

    function formatdate(date) {
        var formattedDate = date.slice(6, 8) + '-' + date.slice(4, 6);
        return formattedDate;
    }
</script>

@endsection

@section('styles')
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

    .form-check .form-check-label {
        font-size: 15px;
    }
</style>
@endsection