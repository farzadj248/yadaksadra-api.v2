<?php

namespace App\Helper;

use Carbon\Carbon;
use Illuminate\Support\Facades\Date;

class CalendarHelper
{

    private static $jalali = true; //Use Jalali Date, If set to false, falls back to gregorian
    private static $convert = true; //Convert numbers to Farsi characters in utf-8
    private static $timezone = null; //Timezone String e.g Asia/Tehran, Defaults to Server Timezone Settings
    private static $temp = array();

    /**
     * jDateTime::Constructor
     *
     * Pass these parameteres when creating a new instance
     * of this Class, and they will be used as defaults.
     * e.g $obj = new jDateTime(false, true, 'Asia/Tehran');
     * To use system defaults pass null for each one or just
     * create the object without any parameters.
     *
     * @author Sallar Kaboli
     * @param $convert bool Converts numbers to Farsi
     * @param $jalali bool Converts date to Jalali
     * @param $timezone string Timezone string
     */
    public function __construct($convert = null, $jalali = null, $timezone = null)
    {
        if ( $jalali   !== null ) self::$jalali = ($jalali === false) ? false : true;
        if ( $convert  !== null ) self::$convert = ($convert === false) ? false : true;
        if ( $timezone !== null ) self::$timezone = ($timezone != null) ? $timezone : null;
    }

    /**
     * jDateTime::Date
     *
     * Formats and returns given timestamp just like php's
     * built in date() function.
     * e.g:
     * $obj->date("Y-m-d H:i", time());
     * $obj->date("Y-m-d", time(), false, false, 'America/New_York');
     *
     * @author Sallar Kaboli
     * @param $format string Acceps format string based on: php.net/date
     * @param $stamp int Unix Timestamp (Epoch Time)
     * @param $convert bool (Optional) forces convert action. pass null to use system default
     * @param $jalali bool (Optional) forces jalali conversion. pass null to use system default
     * @param $timezone string (Optional) forces a different timezone. pass null to use system default
     * @return string Formatted input
     */
    public static function Time()
    {
        $h=Date::now()->hour;
        $m=Date::now()->minute;
        $s=Date::now()->second;
      return $h.':'.$m.':'.$s;
    }

    public static function Date(){
        $response=self::toJalali(Date::now()->year,Date::now()->month,Date::now()->day);
        $y=$response[0];
        $m=$response[1];
        $d=$response[2];
        return $y.'/'.$m.'/'.$d;
    }

    /**
     * jDateTime::gDate
     *
     * Same as jDateTime::Date method
     * but this one works as a helper and returns Gregorian Date
     * in case someone doesn't like to pass all those false arguments
     * to Date method.
     *
     * e.g. $obj->gDate("Y-m-d") //Outputs: 2011-05-05
     *      $obj->date("Y-m-d", false, false, false); //Outputs: 2011-05-05
     *      Both return the exact same result.
     *
     * @author Sallar Kaboli
     * @param $format string Acceps format string based on: php.net/date
     * @param $stamp int Unix Timestamp (Epoch Time)
     * @param $timezone string (Optional) forces a different timezone. pass null to use system default
     * @return string Formatted input
     */
    public static function gDate($format, $stamp = false, $timezone = null)
    {
        return self::date($format, $stamp, false, false, $timezone);
    }

    /**
     * jDateTime::Strftime
     *
     * Format a local time/date according to locale settings
     * built in strftime() function.
     * e.g:
     * $obj->strftime("%x %H", time());
     * $obj->strftime("%H", time(), false, false, 'America/New_York');
     *
     * @author Omid Pilevar
     * @param $format string Acceps format string based on: php.net/date
     * @param $stamp int Unix Timestamp (Epoch Time)
     * @param $jalali bool (Optional) forces jalali conversion. pass null to use system default
     * @param $timezone string (Optional) forces a different timezone. pass null to use system default
     * @return string Formatted input
     */
    public static function strftime($format, $stamp = false,$convert=false, $jalali = null, $timezone = null)
    {
        $str_format_code = array(
            "%a", "%A", "%d", "%e", "%j", "%u", "%w",
            "%U", "%V", "%W",
            "%b", "%B", "%h", "%m",
            "%C", "%g", "%G", "%y", "%Y",
            "%H", "%I", "%l", "%M", "%p", "%P", "%r", "%R", "%S", "%T", "%X", "%z", "%Z",
            "%c", "%D", "%F", "%s", "%x",
            "%n", "%t", "%%"
        );

        $date_format_code = array(
            "D", "l", "d", "j", "z", "N", "w",
            "W", "W", "W",
            "M", "F", "M", "m",
            "y", "y", "y", "y", "Y",
            "H", "h", "g", "i", "A", "a", "h:i:s A", "H:i", "s", "H:i:s", "h:i:s", "H", "H",
            "D j M H:i:s", "d/m/y", "Y-m-d", "U", "d/m/y",
            "\n", "\t", "%"
        );

        //Change Strftime format to Date format
        $format = str_replace($str_format_code, $date_format_code, $format);

        //Convert to date
        return self::date($format, $stamp,$convert, $jalali, $timezone);
    }

    /**
     * jDateTime::Mktime
     *
     * Creates a Unix Timestamp (Epoch Time) based on given parameters
     * works like php's built in mktime() function.
     * e.g:
     * $time = $obj->mktime(0,0,0,2,10,1368);
     * $obj->date("Y-m-d", $time); //Format and Display
     * $obj->date("Y-m-d", $time, false, false); //Display in Gregorian !
     *
     * You can force gregorian mktime if system default is jalali and you
     * need to create a timestamp based on gregorian date
     * $time2 = $obj->mktime(0,0,0,12,23,1989, false);
     *
     * @author Sallar Kaboli
     * @param $hour int Hour based on 24 hour system
     * @param $minute int Minutes
     * @param $second int Seconds
     * @param $month int Month Number
     * @param $day int Day Number
     * @param $year int Four-digit Year number eg. 1390
     * @param $jalali bool (Optional) pass false if you want to input gregorian time
     * @param $timezone string (Optional) acceps an optional timezone if you want one
     * @return int Unix Timestamp (Epoch Time)
     */
    public static function mktime($hour, $minute, $second, $month, $day, $year, $jalali = null, $timezone = null)
    {
        //Defaults
        $month = (intval($month) == 0) ? self::date('m') : $month;
        $day   = (intval($day)   == 0) ? self::date('d') : $day;
        $year  = (intval($year)  == 0) ? self::date('Y') : $year;

        //Convert to Gregorian if necessary
        if ( $jalali === true || ($jalali === null && self::$jalali === true) ) {
            list($year, $month, $day) = self::toGregorian($year, $month, $day);
        }

        //Create a new object and set the timezone if available
        $date = $year.'-'.sprintf("%02d", $month).'-'.sprintf("%02d", $day).' '.$hour.':'.$minute.':'.$second;

        if ( self::$timezone != null || $timezone != null ) {
            $obj = new \DateTime($date, new \DateTimeZone(($timezone != null) ? $timezone : self::$timezone));
        }
        else {
            $obj = new \DateTime($date);
        }

        //Return
        return $obj->format("U");
    }

    /**
     * jDateTime::Checkdate
     *
     * Checks the validity of the date formed by the arguments.
     * A date is considered valid if each parameter is properly defined.
     * works like php's built in checkdate() function.
     * Leap years are taken into consideration.
     * e.g:
     * $obj->checkdate(10, 21, 1390); // Return true
     * $obj->checkdate(9, 31, 1390);  // Return false
     *
     * You can force gregorian checkdate if system default is jalali and you
     * need to check based on gregorian date
     * $check = $obj->checkdate(12, 31, 2011, false);
     *
     * @author Omid Pilevar
     * @param $month int The month is between 1 and 12 inclusive.
     * @param $day int The day is within the allowed number of days for the given month.
     * @param $year int The year is between 1 and 32767 inclusive.
     * @param $jalali bool (Optional) pass false if you want to input gregorian time
     * @return bool
     */
    public static function checkdate($month, $day, $year, $jalali = null)
    {
        //Defaults
        $month = (intval($month) == 0) ? self::date('n') : intval($month);
        $day   = (intval($day)   == 0) ? self::date('j') : intval($day);
        $year  = (intval($year)  == 0) ? self::date('Y') : intval($year);

        //Check if its jalali date
        if ( $jalali === true || ($jalali === null && self::$jalali === true) )
        {
            $epoch = self::mktime(0, 0, 0, $month, $day, $year);

            if( self::date("Y-n-j", $epoch,false) == "$year-$month-$day" ) {
                $ret = true;
            }
            else{
                $ret = false;
            }
        }
        else //Gregorian Date
        {
            $ret = checkdate($month, $day, $year);
        }

        //Return
        return $ret;
    }

    /**
     * System Helpers below
     *
     */
    private static function filterArray($needle, $heystack, $always = array())
    {
        foreach($heystack as $k => $v)
        {
            if( !in_array($v, $needle) && !in_array($v, $always) )
                unset($heystack[$k]);
        }

        return $heystack;
    }

    private static function getDayNames($day, $shorten = false, $len = 1, $numeric = false)
    {
        $ret = '';
        switch ( strtolower($day) ) {
            case 'sat': case 'saturday': $ret = 'شنبه'; $n = 1; break;
            case 'sun': case 'sunday': $ret = 'یکشنبه'; $n = 2; break;
            case 'mon': case 'monday': $ret = 'دوشنبه'; $n = 3; break;
            case 'tue': case 'tuesday': $ret = 'سه شنبه'; $n = 4; break;
            case 'wed': case 'wednesday': $ret = 'چهارشنبه'; $n = 5; break;
            case 'thu': case 'thursday': $ret = 'پنجشنبه'; $n = 6; break;
            case 'fri': case 'friday': $ret = 'جمعه'; $n = 7; break;
        }
        return ($numeric) ? $n : (($shorten) ? mb_substr($ret, 0, $len, 'UTF-8') : $ret);
    }

    public static function getMonthNames($month, $shorten = false, $len = 3)
    {
        $ret = '';
        switch ( $month ) {
            case '1': $ret = 'فروردین'; break;
            case '2': $ret = 'اردیبهشت'; break;
            case '3': $ret = 'خرداد'; break;
            case '4': $ret = 'تیر'; break;
            case '5': $ret = 'مرداد'; break;
            case '6': $ret = 'شهریور'; break;
            case '7': $ret = 'مهر'; break;
            case '8': $ret = 'آبان'; break;
            case '9': $ret = 'آذر'; break;
            case '10': $ret = 'دی'; break;
            case '11': $ret = 'بهمن'; break;
            case '12': $ret = 'اسفند'; break;
        }
        return ($shorten) ? mb_substr($ret, 0, $len, 'UTF-8') : $ret;
    }

    private static function convertNumbers($matches)
    {
        $farsi_array = array("۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹");
        $english_array = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        return str_replace($english_array, $farsi_array, $matches);
    }

    private static function div($a, $b)
    {
        return (int) ($a / $b);
    }

    /**
     * Gregorian to Jalali Conversion
     * Copyright (C) 2000  Roozbeh Pournader and Mohammad Toossi
     *
     */
    public static function toJalali($g_y, $g_m, $g_d)
    {

        $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

        $gy = $g_y-1600;
        $gm = $g_m-1;
        $gd = $g_d-1;

        $g_day_no = 365*$gy+self::div($gy+3, 4)-self::div($gy+99, 100)+self::div($gy+399, 400);

        for ($i=0; $i < $gm; ++$i)
            $g_day_no += $g_days_in_month[$i];
        if ($gm>1 && (($gy%4==0 && $gy%100!=0) || ($gy%400==0)))
            $g_day_no++;
        $g_day_no += $gd;

        $j_day_no = $g_day_no-79;

        $j_np = self::div($j_day_no, 12053);
        $j_day_no = $j_day_no % 12053;

        $jy = 979+33*$j_np+4*self::div($j_day_no, 1461);

        $j_day_no %= 1461;

        if ($j_day_no >= 366) {
            $jy += self::div($j_day_no-1, 365);
            $j_day_no = ($j_day_no-1)%365;
        }

        for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; ++$i)
            $j_day_no -= $j_days_in_month[$i];
        $jm = $i+1;
        $jd = $j_day_no+1;

        return array($jy, $jm, $jd);

    }

    /**
     * Jalali to Gregorian Conversion
     * Copyright (C) 2000  Roozbeh Pournader and Mohammad Toossi
     *
     */
    public static function toGregorian($j_y, $j_m, $j_d)
    {

        $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

        $jy = $j_y-979;
        $jm = $j_m-1;
        $jd = $j_d-1;

        $j_day_no = 365*$jy + self::div($jy, 33)*8 + self::div($jy%33+3, 4);
        for ($i=0; $i < $jm; ++$i)
            $j_day_no += $j_days_in_month[$i];

        $j_day_no += $jd;

        $g_day_no = $j_day_no+79;

        $gy = 1600 + 400*self::div($g_day_no, 146097);
        $g_day_no = $g_day_no % 146097;

        $leap = true;
        if ($g_day_no >= 36525) {
            $g_day_no--;
            $gy += 100*self::div($g_day_no,  36524);
            $g_day_no = $g_day_no % 36524;

            if ($g_day_no >= 365)
                $g_day_no++;
            else
                $leap = false;
        }

        $gy += 4*self::div($g_day_no, 1461);
        $g_day_no %= 1461;

        if ($g_day_no >= 366) {
            $leap = false;

            $g_day_no--;
            $gy += self::div($g_day_no, 365);
            $g_day_no = $g_day_no % 365;
        }

        for ($i = 0; $g_day_no >= $g_days_in_month[$i] + ($i == 1 && $leap); $i++)
            $g_day_no -= $g_days_in_month[$i] + ($i == 1 && $leap);
        $gm = $i+1;
        $gd = $g_day_no+1;

        return array($gy, $gm, $gd);

    }

    public static function gregorian_to_jalali($gy, $gm, $gd,$type) {
        $g_d_m = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);
        $gy2 = ($gm > 2)? ($gy + 1) : $gy;
        $days = 355666 + (365 * $gy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100)) + ((int)(($gy2 + 399) / 400)) + $gd + $g_d_m[$gm - 1];
        $jy = -1595 + (33 * ((int)($days / 12053)));
        $days %= 12053;
        $jy += 4 * ((int)($days / 1461));
        $days %= 1461;
        if ($days > 365) {
            $jy += (int)(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        if ($days < 186) {
            $jm = 1 + (int)($days / 31);
            $jd = 1 + ($days % 31);
        } else{
            $jm = 7 + (int)(($days - 186) / 30);
            $jd = 1 + (($days - 186) % 30);
        }

        $m_name='';
        switch ($jm){
            case 1:
                $m_name="فروردین";
                break;

            case 2:
                $m_name="اردیبهشت";
                break;

            case 3:
                $m_name="خرداد";
                break;

            case 4:
                $m_name="تیر";
                break;

            case 5:
                $m_name="مرداد";
                break;

            case 6:
                $m_name="شهریور";
                break;

            case 7:
                $m_name="مهر";
                break;

            case 8:
                $m_name="آبان";
                break;

            case 9:
                $m_name="آذر";
                break;

            case 10:
                $m_name="دی";
                break;

            case 11:
                $m_name="بهمن";
                break;

            case 12:
                $m_name="اسفند";
                break;
        }

        $d_name="";
        switch (Carbon::now()->format('w')){
            case 0:
                $d_name="یکشنبه";
                break;

            case 1:
                $d_name="دوشنبه";
                break;

            case 2:
                $d_name="سشنبه";
                break;

            case 3:
                $d_name="چهارشنبه";
                break;

            case 4:
                $d_name="پنجشنبه";
                break;

            case 5:
                $d_name="جمعه";
                break;

            case 6:
                $d_name="شنبه";
                break;
        }

        switch ($type){
            case 1:
                return $d_name.' '.$jd.' '.$m_name;
                break;

            case 2:
                return [$jy,$jm,$jd];
                break;

            case 3:
                return $d_name.' '.$jd.' '.$m_name.' '.$jy;
                break;
        }
    }

    public static function gregorian_to_jalali2($date) {
        $e2=explode(' ',$date);
        $e3=explode('-',$e2[0]);

        $gy=$e3[0];
        $gm=$e3[1];
        $gd=$e3[2];

        $g_d_m = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);
        $gy2 = ($gm > 2)? ($gy + 1) : $gy;
        $days = 355666 + (365 * $gy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100)) + ((int)(($gy2 + 399) / 400)) + $gd + $g_d_m[$gm - 1];
        $jy = -1595 + (33 * ((int)($days / 12053)));
        $days %= 12053;
        $jy += 4 * ((int)($days / 1461));
        $days %= 1461;
        if ($days > 365) {
            $jy += (int)(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        if ($days < 186) {
            $jm = 1 + (int)($days / 31);
            $jd = 1 + ($days % 31);
        } else{
            $jm = 7 + (int)(($days - 186) / 30);
            $jd = 1 + (($days - 186) % 30);
        }

        $m_name='';
        switch ($jm){
            case 1:
                $m_name="فروردین";
                break;

            case 2:
                $m_name="اردیبهشت";
                break;

            case 3:
                $m_name="خرداد";
                break;

            case 4:
                $m_name="تیر";
                break;

            case 5:
                $m_name="مرداد";
                break;

            case 6:
                $m_name="شهریور";
                break;

            case 7:
                $m_name="مهر";
                break;

            case 8:
                $m_name="آبان";
                break;

            case 9:
                $m_name="آذر";
                break;

            case 10:
                $m_name="دی";
                break;

            case 11:
                $m_name="بهمن";
                break;

            case 12:
                $m_name="اسفند";
                break;
        }

        return $jd.' '.$m_name.' '.$jy;
    }

    public static function jalali_format($date){
        $e=explode("/",$date);

        switch ($e[1]){
            case 1:
                $m_name="فروردین";
                break;

            case 2:
                $m_name="اردیبهشت";
                break;

            case 3:
                $m_name="خرداد";
                break;

            case 4:
                $m_name="تیر";
                break;

            case 5:
                $m_name="مرداد";
                break;

            case 6:
                $m_name="شهریور";
                break;

            case 7:
                $m_name="مهر";
                break;

            case 8:
                $m_name="آبان";
                break;

            case 9:
                $m_name="آذر";
                break;

            case 10:
                $m_name="دی";
                break;

            case 11:
                $m_name="بهمن";
                break;

            case 12:
                $m_name="اسفند";
                break;
        }

        return $e[2].' '.$m_name.' '. $e[0];
    }

    public static function entebah($m,$d,$lg,$lat,$seconds=1,$dslst=1,$farsi=1){
        $a_2=array(107.695,90.833,0,90.833,94.5,0);
        $doy_1=(($m<7)?($m-1):6) + (($m-1)*30) + $d;
        for($h=0,$i=0;$i<6;$i++){
            $s_m=$m;$s_lg=$lg;
            if($i<5){
                $doy=$doy_1+($h/24);
                $s_m=74.2023+(0.98560026*$doy);
                $s_l=-2.75043+(0.98564735*$doy);
                $s_lst=8.3162159+(0.065709824*floor($doy))+(24.06570984*fmod($doy,1))+($s_lg/15);
                $s_omega=(4.85131-(0.052954*$doy))*0.0174532;
                $s_ep=(23.4384717+(0.00256*cos($s_omega)))*0.0174532;
                $s_u=$s_m;
                for($s_i=1;$s_i<5;$s_i++){
                    $s_u=$s_u-(($s_u-$s_m-(0.95721*sin(0.0174532*$s_u)))/(1-(0.0167065*cos(0.0174532*$s_u))));
                }
                $s_v=2*(atan(tan(0.00872664*$s_u)*1.0168)*57.2957);
                $s_theta=($s_v-$s_m-2.75612-(0.00479*sin($s_omega))+(0.98564735*$doy))*0.0174532;
                $s_delta=asin(sin($s_ep)*sin($s_theta))*57.2957;
                $s_alpha=57.2957*atan2(cos($s_ep)*sin($s_theta),cos($s_theta));
                if($s_alpha>=360)$s_alpha-=360;
                $s_ha=$s_lst-($s_alpha/15);
                $s_zohr=fmod($h-$s_ha,24);
                $loc2hor=((acos(((cos(0.0174532*$a_2[$i])-sin(0.0174532*$s_delta)*sin(0.0174532*$lat))/cos(0.0174532*$s_delta)/cos(0.0174532*$lat)))*57.2957)/15);
                $azan[$i]=fmod((($i<2)?($s_zohr-$loc2hor):(($i>2)?$s_zohr+$loc2hor:$s_zohr)),24);
            }else{
                $azan[$i]=($azan[0]+$azan[3]+24)/2;
            }
            $x=$azan[$i];
            if($dslst==1 and $doy_1>1 and $doy_1<186){$x++;}else{$dslst=0;}
            if($x<0){$x+=24;}elseif($x>=24){$x-=24;}
            $hor=(int)($x);
            $ml=fmod($x,1)*60;
            $min=(int)($ml);
            $mr=round($ml);
            if($mr==60){$mr=0;$hor++;}
            $sec=(int)(fmod($ml,1)*60);
            $a_1[$i]=(($hor<10)?'0':'').$hor.':'.( ($seconds==0) ? ((($mr<10)?'0':'').$mr) : ((($min<10)?'0':'').$min.':'.(($sec<10)?'0':'').$sec) );
            if($h==0){$h=$azan[$i];$i--;}else{$h=0;}
        }
        $out=array(
            's'=>$a_1[0],
            't'=>$a_1[1],
            'z'=>$a_1[2],
            'g'=>$a_1[3],
            'm'=>$a_1[4],
            'n'=>$a_1[5],
            'month'=>$m,
            'day'=>$d,
            'longitude'=>$lg,
            'latitude'=>$lat,
            'show_seconds'=>$seconds,
            'daylight_saving_time'=>$dslst,
            'farsi_numbers'=>$farsi
        );
        if($farsi==1)$out=str_replace(array('0','1','2','3','4','5','6','7','8','9','.'),array('۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٫'),$out);
        return $out;
    }
}
