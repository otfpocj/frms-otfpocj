const express = require('express');
const path = require('path');
const app = express();
const PORT = process.env.PORT || 3000;

// *** กรุณานำ URL ที่ได้จากการ Deploy Web App ใน Google Apps Script มาใส่ตรงนี้ ***
const GAS_WEB_APP_URL = "https://script.google.com/macros/s/AKfycbyedQvODQJgpdUo4hcFGt90Zp99x5WInBSL1KupKnyEAOwFmb1pnK-r3TFand7ujr5S/exec";

app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));

// สั่งให้ส่งหน้า index.html เมื่อผู้ใช้เข้าถึง URL เส้นทางต่างๆ
const routes = ['/', '/login', '/home', '/profile', '/history', '/manage_friends', '/request_rank', '/request_name', '/approve_ranks', '/approve_names', '/event_log'];

routes.forEach(route => {
    app.get(route, (req, res) => {
        res.sendFile(path.join(__dirname, 'public', 'index.html'));
    });
});

// Proxy API: รับคำขอจาก Frontend บน Render แล้วยิงไปหา Google Apps Script
app.post('/api/gas', async (req, res) => {
    try {
        const response = await fetch(GAS_WEB_APP_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(req.body)
        });
        const data = await response.json();
        res.json(data);
    } catch (error) {
        console.error("Proxy Error:", error);
        res.status(500).json({ success: false, message: "เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์หลัก" });
    }
});

app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});
