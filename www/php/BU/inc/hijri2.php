<?PHP
//print_r(Greg2Hijri(date("d")+1,date("m"),date("Y"),false));
//$today = Greg2Hijri(date("d")+1,date("m"),date("Y"),false);
//$today = $today['year'].'/'.$today['month'].'/'.$today['day'];
//echo $today;
/*
 * Code converted by Saleh Jamal (@salehjamal)
 */
function intPart($float)
{
    if ($float < -0.0000001)
        return ceil($float - 0.0000001);
    else
        return floor($float + 0.0000001);
}

function Hijri2Greg($day, $month, $year, $string = false)
{
    $day   = (int) $day;
    $month = (int) $month;
    $year  = (int) $year;

    $jd = intPart((11*$year+3) / 30) + 354 * $year + 30 * $month - intPart(($month-1)/2) + $day + 1948440 - 385;

    if ($jd > 2299160)
    {
        $l = $jd+68569;
        $n = intPart((4*$l)/146097);
        $l = $l-intPart((146097*$n+3)/4);
        $i = intPart((4000*($l+1))/1461001);
        $l = $l-intPart((1461*$i)/4)+31;
        $j = intPart((80*$l)/2447);
        $day = $l-intPart((2447*$j)/80);
        $l = intPart($j/11);
        $month = $j+2-12*$l;
        $year  = 100*($n-49)+$i+$l;
    }
    else
    {
        $j = $jd+1402;
        $k = intPart(($j-1)/1461);
        $l = $j-1461*$k;
        $n = intPart(($l-1)/365)-intPart($l/1461);
        $i = $l-365*$n+30;
        $j = intPart((80*$i)/2447);
        $day = $i-intPart((2447*$j)/80);
        $i = intPart($j/11);
        $month = $j+2-12*$i;
        $year = 4*$k+$n+$i-4716;
    }
    
    $data = array();
    $date['year']  = $year;
    $date['month'] = $month;
    $date['day']   = $day;
    
    if (!$string)
        return $date;
    else
        return     "{$year}-{$month}-{$day}";
}

function Greg2Hijri($day, $month, $year, $string = false)
{
    $day   = (int) $day;
    $month = (int) $month;
    $year  = (int) $year;

    if (($year > 1582) or (($year == 1582) and ($month > 10)) or (($year == 1582) and ($month == 10) and ($day > 14)))
    {
        $jd = intPart((1461*($year+4800+intPart(($month-14)/12)))/4)+intPart((367*($month-2-12*(intPart(($month-14)/12))))/12)-
        intPart( (3* (intPart(  ($year+4900+    intPart( ($month-14)/12)     )/100)    )   ) /4)+$day-32075;
    }
    else
    {
        $jd = 367*$year-intPart((7*($year+5001+intPart(($month-9)/7)))/4)+intPart((275*$month)/9)+$day+1729777;
    }

    $l = $jd-1948440+10632;
    $n = intPart(($l-1)/10631);
    $l = $l-10631*$n+354;
    $j = (intPart((10985-$l)/5316))*(intPart((50*$l)/17719))+(intPart($l/5670))*(intPart((43*$l)/15238));
    $l = $l-(intPart((30-$j)/15))*(intPart((17719*$j)/50))-(intPart($j/16))*(intPart((15238*$j)/43))+29;
    
    $month = intPart((24*$l)/709);
    $day   = $l-intPart((709*$month)/24);
    $year  = 30*$n+$j-30;
    
    $date = array();
    $date['year']  = $year;
    $date['month'] = $month;
    $date['day']   = $day;

    if (!$string)
        return $date;
    else
        return     "{$year}-{$month}-{$day}";
}
?>