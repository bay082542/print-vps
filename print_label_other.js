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

function writeToSelectedPrinter(dataToWrite, successCallback, errorCallback) {
  selected_device.send(
    dataToWrite,
    () => {
      // ถ้าการส่งคำสั่งสำเร็จ
      successCallback("Print command sent successfully.");
    },
    (errorMessage) => {
      // ถ้ามีข้อผิดพลาด
      errorCallback(errorMessage);
    }
  );
}

var successCallback = function (successMessage) {
  alert(successMessage);
};

var errorCallback = function (errorMessage) {
  alert("Error: " + errorMessage);
};

function printbar(item, project, order, prod, partname, dwg, priority, qty_sticker, item_qty, callback) {
  var ITEM = $.trim(item);
  var PROJECT = $.trim(project);
  var ORDER = $.trim(order);
  var PRODUCT = $.trim(prod + "-" + priority);
  var PARTNAME = $.trim(partname);
  if (PARTNAME == "ACCESSORIES PC") {
    PARTNAME = "";
  }
  var DWG = dwg;
  var QR = (ORDER.slice(2, 10) + ITEM.slice(0, 3) + ITEM.slice(4, 6)).replace(" ", "");

  var command = "";
  for (var i = 0; i < qty_sticker; i++) {
    var PAGE = i + 1 + "/" + qty_sticker;
    // ^FT200,500^A0B,22,22^FB350,1,5,L^FH^FN2^FD${DWG}^FS
    command += `
    ^XA
    ^MNW^PMN
    ^PW325^LL528^LS0
    ^FT150,150^GB3,520,3^FS
    ^FT2,300^GB150,3,3^FS
    ^FT2,200^GB150,3,3^FS
    ^FT150,200^GB170,3,3^FS
    ^FT60,480^A0B,45,45^FB350,1,5,L^FH^FN2^FD${ITEM}^FS
    ^FT110,490^A0B,22,22^FB350,1,5,L^FH^FN2^FD${PARTNAME}^FS
    ^FT140,270^A0B,30,30^FB350,1,5,L^FH^FN2^FD${PAGE}^FS
    ^FT220,130^A0B,45,45^FB350,1,5,L^FH^FN2^FD${priority}^FS
    ^FT280,160^A0B,45,45^FB350,1,5,L^FH^FN2^FD${prod}^FS
    
    ^FT250,500^A0B,40,40^FB350,1,5,L^FH^FN2^FD${ORDER}^FS
    ^FT290,500^A0B,22,22^FB350,1,5,L^FH^FN2^FD${PROJECT}^FS
    ^FT15,190^BQN,5,5^FN9^FDQA,${QR}-${String(i + 1).padStart(4, "0")}^FS
    ^XZ
    `;
  }

  if (selected_device) {
    writeToSelectedPrinter(
      command,
      (successMessage) => callback(null, successMessage),
      (errorMessage) => callback(errorMessage, null)
    );
  } else {
    callback("No printer selected", null);
  }
}

function print_vpis(order, packing, jun, p, dwg, qty_sticker, callback) {
  var ORDER = $.trim(order);
  var PACKING = $.trim(packing);
  var JUN = $.trim(jun + "-" + p);
  var DWG = $.trim(dwg);

  var command = "";
  for (let i = 1; i <= qty_sticker; i++) {
    command += `
      ^XA
      ^MNW^PMN
      ^PW325^LL528^LS0
      ^FT50,490^A0B,35,35^FH^CI28^FN1^FD${ORDER}^FS^CI27
      ^FT100,490^A0B,35,35^FH^CI28^FN2^FD${JUN}^FS^CI27
      ^FT150,490^A0B,35,35^FH^CI28^FN3^FD${PACKING} (${i}/${qty_sticker})^FS^CI27
      ^FT290,490^A0B,40,40^FH^CI28^FN7^FD${DWG} (${i}/${qty_sticker})^FS^CI27
      ^FT40,260^BQN,5,5^FN9^FDQA,${ORDER}|${JUN}|${PACKING}|${i}/${qty_sticker}|${DWG}|${i}/${qty_sticker}
      ^XZ
    `;
  }

  console.log(command);
  // if (selected_device) {
  //   writeToSelectedPrinter(
  //     command,
  //     (successMessage) => callback(null, successMessage),
  //     (errorMessage) => callback(errorMessage, null)
  //   );
  // } else {
  //   callback("No printer selected", null);
  // }
}

window.onload = setup;
