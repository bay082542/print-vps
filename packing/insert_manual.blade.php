@extends('layout/template')

@section('content')
    order:
    <input type="text" class="order"><br>
    packing:
    <input type="text" class="packing"><br>
    qty print:
    <input type="text" class="qty"><br>
    User :
    <input type="text" class="user_print">
    <button class="submit">submit</button>


    <P>-------------------------------------------------------------------------------------</P>
    <label>re-print</label><br>
    order:
    <input type="text" class="order_reprint"><br>
    packing:
    <input type="text" class="packing_reprint"><br>
    qty print:
    <input type="text" class="qty_reprint">
    <button class="submit_reprint">submit</button>

    <button onclick="connectAndPrint()">เชื่อมต่อและพิมพ์ผ่าน USB</button>
@endsection

@section('scripts')
    <script src="{{$GLOBALS['cdn']}}sweetalert2/js/sweetalert2@11.min.js"></script>
    <script>

        $('.submit').click(function () {
            const order = $('.order').val();
            const packing = $('.packing').val();
            const qty = $('.qty').val();

            save_print_vps(order, packing, qty);
        })

        $(".submit_reprint").click(function () {
            const order = $(".order_reprint").val();
            const packing = $(".packing_reprint").val();
            const qty = $(".qty_reprint").val();

            save_reprint_vps(order, packing, qty);
        });

        function save_print_vps(order, packing, qty_print) {
            $.ajax({
                url: host + "packing/PKC/get_order_detail",
                type: "POST",
                data: {
                    order: order,
                    packing: packing,
                },
                beforeSend: function () {
                    Swal.fire({
                        title: "กำลังบันทึกข้อมูล...",
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
                    var user = $(".user_print").val();

                    $.ajax({
                        url: host + "packing/PKC/insert_packingorder",
                        type: "POST",
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
                            qty_print: qty_print,
                            user: user
                        },
                        success: function (res) {
                            console.log(res);
                            location.reload();
                        },
                    });
                },
            });
        }

        function save_reprint_vps(order, packing, qty_print) {
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
                        },
                        error: function (res) {
                            console.log(res);
                        }
                    })
                }
            });
        }
    </script>
    <button onclick="printSewoo()">พิมพ์ผ่าน Sewoo</button>

    <script src="https://cdn.qz.io/latest/qz-tray.js"></script>
    <script>
        function printSewoo() {
            qz.websocket.connect().then(() => {
                const config = qz.configs.create("Sewoo LK-P30II"); // ตรวจสอบชื่อใน Devices & Printers
                const data = [
                    '\x1B\x40', // Initialize
                    '\x1B\x61\x01', // center align
                    'ร้านเบย์ ซูเปอร์\n',
                    '-----------------------------\n',
                    '\x1B\x61\x00', // left align
                    'สินค้า         x2    100.00\n',
                    'สินค้า B       x1     50.00\n',
                    '-----------------------------\n',
                    'รวมทั้งสิ้น           150.00\n',
                    '\n',
                    'ขอบคุณที่ใช้บริการค่ะ\n',
                    '\n\n',
                    '\x1D\x56\x41' // Full cut
                ];

                return qz.print(config, data);
            }).then(() => {
                alert("พิมพ์เรียบร้อย");
            }).catch(err => alert("เกิดข้อผิดพลาด: " + err));
        }
    </script>
@endsection