@extends('layout.template2')

@section('styles')
    <style>
        .card {
            height: 89vh;
        }

        .con {
            text-align: Center;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <!-- <h5 class="card-title">Scan PIS</h5> -->

            <div class="con">
                <div class="card-body form-container my-5">
                    <img class="mb-3" src="{{base_url('assets\images\barcode-reader.PNG')}}" alt="" style="width:300px; height:300px;">
                    <!-- <img class="mb-3" src="{{base_url('assets\images\barcode.gif')}}" alt="" style="width:300px; height:300px;"> -->

                    <h1 class="moving-text mt-4" id="scan-text">กรุณาสแกน PIS .</h1>

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
                            <label for="containerQuantityPIS">กรอกจำนวนลังที่ต้องปริ้นสติกเกอร์</label>
                            <input type="number" class="form-control" id="containerQuantityPIS" placeholder="Enter the quantity of containers">
                        </div>
                        <div id="itemsQuantityContainer" class="mt-3"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="check-item"></div>
                        <button type="button" class="btn btn-primary" id="submitButtonPIS">ตกลง</button>
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
        let dots = 1;
        const maxDots = 3; // จำนวนจุดสูงสุด
        const scanText = $('#scan-text')[0];

        setInterval(() => {
            dots = (dots % maxDots) + 1; // เพิ่มจุด
            scanText.textContent = 'กรุณาสแกน PIS ' + '.'.repeat(dots); // อัปเดตข้อความ
        }, 1000); // ทุก 1 วินาที

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: message,
            });
        }

        $(document).ready(function () {
            let scanValue = '';
            $(document).on('keydown', function (e) {
                if (e.key === 'Shift' || e.key === 'F12' || e.key === 'CapsLock' || e.key === 'F5' || e.key === 'Enter') {
                    // ถ้ากด Shift หรือ CapsLock ให้ข้าม
                    return;
                }
                const key = e.key;
                if (key === 'Enter') {
                    console.log('Enter อัตโนมัติจาก Scanner, ข้าม');
                    return;
                }
                if (!/^[a-zA-Z0-9]$/.test(key)) {
                    console.log('กดตัวไม่ใช่ตัวอักษร/ตัวเลข ข้าม');
                    Swal.fire({
                        icon: 'warning',
                        // title: 'Invalid Input',
                        text: 'กรุณาเปลี่ยนคีย์บอร์ดเป็นภาษาอังกฤษ',
                        allowEnterKey: false
                    });
                    return;
                }
                scanValue += e.key;
                if (scanValue.length == 12) {
                    const order = scanValue.substr(0, 7).toUpperCase();
                    const item = scanValue.substr(7, 5).toUpperCase();
                    console.log(order);
                    console.log(item);
                    Swal.fire({
                        title: 'กำลังโหลด...',
                        text: 'กรุณารอสักครู่',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });
                    $.ajax({
                        beforeSend: function () {
                            Swal.showLoading();
                        },
                        complete: function () {
                            Swal.close();
                        },
                        type: "POST",
                        url: host + "packing/pkc/get_pis",
                        data: { order: order, packing: item },
                        dataType: "JSON",
                        success: function (res) {
                            console.log(res);
                            if (res.length === 0) {
                                showError('เกิดข้อผิดพลาด (get_pis) กรุณาลองอีกครั้ง หรือ ติดต่อแผนก IS Tel:2033');
                            }
                            const chk_printt = chk_print(res[0].S01M01, res[0].S01M04).then((chk_print) => {
                                console.log(chk_print);
                                if (chk_print == 2) {
                                    console.log('print');
                                    $("#containerQuantityPIS").val('');

                                    (async () => {
                                        const { value: quantity } = await Swal.fire({
                                            title: "กรอกจำนวนลังที่ต้องปริ้นสติกเกอร์",
                                            input: "number",
                                            inputLabel: "Number of Quantity",
                                            inputPlaceholder: "Enter Number of Quantity.",
                                            inputAttributes: {
                                                required: "true",
                                                min: "1"
                                            },
                                            inputValidator: (value) => {
                                                if (!value || value <= 0) {
                                                    return "กรุณากรอกจำนวนลังที่ถูกต้อง!";
                                                }
                                            }
                                        });
                                        if (quantity) {
                                            print_sticker(res[0].S01M01, res[0].S01M04, quantity);
                                        }
                                    })()

                                } else {
                                    console.log('cannot');
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'พบมีประวัติการปริ้น VPS ORDER นี้แล้ว',
                                        text: 'กรุณาติดต่อแผนก IS Tel:2033',
                                    });
                                }
                            });
                        },
                        error: function (err) {
                            console.log(err);
                            showError('เกิดข้อผิดพลาด (get_pis) กรุณาลองอีกครั้ง หรือ ติดต่อแผนก IS Tel:2033');
                        }
                    });
                    scanValue = '';
                }
            });
        });



        // function chk_print(order, packing) {
        //     return new Promise((resolve, reject) => {
        //         $.ajax({
        //             url: host + "packing/pkc/chk_print",
        //             type: "post",
        //             async: false,
        //             data: {
        //                 order: order,
        //                 packing: packing,
        //             },
        //             success: function (res) {
        //                 console.log(res);
        //                 if (res == 1) {
        //                     resolve(1);
        //                 } else {
        //                     resolve(2);
        //                 }
        //             },
        //             error: function (err) {
        //                 reject(err);
        //             },
        //         });
        //     });
        // }

        function print_sticker(order, packing, qty_sticker) {
            $.ajax({
                type: "POST",
                url: host + "packing/pkc/get_order_detail",
                data: { order: order, packing: packing },
                dataType: "JSON",
                success: function (res) {

                    const data = res.data[0];
                    if (!data) {
                        showError('ไม่พบข้อมูล กรุณาลองอีกครั้ง หรือ ติดต่อแผนก IS Tel:2033');
                        return;
                    }
                    console.log(data);
                    const item = data.S01M04.slice(0, 3) + "-" + data.S01M04.slice(3, 5);
                    const project = data.S01M08;
                    const formattedOrder = formatorderno(data.S01M01);
                    const partname = data.S01M05;
                    const dwg = data.S11M04;
                    const date = data.S11M08.slice(2, 4);
                    const prod = data.SCHEDULE + date;
                    const priority = data.M8K02;
                    const item_qty = '';

                    console.log("Item:", item);
                    console.log("Project:", project);
                    console.log("Order:", formattedOrder);
                    console.log("Product:", prod);
                    console.log("Part Name:", partname);
                    console.log("Drawing:", dwg);
                    console.log("Date:", date);
                    console.log("Priority:", priority);
                    printbar(item, project, formattedOrder, prod, partname, dwg, priority, qty_sticker, item_qty, function (error, result) {
                        if (error) {
                            console.log(error);
                        } else {
                            $("#printModal").modal('hide');
                            insert_log_other(order, packing, qty_sticker);
                            save_print_vps(order, packing, qty_sticker);
                        }
                    })
                },
                error: function (err) {
                    Swal.close();
                    showError('เกิดข้อผิดพลาด (get_pis) กรุณาลองอีกครั้ง หรือ ติดต่อแผนก IS Tel:2033');
                }
            });
        }


    </script>
@endsection