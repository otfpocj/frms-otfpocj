// ==========================================
// ตั้งค่า Folder ID ของ Google Drive สำหรับเก็บรูปโปรไฟล์
// ==========================================
var FOLDER_ID = "ใส่_Folder_ID_ที่นี่"; // นำ ID โฟลเดอร์ใน Google Drive มาใส่ระหว่างเครื่องหมายคำพูด

function doPost(e) {
  try {
    var data = JSON.parse(e.postData.contents);
    var action = data.action;
    
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    
    if (action === 'login') {
      return handleLogin(ss, data.username, data.password);
    } else if (action === 'update_profile') {
      return handleUpdateProfile(ss, data);
    }
    
    return ContentService.createTextOutput(JSON.stringify({
      status: 'error',
      message: 'ไม่พบ Action ที่ระบุ'
    })).setMimeType(ContentService.MimeType.JSON);
    
  } catch (error) {
    return ContentService.createTextOutput(JSON.stringify({
      status: 'error',
      message: 'Server Error: ' + error.toString()
    })).setMimeType(ContentService.MimeType.JSON);
  }
}

function handleLogin(ss, username, password) {
  var sheet = ss.getSheetByName('ผู้ใช้งาน');
  var rows = sheet.getDataRange().getValues();
  
  // คอลัมน์: A=ลำดับ, B=รหัสประจำตัว, C=ชื่อผู้ใช้, D=รหัสผ่าน, E=บทบาท, F=รูปภาพ
  for (var i = 1; i < rows.length; i++) {
    var row = rows[i];
    var dbUsername = String(row[2]).trim();
    var dbPassword = String(row[3]).trim();
    
    if (dbUsername === username) {
      var isPasswordValid = (dbPassword === password);
      
      if (isPasswordValid) {
        return ContentService.createTextOutput(JSON.stringify({
          status: 'success',
          data: {
            user_id: String(row[1]),
            username: dbUsername,
            role: String(row[4]),
            photo_url: String(row[5] || '')
          }
        })).setMimeType(ContentService.MimeType.JSON);
      }
    }
  }
  
  return ContentService.createTextOutput(JSON.stringify({
    status: 'error',
    message: 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง'
  })).setMimeType(ContentService.MimeType.JSON);
}

function handleUpdateProfile(ss, data) {
  var sheet = ss.getSheetByName('ผู้ใช้งาน');
  var rows = sheet.getDataRange().getValues();
  var userId = String(data.user_id);
  
  for (var i = 1; i < rows.length; i++) {
    if (String(rows[i][1]) === userId) {
      var rowNum = i + 1;
      
      // อัปเดตชื่อผู้ใช้ (คอลัมน์ C)
      if (data.username) {
        sheet.getRange(rowNum, 3).setValue(data.username);
      }
      
      // อัปเดตรหัสผ่านใหม่ (คอลัมน์ D)
      if (data.new_password) {
        sheet.getRange(rowNum, 4).setValue(data.new_password);
      }
      
      var photoUrl = String(rows[i][5] || ''); // คอลัมน์ F
      
      // จัดการอัปโหลดรูปภาพลง Google Drive ผ่าน Folder ID ที่กำหนด
      if (data.image_base64 && data.image_name) {
        try {
          var folder;
          if (FOLDER_ID && FOLDER_ID !== "ใส่_Folder_ID_ที่นี่") {
            folder = DriveApp.getFolderById(FOLDER_ID);
          } else {
            // หากยังไม่ได้ใส่ Folder ID จะสร้างโฟลเดอร์สำรองให้อัตโนมัติ
            var folderName = "Friendship_System_Uploads";
            var folders = DriveApp.getFoldersByName(folderName);
            folder = folders.hasNext() ? folders.next() : DriveApp.createFolder(folderName);
          }
          
          var blob = Utilities.newBlob(
            Utilities.base64Decode(data.image_base64), 
            data.image_mime, 
            data.image_name
          );
          
          var file = folder.createFile(blob);
          file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
          
          var fileId = file.getId();
          photoUrl = "https://drive.google.com/uc?export=view&id=" + fileId;
          
          // บันทึกลงคอลัมน์ F (รูปภาพ)
          sheet.getRange(rowNum, 6).setValue(photoUrl);
        } catch (imgError) {
          // จัดการข้อผิดพลาดในการอัปโหลดรูปภาพ
        }
      }
      
      return ContentService.createTextOutput(JSON.stringify({
        status: 'success',
        message: 'อัปเดตโปรไฟล์สำเร็จ',
        photo_url: photoUrl
      })).setMimeType(ContentService.MimeType.JSON);
    }
  }
  
  return ContentService.createTextOutput(JSON.stringify({
    status: 'error',
    message: 'ไม่พบผู้ใช้งานที่ต้องการอัปเดต'
  })).setMimeType(ContentService.MimeType.JSON);
}
