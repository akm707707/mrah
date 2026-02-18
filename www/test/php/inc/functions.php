<?php
//  header('Access-Control-Allow-Origin:*'); 
//  header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS, REQUEST');
//  header('Content-Type: application/json; charset=utf-8');
//  header('Access-Control-Max-Age: 3628800');
mb_internal_encoding('utf-8');
include 'config.php';
include('hijri2.php');	/* include Georgian to Hijri date conversion class */

date_default_timezone_set("Asia/Riyadh");
/* $hdate = Greg2Hijri(date("d"),date("m"),date("Y"),false);
 */
 // $repdate = $hdate['day'].$hdate['month'].substr($hdate['year'],-1);
// $Hdate = $hdate['year'].'/'.$hdate['month'].'/'.$hdate['day']+2;	
$Gdate = date("Y/m/d");

// function arabic(string $text): string
// {
    // $search  = ['female','male','yellow', 'orange', 'blue', 'red', 'green', 'pink', 'white', 'harri', 'najdi', 'naimi', 'swakni', 'rufaidi', 'assaf', 'awasi', 'barbari', 'habashi', 'droper', 'aradi', 'merino', 'goat' , 'other', 'barley50', 'barley40', 'alfalfa', 'hay', 'rowdas', 'mixed', 'bluepanic', 'corn50', 'corn40', 'bread', 'full', 'partial', 'debt', 'water', 'minerals', 'salts', 'vitamins', 'vaccines', 'kilogram', 'miligram', 'gram', 'mililiter', 'liter', 'cube', 'monthly', 'yearly', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', 'active', 'inactive'];
    // $replace = ['انثى', 'ذكر', 'أصفر', 'برتقالي', 'أزرق', 'أحمر', 'أخضر', 'وردي', 'أبيض', 'حري', 'نجدي', 'نعيمي', 'سواكني', 'رفيدي', 'عساف', 'عواسي', 'بربري', 'حبشي', 'دروبر', 'عرادي', 'ميرينو', 'ماعز', 'اخرى', 'شعير 50 كيلو', 'شعير 40 كيلو', 'برسيم', 'تبن', 'رودس', 'مخلوط', 'بلوبانيك', 'ذره 50 كيلو', 'ذره 40 كيلو', 'خبز', 'سداد كامل', 'سداد جزئي', 'مدين بالكمل', 'مياه', 'معادن', 'أملاح', 'فيتامينات', 'تطعيمات', 'كغم', 'ميلي قرام', 'قرام', 'ميلي لتر', 'لتر', 'مكعب', 'شهري', 'سنوي', 'يناير', 'فبراير', 'مارس', 'ابريل', 'مايو', 'يونيو', 'يوليو', 'اغسطس', 'سبتمبر', 'اكتوبر', 'نوفمبر', 'ديسمبر', 'قعال', 'غير فعال'];
    // return str_replace($search, $replace, $text);
// }

function arabic(string $text): string
{
    $search = [
        'female', 'male',
		'yellow', 'orange', 'blue', 'red', 'green', 'pink', 'white', 'none',
        'harri', 'najdi', 'naimi', 'swakni', 'rufaidi', 'assaf', 'awasi', 'barbari',
        'habashi', 'droper', 'aradi', 'merino', 'goat', 'other',
        'barley50', 'barley40', 'alfalfa', 'hay', 'rowdas', 'mixed', 'bluepanic','corn50', 'corn40', 'barn50', 'bread', 
		'full', 'partial', 'debt',
        'water', 'minerals', 'salts', 'vitamins', 'vaccines',
        'kilogram', 'miligram', 'gram', 'mililiter', 'liter', 'cube',
        'monthly', 'yearly',
        '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12',
        'active', 'inactive',
		'live', 'dead','sick', 'sold','gifted', 'killed' , 'mistake',
		'mrah', 'sheep','feed', 'water','others', 'opex' , 'capex' , 
		'users' , 'purchase' , 'suppliers' , 'supplier'
    ];

    $replace = [
        'انثى', 'ذكر', 
		'أصفر', 'برتقالي', 'أزرق', 'أحمر', 'أخضر', 'وردي', 'أبيض', 'بلا وسم',
        'حري', 'نجدي', 'نعيمي', 'سواكني', 'رفيدي', 'عساف', 'عواسي', 'بربري',
        'حبشي', 'دروبر', 'عرادي', 'ميرينو', 'ماعز', 'اخرى',
        'شعير 50 كيلو', 'شعير 40 كيلو', 'برسيم', 'تبن', 'رودس', 'مخلوط', 'بلوبانيك', 'ذره 50 كيلو', 'ذره 40 كيلو', 'نخاله 50 كيلو', 'خبز', 
		'سداد كامل', 'سداد جزئي', 'مدين بالكامل',
        'مياه', 'معادن', 'أملاح', 'فيتامينات', 'تطعيمات',
        'كقم', 'ميلي قرام', 'قرام', 'ميلي لتر', 'لتر', 'مكعب',
        'شهري', 'سنوي',
        'يناير', 'فبراير', 'مارس', 'ابريل', 'مايو', 'يونيو', 'يوليو', 'اغسطس',
        'سبتمبر', 'اكتوبر', 'نوفمبر', 'ديسمبر',
        'قعال', 'غير فعال',
		'سليم', 'نافق','مريض', 'مباع','مهدى', 'مذبوح', 'مضاف عن طريق الخطأ',
		'مراح', 'حلال','علف', 'مياه','مواد اخرى', 'تكاليف تشغيليه' , 'تكاليف رأسماليه' , 
		'إعدادات' , 'مشتريات' , 'موردين' , 'موردين'
    ];

    // Escape the search terms for safe use in regex and add word boundaries
    $patterns = [];
    foreach ($search as $word) {
        $patterns[] = '/\b' . preg_quote($word, '/') . '\b/';
    }

    return preg_replace($patterns, $replace, $text);
}

function unitar(string $text): string
{
    $search  = ['barley50', 'barley40', 'alfalfa', 'hay', 'rowdas', 'mixed', 'bluepanic', 'corn50', 'corn40', 'barn50', 'bread', 'water'];
    $replace = ['كيس', 'كيس', 'بلكه', 'بلكه', 'بلكه', 'بلكه', 'بلكه', 'كيس', 'كيس', 'كيس', 'كيس', 'لتر'];
    return str_replace($search, $replace, $text);
}

function uniten(string $text): string
{
    $search  = ['barley50', 'barley40', 'alfalfa', 'hay', 'rowdas', 'mixed', 'bluepanic', 'corn50', 'corn40', 'barn50', 'bread', 'water'];
    $replace = ['bag', 'bag', 'block', 'block', 'block', 'block', 'block', 'bag', 'bag', 'bag', 'bag', 'liter'];
    return str_replace($search, $replace, $text);
}

// Option 1 - Most common & recommended
function monthsPassed($ts) {
    $start = new DateTime("@$ts");
    $now   = new DateTime();
    
    $years  = $now->format('Y') - $start->format('Y');
    $months = $now->format('n') - $start->format('n');
    
    // If current day hasn't reached the starting day yet → one less month
    if ($now->format('j') < $start->format('j')) {
        $months--;
    }
    
    return max(0, $years * 12 + $months);
}

function HijriDate($x){
	$hdate = Greg2Hijri(date("d"),date("m"),date("Y"),false);
	$Hdate = $hdate['year'].'/'.$hdate['month'].'/'.$hdate['day']+2;	
	return $Hdate;	
	}


function e2a($str){	/* Convert English to Arabic Numerals Function */
    $arabic_eastern = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');    $arabic_western = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    return str_replace($arabic_western, $arabic_eastern, $str);
}
function a2e($str){	/* Convert English to Arabic Numerals Function */
    $arabic_eastern = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');    $arabic_western = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    return str_replace($arabic_eastern, $arabic_western, $str);
}

// function mtoy(int $months): string {
function mtoy($months): string {
    if ($months < 0) {
        return '';	//invalid`
    }
	if ($months == 0) {
		return 'أقل من شهر';
	}
	if ($months === 1) {
		return 'شهر';
	}
	if ($months === 2) {
		return 'شهرين';
	}
	if ($months >= 3 && $months <= 10) {
		return $months . ' أشهر';
	}
	if ($months === 11 ) {
		return $months . ' شهر';
	}

    // 12 or more months
    $years = intdiv($months, 12); // Integer division (PHP 7+)
    $remainingMonths = $months % 12;

    $parts = [];

    // Years part
    // $parts[] = $years === 1 ? '1 year' : $years . ' years';
	if ($years === 1) { $parts[] = 'عام'; }
	if ($years === 2) { $parts[] = 'عامين'; }
	if ($years > 2) { $parts[] = $years . ' أعوام'; }

    // Months part (only if there are remaining months)
    if ($remainingMonths > 0) {
		if ($remainingMonths === 1) { $parts[] = 'شهر'; }
		if ($remainingMonths === 2) { $parts[] = 'شهرين'; }
		if ($remainingMonths > 2 && $remainingMonths < 11 ) { $parts[] = $remainingMonths . ' أشهر'; }
		if ($remainingMonths === 11) { $parts[] = $remainingMonths . ' شهر'; }

    }

    return implode(' و ', $parts);
}

// function mtoy(int $months): string {
// function mtoyshort(int $months): string
function mtoyshort($months): string
{
    if ($months < 0 || $months == '') {
        return '';
    }

    if ($months === 0) {
        return 'أقل من شهر';
    }

    if ($months === 1) return 'شهر';
    if ($months === 2) return 'شهرين';

    if ($months <= 11) {
        $txt = ($months === 11) ? 'شهرًا' : ' أشهر';
        return $months . $txt;
    }

    $years = floor($months / 12);

    if ($years == 1) return 'عام';
    if ($years == 2) return 'عامين';

    return $years . ' أعوام';
}

function eventsorder(&$array) {
    usort($array, function($a, $b) {
        // Extract the number after ^
        preg_match('/\^(\d+)$/', $a, $matchesA);
        preg_match('/\^(\d+)$/', $b, $matchesB);

        $numA = isset($matchesA[1]) ? (int)$matchesA[1] : 0;
        $numB = isset($matchesB[1]) ? (int)$matchesB[1] : 0;

        return $numA - $numB; // Ascending: smallest number first
    });
}
function Choose_One($array){
	return $array[array_rand($array)];
	}

function Get_File_Ext($file_name){
	return mb_strtolower(end(explode('.',$file_name)));
	}
	
function Fileext($file_name){
	$file_name = explode("/",$file_name);	$file_name = explode(";",$file_name[1]);	$file_name = $file_name[0];
	if ( $file_name == 'jpeg' ) { $file_name = 'jpg'; }
	return $file_name;
	}
/*
function GetTags($text,$limit=10,$separaotr=','){
	$text = implode(' ', array_slice(explode(' ',$text),0,$limit));
	$text = trim(mb_strtolower($text));
	$text = str_replace(array('&','^',',',';','<','>','.','_','$','%','#','@','+','=','-','/','*'),NULL,$text);
	$tags = array_unique(explode(' ',$text));
	$num = count($tags);
		for($z=0;$z<$num;$z++){
			$words .= $tags[$z];if($z < $num-1){$words .= $separaotr;}
		}
		return $words;
	}
*/
function Get_Array($field,$sep=','){
	$arr=array_filter(explode($sep,$field));return($arr);
	}

function Get_String($array,$sep=','){
	$result='';for($i=0;$i<count($array);$i++){$result .= $array[$i];if($i < count($array)-1){$result .=$sep;}}return $result;
	}

function Cut_Text($text,$limit=25,$ending=' .... '){
	if(mb_strlen(trim($text))>$limit){$text=mb_substr($text,0,$limit);$text=mb_substr($text, 0, -(mb_strlen(mb_strrchr($text,' '))));$text=$text.$ending;}return $text;
	}

function Is_Valid_Email($address){
	$address=mb_strtolower(trim($address));return (preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+'.'@'.'([-0-9A-Z]+\.)+'.'([0-9A-Z]){2,4}$/i',$address));
	}

function Is_Valid_Url($url){
	$url=mb_strtolower($url);return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
	}
function Security($entry,$type='text'){
	switch($type){
		case 'text':return strip_tags(mysqli_real_escape_string(trim($entry)));break;
		case 'password':return strip_tags(mysqli_real_escape_string($entry));break;
		case 'number':return intval(abs(trim($entry)));break;
		case 'array':$array = array();
			for($i=0;$i<count($entry);$i++){
				$array[] = strip_tags(mysqli_real_escape_string(trim($entry[$i])));
				}
			return $array;break;
		}
	}
function Send_Mail($msg,$from,$to,$subject){
	$headers = "From: $from\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$boundary = uniqid("HTMLEMAIL");
	$headers .= "Content-Type: multipart/alternative;"."boundary = $boundary\r\n\r\n";
	$headers .= "This is a MIME encoded message.\r\n\r\n";
	$headers .= "--$boundary\r\n"."Content-Type: text/plain; charset=utf-8\r\n"."Content-Transfer-Encoding: base64\r\n\r\n";
	$headers .= chunk_split(base64_encode(strip_tags($msg)));
	$headers .= "--$boundary\r\n"."Content-Type: text/html; charset=utf-8\r\n"."Content-Transfer-Encoding: base64\r\n\r\n";
	$headers .=chunk_split(base64_encode($msg));if(@mail($to,$subject,"",$headers)){return true;}else{return false;}
	}
function Random($length,$nums=true,$lower=false,$upper=false,$special=false){
	$pool_lower='abcdefghijklmopqrstuvwxyz';
	$pool_upper='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$pool_nums='0123456789';
	$pool_special='!$%^&*+#~/|';
	$pool='';
	$res='';
		if($lower===true){$pool .=$pool_lower;}
		if($upper===true){$pool .=$pool_upper;}
		if($nums===true){$pool .=$pool_nums;}
		if($special===true){$pool .=$pool_special;}
		if(($length<0) || ($length==0)){return $res;}
		srand((double) microtime()*1000000);for($i=0;$i<$length;$i++) {$charidx=rand()%mb_strlen($pool);$char=mb_substr($pool,$charidx,1);$res .=$char;}
		return $res;
	}
function Time_Passed($timestamp,$type='twitter'){
	if($type == 'twitter'){$timestamp = strtotime($timestamp);}
	else{$timestamp = $timestamp;}$current_time   = strtotime('now');
	if($current_time >= $timestamp){$diff = $current_time - $timestamp;}
	else{$diff = $timestamp - $current_time;}
	$intervals=array ('year' => 31556926, 'month' => 2629744, 'week' => 604800, 'day' => 86400, 'hour' => 3600, 'minute'=> 60, 'yesterday'=> 172800, 'tomorrow'=> 172800);
	$sums = array(3,4,5,6,7,8,9,10);
	$ardays = array('Saturday'=>'السبت','Sunday'=>'الأحد','Monday'=>'الإثنين','Tuesday'=>'الثلاثاء','Wednesday'=>'الأربعاء','Thursday'=>'الخميس','Friday'=>'الجمعة');
	$armonths = array('January'=>'يناير','February'=>'فبراير','March'=>'مارس','April'=>'أبريل','May'=>'مايو','June'=>'يونيو','July'=>'يوليو','August'=>'أغسطس','September'=>'سبتمبر','October'=>'أكتوبر','November'=>'نوفمبر','December'=>'ديسمبر');
	$ampm = array('am'=>'صباحاً','pm'=>'مساءاً');
	if($timestamp > $current_time){
		$diff = $timestamp - $current_time;
		if ($diff >= $intervals['day'] && $diff < $intervals['week']){
			if ($diff >= $intervals['day'] && $diff < $intervals['tomorrow']){
				return 'غداً الساعه '.date('h:i ',$timestamp).$ampm[date('a',$timestamp)];
				}
			else{
				return $ardays[date('l',$timestamp)].((date('l',$timestamp) != 'Friday')?' القادم':' القادمه').' الساعه '.date('h:i ',$timestamp).$ampm[date('a',$timestamp)];
				}
			}
	}
	// if ($diff < 86400){return 'خلال يوم';}
	if ($diff < 86400){return 'اليوم';}
	// if ($diff > 86400 && $diff < 172800){return 'بالأمس';}
	if ($diff > 86400 && $diff < 172800){return 'الأمس';}
	if ($diff > 172800){
		$diff = floor($diff/$intervals['minute']);
		return date('j ',$timestamp).$armonths[date('F',$timestamp)].date(' Y',$timestamp);
	}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// if ($diff < 9){return 'منذ بضع ثوان';}
	// if ($diff > 9 && $diff < 60){return !in_array($diff,$sums) ?'منذ '.$diff.' ثانية' :'منذ '.$diff.' ثوان';}
	// if ($diff >= 60 && $diff < $intervals['hour']){
		// $diff = floor($diff/$intervals['minute']);
		// return date('j ',$timestamp).$armonths[date('F',$timestamp)].' الساعه '.date('h:i ',$timestamp).$ampm[date('a',$timestamp)];
		// }
	// if($diff >= $intervals['hour'] && $diff < $intervals['day']){
		// $diff = floor($diff/$intervals['hour']);
		// if($diff >= 1 && $diff < 2){return 'منذ حوالى ساعه';}
		// else{return !in_array($diff,$sums) ?'منذ '.$diff.' ساعه':'منذ '.$diff.' ساعات';}
		// }
	// if($diff >= $intervals['day'] && $diff < $intervals['week']){
		// if($diff >= $intervals['day'] && $diff < $intervals['yesterday']){
			// return 'أمس الساعه '.date('h:i ',$timestamp).$ampm[date('a',$timestamp)];
			// }
		// else{
			// return $ardays[date('l',$timestamp)].((date('l',$timestamp) != 'Friday')?' الماضى':' الماضيه').' الساعه '.date('h:i ',$timestamp).$ampm[date('a',$timestamp)];
			// }
	// }
	// if($diff >= $intervals['week'] && $diff < $intervals['year']){
		// return date('j ',$timestamp).$armonths[date('F',$timestamp)].' الساعه '.date('h:i ',$timestamp).$ampm[date('a',$timestamp)];
		// }
	// if($diff >= $intervals['year']){
		// return date('j ',$timestamp).$armonths[date('F',$timestamp)].date(' Y',$timestamp).' الساعه '.date('h:i ',$timestamp).$ampm[date('a',$timestamp)];
		// }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
	}

function Time_Passed_MY($timestamp,$type='twitter'){
	$current_time   = strtotime('now');
	$intervals=array ('year' => 31556926, 'month' => 2629744, 'week' => 604800, 'day' => 86400, 'hour' => 3600, 'minute'=> 60, 'yesterday'=> 172800, 'tomorrow'=> 172800);
	$armonths = array('January'=>'يناير','February'=>'فبراير','March'=>'مارس','April'=>'أبريل','May'=>'مايو','June'=>'يونيو','July'=>'يوليو','August'=>'أغسطس','September'=>'سبتمبر','October'=>'أكتوبر','November'=>'نوفمبر','December'=>'ديسمبر');
	
	if($current_time >= $timestamp){$diff = $current_time - $timestamp;}
	else{$diff = $timestamp - $current_time;}

	$diff = floor($diff/$intervals['minute']);
	return $armonths[date('F',$timestamp)].date(' Y',$timestamp);
}
function Object_To_Array($d) {
	if(is_object($d)){$d = get_object_vars($d);}if(is_array($d)){return array_map(__FUNCTION__, $d);}else{return $d;}
	}
function linkify_twitter_status($status_text){
	$status_text = preg_replace('/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/', '<a href="$0" target="_blank">$0</a>',$status_text);
	$status_text = preg_replace('/(?!\b)@(\\w+\b)/', '<a href="https://twitter.com/$1" target="_blank">$1</a>',$status_text);
	$status_text = preg_replace('/(?!\b)#(\\S+)/', '<a href="https://twitter.com/search?q=%23$1&src=hash" target="_blank">$0</a>',$status_text);
	return $status_text;
	}
function Get_Error($code){
	$error_codes = array('200' => 'العمليه تمت بنجاح.','304' => 'الخطأ 304, لا توجد بيانات جديده لإرجاعها','400' => 'الخطأ 400, إتصال خطأ يجب أن يزود الإتصال بشفرات التطبيق','401' => 'الخطأ 401, يجب أن يقوم العضو بإعادة إعطاء الصلاحيات للتطبيق لكى يستطيع التطبيق إستخدام حسابه','403' => 'الخطأ 401, طلب غير مفهوم. ','404' => 'Error:404, The URI requested is invalid or the resource requested, such as a user, does not exists. Also returned when the requested format is not supported by the requested method.','406' => 'Error:406, Returned by the Search API when an invalid format is specified in the request.','410' => 'Error:410, This resource is gone. Used to indicate that an API endpoint has been turned off. For example: "The Twitter REST API v1 will soon stop functioning. Please migrate to API v1.1."','420' => 'Error:420, Returned by the version 1 Search and Trends APIs when you are being rate limited.','422' => 'الخطأ 422 , لا يمكن إتمام هذه العمليه الآن.','429' => 'الخطأ 429, لقد تجاوزت الحد الأقصى المسموح به فى تويتر من فضلك حاول فى وقت لاحق.','500' => 'الخطأ 500, حدث خطأ ما من فضلك راسل الدعم الفنى فى تويتر لنتمكن من مساعدتك.','502' => 'الخطأ 502, يتم ترقية تويتر الأن من فضلك حاول فى وقت لاحق.','503' => 'الخطأ 503, سيرفرات تويتر خارج الخدمه الأن من فضلك حاول فى وقت لاحق.','504' => 'الخطأ 504 , سيرفرات تويتر مشغوله جداً الآن حاول فى وقت لاحق.','32'  => 'الخطأ 32, لا يمكن مصادقتك لأنك ألغيت الصلاحيه للتطبيق أو أن التطبيق تم حظره.','34'  => 'الخطأ 34, عفواً هذه الصفحه غير موجوده بتويتر.','64'  => 'الخطأ 64, تم تعليق حسابك وغير مسموح به للوصول إلى هذه الميزه.','68'  => 'الخطأ 68, تم إلغاء التعامل بإصدار تطبيقات تويتر الأول. برجاء التعامل بالإصدار 1.1','88'  => 'الخطأ 88, تم تجاوز الحد المسموح به من تويتر لهذا العضو أو التطبيق.','89'  => 'الخطأ 89, رمز غير صالح أو منتهى الصلاحيه','92'  => 'الخطأ 92, يجب تفعيل خاصية SSL فى موقعك.','130' => 'الخطأ 130, تويتر خارج الخدمه الأن من فضلك حاول فى وقت لاحق.','131' => 'الخطأ 131, حدث خطأ داخلى بتويتر غير معروف.','135' => 'الخطأ 135, برجاء التأكد من شفرات التطبيق الخاص بالسكربت أو صلاحيات الحساب.','161' => 'الخطأ 161 , لا يستطيع هذا الحساب متابعة المزيد من الحسابات فى الوقت الحاضر.','179' => 'Error:179, Corresponds with HTTP 403 - thrown when a Tweet cannot be viewed by the authenticating user, usually due to the tweets author having protected their tweets.','185' => 'الخطأ 185, هذا الحساب تجاوز الحد الأقصى للتغريد فى اليوم.','187' => 'الخطأ 187, تم التغريد بنفس هذه التغريده من نفس الحساب من قبل.','215' => 'الخطأ 215, بيانات المصادقه مع التطبيق غير صحيح أو مطلوبه.','226' => 'الخطأ 226, لا يمكن إكمال هذا الإجراء فى الوقت الحالى لحماية مستخدمينا من السبام والأنشطه الخبيثه لأن هذا الطلب يبدو وكأنه تم بطريقه آليه .','231' => 'الخطأ 231, يجب الدخول لحسابك بتويتر أولا لأنه مطلوب بعض البيانات والتفعيلات.','251' => 'الخطأ 251, لقد طلبت رابط تم إلغاءه من خدمة تويتر.','261' => 'الخطأ 261, التطبيق لا يستطيع عمل إجراءات الكتابه برجاء مراجعة صلاحيات التطبيق.','271' => 'الخطأ 271, لا يمكنك عمل كتم لنفسك.','272' => 'الخطأ 272, أنت لم تعمل كتم لهذا الحساب من قبل.');
	if(array_key_exists($code,$error_codes)){return $error_codes[$code];}
	else{return 'الخطأ غير معروف';}
	}
function Backup_Tables($tables = '*'){
	$data = "/*-------------------------------------"."\n  SQL DB BACKUP ".date("d.m.Y H:i")." "."\n  HOST: {$host}"."\n  DATABASE: {$name}"."\n  TABLES: {$tables}"."\n--------------------------------------*/\n";
	if($tables == '*'){
		$tables = array();
		$result = mysql_query("SHOW TABLES");
		while($row = mysql_fetch_row($result)){$tables[] = $row[0];}
		}else{$tables = is_array($tables) ? $tables : explode(',',$tables);}
		foreach($tables as $table){
			$data .= "\n/*-------------------------------------------";
			$data .= "\n   Table structure for table: `{$table}`   \n";
			$data .= "-------------------------------------------*/\n\n";
			$data .= "DROP TABLE IF EXISTS `{$table}`;\n";
			$res   = mysql_query("SHOW CREATE TABLE `{$table}`");
			$row   = mysql_fetch_row($res);
			$data .= $row[1].";\n";
			$result = mysql_query("SELECT * FROM `{$table}`");
			$num_rows = mysql_num_rows($result);
			if($num_rows > 0){
				$vals = Array(); $z=0;for($i=0;$i<$num_rows;$i++){$items = mysql_fetch_row($result);$vals[$z] = '(';for($j=0;$j<count($items);$j++){if(isset($items[$j])){$vals[$z].= '\''.mysqli_real_escape_string($items[$j] ).'\'';}else{$vals[$z].= 'NULL';}if($j<(count($items)-1)){$vals[$z].= ',';}}$vals[$z].= ")"; $z++;}$sel_columns = mysql_query("SHOW COLUMNS FROM `{$table}`");$num_columns = mysql_num_rows($sel_columns);if($num_columns > 0){$string = '(';for($i=0;$i<$num_columns;$i++){$row_columns = mysql_fetch_array($sel_columns);$string .= '`'.$row_columns['Field'].'`';if($i<($num_columns-1)){$string .= ',';}}$string .= ')';}$data .= "\n/*-------------------------------------------";$data .= "\n   Dumping data for table: `{$table}`   \n";$data .= "-------------------------------------------*/\n\n";$data.= "INSERT INTO `{$table}` {$string} VALUES ";$data .= implode(";\nINSERT INTO `{$table}` {$string} VALUES ", $vals).";\n";
				}
			}
			return $data;
	}
	class Token{
		public static function generate(){return $_SESSION['token'] = base64_encode(Random(32,true,true,false,true));}public static function check($token){if(isset($_SESSION['token']) && $token === $_SESSION['token']){unset($_SESSION['token']);return true;}unset($_SESSION['token']);return false;}
		}
?>