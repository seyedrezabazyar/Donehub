<?php

namespace Modules\DateConverter\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\DateConverter\Services\CalendarConverter;

class DateConverterController extends Controller
{
    protected $converter;

    public function __construct(CalendarConverter $converter)
    {
        $this->converter = $converter;
    }

    public function convert(Request $request)
    {
        $date = $request->query('date');
        $from = strtolower($request->query('from'));
        $to = strtolower($request->query('to'));

        $allowedCalendars = ['gregorian', 'jalali', 'hijri'];

        if (!in_array($from, $allowedCalendars) || !in_array($to, $allowedCalendars)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid calendar type'
            ]);
        }

        if ($from === $to) {
            return response()->json([
                'success' => true,
                'input' => $date,
                'calendar' => ['from' => $from, 'to' => $to],
                'direction' => "$from → $to",
                'output' => $date
            ]);
        }

        try {
            switch ("$from-$to") {
                // مسیرهای مستقیم
                case 'gregorian-jalali':
                    $output = $this->converter->gregorianToJalali($date);
                    break;

                case 'jalali-gregorian':
                    $output = $this->converter->jalaliToGregorian($date);
                    break;

                case 'gregorian-hijri':
                    $output = $this->converter->gregorianToHijri($date);
                    break;

                case 'hijri-gregorian':
                    $output = $this->converter->hijriToGregorian($date);
                    break;

                // مسیرهای غیرمستقیم از طریق Gregorian
                case 'jalali-hijri':
                    $g = $this->converter->jalaliToGregorian($date);
                    $output = $this->converter->gregorianToHijri($g);
                    break;

                case 'hijri-jalali':
                    $g = $this->converter->hijriToGregorian($date);
                    $output = $this->converter->gregorianToJalali($g);
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Conversion direction not supported'
                    ]);
            }

            return response()->json([
                'success' => true,
                'input' => $date,
                'calendar' => ['from' => $from, 'to' => $to],
                'direction' => "$from → $to",
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date format'
            ]);
        }
    }
}
