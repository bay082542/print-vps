$(document).ready(function () {
  // $("#orderTable").DataTable();

  // Open the modal when an order number link is clicked
  // $("#containerQuantity").on("keyup", function () {
  //   var containerQuantity = $(this).val();
  //   var itemsQuantityHtml = "";
  //   for (var i = 0; i < containerQuantity; i++) {
  //     itemsQuantityHtml += `
  //       <div class="form-group mt-3">
  //         <label for="itemQuantity${i}">กรอกจำนวนชิ้นสำหรับใบที่ ${i + 1}</label>
  //         <input type="number" class="form-control" id="itemQuantity${i}" placeholder="Enter the quantity of items for label ${i + 1}">
  //       </div>
  //     `;
  //   }
  //   $("#itemsQuantityContainer").html(itemsQuantityHtml);
  // });

  function fetchOrderDetails(order, packing) {
    return new Promise((resolve, reject) => {
      $.ajax({
        type: "POST",
        url: host + "packing/PKC/check_status_open",
        data: {
          order: order,
          packing: packing,
        },
        dataType: "json",
        success: function (response) {
          resolve(response);
        },
        error: function (err) {
          reject(err);
        },
      });
    });
  }

  const socket = io("https://amecwebtest.mitsubishielevatorasia.co.th/lockpis", {
    query: { empno: $("#sempno").val().toString() },
  });

  socket.on("connect", () => {
    console.log("Socket connected", socket.id);
  });

  socket.on("disconnect", () => {
    console.log("Socket disconnected");
  });

  socket.on("connect_error", (err) => {
    console.error("Connection error:", err.message);
  });
  function check_open(order, packing) {
    return new Promise((resolve, reject) => {
      // ❗ ใช้ emit + once เพื่อไม่ผูกซ้ำ
      socket.emit("lock_item", { itemId: packing, order: order });

      socket.once("lock_status", (data) => {
        console.log("lock item", data);
        if (data.status === false) {
          Swal.fire({
            icon: "warning",
            title: "รายการถูกเปิดอยู่",
            html: "โดย : <b>" + data.user.STNAME + "</b> ( " + data.user.SSEC + " ) ",
          });
          return reject("locked");
        }
        resolve(); // ปล่อยให้ทำงานต่อ
      });
    });
  }

  $("#exampleModal").on("hidden.bs.modal", function () {
    console.log("Modal is fully hidden.");
    const order = $(".order-name").attr("data-order");
    const packing = $(".packing-no").text().replace(/-/g, "");

    console.log("Order:", order);
    console.log("Packing:", packing);

    socket.emit("unlock_item", { itemId: packing, order: order });

    // ถ้าต้องการอัปเดตใน DB จริง ๆ ใช้ $.post ตรงนี้ได้
  });

  $("#orderTable").on("click", ".btn-print", async function (event) {
    event.preventDefault();
    const table = $(".detail-table");
    // table.empty();
    $(".order-detail").empty();
    var row = $(this).closest("tr");

    var orderName = row.find(".order-name").text();
    var orderNo = formatorderno($(this).data("order"));
    var packingNo = row
      .find("td:nth-child(3)")
      .text()
      .replace(/^(\d{3})(\d{2})$/, "$1-$2");

    var order = $(this).data("order");
    var packing = $(this).data("packing");

    try {
      await check_open(order, packing);
    } catch (e) {
      console.warn("Blocked by lock status:", e);
      return; // ❌ หยุดการทำงาน ถ้า order ถูกล็อก
    }

    $(".order-name").attr("data-order", order);
    $(".modal-footer").removeClass("d-none");
    // -------------------- insert Status open ---------------------
    $.ajax({
      type: "POST",
      url: host + "packing/PKC/get_order_detail",
      data: {
        order: order,
        packing: packing,
      },
      // async: false,
      dataType: "json",
      beforeSend: function () {
        Swal.fire({
          title: "กำลังโหลดข้อมูล...",
          text: "กรุณารอสักครู่.",
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          },
        });
      },
      success: async function (response) {
        Swal.close();
        const data = response.data[0];
        $(".order-name").text(data.S01M08);
        $(".order-no").text(orderNo);
        $(".packing-no").text(packingNo);
        $(".item").text(data.S01M06);
        $(".schedule").text(data.SCHEDULE);
        $(".priority").text(data.M8K02);
        $(".main-part-name").text(data.S11M05);
        $(".part-name").text(data.S11M06);
        $(".model").text(data.S01M07);
        $(".f-date").text(formatdate(data.S01M14));
        $(".p-date").text(formatdate(data.S01M15));
        $(".date-p").text(data.S11M08);

        const chkprintPromises = [];

        for (let i = 0; i < 15; i++) {
          const currentDetail = response.data[i] || {};
          // ดึงค่าพารามิเตอร์สำหรับแต่ละรอบ
          if ($("#agent").val() && currentDetail.S11M06) {
            chkprintPromises.push(chk_print_vpis(currentDetail.S11M01, currentDetail.S11M02, currentDetail.S11M04));
            // chkprintPromises.push(chk_print_vpis('EOBU60054', '12803', 'BA176B346 G08'));
          } else {
            chkprintPromises.push(Promise.resolve(null)); // กรณีที่ไม่ต้องการเช็ค
          }
        }

        // รอให้ทุกคำขอเสร็จสิ้น
        const chkprintResults = await Promise.all(chkprintPromises);

        let lineCount = 0;

        for (let i = 0; i < response.data.length && lineCount < 15; i++) {
          const currentDetail = response.data[i] || {};
          const partNoInput = currentDetail.S11M05 ? `<input type="hidden" class="part_no" value="${currentDetail.S11M06}">` : "";
          const dwNoInput = currentDetail.S11M06 ? `<input type="hidden" class="dw_no" value="${currentDetail.S11M06}">` : "";
          const qtyInput = currentDetail.S11M09 ? `<input type="hidden" class="qty" value="${currentDetail.S11M09}">` : "";

          const chkprint = chkprintResults[i];
          let printButton = "";
          if ($("#agent").val() && currentDetail.S11M06 && chkprint.length === 0) {
            printButton = `<td style="text-align:center;"><button class='btn btn-primary print_vpis' data-packing=${currentDetail.S11M02} data-jun='${currentDetail.JUN}' data-order='${currentDetail.S11M01}' data-dwg='${currentDetail.S11M06}' data-qty='${currentDetail.S11M09}' data-p='${currentDetail.M8K02}'>Print VPIS</button></td>`;
          } else {
            printButton = "<td></td>";
          }

          // ก่อนแสดง ตรวจว่าแถวนี้ต้องใช้กี่บรรทัด
          const linesRequired = currentDetail.LVAL ? 2 : 1;
          if (lineCount + linesRequired > 15) break;

          // บรรทัดหลัก
          table.append(`
            <tr class="order-detail" style="line-height:15px;">
              <td style="text-align:center;">${lineCount + 1}</td>
              <td>${currentDetail.S11M05 ? currentDetail.S11M05 + partNoInput : ""}</td>
              <td>${currentDetail.S11M06 ? currentDetail.S11M06 + dwNoInput : ""}</td>
              <td style="text-align:center;">${currentDetail.S11M09 ? currentDetail.S11M09 + qtyInput : ""}</td>
              <td style="width:30%; font-size:9pt; padding:3px;">${currentDetail.REMARK || ""}</td>
              ${$("#agent").val() ? printButton : ""}
            </tr>
          `);
          lineCount++;

          // บรรทัด LVAL แยก
          if (currentDetail.LVAL) {
            table.append(`
              <tr class="order-detail">
                <td style="text-align:center;">${lineCount + 1}</td>
                <td></td>
                <td style="font-style: italic;">${currentDetail.LVAL.trim()}</td>
                <td></td>
                <td></td>
                ${$("#agent").val() ? "<td></td>" : ""}
              </tr>
            `);
            lineCount++;
          }
        }

        // เติมบรรทัดเปล่าให้ครบ 15
        for (let i = lineCount; i < 15; i++) {
          table.append(`
            <tr class="order-detail">
              <td style="text-align:center;">${i + 1}</td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              ${$("#agent").val() ? "<td></td>" : ""}
            </tr>
          `);
        }

        $(".modal-footer").removeClass("d-none");
        $("#exampleModal").modal("show");
      },
      complete: function () {
        // $("#loader").addClass("d-none");
      },
    });
    // -----------------------------------------------------------
  });

  $("#orderTable").on("click", ".btn-view", function (event) {
    console.log("view btn clicked");
    event.preventDefault();
    var row = $(this).closest("tr");
    var order = $(this).data("order");
    var packing = $(this).data("packing");
    var orderNo = formatorderno($(this).data("order"));
    var packingNo = row
      .find("td:nth-child(3)")
      .text()
      .replace(/^(\d{3})(\d{2})$/, "$1-$2");
    const table = $(".detail-table");
    $(".order-detail").empty();
    $.ajax({
      type: "POST",
      url: host + "packing/PKC/get_order_detail",
      data: { order: order, packing: packing },
      dataType: "json",
      beforeSend: function () {
        Swal.fire({
          title: "กำลังโหลดข้อมูล...",
          text: "กรุณารอสักครู่.",
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          },
        });
      },
      success: function (response) {
        Swal.close();
        const data = response.data[0];
        $(".order-name").text(data.S01M08);
        $(".order-no").text(orderNo);
        $(".packing-no").text(packingNo);
        $(".item").text(data.S01M06);
        $(".schedule").text(data.SCHEDULE);
        $(".priority").text(data.M8K02);
        $(".main-part-name").text(data.S11M05);
        $(".part-name").text(data.S11M06);
        $(".model").text(data.S01M07);
        $(".f-date").text(formatdate(data.S01M14));
        $(".p-date").text(formatdate(data.S01M15));
        $(".date-p").text(data.S11M08);
        $(".model").text(data.S01M07);

        let lineCount = 0;
        for (let i = 0; i < response.data.length && lineCount < 15; i++) {
          const currentDetail = response.data[i] || {};
          console.log("Current detail:", currentDetail);
          const partNoInput = currentDetail.S11M05 ? `<input type="hidden" class="part_no" value="${currentDetail.S11M06}">` : "";
          const dwNoInput = currentDetail.S11M06 ? `<input type="hidden" class="dw_no" value="${currentDetail.S11M06}">` : "";
          const qtyInput = currentDetail.S11M09 ? `<input type="hidden" class="qty" value="${currentDetail.S11M09}">` : "";
          const remarkInput = `<input class="form-control remark" style="height:8mm; color:red;" value="${currentDetail.REMARK || ""}">`;

          // table.append(`
          //   <tr class="order-detail" style="line-height:${currentDetail.REMARK ? "normal" : ""};">
          //     <td style="text-align:center;">${i + 1}</td>
          //     <td>${currentDetail.S11M05 ? currentDetail.S11M05 + partNoInput : ""}</td>
          //     <td>${currentDetail.S11M06 ? currentDetail.S11M06 + dwNoInput : ""}</td>
          //     <td style="width:8%; text-align:center;">${currentDetail.S11M09 ? currentDetail.S11M09 + qtyInput : ""}</td>
          //     <td style="width:30%; font-size:10pt; padding:3px;">${currentDetail.REMARK || ""}</td>
          //   </tr>
          // `);

          const linesRequired = currentDetail.LVAL ? 2 : 1;
          if (lineCount + linesRequired > 15) break;

          // บรรทัดหลัก
          table.append(`
            <tr class="order-detail" style="line-height:${currentDetail.REMARK ? "normal" : ""};">
              <td style="text-align:center;">${i + 1}</td>
              <td>${currentDetail.S11M05 ? currentDetail.S11M05 + partNoInput : ""}</td>
              <td>${currentDetail.S11M06 ? currentDetail.S11M06 + dwNoInput : ""}</td>
              <td style="width:8%; text-align:center;">${currentDetail.S11M09 ? currentDetail.S11M09 + qtyInput : ""}</td>
              <td style="width:30%; font-size:10pt; padding:3px;">${currentDetail.REMARK || ""}</td>
            </tr>
          `);
          lineCount++;

          // บรรทัด LVAL แยก
          if (currentDetail.LVAL) {
            table.append(`
              <tr class="order-detail">
                <td style="text-align:center;">${lineCount + 1}</td>
                <td></td>
                <td style="font-style: italic;">${currentDetail.LVAL.trim()}</td>
                <td></td>
                <td></td>
                ${$("#agent").val() ? "<td></td>" : ""}
              </tr>
            `);
            lineCount++;
          }
        }

        for (let i = lineCount; i < 15; i++) {
          table.append(`
            <tr class="order-detail">
              <td style="text-align:center;">${i + 1}</td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              ${$("#agent").val() ? "<td></td>" : ""}
            </tr>
          `);
        }
        $(".modal-footer").addClass("d-none");
        $("#exampleModal").modal("show");
      },
      error: function () {
        Swal.fire({
          icon: "error",
          title: "เกิดข้อผิดพลาด",
          text: "ไม่สามารถโหลดข้อมูลได้",
        });
      },
    });
  });

  function chk_print_vpis(order, packing, drawing) {
    return new Promise((resolve, reject) => {
      $.ajax({
        url: host + "packing/pkc/chk_print_vpis",
        type: "POST",
        data: {
          order: order,
          packing: packing,
          drawing: drawing,
        },
        dataType: "json",
        success: function (response) {
          resolve(response);
        },
        error: function (err) {
          reject(err);
        },
      });
    });
  }

  $("#checkButton").click(function () {
    const checkValue = $("input[name='flexRadioDefault']:checked").val();
    if (checkValue) {
      if (checkValue === "1") {
        alert("ชิ้นงานไม่ผ่านการตรวจสอบ ไม่สามารถรับชิ้นงานได้");
      } else {
        $("#Qc_Check").modal("hide");
        $("#exampleModal").modal("show");
      }
    } else {
      alert("กรุณาตรวจสอบความผิดปกติของชิ้นงาน");
    }
  });

  // $(document).on("click", ".print_vpis", function () {
  //   const order = $(this).data("order");
  //   console.log(order);
  //   $("#exampleModal").modal("hide");
  //   (async () => {
  //     const { value: quantity } = await Swal.fire({
  //       title: "กรอกจำนวนลังที่ต้องปริ้นสติกเกอร์",
  //       input: "number",
  //       inputLabel: "Number of Quantity",
  //       inputPlaceholder: "Enter Number of Quantity.",
  //       inputAttributes: {
  //         required: "true",
  //         min: "1",
  //       },
  //       inputValidator: (value) => {
  //         if (!value || value <= 0) {
  //           return "กรุณากรอกจำนวนลังที่ถูกต้อง!";
  //         }
  //       },
  //     });
  //     if (quantity) {
  //       // $("#exampleModal").modal("show");
  //       // console.log(order);
  //       $("#" + order).trigger("click");
  //     }
  //   })();
  // });

  // // console.log(data);

  // async function print_vpis() {
  //   try {
  //     const data = await get_order_detail("EOMM42012", "22101");
  //     console.log(data);
  //   } catch (error) {
  //     console.error("Error occurred while printing VPIS:", error);
  //   }
  // }

  // function get_order_detail(order, packing) {
  //   return new Promise((resolve, reject) => {
  //     $.ajax({
  //       url: host + "packing/PKC/get_order_detail",
  //       type: "POST",
  //       data: { order: order, packing: packing },
  //       dataType: "json",
  //       success: function (res) {
  //         resolve(res.data[0]);
  //       },
  //       error: function (err) {
  //         reject(err);
  //       },
  //     });
  //   });
  // }

  // // เรียกใช้ฟังก์ชัน
  // print_vpis();

  $(document).on("click", ".save-btn", async function () {
    console.log("save btn  clicked");
    const arr_qty = getValuesFromClass(".qty");
    const arr_con = getValuesFromClass(".con_qty");
    const arr_dw = getValuesFromClass(".dw_no");
    const arr_remark = getValuesFromClass(".remark");
    const part_no = getValuesFromClass(".part_no");
    const project = $(".order-name").text().trim();
    const date = $(".date-p").text().slice(2, 4).trim();
    const prod = $(".schedule").text().trim() + "/" + date;
    const priority = $(".priority").text().trim();
    const part = $(".main-part-name").text().trim();
    const dw_print = $(".part-name").text().trim();

    var order_no = $(".order-no").text();
    var packing_no = $(".packing-no").text();

    // console.log(arr_qty);
    // console.log(arr_con);

    const data = {
      order_no: order_no,
      packing_no: packing_no,
      qty: arr_qty,
      dw: arr_dw,
      part_no: part_no,
      con_qty: arr_con,
      remark: arr_remark,
      checkItem: "2",
    };

    const chk_printtt = await chk_print(order_no.replace(/\s/g, "").replace(/-/g, ""), packing_no.replace(/-/g, ""));

    console.log(chk_printtt);
    if (chk_printtt === 2) {
      console.log("yes");
      $("#printModal").modal("show");
      $("#exampleModal").modal("hide");
      $(document).on("click", "#submitButton", function () {
        // })
        // $("#submitButton").click(function() {
        const containerQuantity = $("#containerQuantity").val().trim();
        if (containerQuantity <= 0) {
          Swal.fire({
            icon: "error",
            title: "ข้อผิดพลาด",
            text: "กรุณากรอกจำนวนลังที่ถูกต้อง",
          });
          return;
        }
        const itemsQuantities = "";

        data.qty_sticker = containerQuantity;

        if (containerQuantity !== null) {
          const order = order_no.replace(/\s/g, "").replace(/-/g, "");
          const packing = packing_no.replace(/-/g, "");
          printbar(packing_no, project, order_no, prod, part, dw_print, priority, containerQuantity, itemsQuantities, function (error, result) {
            if (error) {
              alert("Error:" + error);
            } else {
              $.ajax({
                url: host + "packing/PKC/insert_print_log",
                type: "POST",
                data: {
                  order_no: order_no,
                  packing_no: packing_no,
                  qty: containerQuantity,
                  qty_item: itemsQuantities,
                },
                success: function (res) {
                  console.log("success");
                },
              });
              save_print_vps(order, packing, containerQuantity);
              insert_log_other(order, packing, containerQuantity);
              // location.reload();
            }
          });
          $("#containerQuantity").val("");
          $("#itemsQuantityContainer").empty();
          $("#printModal").modal("hide");
        }
      });
    } else {
      alert("พบมีประวัติการปริ้น VPS ORDER นี้แล้ว กรุณาติดต่อแผนก IS");
    }
  });
});

function allQuantitiesMatch(arr_qty, arr_con) {
  let chk_qty = 0;
  $.each(arr_qty, function (index, val) {
    if (val === arr_con[index]) {
      chk_qty++;
    }
  });
  return chk_qty === arr_qty.length;
}

function getValuesFromClass(className) {
  const values = [];
  $(className).each(function () {
    values.push($(this).val().trim());
  });
  return values;
}

function formatdate(date) {
  var formattedDate = date.slice(6, 8) + "-" + date.slice(4, 6);
  return formattedDate;
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

function save_print_vps(order, packing, qty_print) {
  console.log(order, packing, qty_print);
  $.ajax({
    url: host + "packing/PKC/get_order_detail",
    type: "POST",
    data: {
      order: order,
      packing: packing,
    },
    success: function (res) {
      console.log(res);
      var data = JSON.parse(res).data[0];
      console.log("data" + data);
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
          Swal.fire({
            title: "กำลังบันทึกข้อมูล...",
            text: "กรุณารอสักครู่.",
            allowOutsideClick: false,
            didOpen: () => {
              Swal.showLoading();
            },
          });
        },
        success: function (res) {
          Swal.close();
          console.log(res);
          location.reload();
        },
        error: function (err) {
          console.error("Error occurred while inserting packing order:", err);
        },
      });
    },
    error: function (err) {
      console.error("Error occurred while getting order detail:", err);
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
        console.error("Network error occurred while checking print:", err);
        reject(err);
      },
    });
  });
}

function formatorderno(order) {
  return `${order.slice(0, 1)}-${order.slice(1, 3)} ${order.slice(3, 8)}-${order.slice(8, 9)}`;
}

function insert_log_other(order, packing, print_qty, reprint_cause = null, other_cause = null) {
  $.ajax({
    url: host + "packing/PKC/insert_printlog_other",
    type: "POST",
    data: {
      order_no: order,
      packing_no: packing,
      print_qty: print_qty,
      reprint_cause: reprint_cause,
      remark: other_cause,
    },
    success: function (res) {
      console.log(res);
    },
    error: function (err) {
      console.error("Error occurred:", err);
    },
  });
}
