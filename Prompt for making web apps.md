🔧 Complete AI Prompt – “Build a Full-Stack FM-Radio Website with Live Facebook Video Embed & CMS”
1. Project Scope
Create a responsive FM-radio station website (public + admin) that:
Streams live audio (URL configurable)
Embeds Facebook-Live VIDEO (URL configurable)
Displays program schedule (CRUD)
Provides a Bootstrap-5 CMS (admin login, settings, programs)
Uses PHP + MySQL + Bootstrap 5 only (no frameworks)
2. Folder Tree Required
Copy
fm-radio/
├── index.php                 (public home page)
├── admin/
│   ├── login.php            (auth)
│   ├── dashboard.php        (stats + FB-live player)
│   ├── settings.php         (station + FB embed URL)
│   ├── programs.php         (CRUD schedule)
│   └── logout.php
├── includes/
│   ├── config.php           (DB constants)
│   ├── db.php               (PDO singleton)
│   └── functions.php        (getSetting(), isAdminLoggedIn(), etc.)
├── assets/
│   ├── css/style.css        (public theme)
│   ├── css/admin.css        (admin theme)
│   ├── js/index.js          (player + volume + now-playing refresh)
│   └── images/              (uploaded logos)
├── api/
│   └── get-current-program.php (AJAX now-playing)
└── database.sql             (import once)
3. Database (MySQL)
Tables:
settings (key, value) – station name, live-stream URL, facebook_embed_url, colors, contact, socials
programs (id, title, description, host, day_of_week, start_time, end_time, is_active, image)
admins (id, username, password) – default admin / pass1234
4. Public Pages (Bootstrap 5)
A. index.php
Hero, audio player, Facebook-Live VIDEO (only if facebook_embed_url set), program schedule (7-day tabs), about, contact, footer
Dynamic colours from settings (--primary-color, --secondary-color)
Now-playing refreshes every 30 s via AJAX → api/get-current-program.php
B. assets/js/index.js
Play/pause button, volume slider (localStorage), background-play, tiny visualiser (optional canvas)
5. Admin CMS (Bootstrap 5)
A. admin/login.php
Username / password → session → redirect dashboard
B. admin/dashboard.php
Stats cards (total/active programs, station name, stream host)
Facebook-Live VIDEO embedded (reads facebook_embed_url from settings)
Table of recent programs (quick links to edit)
C. admin/settings.php
Three tabs: General, Appearance, Security
General: station name, description, live-stream URL, Facebook Embed URL, contact, socials
Appearance: primary/secondary colour pickers, logo upload
Security: change admin password
UPSERT logic (INSERT … ON DUPLICATE KEY UPDATE) so first-save always works
Active-tab memory after save (tiny JS)
D. admin/programs.php
Full CRUD: add, edit, delete, active toggle
Reorder by day + start time (MySQL FIELD())
Image upload optional
6. Dynamic Features
Table
Copy
Feature	Source
Audio stream URL	settings.live_stream_url
Facebook-Live VIDEO	settings.facebook_embed_url (embed iframe)
Station colours	CSS vars --primary-color, --secondary-color
Now-playing	AJAX → api/get-current-program.php every 30 s
Logo	uploaded file → /assets/images/logo_*.ext
Admin login	password_hash() / password_verify()
7. Tech Stack Constraints
PHP 8+ (use __DIR__, typed params, null-coalesce)
MySQL 5.7+ (PDO prepared statements)
Bootstrap 5.3 CDN only – no custom build
No frameworks (no Laravel, no Vue, no React)
No build tools – plain .php, .css, .js files
8. Quick Start Commands (for AI)
Create database fm_radio and import database.sql
Drop files into xampp/htdocs/fm/
Browse http://localhost/fm/ (public) and http://localhost/fm/admin/login.php (admin)
Default login: admin / pass1234
Paste Facebook Embed URL in Settings → General → Facebook Embed URL → Save
Public home page now shows audio stream + Facebook-Live video
9. Deliverables Checklist
[ ] All files listed in section 2 exist and work
[ ] index.php embeds Facebook-Live video (configurable)
[ ] Admin can change video URL without touching code
[ ] UPSERT settings – no “0 rows affected” error
[ ] Responsive – mobile & desktop
[ ] No PHP warnings – use __DIR__, htmlspecialchars(), isset()
[ ] Security – PDO prepared statements, password_hash, requireAdminLogin()
10. One-Sentence Summary for AI
“Build a PHP-MySQL-Bootstrap5 FM-radio public site with configurable audio stream + Facebook-Live video embed, plus a full admin CMS (login, settings, program CRUD) using only plain PHP files – no frameworks, no build tools.”