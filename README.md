# Kooragoal Local Sports Hub

منصة محلية متكاملة لسحب بيانات كرة القدم من **API-FOOTBALL** وتخزينها في قاعدة بيانات MySQL مع لوحة تحكم وحماية متقدمة. النظام مصمم ليعمل على استضافة CPanel (Apache + PHP) مع مهام مجدولة عبر Cron Jobs.

## المتطلبات

- PHP 8.0 أو أحدث مع ملحقات: `curl`, `pdo_mysql`, `json`
- MySQL 5.7 أو أحدث
- صلاحية لإنشاء مهام مجدولة (Cron)
- إمكانية تفعيل HTTPS وتهيئة الجدار الناري/Cloudflare

## التثبيت السريع

1. **رفع الملفات**: انسخ محتوى هذا المستودع داخل مجلد الموقع على الاستضافة (`public_html/` أو مجلد فرعي) مع التأكد أن مجلد `public/` هو جذر الويب.
2. **إنشاء قاعدة البيانات**: أنشئ قاعدة البيانات `yacinetv_football_db` (أو عدل الاسم في `config/config.php`) ثم نفّذ ملف المخطط `database/schema.sql` لإضافة الجداول.
3. **تهيئة الإعدادات**:
   - حدث بيانات الدخول وقيم الاتصال بقاعدة البيانات داخل `config/config.php`.
   - حدّث قائمة الدوريات في `config/leagues.php` حسب الـ ID الرسمي في API-FOOTBALL.
   - الرمز الافتراضي للوصول إلى الـ API هو `local-api-token` ويمكن تغييره من الملف `config/tokens.php`.
   - لتوليد رمز API محلي جديد استخدم: `php -r "echo password_hash('TOKEN', PASSWORD_BCRYPT);"` ثم أضفه داخل `config/tokens.php`.
4. **الأذونات**: امنح مجلد `storage/` صلاحية الكتابة (755 أو 775) حتى يتم إنشاء السجلات والنسخ الاحتياطية.
5. **تأمين النطاق**: حدّث المصفوفة `domain_whitelist` في `config/config.php` بالقيم المسموح لها بالوصول إلى الـ API.
6. **لوحة التحكم**: افتح `https://yoursite.com/admin/` وسجل الدخول بالمستخدم الافتراضي (`admin` / `admin123`) ثم غيّر كلمة المرور من الملف `config/config.php`.
7. **المهام المجدولة**: أضف المهام التالية من CPanel (أو عبر `crontab -e`):

| التوقيت | الأمر |
| --- | --- |
| يوميًا 00:05 | `php /path/to/project/cron/get_daily_fixtures.php` |
| كل 25 ثانية | `php /path/to/project/cron/get_live_matches.php` |
| كل 50 ثانية | `php /path/to/project/cron/get_events_stats.php` |
| كل 5 دقائق | `php /path/to/project/cron/get_lineups.php` |
| كل ساعتين | `php /path/to/project/cron/get_standings.php` |
| شهريًا | `php /path/to/project/cron/get_players_teams.php` |
| يوميًا 04:00 | `php /path/to/project/cron/create_backup.php` |

> **ملاحظة**: يمكن استخدام اسكربت Shell لتشغيل مهمة التحديث السريع كل 25 ثانية داخل Screen أو Supervisor إذا كان الـ Cron لا يدعم أقل من دقيقة.

## نقاط الحماية

- التحقق من النطاقات المسموح بها قبل الرد على أي طلب API.
- التحقق من رموز الدخول (Bearer Token) لكل Endpoint.
- تحديد معدل استهلاك الـ API (Rate Limiting) لكل رمز/عنوان IP.
- تسجيل كافة العمليات في `storage/logs/app.log` للمتابعة.
- إنشاء نسخة احتياطية يومية مضغوطة لكل البيانات المهمة.

## الواجهات المتاحة

| الغرض | الرابط |
| --- | --- |
| قائمة الدوريات | `/api/leagues` |
| مباريات اليوم | `/api/fixtures/today` |
| المباريات الجارية | `/api/fixtures/live` |
| ترتيب دوري | `/api/standings/{league_id}` |
| الهدافين | `/api/scorers/{league_id}` |
| أحداث مباراة | `/api/events/{fixture_id}` |
| إحصائيات مباراة | `/api/stats/{fixture_id}` |
| التشكيل | `/api/lineup/{fixture_id}` |

جميع الروابط تتطلب تمرير رمز صالح عبر `Authorization: Bearer TOKEN` أو `?token=TOKEN`.

## تخصيص النظام

- لتعديل فترة التخزين المؤقت للطلبات عدل قيمة `rate_limit` أو استخدم كاش خارجي مثل Redis.
- لإضافة دوري جديد، أضف سجلاً في `config/leagues.php` ثم نفذ `cron/get_standings.php` لتحميل البيانات.
- يمكن تغيير تصميم لوحة التحكم عبر تعديل الملفات في `public/assets/css/`.

## الدعم

في حال ظهور أي خطأ، تحقق من:
- سجل الأخطاء في `storage/logs/app.log`
- حدود API-FOOTBALL اليومية `6200` طلب/يوم تقريبًا حسب الجدولة الحالية
- إعدادات الاتصال بقاعدة البيانات والصلاحيات داخل الاستضافة

