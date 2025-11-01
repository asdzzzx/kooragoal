# قائمة الملفات الرئيسية

```
yacine-tv-football/
├── index.php
├── .htaccess
├── football_db.sql
├── README.md
├── INSTALLATION.md
├── FILES_CHECKLIST.md
├── composer.json
├── config/
│   ├── config.php
│   └── Database.php (غير مستخدم، يعتمد النظام على `src/Services/Database.php`)
├── src/
│   ├── bootstrap.php
│   └── Services/
│       ├── ApiClient.php
│       ├── Container.php
│       ├── Database.php
│       ├── Logger.php
│       ├── Scheduler.php
│       ├── Security/
│       │   ├── AuthManager.php
│       │   └── CsrfTokenManager.php
│       └── Updaters/UpdateManager.php
├── admin/
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── fixtures.php
│   ├── live_fixtures.php
│   ├── leagues.php
│   ├── teams.php
│   ├── standings.php
│   ├── scorers.php
│   ├── statistics.php
│   ├── match_details.php
│   ├── match_events.php
│   ├── match_stats.php
│   ├── updates.php
│   ├── users.php
│   ├── admins.php (تحويل إلى users)
│   ├── logs.php
│   ├── settings.php
│   ├── profile.php
│   ├── run-task.php (متوافق مع الإصدارات السابقة)
│   ├── includes/
│   │   ├── header.php
│   │   ├── footer.php
│   │   ├── sidebar.php
│   │   └── check_auth.php
│   └── ajax/
│       ├── manual_update.php
│       ├── initial_fetch.php
│       ├── update_live.php
│       ├── update_match.php
│       ├── delete_match.php
│       └── check_updates.php
├── public/
│   ├── index.php
│   ├── today.php
│   ├── live.php
│   ├── league.php
│   ├── match.php
│   ├── events.php
│   ├── stats.php
│   ├── standings.php
│   ├── scorers.php
│   ├── lineups.php
│   └── includes/
│       ├── site-header.php
│       └── site-footer.php
├── includes/bootstrap.php
├── database/migrations/001_schema.sql
├── logs/
│   └── .htaccess
└── storage/
    └── cache/ (احتياطي، غير مستخدم حاليًا)
```
