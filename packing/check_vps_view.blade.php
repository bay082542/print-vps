@extends('layout/template_tailwind')

@section('styles')
    <style>
        #my_modal_1 {
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
    </style>
@endsection
@section('content')
    <div class="bg-base-200 flex items-center justify-center min-h-[calc(100vh-95px)]">
        <div class="card shadow-xl bg-white w-full max-w-md">
            <div class="card-body flex flex-col items-center justify-center">
                <h2 class="card-title text-primary">Scan QR CODE (VPS)</h2>
                <div class="form-control w-full max-w-xs">
                    <input id="barcode" type="text" class="input input-bordered input-primary text-center text-xl" placeholder="สแกน QR CODE หรือกรอกตัวเลข" autofocus autocomplete="off">

                </div>
                <button class="btn btn-success mt-4 justify-center" id="btn-search">Search</button>
                <div id="result" class="mt-6 text-center text-green-600 text-lg font-bold"></div>
            </div>


        </div>

    </div>
    
    <!-- <label for="pis-modal" class="btn btn-info">Test Modal</label> -->

    <button class="btn" onclick="my_modal_1.showModal()">open modal</button>
    <button onclick="openModalByJs()">เปิด modal ด้วย JS</button>
    <dialog id="my_modal_1" class="modal">
        <div class="modal-box w-11/12 max-w-6xl">
            <h3 class="text-lg font-bold mb-5 p-3 border-b-1">PIS</h3>           
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
                    <th rowspan="2" style="font-size:2.5rem;">
                        <label class="priority"></label>
                        <p class="date-p hidden"></p>
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
                    <th>REMARK</th>
                </tr>

            </table>
            <div class="modal-action">
                <form method="dialog">
                    <!-- if there is a button in form, it will close the modal -->
                    <button class="btn">Close</button>
                </form>
            </div>
        </div>
    </dialog>

@endsection

@section('scripts')
    <script src="{{ $_ENV['SCRIPTS'] }}check_vps.js?ver={{ date('Ymdhis') }}"></script>
    
    <script>
function openModalByJs() {
  document.getElementById('my_modal_1').showModal();
  // หรือ my_modal_1.showModal();
}
</script>
@endsection