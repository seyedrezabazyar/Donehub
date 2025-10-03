Date Converter API Documentation

1. Convert Date

Convert a date between three calendars: Gregorian, Jalali, and Hijri.

Endpoint
GET /convert-date
| Parameter | Type   | Description                                      | Example      |
| --------- | ------ | ------------------------------------------------ | ------------ |
| `date`    | string | Input date in `YYYY-MM-DD` format                | `2025-10-03` |
| `from`    | string | Source calendar (`gregorian`, `jalali`, `hijri`) | `gregorian`  |
| `to`      | string | Target calendar (`gregorian`, `jalali`, `hijri`) | `jalali`     |

Response

Success (200)

{
  "success": true,
  "input": "2025-10-03",
  "calendar": {
    "from": "gregorian",
    "to": "jalali"
  },
  "direction": "gregorian → jalali",
  "output": "1404-07-11"
}

Error

{
  "success": false,
  "message": "Invalid calendar type"
}

or

{
  "success": false,
  "message": "Conversion direction not supported"
}

Examples

Gregorian → Jalali
curl "http://127.0.0.1:8000/convert-date?date=2025-10-03&from=gregorian&to=jalali"

Response
{
  "success": true,
  "input": "2025-10-03",
  "calendar": {"from": "gregorian", "to": "jalali"},
  "direction": "gregorian → jalali",
  "output": "1404-07-11"
}

Jalali → Hijri
curl "http://127.0.0.1:8000/convert-date?date=1404-07-11&from=jalali&to=hijri"

Response
{
  "success": true,
  "input": "1404-07-11",
  "calendar": {"from": "jalali", "to": "hijri"},
  "direction": "jalali → hijri",
  "output": "1447-05-09"
}

Hijri → Jalali
curl "http://127.0.0.1:8000/convert-date?date=1447-03-10&from=hijri&to=jalali"
Response
{
  "success": true,
  "input": "1447-03-10",
  "calendar": {"from": "hijri", "to": "jalali"},
  "direction": "hijri → jalali",
  "output": "1404-06-12"
}

Notes

Conversion between Jalali and Hijri is done indirectly through Gregorian.

If from and to are the same, the same input date will be returned.

All dates use the YYYY-MM-DD format.
