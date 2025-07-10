var selected_device;
var devices = [];
function setup() {
  //Get the default device from the application as a first step. Discovery takes longer to complete.
  BrowserPrint.getDefaultDevice(
    "printer",
    function (device) {
      //Add device to list of devices and to html select element
      selected_device = device;
      devices.push(device);

      //Discover any other devices available to the application
      BrowserPrint.getLocalDevices(
        function (device_list) {
          for (var i = 0; i < device_list.length; i++) {
            //Add device to list of devices and to html select element
            var device = device_list[i];
            if (!selected_device || device.uid != selected_device.uid) {
              devices.push(device);
            }
          }
        },
        function () {
          alert("Error getting local devices");
        },
        "printer"
      );
    },
    function (error) {
      alert("ไม่พบ Printer กรุณาติดต่อ แผนก IS Tel:2033");
      // alert(error);
    }
  );
}

function writeToSelectedPrinter(dataToWrite) {
  selected_device.send(dataToWrite, undefined, errorCallback);
}

var errorCallback = function (errorMessage) {
  alert("Error: " + errorMessage);
};

async function printbar(item, project, order, prod, partname, dwg, priority, qty_sticker, item_qty) {
  var ITEM = $.trim(item);
  var PROJECT = $.trim(project);
  var ORDER = $.trim(order);
  var PRODUCT = $.trim(prod + "-" + priority);
  var PARTNAME = $.trim(partname);
  var DWG = dwg;
  // var qtysticker = qty_sticker;
  var ORDERNUMBER = await GetVPCOrder(order);
  const orderNumber = ORDERNUMBER ? ORDERNUMBER : "";

  var text_qr = ORDER.slice(2, 10) + ITEM.slice(0, 3) + ITEM.slice(4, 6);
  var QR = text_qr.replace(" ", "");

  console.log(item_qty);
  console.log(orderNumber);
  let commands = "";
  for (let i = 0; i < qty_sticker; i++) {
    const PAGE = `${i + 1}/${qty_sticker}`;
    const pageNumber = String(i + 1).padStart(4, "0");

    // let command = [
    //   "^XA",
    //   "^MNW^PMN",
    //   "^PW325^LL528^LS0",
    //   `^FT49,490^A0B,40,40^FB350,1,5,L^FH\\\\^CI28^FN1^FD${ITEM}^FS^CI27`,
    //   `^FT90,490^A0B,22,22^FB350,1,5,L^FH\\\\^CI28^FN2^FD${PARTNAME}^FS^CI27`,
    //   `^FT120,490^A0B,22,22^FB350,1,5,L^FH\\\\^CI28^FN3^FD${DWG}^FS^CI27`,
    //   `^FT150,490^A0B,22,22^FB350,1,5,L^FH\\\\^CI28^FN4^FD${PROJECT}^FS^CI27`,
    //   `^FT200,490^A0B,41,41^FB350,1,5,L^FH\\\\^CI28^FN5^FD${ORDER}^FS^CI27`,
    //   `^FT250,490^A0B,41,41^FB350,1,5,L^FH\\\\^CI28^FN6^FD${PRODUCT}^FS^CI27`,
    //   `^FT290,490^A0B,22,22^FB350,1,5,L^FH\\\\^CI28^FN7^FDQ'TY ${item_qty[i]} Pcs./Box^FS^CI27`,
    //   `^FT300,100^A0B,30,30^FB350,1,5,L^FH\\\\^CI28^FN8^FD${PAGE}^FS^CI27^FX QR CODE ^FS`,
    //   `^FT80,260^BQN,7,7^FN9^FDQA,${QR}-${pageNumber}^FS`,
    //   "^XZ",
    // ];

    commands += `
    ^XA
    ^MMT
    ^PW325
    ^LL528
    ^LS0
    ^FT50,490^A0B,50,50^FB350,1,5,L^FD${ITEM}^FS
    ^FT30,70^A0B,28,28^FB350,1,5,L^FDVPS^FS
    ^FT80,490^A0B,25,25^FB350,1,5,L^FD${PARTNAME}^FS
    ^FT110,490^A0B,25,25^FB350,1,5,L^FD${DWG}^FS
    ^FT140,490^A0B,25,25^FB350,1,5,L^FD${orderNumber}^FS
    ^FT170,490^A0B,25,25^FB350,1,5,L^FD${PROJECT}^FS
    ^FT220,490^A0B,45,45^FB350,1,5,L^FD${ORDER}^FS
    ^FT270,490^A0B,45,45^FB350,1,5,L^FD${PRODUCT}^FS
    ^FT300,490^A0B,22,22^FB350,1,5,L^FDQ'ty : ${item_qty[i]} pcs./Box^FS
    ^FT70,180^A0B,30,30^FB350,1,5,L^FD${PAGE}^FS
    ^FT95,220^BQN,2,6^FDQA,${QR}-${pageNumber}^FS
    ^FT280,180^A0B,18,18^FB350,1,5,L^FD MADE IN THAILAND^FS
    ^FT300,217^A0B,18,18^FB350,1,5,L^FD MANUFACTURER : AMEC^FS
    ^XZ
    `;
  }

  console.log(commands);
  writeToSelectedPrinter(commands);
}

function GetVPCOrder(order) {
  const host = $("meta[name=base_url]").attr("content");
  return new Promise((resolve, reject) => {
    $.ajax({
      url: host + "packing/pkc/GetVpcOrder",
      method: "POST",
      dataType: "json",
      data: {
        order: order,
      },
      success: function (response) {
        if (response[0]) {
          console.log("response : "+response);
          resolve(response[0].ORDERNUMBER);
        } else {
          resolve(''); // เพิ่มการจัดการ error
        }
      },
      error: function (error) {
        reject(error);
      },
    });
  });
}
window.onload = setup;
