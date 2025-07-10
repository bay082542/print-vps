@extends('layout/template')

@section('styles')
<style>
    .header {
        background: #1a237e;
        color: #fff;
        padding: 24px;
        font-size: 24px;
        font-weight: 600;
        border-radius: 12px 12px 0 0;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .form-container {
        background-color: #fff;
        padding: 32px;
        border-radius: 0 0 12px 12px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 6px;
    }

    .submit-btn {
        background: linear-gradient(90deg, #1565c0, #0d47a1);
        color: white;
        border: none;
        padding: 14px;
        font-weight: 600;
        border-radius: 10px;
        width: 100%;
        transition: background 0.25s ease-in-out;
    }

    .submit-btn:hover {
        background: #0b3c91;
    }

    .container-menu {
        min-height: 80vh;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        padding-top: 40px;
        padding-bottom: 40px;
    }

    .card {
        cursor: pointer;
        width:250px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        text-align: center;
        min-height: 260px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 10px;
    }

    .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        background-color: #f0f0f0;
    }

    .card-title {
        font-size: 24pt;
    }

    .container {
        max-width: 600px;
        width: 90%;
        margin: auto;
    }

    @media (max-width: 768px) {
        .card-title {
            font-size: 20pt;
        }

        .header {
            font-size: 20pt;
        }

        .form-container {
            padding: 24px;
        }
    }
</style>

@endsection

@section('content')

<div class="container-menu mb-5">
    <div class="" style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); display: flex; align-items: center;  margin-bottom: 80px;">
        <label for="item_packing" class="form-label" style="margin-right: 10px;">PACKING NO:</label>
        <label class="form-label packing_local" for="" style="font-size:20pt;"></label>&emsp;
        <select id="item_packing" name="item_packing" class="form-select d-none" style=" text-align:center; width: 120px; flex-grow: 1;" required>
            <!-- <option value="">Select...</option> -->
        </select>
        <button class="btn btn-primary" id="change_packing">change</button>
    </div>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-auto g-4 menu-container">
        <div class="col">
            <div class="card" id="idtag" style="height:300px;">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <h5 class="card-title">ID-Tag</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card" id="pis" style="height:300px;">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <h5 class="card-title">SCAN PIS</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card" id="jun" style="height:300px;">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <h5 class="card-title">JUN<br> P<br> ORDER</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card" id="manual_order" style="height:300px;">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <h5 class="card-title">Manual Order</h5>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card" id="Issue_card" style="height:300px;">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <h5 class="card-title">Issue Card</h5>
                </div>
            </div>
        </div>
        @php
            $author = ['06127','07214','13285'];
        @endphp   
        @if(($_SESSION['user']->SDIVCODE == '060101' && $_SESSION['user']->SPOSCODE <= '50' && $_SESSION['user']->SPOSCODE >= '30') || $_SESSION['user']->SSECCODE == '050604' || in_array($_SESSION['user']->SEMPNO, $author)) 
            <div class="col">
                <div class="card" id="reprint" style="height:300px;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <h5 class="card-title">RE-PRINT</h5>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
<div class="container mt-5 d-none" id="jun_p">
    <div class="header">
        VPS Sticker
    </div>
    <div class="form-container">
        <form action="{{base_url('packing/pkc/list_order')}}" id="form-list" method="POST" class="form-section">

            <div class="mb-4">
                <label for="packing_no" class="form-label">Select PACKING NO:</label><br>
                <select id="packing_no" name="packing_no" class="form-control" style="width:100%;" required>
                    <option value="">Select...</option>
                    @foreach ($packNo as $pack)
                        <option value="{{ $pack->PACKNO }}">{{ $pack->PACKNO }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="schedule" class="form-label">Select P:</label><br>
                <select id="schedule" name="schedule" class="form-select" style="width:100%;">
                    <option value="">Select...</option>
                    @for ($i = 1; $i <= 4; $i++)
                        <option value="P{{$i}}">P{{ $i }}</option>
                    @endfor
                </select>
            </div>

            <div class="mb-4">
                <label for="selectJun" class="form-label">Select JUN:</label>

                <div class="col">
                    <!-- <input type="month" id="date_jun" name="date_jun" class="form-control" required> -->
                    <select name="date_jun" id="date_jun" class="form-select" style="width:100%;">
                        <option value="">--select jun--</option>
                    </select>
                </div>
            </div>



            <div style="text-align:center;">
                <button class="btn btn-success">ยืนยัน</button>
                <button class="btn btn-primary btn-back">ย้อนกลับ</button>
            </div>
            <!-- <button type="submit" class="submit-btn">Submit</button> -->
        </form>
    </div>
</div>

<div class="container d-none" id="scan_pis">
    <div class="card">
        <div class="card-header">
            Scan PIS
        </div>
        <div class="card-body">
            test
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $("#packing_no").select2();
        $("#date_jun").select2();
        $("#schedule").select2();
        // $("#item_packing").select2();
        const selectedPackingNo = localStorage.getItem('selectedPackingNo');
        if (selectedPackingNo) {
            const formattedPackNo = selectedPackingNo.slice(0, 3) + '-' + selectedPackingNo.slice(3);
            $(".packing_local").text(formattedPackNo);
        } else {
            $(".menu-container").addClass("d-none");
            $("#item_packing").removeClass("d-none");
            $("#change_packing").addClass("d-none");
        }


        fetch('{{base_url('packing/pkc/get_pckodr')}}')
            .then(response => response.json())
            .then(data => {
                $("#item_packing").append(`<option value="">Select...</option>`);
                data.forEach(item => {
                    const formattedPackNo = item.PACKNO.slice(0, 3) + '-' + item.PACKNO.slice(3);
                    $("#item_packing").append(`<option value="${item.PACKNO}">${formattedPackNo}</option>`);
                });

                if (selectedPackingNo) {
                    $("#item_packing").val(selectedPackingNo);
                }
            });

        $("#item_packing").change(function () {
            const packNo = $(this).val();
            if (packNo) {
                localStorage.setItem('selectedPackingNo', packNo);
                const formattedPackNo = packNo.slice(0, 3) + '-' + packNo.slice(3);
                $(".packing_local").text(formattedPackNo);
                $(".menu-container").removeClass("d-none");
                $("#item_packing").toggleClass('d-none');
                $("#change_packing").toggleClass('d-none');
                $(".packing_local").removeClass('d-none');
            } else {
                $(".menu-container").addClass("d-none");
            }
        });

        $("#change_packing").click(function () {
            const selectedPackingNo = localStorage.getItem('selectedPackingNo');
            const formattedPackNo = selectedPackingNo.slice(0, 3) + '-' + selectedPackingNo.slice(3);
            $("#item_packing").toggleClass('d-none');
            $(".packing_local").toggleClass('d-none');
            $("#change_packing").toggleClass('d-none');
            $(".packing_local").text(formattedPackNo);
        });
        // $("#schedule").addClass('form-select');


        // $.ajax({
        //     type: "GET",
        //     url: host + 'packing/PKC/get_calendar',
        //     dataType: "JSON",
        //     success: function (response) {

        //         console.log(response);

        //         $.each(response, function (index, value) {
        //             console.log(value.SCHDMFG);
        //         });
        //         // $("#date_jun").append()
        //     }
        // });

        $("#schedule").change(function () {
            const val = $(this).val();
            $("#date_jun").empty();
            $.ajax({
                type: "GET",
                url: host + 'packing/PKC/get_calendar',
                dataType: "JSON",
                success: function (response) {
                    $("#date_jun").append('<option>--select jun--</option>');
                    $.each(response, function (index, value) {
                        if (value.PRIORITY === val) {
                            // console.log(value);
                            $("#date_jun").append('<option value="' + value.SCHDMFG + '">' + value.SCHDMFG + '</option>');
                        }
                    });
                }
            });
        });
    });

    $("#form-list").submit(function () {
        $(".submit-btn").prop('disabled', true);

        // Change the button text to 'loading....'
        $(".submit-btn").text('loading....');

    });

    $("#jun").click(function () {
        // const selectedPackingNo = localStorage.getItem('selectedPackingNo');
        // $("#jun_p").removeClass('d-none');
        // $(".container-menu").addClass("d-none");
        // $("#packing_no").val(selectedPackingNo).trigger('change');
        window.location.href = 'list_order';
    });

    $("#pis").click(function () {
        window.location.href = 'scan_pis';
    });

    $("#manual_order").click(function () {
        window.location.href = 'manual_order';
    });

    $("#reprint").click(function () {
        window.location.href = 'reprintPackingOrder';
    });

    $("#idtag").click(function () {
        window.location.href = 'scan_idtag';
    });

    $("#Issue_card").click(function(){
        window.location.href = './';
    });



    $(".btn-back").click(function (e) {
        e.preventDefault();
        $("#jun_p").toggleClass('d-none');
        $(".container-menu").toggleClass('d-none');
    });


</script>
@endsection