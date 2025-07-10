@extends('layout.template2')
@section('styles')
<style>
    .card {
        height: 89vh;
    }

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
        /* margin-top: 30px; */
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
        width: 30%;
    }
</style>
@endsection
@section('content')

<div class="container mt-5" id="order_form_container">
    <div class="header">
        VPS Sticker
    </div>
    <div class="form-container">
        <div class="mb-4">
            <label for="order" class="form-label">Order :</label>
            <input type="text" class="form-control" id="order" placeholder="e.g. EOBU89023">
        </div>
        <!-- <div class="mb-4">
            <label for="packing" class="form-label">Item-Packing:</label>
            <input type="text" class="form-control" id="packing" placeholder="e.g. 29502">
        </div> -->
        <div class="mb-4">
            <label for="containerQuantityManual" class="form-label">จำนวนลังที่ต้องปริ้นสติกเกอร์:</label>
            <input type="number" class="form-control" id="containerQuantityManual" placeholder="จำนวนสติ๊กเกอร์">
        </div>
        <div class="text-center">
            <button class="btn btn-primary" id="btn-submit-order">Submit</button>
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
        const orderFormContainer = $("#order_form_container");
        orderFormContainer.find("#btn-submit-order").click(function () {
            const order = $("#order").val();
            const packing = localStorage.getItem('selectedPackingNo');
            // const packing = $("#packing").val();
            const containerQuantity = $("#containerQuantityManual").val().trim();

            if (!order || !packing || !containerQuantity) {
                Swal.fire({
                    icon: "warning",
                    title: "ข้อมูลไม่ครบ",
                    text: "กรุณากรอกข้อมูลให้ครบ",
                });
                return;
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
                    if (res.data.length === 0) {
                        Swal.fire({
                            icon: "error",
                            title: "ข้อผิดพลาด",
                            text: "ไม่พบข้อมูล กรุณาตรวจสอบข้อมูลอีกครั้ง",
                        });
                    } else {

                        const data = res.data[0];
                        $.ajax({
                            url: host + "packing/pkc/chk_print",
                            type: "POST",
                            data: { order: order, packing: packing },
                            success: function (ress) {
                                console.log(ress);
                                if (ress == 1) {
                                    Swal.fire({
                                        icon: "error",
                                        title: "ข้อผิดพลาด",
                                        text: "พบมีประวัติการปริ้น VPS ORDER นี้แล้ว กรุณาติดต่อแผนก IS Tel:2033",
                                    });
                                    return;
                                } else {
                                    const item = data.S01M04.slice(0, 3) + "-" + data.S01M04.slice(3, 5);
                                    const project = data.S01M08;
                                    const order2 = formatorderno(data.S01M01);
                                    const partname = data.S01M05;
                                    const dwg = data.S11M04;
                                    const date = data.S11M08.slice(2, 4);
                                    const prod = data.SCHEDULE + date;
                                    const priority = data.M8K02;
                                    const item_qty = '';

                                    printbar(item, project, order2, prod, partname, dwg, priority, containerQuantity, item_qty, function (error, result) {
                                        if (error) {
                                            Swal.fire({
                                                icon: "error",
                                                title: "ข้อผิดพลาด",
                                                text: "เกิดข้อผิดพลาดในการพิมพ์สติกเกอร์: " + error + " กรุณติดต่อแผนก IS Tel:2033",
                                            });
                                        } else {
                                            insert_log_other(order, packing, containerQuantity);
                                            save_print_vps(order, packing, containerQuantity);
                                        }
                                    });
                                }
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                Swal.fire({
                                    icon: "error",
                                    title: "ข้อผิดพลาด",
                                    text: "เกิดข้อผิดพลาดในการตรวจสอบการปริ้น: " + textStatus + ' : ' + errorThrown,
                                });
                            }
                        });
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log("AJAX error: " + textStatus + ' : ' + errorThrown);
                }
            });
        });
    });
</script>
@endsection