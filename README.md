# ระบบทะเบียนมิตรภาพ — เวอร์ชันไม่ใช้ API Token

## ไฟล์
```text
sys/
  index.php
  login.php
  dashboard.php
  logout.php
  con/config.php
gas/
  Code.gs
```

## ตั้งค่า Google Apps Script
1. เปิด Apps Script ที่ผูกกับ Google Sheets ฐานข้อมูลระบบทะเบียนมิตรภาพ
2. แทนที่โค้ดใน `Code.gs` ด้วยไฟล์ `gas/Code.gs` แล้วบันทึก
3. Deploy → New deployment → Web app
4. เลือก Execute as: Me
5. เลือกสิทธิ์การเข้าถึงที่ทำให้ PHP บน Render เรียก Web App ได้ ตามตัวเลือกที่บัญชีแสดง
6. Deploy แล้วคัดลอก URL ที่ลงท้าย `/exec`
7. หลังแก้โค้ด ให้ Deploy → Manage deployments → Edit → New version → Deploy

## ตั้งค่า Render
เพิ่ม Environment Variable เพียงรายการเดียว:
- `GAS_URL` = URL ของ Google Apps Script Web App ที่ลงท้าย `/exec`

เวอร์ชันนี้ไม่มี `API_TOKEN` และไม่มี `GAS_API_KEY`

## โครงสร้าง Google Sheets
แท็บต้องชื่อ `ผู้ใช้งาน` และคอลัมน์เป็น:
- A: ลำดับ
- B: รหัสประจำตัว
- C: ชื่อผู้ใช้
- D: รหัสผ่าน
- E: บทบาท
- F: รูปภาพ

แถวแรกเป็นหัวตาราง และข้อมูลเริ่มจากแถวที่ 2

## หน้าเว็บ
- `sys/index.php`: ตรวจ session แล้วส่งไปหน้า login หรือ dashboard
- `sys/login.php`: แบบฟอร์มเข้าสู่ระบบ พร้อม CSRF token
- `sys/dashboard.php`: หน้าเริ่มต้นหลังเข้าสู่ระบบ
- `sys/logout.php`: ออกจากระบบ
- `sys/con/config.php`: ตั้งค่า session และเรียก Apps Script

## ข้อจำกัดด้านความปลอดภัย
ตามที่กำหนด เวอร์ชันนี้ไม่ใช้ API Token ระหว่าง PHP กับ Apps Script ดังนั้น URL ของ Apps Script ที่เปิดให้ภายนอกเรียกได้อาจถูกผู้อื่นเรียก endpoint เข้าสู่ระบบโดยตรง และอาจถูกทดลองรหัสผ่านได้ โค้ดนี้ไม่ส่งข้อมูลทั้งชีต แต่ไม่ได้ทำให้ endpoint เป็นส่วนตัว การจำกัดการเข้าถึงด้วยสิทธิ์บัญชี Google หรือการย้ายการตรวจสอบข้อมูลไปยัง backend ที่ควบคุมสิทธิ์ได้ จะปลอดภัยกว่า

โค้ดนี้เปรียบเทียบรหัสผ่านกับค่าคอลัมน์ D แบบตรงตัว หากชีตเก็บรหัสผ่านเป็นข้อความธรรมดา ยังไม่ปลอดภัยสำหรับบัญชีจริง ควรเปลี่ยนเป็นการเก็บ password hash และเพิ่ม rate limiting ก่อนใช้จริง
