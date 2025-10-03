<?php

namespace Modules\DateConverter\Services;

class CalendarConverter
{
    /********** میلادی ⇄ جلالی **********/
    public function gregorianToJalali($gDate)
    {
        list($gy, $gm, $gd) = explode('-', $gDate);
        $gy = (int)$gy; $gm = (int)$gm; $gd = (int)$gd;

        $g_d_m = [0,31,59,90,120,151,181,212,243,273,304,334];
        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = 355666 + (365*$gy) + intval(($gy2+3)/4) - intval(($gy2+99)/100) + intval(($gy2+399)/400) + $gd + $g_d_m[$gm-1];

        $jy = -1595 + (33*intval($days/12053));
        $days %= 12053;
        $jy += 4*intval($days/1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intval(($days-1)/365);
            $days = ($days-1)%365;
        }

        $jm = ($days < 186) ? 1 + intval($days/31) : 7 + intval(($days-186)/30);
        $jd = 1 + (($days < 186) ? $days%31 : ($days-186)%30);

        return sprintf('%04d-%02d-%02d', $jy, $jm, $jd);
    }

    public function jalaliToGregorian($jDate)
    {
        list($jy, $jm, $jd) = explode('-', $jDate);
        $jy = (int)$jy; $jm = (int)$jm; $jd = (int)$jd;

        $jy += 1595;
        $days = -355668 + (365*$jy) + intval($jy/33)*8 + intval((($jy%33)+3)/4) + $jd;

        if ($jm < 7) {
            $days += ($jm-1)*31;
        } else {
            $days += (($jm-7)*30) + 186;
        }

        $gy = 400*intval($days/146097);
        $days %= 146097;

        if ($days > 36524) {
            $gy += 100*intval(--$days/36524);
            $days %= 36524;
            if ($days >= 365) $days++;
        }

        $gy += 4*intval($days/1461);
        $days %= 1461;

        if ($days > 365) {
            $gy += intval(($days-1)/365);
            $days = ($days-1)%365;
        }

        $gd = $days+1;
        $leap = ($gy%4==0 && $gy%100!=0) || ($gy%400==0);
        $months = [0,31,($leap?29:28),31,30,31,30,31,31,30,31,30,31];

        for ($gm=1; $gm<=12; $gm++) {
            if ($gd <= $months[$gm]) break;
            $gd -= $months[$gm];
        }

        return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
    }

    /********** میلادی ⇄ قمری **********/
    public function gregorianToHijri($gDate)
    {
        list($gy, $gm, $gd) = explode('-', $gDate);
        $gy = (int)$gy; $gm = (int)$gm; $gd = (int)$gd;

        $jd = intval((1461*($gy + 4800 + intval(($gm - 14)/12)))/4) +
              intval((367*($gm - 2 - 12*intval(($gm - 14)/12)))/12) -
              intval((3*intval(($gy + 4900 + intval(($gm - 14)/12))/100))/4) + $gd - 32075;

        $l = $jd - 1948440 + 10632;
        $n = intval(($l - 1)/10631);
        $l = $l - 10631*$n + 354;
        $j = (intval((10985 - $l)/5316)) * (intval((50*$l)/17719)) + (intval($l/5670))*(intval((43*$l)/15238));
        $l = $l - (intval((30 - $j)/15))* (intval((17719*$j)/50)) - (intval($j/16))*(intval((15238*$j)/43)) + 29;
        $hm = intval($l/30)+1;
        $hd = ($l % 30)+1;
        $hy = 30*$n + $j - 30;

        return sprintf('%04d-%02d-%02d', $hy, $hm, $hd);
    }

    public function hijriToGregorian($hDate)
    {
        list($hy, $hm, $hd) = explode('-', $hDate);
        $hy = (int)$hy; $hm = (int)$hm; $hd = (int)$hd;

        $jd = intval((11*$hy + 3)/30) + 354*$hy + 30*$hm - intval(($hm-1)/2) + $hd + 1948440 - 385;

        if ($jd > 2299160) {
            $l = $jd + 68569;
            $n = intval((4*$l)/146097);
            $l = $l - intval((146097*$n + 3)/4);
            $i = intval((4000*($l+1))/1461001);
            $l = $l - intval((1461*$i)/4) + 31;
            $j = intval((80*$l)/2447);
            $gd = $l - intval((2447*$j)/80);
            $l = intval($j/11);
            $gm = $j + 2 - 12*$l;
            $gy = 100*($n-49) + $i + $l;
        } else {
            $j = $jd + 1402;
            $k = intval(($j-1)/1461);
            $l = $j - 1461*$k;
            $n = intval(($l-1)/365) - intval($l/1461);
            $i = $l - 365*$n + 30;
            $j = intval((80*$i)/2447);
            $gd = $i - intval((2447*$j)/80);
            $i = intval($j/11);
            $gm = $j + 2 - 12*$i;
            $gy = 4*$k + $n + $i - 4716;
        }

        return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
    }

    /********** تبدیل عمومی با پشتیبانی کامل **********/
    public function convert($date, $from, $to)
    {
        $from = strtolower($from);
        $to = strtolower($to);

        if ($from === $to) return $date;

        // مسیرهای مستقیم
        if ($from === 'gregorian' && $to === 'jalali') return $this->gregorianToJalali($date);
        if ($from === 'jalali' && $to === 'gregorian') return $this->jalaliToGregorian($date);
        if ($from === 'gregorian' && $to === 'hijri') return $this->gregorianToHijri($date);
        if ($from === 'hijri' && $to === 'gregorian') return $this->hijriToGregorian($date);

        // مسیرهای غیرمستقیم از طریق Gregorian
        if ($from === 'jalali' && $to === 'hijri') {
            $g = $this->jalaliToGregorian($date);
            return $this->gregorianToHijri($g);
        }

        if ($from === 'hijri' && $to === 'jalali') {
            $g = $this->hijriToGregorian($date);
            return $this->gregorianToJalali($g);
        }

        throw new \Exception("Conversion direction not supported");
    }
}
