/**
 * ระบบทะเบียนมิตรภาพ — Login endpoint (ไม่ใช้ API Token)
 *
 * Google Sheet tab: ผู้ใช้งาน
 * A ลำดับ | B รหัสประจำตัว | C ชื่อผู้ใช้ | D รหัสผ่าน | E บทบาท | F รูปภาพ
 *
 * Endpoint นี้รับเฉพาะ POST action=login และคืนเฉพาะข้อมูลผู้ใช้ที่เข้าสู่ระบบสำเร็จ
 * ห้ามเพิ่ม endpoint ส่งข้อมูลทั้งชีตโดยไม่ตรวจสอบสิทธิ์
 */

function doPost(e) {
  try {
    const params = (e && e.parameter) ? e.parameter : {};
    if (String(params.action || '') !== 'login') {
      return jsonResponse_({ success: false, message: 'Unsupported action' });
    }

    const username = String(params.username || '').trim();
    const password = String(params.password || '');

    if (!username || !password || username.length > 200 || password.length > 2000) {
      return jsonResponse_({ success: false, message: 'Invalid credentials' });
    }

    const spreadsheet = SpreadsheetApp.getActiveSpreadsheet();
    if (!spreadsheet) {
      return jsonResponse_({ success: false, message: 'Service unavailable' });
    }

    const sheet = spreadsheet.getSheetByName('ผู้ใช้งาน');
    if (!sheet) {
      return jsonResponse_({ success: false, message: 'Service unavailable' });
    }

    const values = sheet.getDataRange().getDisplayValues();

    // First row is the header. Username is column C; password is column D.
    for (let i = 1; i < values.length; i++) {
      const row = values[i];
      const storedUsername = String(row[2] || '').trim();
      const storedPassword = String(row[3] || '');

      if (storedUsername === username && storedPassword === password) {
        return jsonResponse_({
          success: true,
          user: {
            id: String(row[1] || ''),
            username: storedUsername,
            role: String(row[4] || ''),
            image: String(row[5] || '')
          }
        });
      }
    }

    return jsonResponse_({ success: false, message: 'Invalid credentials' });
  } catch (err) {
    console.error(err);
    return jsonResponse_({ success: false, message: 'Service unavailable' });
  }
}

function doGet() {
  // No spreadsheet data is exposed through GET.
  return jsonResponse_({ success: false, message: 'Use POST' });
}

function jsonResponse_(data) {
  return ContentService
    .createTextOutput(JSON.stringify(data))
    .setMimeType(ContentService.MimeType.JSON);
}
