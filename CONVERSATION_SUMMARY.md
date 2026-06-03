# ملخص جلسة العمل — Wafra Gulf System

> **التاريخ:** 2026-05-31
> **المشروع:** Laravel 11 API backend لإدارة كروت العمولات
> **الخادم:** system-wafragulf.online (IP 173.231.231.39, cPanel)
> **النشر:** `.\deploy.ps1 -SkipCommit`

---

## 1. نظرة عامة على النظام

نظام إدارة كروت العمولات (Commission Cards) لشركة وفرة الخليجية، يدعم:

- **4 أدوار:** المدير المالي (Finance Admin) / مدير الفرع (Branch Manager) / كول سنتر (CC) / مشاهد (Viewer)
- **واجهة ثنائية اللغة** (عربي/إنجليزي) عبر قواميس JavaScript و `data-i18n`
- **استيراد Excel** + تصدير PDF/Excel
- **مركز اتصال (CC)** لإنشاء الكروت وإرسالها للفروع

---

## 2. التغييرات المنجزة في هذه الجلسة

### 2.1 لوحة المتابعة (Dashboard)
| التغيير | الملف |
|---------|-------|
| عرض **الحسابات الفريدة** (COUNT DISTINCT) بدل إجمالي السجلات | `CommissionCardController.php` (`unique_accounts`) |
| إصلاح تسمية مخطط الفروع لتعرض الرقم الفريد بدل 5000 | `dashboard/index.blade.php` |
| **Caching** للبيانات عند تبديل اللغة (بدون إعادة طلب API) | `dashboard/index.blade.php` |

### 2.2 الترجمة الكاملة للإنجليزية
- **القائمة الجانبية لصفحات الكروت** (`_nav.blade.php`) — تُترجم عبر `applyLang` العام
- **رسائل API** (تعذّر الاتصال / غير مصرح / خطأ الخادم) — ثنائية اللغة
- **صفحة التقارير** — كل دوال التصدير (Excel/PDF) ورؤوس الأعمدة وأسماء الـ sheets
- **صفحة الإعدادات** — رسائل خطأ المدير/الموظف
- **خانات البحث** — placeholders تتبدّل مع اللغة (search بدل بحث)
- **القائمة المنبثقة بالموبايل** — عبر `getMobTitle()` و `data-i18n`

### 2.3 إصلاح صفحة الكروت (عدم التحميل)
- **السبب:** استدعاء `loadFilterOptions()` و `loadCards()` بالتوازي يضاعف الحمل
- **الحل:** دمج التحميل + زر إعادة محاولة عند الفشل + إرسال `broker_name` للـ API

### 2.4 إصلاح زر تعديل المديرين
- **السبب الجذري:** الـ API يُرجع الصلاحيات كـ **object** `{cards:true,...}` بينما `setPerms()` يتوقع **array** → `TypeError` صامت
- **الحل:** تحويل تلقائي object → array قبل `setPerms()`
- **إضافة:** خانة رفع صورة المدير في modal التعديل (مع معاينة فورية)

### 2.5 إصلاح توليد التقارير (الإصدار القديم)
- **السبب الجذري:** `rptPageApplyLang()` كانت تستخدم `textContent` على `pt-tbl-result` فتحذف العنصر الابن `rpt-count` → `renderTable()` يرمي `TypeError` على null
- **الحل:** حفظ العنصر الابن + `try-catch` حول الترجمة

### 2.6 إعادة تصميم صفحة التقارير بالكامل (جديد)
صفحتان منفصلتان بدل 7 تبويبات معقدة:

**`/reports` — لوحة التقارير (تحميل تلقائي):**
- 5 KPIs + 5 رسوم بيانية (شهري combo / NEW vs SUB / مقارنة الفروع / أفضل البروكرات / توزيع العمولات + الحالات)
- الفلاتر تُطبّق فوراً بـ debounce — بدون زر "توليد"

**`/reports/table` — جدول البيانات:**
- Pagination على المتصفح (50/100/200/500)
- بحث لحظي + فلاتر متقدمة + KPI strip + تصدير Excel/PDF

**ملفات:** `reports/index.blade.php` (إعادة كتابة) + `reports/table.blade.php` (جديد) + route + controller method

### 2.7 صفحة دليل التشغيل (إعادة بناء كاملة)
- **4 تبويبات للأدوار** + مخططات تدفق + خطوات مفصّلة
- **جدول مقارنة الصلاحيات** (11 صلاحية)
- **دليل المستخدم** (4 بطاقات) + **ملخص الأدوار** + **خارطة الطريق** (4 مراحل)
- ثنائي اللغة بالكامل

---

## 3. مشكلة صفحة الدليل — التشخيص الكامل

### المشكلة الأصلية: HTTP 500
**السبب الجذري:** قواعد CSS التي تبدأ بـ `@` (مثل `@media` و `@keyframes`) داخل ملف Blade.
Blade يعامل `@word(...)` كـ **directive** ويحاول تنفيذه → `Unknown directive: media` → HTTP 500.

### الحلول المُجرّبة (بالترتيب):
1. ❌ `@@media` escape — يعمل أحياناً لكن غير متسق
2. ✅ **`@verbatim ... @endverbatim`** حول كتلة `<style>` — الحل الجذري: Blade يتجاهل كل شيء بالداخل
3. ✅ **إعادة كتابة كاملة** بأسماء classes بسيطة (`gd-*`)

### الوضع الحالي:
- الملف نظيف: لا `@` directives شاردة، لا `{{ }}` متضاربة، البنية متوازنة
- `@verbatim` يحيط بالـ CSS → `@media` بالداخل آمن

---

## 4. ⚠️ مشكلة "Server is temporarily limiting requests" (429)

### السبب:
سكربت النشر `deploy.ps1` يقوم في **كل عملية نشر** بـ:
- الكتابة فوق **~22 ملف cache** (لإبطالها)
- إعادة رفع **~29 ملف blade** (لتجاوز الـ cache)
= **~51 طلب API في ثوانٍ معدودة**

مع تكرار النشر السريع (10+ مرات)، الاستضافة (cPanel/LiteSpeed) فعّلت **rate limiting / DDoS protection** → يردّ بـ 429 على كل الطلبات.

### الحل:
1. **انتظار 15-60 دقيقة** حتى يُرفع الحظر تلقائياً (لا يحتاج تدخل)
2. **تقليل عدد عمليات النشر** المتتالية
3. **تحسين سكربت النشر** ليرفع فقط ملفات blade المُعدّلة بدل كل الـ 29 (مذكور في القسم 5)

---

## 5. تحسين مقترح لسكربت النشر

السكربت الحالي يعيد رفع **كل** ملفات blade في كل نشر (Step 5C). الأفضل:
- رفع فقط الملفات المُتغيّرة (`git diff`)
- إبطال cache الملفات المُتغيّرة فقط بدل كلها
- إضافة `Start-Sleep` بين الطلبات لتجنب الـ rate limit

هذا يقلّل طلبات API من ~51 إلى ~3-5 لكل نشر.

---

## 6. قيود وملاحظات

- **GitHub push يفشل** بسبب نقص صلاحية `workflow` على الـ PAT — لكن النشر يكمل عبر cPanel API مباشرة (لا يؤثر)
- **`git geometric-repack` يفشل** (Permission denied على multi-pack-index) — لا يؤثر على commits
- الملفات المؤقتة (`tmp_writer.php`, `login-designs-preview.html`) مُستثناة من النشر و git

---

## 7. الملفات المعدّلة (الأبرز)

```
app/Http/Controllers/Api/CommissionCardController.php   — unique_accounts
app/Http/Controllers/Web/WebController.php              — reportsTable()
routes/web.php                                          — /reports/table
resources/views/dashboard/index.blade.php              — KPIs + caching
resources/views/reports/index.blade.php                — إعادة تصميم كاملة
resources/views/reports/table.blade.php                — جديد
resources/views/guide/index.blade.php                  — إعادة بناء كاملة
resources/views/managers/index.blade.php               — إصلاح التعديل + صورة
resources/views/cards/index.blade.php                  — إصلاح التحميل
resources/views/layouts/app.blade.php                  — ترجمة + getMobTitle
resources/views/settings/index.blade.php               — ترجمة رسائل
resources/views/cards/search.blade.php                 — placeholder
resources/views/cards/tree.blade.php                   — toast bilingual
```

---

*تم إنشاء هذا الملخص تلقائياً في نهاية الجلسة.*
