HighwayGo - Highway Service Platform
Frontend: HTML + CSS + JavaScript (static .html files)   Backend: PHP + MySQL (api.php JSON API)

SETUP (XAMPP / WAMP / any PHP 8+ hosting):
1. Folder ko htdocs (ya public_html) me copy karein: htdocs/highway-assist
2. Apache + MySQL start karein. config.php me DB user/password check karein.
3. Browser me kholein: http://localhost/highway-assist/install.php  (database + demo data ban jayega)
4. Uske baad install.php DELETE kar dein. Website: http://localhost/highway-assist/index.html

LOGINS: admin@highway.com/admin123 | provider@highway.com/provider123 | customer@highway.com/customer123
PANELS: /customer/index.html  /provider/index.html  /admin/index.html
FILES: *.html (pages) | assets/style.css | assets/app.js (shared JS) | api.php + config.php (PHP/MySQL backend)
AI: default me built-in assistant. Smart AI ke liye config.php me AI_API_KEY (Anthropic) daalein.
LIVE TRACKING: browser location allow karein. HTTPS ya localhost par hi GPS chalta hai. Map tiles ke liye internet chahiye.
