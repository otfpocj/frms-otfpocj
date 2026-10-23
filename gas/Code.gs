/**
 * ระบบทะเบียนมิตรภาพ — Login endpoint
 *
 * Required Script Property:
 *   API_TOKEN = same secret as Render's GAS_API_KEY
 *
 * Deploy as a Web App. The PHP server sends POST form data.
 * This endpoint only performs login and does not expose the whole spreadsheet.
 */

function doPost(e) {
  try {
    const params = (e && e.parameter) ? e.parameter : {};
    const expectedToken = PropertiesService.getScriptProperties().getProperty('API_TOKEN') || '';
    const suppliedToken = String(params.api_key || '');

    if (!expectedToken || !constantTimeEquals_(expectedToken, suppliedToken)) {
      return jsonResponse_({ success: false, message: 'Unauthorized' });
    }

    const action = String(params.action || '');
    if (action !== 'login') {
      return jsonResponse_({ success: false, message: 'Unsupported action' });
    }

    const username = String(params.username || '').trim();
    const password = String(params.password || '');

    if (!username || !password) {
      return jsonResponse_({ success: false, message: 'Missing credentials' });
    }

    const spreadsheet = SpreadsheetApp.getActiveSpreadsheet();
    if (!spreadsheet) {
      return jsonResponse_({ success: false, message: 'Spreadsheet unavailable' });
    }

    const sheet = spreadsheet.getSheetByName('ผู้ใช้งาน');
    if (!sheet) {
      return jsonResponse_({ success: false, message: 'User sheet not found' });
    }

    const values = sheet.getDataRange().getDisplayValues();
    // Expected columns: A ลำดับ, B รหัสประจำตัว, C ชื่อผู้ใช้,
    // D รหัสผ่าน, E บทบาท, F รูปภาพ. First row is the header.
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
    return jsonResponse_({ success: false, message: 'Internal error' });
  }
}

function doGet() {
  // Do not return sheet contents through GET.
  return jsonResponse_({ success: false, message: 'Use POST' });
}

function constantTimeEquals_(a, b) {
  a = String(a);
  b = String(b);
  if (a.length !== b.length) return false;

  let result = 0;
  for (let i = 0; i < a.length; i++) {
    result |= a.charCodeAt(i) ^ b.charCodeAt(i);
  }
  return result === 0;
}

function jsonResponse_(data) {
  return ContentService
    .createTextOutput(JSON.stringify(data))
    .setMimeType(ContentService.MimeType.JSON);
}
