@extends('layout.template2')
@section('styles')
<style>
    body {
        background-color: #f0f0f0;
    }

    .container {
        margin-top: 20px;
    }

    .table thead th {
        background-color: #007bff;
        color: white;
    }

    .table tbody td {
        background-color: white;
    }

    .form-container {
        background-color: white;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }
</style>
@endsection
@section('content')
<div class="container">
    <h2 class="mb-4">RE-PRINT PERMISSION</h2>
    <div class="row">
        <div class="col-md-8">
            <table class="table table-bordered" id="table-permission">
                <thead>
                    <tr>
                        <!-- <th>ID</th> -->
                        <th>EmpCode</th>
                        <th>Packing-Item</th>

                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
        <div class="col-md-4">
            <div class="form-container">

                <div class="mb-3">
                    <label for="EmpCode" class="form-label">EmpCode</label>
                    <!-- <input type="text" class="form-control" id="EmpCode"> -->
                    <select name="" id="EmpCode" class="form-select">
                        <option value="">--กรุณาเลือก--</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="Packing" class="form-label">Packing-Item</label>
                    <select name="" id="Packing" class="form-select">
                        <option value="">--กรุณาเลือก--</option>
                    </select>
                </div>
                <!-- <div class="mb-3">
                        <label for="groupHomePage" class="form-label">Print Permission</label>
                        <input type="text" class="form-control" id="groupHomePage">
                    </div> -->
                <button type="submit" id="add-btn" class="btn btn-primary">Add</button>

            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')


<script>
    $(document).ready(function () {
        $("#EmpCode").select2();
        $("#Packing").select2();
        $("#table-permission").DataTable();

        get_dataTable();
        $.ajax({
            type: "GET",
            url: "get_user",
            dataType: "JSON",
            success: function (response) {
                var EmpCode = $("#EmpCode");
                console.log(response);
                response.forEach(item => {
                    EmpCode.append('<option value="' + item.SEMPNO + '">' + item.SEMPNO + ' - ' + item.STNAME + '</option>');
                });
            }
        });

        $.ajax({
            type: "GET",
            url: "get_pckodr",
            dataType: "JSON",
            success: function (response) {
                var packing = $("#Packing");
                console.log(response);
                response.forEach(item => {
                    packing.append('<option value="' + item.PACKNO + '">' + item.PACKNO + '</option>');
                });
            }
        });


    });

    function get_dataTable() {
        $.ajax({
            type: "GET",
            url: "get_permission",
            dataType: "JSON",
            success: function (res) {
                const table = $("#table-permission tbody");
                let rows = '';
                table.empty();

                res.forEach(item => {
                    rows += '<tr>' +
                        '<td>' + item.EMPCODE + '</td>' +
                        '<td>' + item.PACKING + '</td>' +
                        '</tr>';
                })

                table.append(rows);
            }
        });
    }
    $("#add-btn").click(function () {
        const empcode = $("#EmpCode").val();
        const packing = $("#Packing").val();

        $.post("insert_permission", { empcode, packing }, function (response) {
            if (response == 2) {
                alert("ข้อมูลซ้ำ! กรุณาตรวจสอบ");
            } else if (response == 1) {
                get_dataTable();
            } else {
                alert("เกิดข้อผิดพลาด กรุณาลองอีกครั้ง");
            }
        });
    });

</script>
@endsection