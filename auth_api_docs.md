# مستندات API ماژول احراز هویت (Auth)

این مستندات به توسعه‌دهندگان فرانت‌اند کمک می‌کند تا با API ماژول احراز هویت کار کنند.

---

## ۱. اطلاعات کلی

### URL پایه (Base URL)
تمامی آدرس‌های API که در این مستند ذکر شده‌اند، به دنبال URL پایه برنامه شما قرار می‌گیرند. پیشوند اصلی این ماژول `/api/auth` است.
```
/api/auth
```

### احراز هویت (Authentication)
بسیاری از اندپوینت‌ها نیازمند احراز هویت هستند. توکن دسترسی (`access_token`) باید در هدر `Authorization` با پیشوند `Bearer` ارسال شود.
```
Authorization: Bearer <YOUR_ACCESS_TOKEN>
```

---

## ۲. جریان احراز هویت عمومی

### ۲.۱. بررسی وجود کاربر
- **متد:** `POST` | **آدرس:** `/api/auth/check-user`
- **توضیح:** بررسی می‌کند آیا کاربری با شناسه (ایمیل/موبایل) وجود دارد.
- **درخواست:** `{ "identifier": "user@example.com" }`

### ۲.۲. ارسال کد یکبار مصرف (OTP)
- **متد:** `POST` | **آدرس:** `/api/auth/send-otp`
- **توضیح:** کد OTP را به شناسه کاربر ارسال می‌کند.
- **درخواست:** `{ "identifier": "user@example.com" }`

### ۲.۳. تایید کد یکبار مصرف (ورود/ثبت‌نام)
- **متد:** `POST` | **آدرس:** `/api/auth/verify-otp`
- **توضیح:** کد OTP را تایید کرده و در صورت نیاز کاربر جدید می‌سازد.
- **درخواست:** `{ "identifier": "user@example.com", "otp": "123456", "name": "نام کاربر" }`

### ۲.۴. ورود با رمز عبور
- **متد:** `POST` | **آدرس:** `/api/auth/login-password`
- **توضیح:** ورود با استفاده از شناسه و رمز عبور.
- **درخواست:** `{ "identifier": "user@example.com", "password": "user_password" }`

### ۲.۵. تازه‌سازی توکن (Refresh Token)
- **متد:** `POST` | **آدرس:** `/api/auth/refresh`
- **توضیح:** دریافت `access_token` جدید با استفاده از `refresh_token`. (نیاز به احراز هویت با `refresh_token`)

---

## ۳. مدیریت پروفایل کاربر
(نیاز به احراز هویت با `access_token`)

### ۳.۱. دریافت اطلاعات کاربر
- **متد:** `GET` | **آدرس:** `/api/auth/user`

### ۳.۲. خروج از حساب کاربری
- **متد:** `POST` | **آدرس:** `/api/auth/logout`

### ۳.۳. خروج از تمام دستگاه‌ها
- **متد:** `POST` | **آدرس:** `/api/auth/logout-all`

### ۳.۴. به‌روزرسانی پروفایل
- **متد:** `POST` | **آدرس:** `/api/auth/profile/update`
- **درخواست:** `{ "name": "نام جدید", "username": "new_username", ... }`

### ۳.۵. تنظیم رمز عبور
- **متد:** `POST` | **آدرس:** `/api/auth/password/set`
- **درخواست:** `{ "password": "...", "password_confirmation": "..." }`

### ۳.۶. تغییر رمز عبور
- **متد:** `POST` | **آدرس:** `/api/auth/password/update`
- **درخواست:** `{ "current_password": "...", "password": "...", "password_confirmation": "..." }`

### ۳.۷. ارسال و تایید ایمیل
- `POST /api/auth/email/send-verification` | **درخواست:** `{ "email": "..." }`
- `POST /api/auth/email/verify` | **درخواست:** `{ "email": "...", "otp": "..." }`

### ۳.۸. ارسال و تایید موبایل
- `POST /api/auth/phone/send-verification` | **درخواست:** `{ "phone": "..." }`
- `POST /api/auth/phone/verify` | **درخواست:** `{ "phone": "...", "otp": "..." }`

---

## ۴. مدیریت کاربران (ادمین)
(نیاز به احراز هویت و دسترسی ادمین)

### ۴.۱. لیست کاربران
- **متد:** `GET` | **آدرس:** `/api/auth/users`
- **توضیح:** دریافت لیست کاربران با فیلتر، مرتب‌سازی و صفحه‌بندی.
- **پارامترها:** `search`, `sort_by`, `sort_order`, `per_page`, `page`

### ۴.۲. آمار کاربران
- **متد:** `GET` | **آدرس:** `/api/auth/users/statistics`

### ۴.۳. نمایش کاربر خاص
- **متد:** `GET` | **آدرس:** `/api/auth/users/{id}`

### ۴.۴. به‌روزرسانی کاربر
- **متد:** `PUT` | **آدرس:** `/api/auth/users/{id}`
- **درخواست:** `{ "name": "...", "email": "...", ... }`

### ۴.۵. حذف کاربر
- **متد:** `DELETE` | **آدرس:** `/api/auth/users/{id}`

### ۴.۶. قفل/آزاد کردن حساب
- **متد:** `POST` | **آدرس:** `/api/auth/users/{id}/toggle-lock`

### ۴.۷. بازنشانی رمز عبور
- **متد:** `POST` | **آدرس:** `/api/auth/users/{id}/reset-password`
- **درخواست:** `{ "password": "...", "password_confirmation": "..." }`

### ۴.۸. تایید دستی ایمیل/موبایل
- `POST /api/auth/users/{id}/verify-email`
- `POST /api/auth/users/{id}/verify-phone`

---

## ۵. مدیریت نقش‌ها (ادمین)
(نیاز به احراز هویت و دسترسی ادمین)

### ۵.۱. لیست نقش‌ها
- **متد:** `GET` | **آدرس:** `/api/auth/roles`
- **توضیح:** لیست تمام نقش‌ها را برمی‌گرداند.

### ۵.۲. ایجاد نقش جدید
- **متد:** `POST` | **آدرس:** `/api/auth/roles`
- **دسترسی:** `roles.create`
- **درخواست:** `{ "name": "new_role", "display_name": "New Role", "permission_ids": [1, 2] }`

### ۵.۳. نمایش نقش خاص
- **متد:** `GET` | **آدرس:** `/api/auth/roles/{id}`

### ۵.۴. به‌روزرسانی نقش
- **متد:** `PUT` | **آدرس:** `/api/auth/roles/{id}`
- **دسترسی:** `roles.edit`
- **درخواست:** `{ "display_name": "Updated Name", "description": "..." }`

### ۵.۵. حذف نقش
- **متد:** `DELETE` | **آدرس:** `/api/auth/roles/{id}`
- **دسترسی:** `roles.delete`

### ۵.۶. اختصاص/حذف نقش به کاربر
- **دسترسی:** `users.manage_roles`
- `POST /api/auth/roles/user/{userId}/assign` | **درخواست:** `{ "role_id": ... }`
- `POST /api/auth/roles/user/{userId}/remove` | **درخواست:** `{ "role_id": ... }`

### ۵.۷. دریافت کاربران یک نقش
- **متد:** `GET` | **آدرس:** `/api/auth/roles/{id}/users`

---

## ۶. مدیریت دسترسی‌ها (ادمین)
(نیاز به احراز هویت و دسترسی ادمین)

### ۶.۱. لیست دسترسی‌ها
- **متد:** `GET` | **آدرس:** `/api/auth/permissions`
- **توضیح:** لیست تمام دسترسی‌ها را به صورت گروه‌بندی شده برمی‌گرداند.

### ۶.۲. ایجاد دسترسی جدید
- **متد:** `POST` | **آدرس:** `/api/auth/permissions`
- **دسترسی:** `roles.edit`
- **درخواست:** `{ "name": "posts.create", "display_name": "Create Posts", "group": "Posts" }`

### ۶.۳. به‌روزرسانی دسترسی
- **متد:** `PUT` | **آدرس:** `/api/auth/permissions/{id}`
- **دسترسی:** `roles.edit`
- **درخواست:** `{ "display_name": "...", "group": "...", "description": "..." }`

### ۶.۴. حذف دسترسی
- **متد:** `DELETE` | **آدرس:** `/api/auth/permissions/{id}`
- **دسترسی:** `roles.edit`

### ۶.۵. دریافت دسترسی‌های یک نقش
- **متد:** `GET` | **آدرس:** `/api/auth/permissions/role/{roleId}`
- **توضیح:** لیست دسترسی‌های اختصاص داده شده به یک نقش خاص را برمی‌گرداند.

### ۶.۶. به‌روزرسانی دسترسی‌های یک نقش
- **متد:** `PUT` | **آدرس:** `/api/auth/permissions/role/{roleId}`
- **دسترسی:** `roles.edit`
- **توضیح:** لیست دسترسی‌های یک نقش را جایگزین می‌کند.
- **درخواست:** `{ "permission_ids": [1, 2, 3] }`

---

## ۷. مدل‌های داده (Data Models)

### آبجکت Tokens
```json
{
  "access_token": "string",
  "refresh_token": "string"
}
```

### آبجکت User
```json
{
  "id": "integer",
  "name": "string",
  "username": "string|null",
  "email": "string|null",
  "phone": "string|null",
  "email_verified_at": "datetime|null",
  "phone_verified_at": "datetime|null",
  "avatar_url": "string|null",
  "is_admin": "boolean",
  "has_password": "boolean",
  "roles": [
    {
      "id": "integer",
      "name": "string",
      "display_name": "string"
    }
  ],
  "permissions": [
    {
      "id": "integer",
      "name": "string",
      "display_name": "string",
      "group": "string"
    }
  ]
}
```

### آبجکت Role
```json
{
  "id": "integer",
  "name": "string",
  "display_name": "string",
  "description": "string|null",
  "users_count": "integer"
}
```

### آبجکت Permission
```json
{
  "id": "integer",
  "name": "string",
  "display_name": "string",
  "group": "string",
  "description": "string|null"
}
```