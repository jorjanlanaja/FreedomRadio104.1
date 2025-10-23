C:\xampp\htdocs\fm
│
├── index.php                 (main website)
├── database.sql              (import once into phpMyAdmin)
│
├── admin
│   ├── login.php             ← you are here
│   ├── dashboard.php
│   ├── settings.php
│   ├── logout.php
│   └── programs.php          (optional, for CRUD-ing shows)
│
├── includes
│   ├── config.php            (DB constants)
│   ├── db.php                (PDO singleton)
│   └── functions.php         (getSetting(), isAdminLoggedIn(), etc.)
│
├── assets
│   ├── css
│   │   ├── style.css         (public site theme)
│   │   └── admin.css         (admin pages theme)
│   ├── js
│   │   ├── main.js           (public site JS)
│   │   └── admin.js          (admin JS)
│   └── images                (uploaded logos, program images, etc.)
│
└── api
    ├── get-current-program.php
    ├── save-settings.php
    └── programs.php          (AJAX endpoints)