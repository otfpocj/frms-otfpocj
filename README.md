# ระบบทะเบียนมิตรภาพ — ชุดไฟล์เริ่มต้น

## โครงสร้าง
```text
sys/
  index.php
  login.php
  dashboard.php
  logout.php
  con/
    config.php
gas/
  Code.gs
```

## 1) ตั้งค่า Google Apps Script
1. เปิด Apps Script ที่ผูกกับ Google Sheets ฐานข้อมูลระบบทะเบียนมิตรภาพ
2. วางเนื้อหา `gas/Code.gs` ลงในไฟล์ `Code.gs` แล้วบันทึก
3. ไปที่ **Project Settings → Script Properties**
4. เพิ่ม property:
   - Property: `API_TOKEN`
   - Value: สร้าง secret แบบสุ่มยาวอย่างน้อย 32 ตัวอักษร เก็บไว้เป็นความลับ
5. Deploy → New deployment → Web app
   - Execute as: Me
   - Who has access: เลือกตัวเลือกที่ทำให้ PHP server เรียก Web App ได้ตามตัวเลือกที่บัญชีแสดง (โดยทั่วไปอาจเป็น Anyone)
6. กด Deploy แล้วคัดลอก URL ที่ลงท้าย `/exec`
7. หากแก้โค้ดภายหลัง ให้ Deploy → Manage deployments → Edit → New version → Deploy

**ข้อสำคัญ:** endpoint นี้ไม่ส่งข้อมูลทั้งชีตออกมา แต่การตั้งค่าให้ Web App เรียกได้จากภายนอกยังต้องเก็บ `API_TOKEN` เป็นความลับ และควรตรวจสอบสิทธิ์การเข้าถึงโครงการ Apps Script/ชีตด้วย

## 2) ตั้งค่า Render
เพิ่ม Environment Variables ในบริการ Render ที่รัน PHP:
- `GAS_URL` = URL Web App ที่ลงท้าย `/exec`
- `GAS_API_KEY` = ค่าเดียวกับ `API_TOKEN` ใน Script Properties

อย่าใส่ secret ในไฟล์ PHP ที่จะอัปโหลดขึ้น GitHub และอย่าใส่ไว้ใน HTML/JavaScript ฝั่งผู้ใช้

## 3) ตรวจสอบชื่อแท็บและคอลัมน์
ใน Google Sheets ต้องมีแท็บชื่อ **ผู้ใช้งาน** และคอลัมน์เรียงตามนี้:
- A: ลำดับ
- B: รหัสประจำตัว
- C: ชื่อผู้ใช้
- D: รหัสผ่าน
- E: บทบาท
- F: รูปภาพ

โค้ดถือว่าแถวแรกเป็นหัวตาราง และเริ่มค้นหาผู้ใช้จากแถวที่ 2

## 4) เส้นทางหน้าเว็บ
- `sys/index.php`: ถ้าไม่มี session จะส่งไป `login.php`; ถ้ามี session จะส่งไป `dashboard.php`
- `sys/login.php`: แบบฟอร์มเข้าสู่ระบบและตรวจ CSRF token
- `sys/dashboard.php`: หน้าเริ่มต้นหลังเข้าสู่ระบบ
- `sys/logout.php`: ออกจากระบบและล้าง session
- `sys/con/config.php`: ฟังก์ชัน session และเรียก Apps Script จากฝั่ง PHP

ถ้าต้องการให้หน้าเว็บเปิดที่โดเมนหลักแล้วเข้าสู่ `/sys/` ให้ตั้งค่า root/redirect ของ Render ตามโครงสร้างโปรเจกต์ของคุณ หรือย้ายไฟล์ `sys` ไปยัง document root ตามที่โฮสต์รองรับ

## 5) ข้อจำกัดด้านรหัสผ่าน
โค้ด GAS นี้เปรียบเทียบค่ารหัสผ่านกับคอลัมน์ D แบบตรงตัว เพื่อให้ตรงกับโครงสร้างชีตที่ระบุไว้ หากคอลัมน์ D เก็บรหัสผ่านจริงแบบข้อความธรรมดา นี่ **ยังไม่ใช่การจัดเก็บรหัสผ่านที่ปลอดภัยสำหรับระบบใช้งานจริง** ควรวางแผนเปลี่ยนเป็น password hashing และออกแบบการตรวจสอบที่รองรับ hash ก่อนใช้งานจริงกับบัญชีจริง ห้ามใช้รหัสผ่านเดียวกับบริการอื่น

## 6) ถ้าเข้าสู่ระบบไม่ได้
- ตรวจว่า `GAS_URL` เป็น URL `/exec` ของ deployment ล่าสุด
- ตรวจว่า `GAS_API_KEY` ใน Render ตรงกับ `API_TOKEN` ใน Script Properties ทุกตัว
- ตรวจว่า deployment ใช้เวอร์ชันล่าสุดหลังแก้โค้ด
- ตรวจชื่อแท็บ `ผู้ใช้งาน` และคอลัมน์ C/D
- ดู Logs ใน Render และ Apps Script โดยอย่าบันทึกรหัสผ่านหรือ secret ลง log
