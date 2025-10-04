# Password Generator Module

ماژول تولید رمز عبور تصادفی

## نصب

این ماژول به صورت خودکار توسط Laravel Auto-Discovery بارگذاری می‌شود.

## استفاده

### API Endpoint

**POST** `/api/password-generator/generate`

### پارامترها

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| length | integer | Yes | طول رمز عبور (1-50) |
| include_numbers | boolean | No | شامل اعداد |
| include_lowercase | boolean | No | شامل حروف کوچک |
| include_uppercase | boolean | No | شامل حروف بزرگ |
| include_symbols | boolean | No | شامل سیمبل‌ها |

**نکته:** حداقل یکی از 4 گزینه باید `true` باشد.

### مثال درخواست
```json
{
    "length": 16,
    "include_numbers": true,
    "include_lowercase": true,
    "include_uppercase": true,
    "include_symbols": true
}