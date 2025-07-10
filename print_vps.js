$(document).ready(function () {
  // printbar('203-89', 'MET-V250018', 'E-T901U63-0', '25/03Y', 'SEMICONDUCTOR', 'KCR-945B', 'P1', '1', '1');
  $("#openModalButton").on("click", function () {
    $("#printModal").modal("show");
  });

  $("#containerQuantity").on("keyup", function () {
    var containerQuantity = $(this).val();
    var itemsQuantityHtml = "";
    for (var i = 0; i < containerQuantity; i++) {
      itemsQuantityHtml += `
        <div class="form-group mt-3">
          <label for="itemQuantity${i}">กรอกจำนวนชิ้นสำหรับใบที่ ${i + 1}</label>
          <input type="number" class="form-control" id="itemQuantity${i}" placeholder="Enter the quantity of items for label ${i + 1}">
        </div>
      `;
    }
    $("#itemsQuantityContainer").html(itemsQuantityHtml);
  });

  $(".btn_submit").click(function () {
    order_table();
  });
  $(document).on("click", ".btn_modal", function () {
    var order = $(this).attr("data_order");
    var packing = $(this).attr("data_packing");
    $.ajax({
      url: host + "packing/PKC/get_order_detail",
      type: "POST",
      data: {
        order: order,
        packing: packing,
      },
      beforeSend: function () {
        $("#loader").removeClass("d-none");
      },
      success: function (res) {
        var data = JSON.parse(res).data[0];

        var detail = JSON.parse(res);
        // console.log(detail.data[0]);
        // console.log(data.S01M08);
        $(".order-name").text(data.S01M08);
        $(".order-no").text(formatorderno(data.S01M01));
        $(".packing-no").text(data.S01M04.slice(0, 3) + "-" + data.S01M04.slice(3, 5));
        $(".item").text(data.S01M06);
        $(".schedule").text(data.SCHEDULE);
        $(".priority").text(data.M8K02);
        $(".main-part-name").text(data.S11M05);
        $(".part-name").text(data.S11M06);
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
                        <td>${currentDetail.S11M05 ? currentDetail.S11M05 : ""}</td>
                        <td>${currentDetail.S11M04 ? currentDetail.S11M04 + '<input type="hidden" class="dw_no" value="' + currentDetail.S11M04 + '">' : ""}</td >
                        <td style="width:8%;">${currentDetail.S11M09 ? currentDetail.S11M09 + '<input type="hidden" class="qty" value="' + currentDetail.S11M09 + '">' : ""}</td>
                        <td style="padding:0;width:8%;">${currentDetail.S11M09 ? '<input type="number" class="form-control con_qty" style="height:8mm; color:red; border:1px solid red;" value="' + (currentDetail.CON_QTY ? currentDetail.CON_QTY : "") + '">' : ""}</td>
                        <td style="padding:0;">${currentDetail.S11M09 ? '<input class="form-control remark" style="height:8mm; color:red;" value="' + (currentDetail.REMARK ? currentDetail.REMARK : "") + '">' : ""}</td>
                    </tr >
                `);
        }

        // console.log(res);
        // $('#exampleModal').modal('show');

        // $("#Qc_Check").modal("show");
        $("#exampleModal").modal("show");
      },
      complete: function () {
        $("#loader").addClass("d-none");
      },
    });
  });

  $(".save-btn").click(async function () {
    const order_no = $(".order-no").text().trim();
    const packing_no = $(".packing-no").text().trim();
    const project = $(".order-name").text().trim();
    const priority = $(".priority").text().trim();
    const part = $(".main-part-name").text().trim();
    const date = $(".date-p").text().slice(2, 4).trim();
    const prod = $(".schedule").text().trim() + date;
    const dw_print = $(".part-name").text().trim();

    const arr_qty = getValuesFromClass(".qty");
    const arr_dw = getValuesFromClass(".dw_no");
    const arr_con = getValuesFromClass(".con_qty");
    const arr_remark = getValuesFromClass(".remark");

    $("#exampleModal").modal("hide");
    const chk_printtt = await chk_print(order_no.replace(/\s/g, "").replace(/-/g, ""), packing_no.replace(/-/g, ""));
    console.log(chk_printtt);
    if (chk_printtt === 2) {
      QC_check_sequence(order_no.replace(/\s/g, "").replace(/-/g, ""), packing_no.replace(/-/g, ""), arr_dw, arr_qty, function (qcDone) {
        if (qcDone) {
          showPrintModal(order_no, packing_no, project, prod, priority, part, arr_qty, arr_dw, arr_con, arr_remark, dw_print);
        }
      });
      // if (!validateConQty(arr_con)) {
      //   alert("กรุณากรอกจำนวน Con. QTY");
      //   return;
      // }

      // if (allQuantitiesMatch(arr_qty, arr_con)) {
      //   showPrintModal(order_no, packing_no, project, prod, priority, part, arr_qty, arr_dw, arr_con, arr_remark, dw_print);
      // } else {
      //   insertOrderDetail(order_no, packing_no, project, prod, priority, part, arr_qty, arr_dw, arr_con, arr_remark, dw_print);
      // }
    } else {
      alert("พบมีประวัติการปริ้น VPS ORDER นี้แล้ว กรุณาติดต่อแผนก IS");
    }
  });

  async function QC_check_sequence(order, packing, dwgArr, qtyArr, doneCallback) {
    try {
      // 1. ส่ง order, packing, dwgArr ไปที่ ajax (ยิงทีเดียว)
      const response = await $.ajax({
        url: host + "packing/PKC/getPURcode",
        type: "post",
        dataType: "json",
        data: {
          order: order,
          packing: packing,
          dwg: dwgArr, // ส่ง array ไปเลย (controller ต้องรับเป็น array ได้)
        },
      });

      // 2. ถ้าไม่มีข้อมูล
      if (!response || !response.length) {
        alert("ไม่พบข้อมูลตรวจสอบคุณภาพสำหรับ Order นี้");
        if (typeof doneCallback === "function") doneCallback(false);
        return;
      }

      // 3. วนแสดงทีละตัว
      let current = 0;
      let isNext = false;

      let qcPics = [];
      let noPics = [];

      function showModalForCurrent() {
        isNext = false;

        if (current >= response.length) {
          // --- จบรอบ ตรวจสอบครบ
          if (typeof doneCallback === "function") doneCallback(true);
          // console.log(qcPics);
          $.ajax({
            url: host + "packing/PKC/uploadQcPics", // เปลี่ยนเป็น endpoint ของคุณ
            type: "POST",
            data: { pics: JSON.stringify(qcPics) }, // แปลงเป็น JSON string
            dataType: "json",
            success: function (res) {
              console.log(res);
              // alert("อัปโหลดรูป QC สำเร็จ");
            },
          });
          // qcPics.forEach((img, idx) => {

          // });
          return;
        }

        show_QC_modal(response[current], qtyArr ? qtyArr[current] : "", current);

        $("#qc-ok-btn")
          .off()
          .on("click", function () {
            // เก็บรูป (ถ้ามี)
            if ($("#imgQc").hasClass("new_pic")) {
              const takenImage = $("#imgQc").attr("src");
              qcPics[current] = {
                img: takenImage,
                dwg: dwgArr[current],
              };
            }
            isNext = true;
            $("#Qc_Check").modal("hide");
          });

        // รอ modal ถูกปิด (กด OK หรือปิด)
        $("#Qc_Check")
          .off("hidden.bs.modal")
          .on("hidden.bs.modal", function () {
            if (isNext) {
              current++; // current++ ทีหลังจาก modal ปิด
              showModalForCurrent();
            }
          });
      }

      showModalForCurrent();
    } catch (error) {
      console.error("เกิดข้อผิดพลาด:", error);
      alert("เกิดข้อผิดพลาดในการดึงข้อมูลตรวจสอบคุณภาพ");
      if (typeof doneCallback === "function") doneCallback(false);
    }
  }

  function show_QC_modal(data, qty, i) {
    // เช็คว่ามี object ก่อน
    const { PURCODE, masterPacking, dataOrder } = data;
    console.log(data);
    const masterPack = (masterPacking && masterPacking[0]) || {};
    const dataOrderDetails = (dataOrder && dataOrder[i]) || {};

    // สัญลักษณ์
    const checkMark = '<span class="text-success fw-bold">✔</span>';
    const crossMark = '<span class="text-danger fw-bold">✖</span>';

    // j2mth mapping
    const j2mth = (dataOrderDetails.S01M09 || "").trim();
    const lastCharMap = { 1: "X", 2: "A", 3: "Y", 4: "B", 5: "Z", 6: "C" };
    const lastChar = j2mth.slice(-1);
    const replacedChar = lastCharMap[lastChar] || lastChar;
    const newJ2MTH = j2mth ? j2mth.slice(0, -1) + replacedChar : "";
    const dwgCode = (dataOrderDetails.S11M04 || "").trim().replace(/\s/g, "");
    const timestamp = new Date().getTime();
    const isPurCode = !!PURCODE;
    const imgSrc = `${host}packing/pkc/preview/${PURCODE || dwgCode}.jpg?ts=${timestamp}&is_pur=${isPurCode ? 1 : 0}`;
    $("#imgQc").attr("src", imgSrc);
    if (!PURCODE) {
      $("#qc-action").html(`
        <button class="btn-take-picture" id="btnOpenCamera">
            <i class="bi bi-camera me-2"></i> TAKE PICTURE
        </button>
    `);
    } else {
      $("#qc-action").html(`
        <div class="icheck-primary d-inline">
            <input type="checkbox" name="qcNoPic" id="qcNoPic" value="no_picture">
            <label for="qcNoPic">
                <i class="bi bi-camera-off me-2"></i>NO. PICTURE
            </label>
        </div>
    `);
    }

    // clear ก่อน
    $("#purCode, #dwgQc, #DiscQc, #itemQc, #prodQc").val("");
    $("#imgQc").attr("src", "");
    $("#imgQc").removeClass("new_pic");
    $(".qty-value").text("");
    ["#clr_bag", "#aircap", "#mois_proof", "#anti_static", "#foam"].forEach((id) => $(id).html(""));
    $("#qty_box, #box_no").text("-");
    $("#btnRetake").hide();

    // set value
    $("#purCode").val(PURCODE || "");
    $("#dwgQc").val(dataOrderDetails.S11M04 || "");
    $("#DiscQc").val(dataOrderDetails.S11M05 || "");
    $("#itemQc").val(dataOrderDetails.S01M06 || "");
    $("#prodQc").val(newJ2MTH);
    $("#imgQc").attr("src", imgSrc);
    $(".qty-value").text(qty || "");

    const updateCheckMark = (elementId, condition) => {
      $(elementId).html(condition ? checkMark : crossMark);
    };

    updateCheckMark("#clr_bag", masterPack.CLR_BAG === "1");
    updateCheckMark("#aircap", masterPack.AIRCAP === "1");
    updateCheckMark("#mois_proof", masterPack.MOISTURE_PROOF === "1");
    updateCheckMark("#anti_static", masterPack.ANTI_STATIC === "1");
    updateCheckMark("#foam", masterPack.FOAM === "1");

    $("#qty_box").text(masterPack.QTY_BOX || "-");
    $("#box_no").text(masterPack.BOXNO || "-");

    $("#Qc_Check").modal("show");
  }

  let stream = null;
  let imageData = null;

  // เมื่อคลิกปุ่ม TAKE PICTURE (หรือ .btn-take-picture ที่คุณมีเดิม)
  $(document).on("click", ".btn-take-picture", async function () {
    $("#imgQc, #btnUpload, #btnRetake").hide();
    // $("#videoQc, #btnSnap").show();
    // $(this).hide(); // ซ่อนปุ่ม TAKE PICTURE ดั้งเดิม

    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: true });
      $("#videoQc").get(0).srcObject = stream;
      $(this).hide();
      $("#videoQc, #btnSnap").show();
    } catch (error) {
      alert("ไม่พบอุปกรณ์กล้อง โปรดเชื่อมต่ออุปกรณ์และลองใหม่อีกครั้ง");
      $(".btn-take-picture").show();
      $("#imgQc").show();
    }
    // stream = await navigator.mediaDevices.getUserMedia({ video: true });
  });

  // ถ่ายรูป
  $("#btnSnap").on("click", function () {
    const canvas = $("#canvasQc").get(0);
    const video = $("#videoQc").get(0);
    canvas.getContext("2d").drawImage(video, 0, 0, 500, 500);
    imageData = canvas.toDataURL("image/png");

    // ปิดกล้อง
    if (stream) {
      stream.getTracks().forEach((track) => track.stop());
      stream = null;
    }

    // แสดงภาพ และปุ่มถ่ายใหม่ + ปุ่มอัพโหลด
    $("#videoQc, #btnSnap").hide();
    $("#imgQc").attr("src", imageData).show();
    $("#imgQc").addClass("new_pic");
    $("#btnRetake, #btnUpload").show();
  });

  // ถ่ายใหม่ (Retake)
  $("#btnRetake").on("click", async function () {
    $("#imgQc, #btnUpload, #btnRetake").hide();
    $("#videoQc, #btnSnap").show();

    // เปิดกล้องใหม่
    stream = await navigator.mediaDevices.getUserMedia({ video: true });
    $("#videoQc").get(0).srcObject = stream;
  });

  $("#qc-ok-btn").on("click", function () {
    // reset radio
    $("#qcOk, #qcNg, #qcNoPic").prop("checked", false);

    $("#Qc_Check").modal("hide");
  });

  // async function QC_check(order, packing, dwg, qty) {
  //   try {
  //     const response = await $.ajax({
  //       url: host + "packing/PKC/getPURcode",
  //       type: "post",
  //       dataType: "json",
  //       data: {
  //         order,
  //         packing,
  //         dwg,
  //       },
  //     });

  //     if (!response || !response[0]) {
  //       alert("ไม่พบข้อมูลตรวจสอบคุณภาพสำหรับ Order นี้");
  //       return;
  //     }

  //     const { PURCODE, masterPacking, dataOrder } = response[0];
  //     const masterPack = masterPacking[0];
  //     const dataOrderDetails = dataOrder[0];

  //     const checkMark = '<span class="text-success fw-bold">✔</span>';
  //     const crossMark = '<span class="text-danger fw-bold">✖</span>';

  //     // Map the last character of S01M09
  //     const j2mth = dataOrderDetails.S01M09.trim();
  //     const lastCharMap = {
  //       1: "X",
  //       2: "A",
  //       3: "Y",
  //       4: "B",
  //       5: "Z",
  //       6: "C",
  //     };
  //     const lastChar = j2mth.slice(-1);
  //     const replacedChar = lastCharMap[lastChar] || lastChar;
  //     const newJ2MTH = j2mth.slice(0, -1) + replacedChar;

  //     // Update form fields
  //     $("#purCode").val(PURCODE);
  //     $("#dwgQc").val(dataOrderDetails.S11M04);
  //     $("#DiscQc").val(dataOrderDetails.S11M05);
  //     $("#itemQc").val(dataOrderDetails.S01M06);
  //     $("#prodQc").val(newJ2MTH);
  //     $("#imgQc").attr("src", `${host}packing/pkc/preview/${PURCODE}.jpg`);
  //     $(".qty-value").text(qty[0]);

  //     // Helper function to update check/cross marks
  //     const updateCheckMark = (elementId, condition) => {
  //       $(elementId).html(condition ? checkMark : crossMark);
  //     };

  //     updateCheckMark("#clr_bag", masterPack.CLR_BAG === "1");
  //     updateCheckMark("#aircap", masterPack.AIRCAP === "0"); // Still "0" for yes
  //     updateCheckMark("#mois_proof", masterPack.MOISTURE_PROOF === "1");
  //     updateCheckMark("#anti_static", masterPack.ANTI_STATIC === "1");
  //     updateCheckMark("#foam", masterPack.FOAM === "1");

  //     $("#qty_box").text(masterPack.QTY_BOX);
  //     $("#box_no").text(masterPack.BOXNO || "-");

  //     $("#Qc_Check").modal("show");
  //     console.log("order:", order, "dwg:", dwg, "response:", response[0]);
  //   } catch (error) {
  //     console.error("Error during QC check:", error);
  //     alert("เกิดข้อผิดพลาดในการดึงข้อมูลตรวจสอบคุณภาพ");
  //   }
  // }

  $("#checkButton").click(function () {
    const check_value = $("input[name='flexRadioDefault']:checked").val();
    if (check_value) {
      if (check_value === "1") {
        insert_ng(check_value);
        // window.location.href = "";
      } else {
        $("#Qc_Check").modal("hide");
        $("#exampleModal").modal("show");
      }
    } else {
      alert("กรุณาตรวจสอบความผิดปกติของชิ้นงาน");
    }
  });

  function insert_ng(checkValue) {
    const orderNo = $(".order-no").text().trim().replace(/\s|-/g, "");
    const packingNo = $(".packing-no").text().trim().replace(/-/g, "");

    console.log(orderNo);
    console.log(packingNo);

    $.ajax({
      type: "POST",
      url: host + "packing/PKC/insert_ng",
      data: {
        order_no: orderNo,
        packing_no: packingNo,
        check_value: checkValue,
      },
      success: function (response) {
        alert("ชิ้นงานไม่ผ่านการตรวจสอบ ไม่สามารถรับชิ้นงานได้");
        window.location.href = "";
      },
    });
  }

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
        return false; // break out of $.each loop
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
    $("#printModal").modal("show");
    $("#submitButton")
      .off()
      .on("click", function () {
        const containerQuantity = $("#containerQuantity").val().trim();

        if (containerQuantity <= 0) {
          Swal.fire({
            icon: "error",
            title: "ข้อผิดพลาด",
            text: "กรุณากรอกจำนวนลังที่ถูกต้อง",
          });
          return;
        }

        const itemsQuantities = getItemsQuantities(containerQuantity);
        if (!itemsQuantities.valid) {
          Swal.fire({
            icon: "error",
            title: "ข้อผิดพลาด",
            text: "กรุณากรอกจำนวนชิ้นที่ถูกต้องสำหรับแต่ละใบ",
          });
          return;
        }
        insertOrderDetail(order_no, packing_no, project, prod, priority, part, arr_qty, arr_dw, arr_con, arr_remark, dw_print, (checkItem = 2), containerQuantity, itemsQuantities.values);
        $("#containerQuantity").val("");
        $("#itemsQuantityContainer").empty();
        $("#printModal").modal("hide");
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
    return {
      valid,
      values: itemsQuantities,
    };
  }

  function insertOrderDetail(order_no, packing_no, project, prod, priority, part, arr_qty, arr_dw, arr_con, arr_remark, dw_print, checkItem = 2, qty_sticker = null, itemsQuantities = null) {
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
      checkItem: checkItem,
    };

    if (qty_sticker !== null) {
      data.qty_sticker = qty_sticker;
    }

    $.ajax({
      url: host + "packing/PKC/insert_order_detail",
      type: "POST",
      data: data,
      success: function (res) {
        console.log(res);
        order_table();

        if (qty_sticker !== null) {
          setTimeout(() => {
            const IP = printbar(packing_no, project, order_no, prod, part, dw_print, priority, qty_sticker, itemsQuantities);
            const order = order_no.replace(/\s/g, "").replace(/-/g, "");
            const packing = packing_no.replace(/-/g, "");
            insertPrintLog(order, packing, IP, qty_sticker, itemsQuantities);
            save_print_vps(order, packing, qty_sticker);
          }, 500);
        }
      },
      error: function (error) {
        console.log(error);
      },
    });
  }

  function insertPrintLog(order, packing, IP, qty, itemsQuantities) {
    $.ajax({
      url: host + "packing/PKC/insert_print_log",
      type: "POST",
      data: {
        order_no: order,
        packing_no: packing,
        printer: IP,
        qty: qty,
        qty_item: itemsQuantities,
      },
      success: function (res) {
        console.log("success");
      },
    });
  }

  function formatdate(date) {
    var formattedDate = date.slice(6, 8) + "-" + date.slice(4, 6);
    return formattedDate;
  }

  function formatorderno(order) {
    var orderno = order.slice(0, 1) + "-" + order.slice(1, 3) + " " + order.slice(3, 8) + "-" + order.slice(8, 9);
    return orderno;
  }

  function order_table() {
    var text = $(".input_qr").val();
    var txt_arr = text.split("|");
    var issueNos = [];

    $.each(txt_arr, function (key, txt) {
      issueNos.push(txt.substring(0, 10));
    });

    console.log(issueNos);

    // Send all issue numbers in one request
    $.ajax({
      url: host + "packing/PKC/get_detail_issue_batch",
      type: "post",
      data: {
        issue_nos: issueNos,
      },
      beforeSend: function () {
        $("#loading_waiting").removeClass("d-none");
      },
      success: function (res) {
        var data = JSON.parse(res);

        $(".div_table").removeClass("d-none");
        $(".containerrr").addClass("d-none");
        // console.log(res);

        console.log(data.data);
        $("#orderTable tbody").empty();
        // Assuming 'data' is an array of order details
        $.each(data.data, function (index, orderDetails) {
          // var orderDetails = orderDetailsArray[index];

          // Append each order detail to the table
          $("#orderTable tbody").append(
            "<tr>" +
              "<td>" +
              orderDetails.J2ODR +
              "</td>" +
              '<td><a class="btn_modal" data_order="' +
              orderDetails.S01ORD +
              '" data_packing="' +
              orderDetails.S01M04 +
              '">' +
              formatorderno(orderDetails.S01ORD) +
              "</a></td>" +
              "<td>" +
              orderDetails.S01M04.slice(0, 3) +
              "-" +
              orderDetails.S01M04.slice(3, 5) +
              "</td>" +
              "<td>" +
              orderDetails.S01M08 +
              "</td>" +
              // '<td>' + orderDetails.J2RQTY + '</td>' +
              "</tr>"
          );
        });
      },
      complete: function () {
        $("#loading_waiting").addClass("d-none");
        // location.reload();
      },
    });
  }

  function save_print_vps(order, packing, qty_print) {
    $.ajax({
      url: host + "packing/PKC/get_order_detail",
      type: "POST",
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
          },
          beforeSend: function () {
            $("#loading-overlay").show();
          },
          success: function (res) {
            $("#loading-overlay").hide();
            console.log(res);
          },
        });
      },
    });
  }

  function chk_print(order, packing) {
    return new Promise((resolve, reject) => {
      $.ajax({
        url: host + "packing/pkc/chk_print",
        type: "post",
        data: {
          order: order,
          packing: packing,
        },
        success: function (res) {
          console.log("res" + res);
          if (res == 1) {
            resolve(1);
          } else {
            resolve(2);
          }
        },
        error: function (err) {
          reject(err);
        },
      });
    });
  }

  $(".test").keyup(function () {
    $(".form-container").find(".xxxx").remove(); // Remove all .xxxx elements
    var row = $(this).val(); // Get the input value

    for (let index = 0; index < row; index++) {
      $(".form-container").append(`
        <input type="text" class="xxxx">
    `);
    }
  });

  $(".Test_button").click(function () {
    $("#Qc_Check").modal("show");
  });
});
