#!/bin/bash

# رنگ‌ها برای خروجی
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}=== Password Generator Module - API Test ===${NC}\n"

# آدرس API
API_URL="http://localhost:8000/api/password-generator/generate"

# تست 1: همه گزینه‌ها فعال
echo -e "${YELLOW}Test 1: All options enabled${NC}"
curl -X POST $API_URL \
  -H "Content-Type: application/json" \
  -d '{
    "length": 16,
    "include_numbers": true,
    "include_lowercase": true,
    "include_uppercase": true,
    "include_symbols": true
  }' | json_pp
echo -e "\n"

# تست 2: فقط اعداد و حروف کوچک
echo -e "${YELLOW}Test 2: Only numbers and lowercase${NC}"
curl -X POST $API_URL \
  -H "Content-Type: application/json" \
  -d '{
    "length": 12,
    "include_numbers": true,
    "include_lowercase": true,
    "include_uppercase": false,
    "include_symbols": false
  }' | json_pp
echo -e "\n"

# تست 3: فقط حروف بزرگ
echo -e "${YELLOW}Test 3: Only uppercase letters${NC}"
curl -X POST $API_URL \
  -H "Content-Type: application/json" \
  -d '{
    "length": 10,
    "include_numbers": false,
    "include_lowercase": false,
    "include_uppercase": true,
    "include_symbols": false
  }' | json_pp
echo -e "\n"

# تست 4: خطا - هیچ گزینه‌ای انتخاب نشده
echo -e "${YELLOW}Test 4: Error - No options selected${NC}"
curl -X POST $API_URL \
  -H "Content-Type: application/json" \
  -d '{
    "length": 10,
    "include_numbers": false,
    "include_lowercase": false,
    "include_uppercase": false,
    "include_symbols": false
  }' | json_pp
echo -e "\n"

# تست 5: خطا - طول نامعتبر
echo -e "${YELLOW}Test 5: Error - Invalid length${NC}"
curl -X POST $API_URL \
  -H "Content-Type: application/json" \
  -d '{
    "length": 100,
    "include_numbers": true
  }' | json_pp
echo -e "\n"

# تست 6: رمز خیلی کوتاه
echo -e "${YELLOW}Test 6: Very short password (length=1)${NC}"
curl -X POST $API_URL \
  -H "Content-Type: application/json" \
  -d '{
    "length": 1,
    "include_symbols": true
  }' | json_pp
echo -e "\n"

# تست 7: رمز خیلی بلند
echo -e "${YELLOW}Test 7: Maximum length password (length=50)${NC}"
curl -X POST $API_URL \
  -H "Content-Type: application/json" \
  -d '{
    "length": 50,
    "include_numbers": true,
    "include_lowercase": true,
    "include_uppercase": true,
    "include_symbols": true
  }' | json_pp
echo -e "\n"

echo -e "${GREEN}=== Tests Completed ===${NC}"