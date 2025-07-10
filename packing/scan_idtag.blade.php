@extends('layout.template2')
@section('styles')
<style>
    .card {
        margin: 0 auto;
        height: 100vh;
    }
</style>
@endsection
@section('content')
<div class="loader-overlay">
    <div class="loader"></div>
</div>
<div class="card">
    <div class="card-header text-center">
        <h3>Scan ID Tag</h3>
    </div>
    <div class="card-body d-flex justify-content-center align-items-center main-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="id_tag">ID Tag</label>
                        <input type="text" class="form-control" id="id_tag" placeholder="Enter ID Tag" maxlength="11">
                    </div>
                    <button class="btn btn-primary w-100 mt-3" id="submit">Submit</button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body-details detail-section d-none mt-5">
        <div class="container" style="border:0.5px solid #B6B6B6; border-radius: 10px; padding: 20px;">
            <label for="control_no" style="font-size:10pt;">CONTROL NO : </label>&nbsp;<label id="control_no" style="font-weight: bold;"></label>
            <table class="table table-striped" id="table" style="width: 100%; margin-top: 20px;">
                <thead>
                    <tr>
                        <th scope="col">Order</th>
                        <th scope="col">Packing</th>
                        <th scope="col" style="text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- <tr>
                        <td>2</td>
                        <td>3</td>
                        <td>4</td>
                    </tr> -->
                </tbody>
            </table>
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
                        <label for="containerQuantitytag">กรอกจำนวนลังที่ต้องปริ้นสติกเกอร์</label>
                        <input type="number" class="form-control" id="containerQuantitytag" placeholder="Enter the quantity of containers">
                    </div>
                    <div id="itemsQuantityContainer" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <div class="check-item"></div>
                    <button type="button" class="btn btn-primary" id="submitButtontag">Submit</button>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection
@section('scripts')
<script src="<?php echo base_url(); ?>assets/dist/print/BrowserPrint-2.0.0.75.min.js"></script>
<Script src="<?php echo base_url(); ?>assets/script/issue_kanban/print_label_other.js?ver={{date('YmdHis')}}"></Script>
<script src="{{$GLOBALS['cdn']}}sweetalert2/js/sweetalert2@11.min.js"></script>
<script src="{{ base_url('assets/dist/js/print_vps_other.min.js') }}?ver={{ date('YmdHis') }}"></script>

<script>
    $(document).ready(function () {
        $('#submit').click(function () {
            var id_tag = $('#id_tag').val();
            get_idtag(id_tag);
        });



        $(document).on('click', '.print', function () {
            var order = $(this).attr('id-order');
            var packing = $(this).attr('id-packing');
            var id_tag = $('#control_no').text();
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
                success: function (data) {
                    Swal.close();
                    data = data.data[0];
                    const packing = data.S01M04;
                    const project = data.S01M08;
                    const format_order = formatorderno(data.S01M01);
                    const partname = data.S01M05.trim();
                    const dwg = data.S11M04;
                    const date = data.S11M08.slice(2, 4);
                    const prod = data.SCHEDULE + date;
                    const priority = data.M8K02;
                    const item_qty = '';
                    $('#printModal').modal('show');

                    $(document).off('click', '#submitButtontag');
                    $(document).on('click', '#submitButtontag', function () {
                        const containerQuantity = $("#containerQuantitytag").val().trim();
                        if (containerQuantity <= 0) {
                            Swal.fire({
                                icon: "error",
                                title: "ข้อผิดพลาด",
                                text: "กรุณากรอกจำนวนลังที่ถูกต้อง",
                            });
                            return;
                        }



                        printbar(packing, project, format_order, prod, partname, dwg, priority, containerQuantity, item_qty, function (error, result) {
                            if (error) {
                                Swal.fire({
                                    icon: "error",
                                    title: "ข้อผิดพลาด",
                                    text: "เกิดข้อผิดพลาดในการพิมพ์สติกเกอร์: " + error + " กรุณติดต่อแผนก IS Tel:2033",
                                });
                            } else {
                                insert_log_other(order, packing, containerQuantity);
                                var production = data.S11M08;
                                var item = data.S01M06.trim();
                                var project = data.S11M06;
                                var piscode = order.substring(1, 8) + packing;
                                var qty_print = containerQuantity;

                                save_print_vps(order, packing, containerQuantity);
                                // $.ajax({
                                //     url: host + "packing/PKC/insert_packingorder",
                                //     type: "POST",
                                //     data: {
                                //         order: order,
                                //         packing: packing,
                                //         production: production,
                                //         p: priority,
                                //         item: item,
                                //         partname: partname,
                                //         project: project,
                                //         sche: prod,
                                //         piscode: piscode,
                                //         qty_print: qty_print,
                                //     },
                                //     success: function (res) {
                                //         console.log(res);
                                //         get_idtag(id_tag);
                                //     },
                                // });
                                $('#printModal').modal('hide');
                                // location.reload();
                            }
                        });
                    });
                }
            });
        });


        function get_idtag(id_tag) {
            $("#containerQuantitytag").val('');
            console.log(id_tag);
            $.ajax({
                type: 'POST',
                url: host + 'packing/pkc/search_idtag',
                data: { id_tag: id_tag },
                dataType: 'json',
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
                success: function (data) {
                    Swal.close();
                    if (data.length === 0) {
                        alert('No data found');
                        return;
                    } else {
                        $('.main-section').addClass('d-none');
                        $('.detail-section').removeClass('d-none');
                    }

                    var table = $("#table tbody");
                    table.empty();
                    $.each(data, function (index, item) {
                        table.append("<tr><td>" + item.order + "</td><td>" + item.packing + "</td><td style='text-align:center;'><button id-order='" + item.order + "' id-packing='" + item.packing + "' class='btn btn-primary print'>Print</button></td></tr>");
                    });

                    $("#control_no").text(id_tag);
                    console.log(data);
                }
            });
        }

    });
</script>
@endsection