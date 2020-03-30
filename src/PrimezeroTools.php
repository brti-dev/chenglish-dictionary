<?php

namespace Pced;

define("DATA_DIR", __DIR__."/../data");

/**
 *
 * @author eric okorie
 * @package pzphp
 * @subpackage PrimezeroTools
 * @version 0.9.4
 * @copyright 2007-2008
 * @uses pzpinyin 		-- process pinyin, phonetics, and romanizations
 * @uses pzhanzi 		-- process chinese characters (hanzi)
 * @uses pzcedict 		-- process cedict-compatible files
 * @uses pznumbers 		-- process various chinese numbers and quantities
 * @uses pzkelvin 		-- process thermodynamically-sensible temperatures
 * 
 * @link http://code.google.com/p/pzphp/wiki/PrimezeroTools
 *
 * @todo params and returns for each and for all
 * @todo 
 *
 *
 */

class PrimezeroTools {
	
	
	
## ______________________________________________________________________________________________
## PrimezeroTools - __construct .. constructor may act as pre-flight checklist?

	
	function __construct() {
		
		
			if( !file_exists(DATA_DIR."/idx33.txt") ){
			
				exit("[PrimeZeroTools][".__LINE__."] idx33.txt - pinyin-hanzi index not found");
							
			} elseif( !file_exists(DATA_DIR."/idx55.txt") ){
			
				exit("[PrimeZeroTools][".__LINE__."] idx55.txt - pinyintype index not found");
							
			} elseif( !file_exists(DATA_DIR."/idx88.txt") ){
				
				exit("[PrimeZeroTools][".__LINE__."] idx88.txt - traditional index not found");
				
			} elseif( !file_exists(DATA_DIR."/idx99.txt") ){				
				
				exit("[PrimeZeroTools][".__LINE__."] idx99.txt - simplified index not found");
			}
		
		
	}
	
	

## ______________________________________________________________________________________________
## PrimezeroTools - Mega Methods ... the methods that take all the glory
## usually with one parameter tied behind their back ... 
## Modifying code below this line may help, but may also harm.



	function pzkelvin_go($n, $display = FALSE){
	
		$n = $this->pzkelvin_temperature_convert($n, $display);
	
	return $n;	
	}


	function pznumbers_go($n){
		
		$n = $this->pznumbers_convert($n);
		
	return $n;	
	}


	function pzpinyin_tonedisplay_convert_to_number($n){
	
		$n =	$this->pzpinyin_tonedisplay_change($n,'tone_number');
	
	return $n;	
	}


	function pzpinyin_tonedisplay_convert_to_mark($n){
	
		$n =	$this->pzpinyin_tonedisplay_change($n,'tone_mark');
	
	return $n;
	}		



	function pzpinyin_pinyintype_hanyu_pinyin_to_zhuyin($n){
	
		$n =	$this->pzpinyin_pinyintype_change($n,'zhuyin','zhuyin');
	
	return $n;	
	}

	function pzpinyin_pinyintype_zhuyin_to_hanyu_pinyin($n){
	
		$n =	$this->pzpinyin_pinyintype_change($n,'hanyu_pinyin','zhuyin');
	
	return $n;
	}




	function pzpinyin_pinyintype_hanyu_pinyin_to_yale($n){
	
		$n =	$this->pzpinyin_pinyintype_change($n,'yale','yale');
	
	return $n;	
	}

	function pzpinyin_pinyintype_yale_to_hanyu_pinyin($n){
	
		$n =	$this->pzpinyin_pinyintype_change($n,'hanyu_pinyin','yale');
	
	return $n;
	}




	function pzpinyin_pinyintype_hanyu_pinyin_to_tongyong_pinyin($n){
	
		$n =	$this->pzpinyin_pinyintype_change($n,'tongyong_pinyin','tongyong_pinyin');
	
	return $n;	
	}

	function pzpinyin_pinyintype_tongyong_pinyin_to_hanyu_pinyin($n){
	
		$n =	$this->pzpinyin_pinyintype_change($n,'hanyu_pinyin', 'tongyong_pinyin');
	
	return $n;
	}





## ______________________________________________________________________________________________
## PrimezeroTools - Puny (Helper) Methods ... the methods that make the Mega Methods look good.
## Modifying code below this line may not yield added nutritional value.


	// zzzzz....
	function pzpinyin_cmp($a,$b)
	{
		// nothing in here //		
	return strlen($a) < strlen($b); // returns TRUE OR FALSE (1 | 0)
	}		

	// zzzzzzzz....... 
	function pzpinyin_pinyintable_trimwalk(&$pinyinItem)
	{
	  $pinyinItem = trim($pinyinItem);
	}

	
	// zzzzzzzzz....
	function pzpinyin_pinyin_cleanup($query){			

		$query = str_replace("u:","uu",$query);
		$query = str_replace("v","uu",$query);	
		$query = stripslashes($query);

	return $query;	
	}
	
	function pzpinyin_tonedisplay_stripnumbers($n){				
		
		$n = str_replace("0","",$n);			
		$n = str_replace("9","",$n);
		$n = str_replace("8","",$n);
		$n = str_replace("7","",$n);
		$n = str_replace("6","",$n);
		
		$n = str_replace("5","",$n);
		$n = str_replace("4","",$n);
		$n = str_replace("3","",$n);
		$n = str_replace("2","",$n);
		$n = str_replace("1","",$n);
	
	return $n;
	}

	function pzpinyin_pinyintype_listall(){ 

		$romans = array( // perhaps this should be placed in an external file for re-deployment

			0=>"zhuyin",
			1=>"wade_giles",
			2=>"mps2",
			3=>"yale",
			4=>"tongyong_pinyin",
			5=>"hanyu_pinyin",
			6=>"gyr1",
			7=>"gyr2",
			8=>"gyr3",		
			9=>"gyr4"	

			);
	
	return $romans;	
	}	
	
	
	

	// transcribes Chinese into pinyin character-by-character
	function pzpinyin_hanyu_pinyin_transcribe_to_hanzi($inputString){

		// takes string and breaks into array
		$arrayFromString 	= str_split($inputString); 

		// tablet is where the characters are transcribed		
		$tablet 			= "";					

		// let us look at each character
		foreach($arrayFromString as $eachCharacter) {

				// keep knitting with this new string
				$tablet 	= $tablet . $this->pzpinyin_gethanzi_by_pinyin($eachCharacter); 
		}

	return $tablet; 	
	} // end FCN pzpinyin_transcribe_to_hanzi
		
		
		

	function pzpinyin_tonemarks_loadindex(){

		$tone_marks_array =  array(
			
		"u:1e" => "üe", 	"u:2e" => "ǘe", 	"u:3e" => "ǚe", 	"u:4e" => "ǜe", 
		"uu1an" => "üan", 	"uu2an" => "ǘan", 	"uu3an" => "ǚan", 	"uu4an" => "ǜan", 
		"u:1an" => "üan", 	"u:2an" => "ǘan", 	"u:3an" => "ǚan", 	"u:4an" => "ǜan", 
		"un1" => "ūn", 		"un2" => "ún", 		"un3" => "ŭn", 		"un4" => "ùn",
		"uang1" => "uāng", 	"uang2" => "uáng", 	"uang3" => "uăng", 	"uang4" => "uàng", 
		"uan1" => "uān", 	"uan2" => "uán", 	"uan3" => "uăn", 	"uan4" => "uàn",  
		"un1" => "ūn", 		"un2" => "ún", 		"un3" => "ŭn", 		"un4" => "ùn",
		"ui1" => "uī", 		"ui2" => "uí", 		"ui3" => "uĭ", 		"ui4" => "uì", 
		"uai1" => "uāi", 	"uai2" => "uái", 	"uai3" => "uăi", 	"uai4" => "uài", 
		"uo1" => "uō",	"uo2" => "uó",	"uo3" => "uŏ",	"uo4" => "uò",	
		"ua1" => "uā",	"ua2" => "uá",	"ua3" => "uă",	"ua4" => "uà",
		"iong1" => "iōng",	"iong2" => "ióng",	"iong3" => "iŏng",	"iong4" => "iòng",	
		"ing1" => "īng",	"ing2" => "íng",	"ing3" => "ĭng",	"ing4" => "ìng",
		"iang1" => "iāng",	"iang2" => "iáng",	"iang3" => "iăng",	"iang4" => "iàng",
		"in1" => "īn",	"in2" => "ín",	"in3" => "ĭn",	"in4" => "ìn",
		"ian1" => "iān",	"ian2" => "ián",	"ian3" => "iăn",	"ian4" => "iàn",	
		"iu1" => "iū",	"iu2" => "iú",	"iu3" => "iŭ",	"iu4" => "iù",	
		"ie1" => "iē",	"ie2" => "ié",	"ie3" => "iĕ",	"ie4" => "iè",	
		"iao1" => "iāo",	"iao2" => "iáo",	"iao3" => "iăo",	"iao4" => "iào",
		"ia1" => "iā",	"ia2" => "iá",	"ia3" => "iă",	"ia4" => "ià",
		"ong1" => "ōng",	"ong2" => "óng",	"ong3" => "ŏng",	"ong4" => "òng",
		"eng1" => "ēng",	"eng2" => "éng",	"eng3" => "ĕng",	"eng4" => "èng",
		"ang1" => "āng",	"ang2" => "áng",	"ang3" => "ăng",	"ang4" => "àng",
		"en1" => "ēn",	"en2" => "én",	"en3" => "ĕn",	"en4" => "èn",	
		"an1" => "ān",	"an2" => "án",	"an3" => "ăn",	"an4" => "àn",	
		"ou1" => "ōu",	"ou2" => "óu",	"ou3" => "ŏu",	"ou4" => "òu",	
		"ao1" => "āo",	"ao2" => "áo",	"ao3" => "ăo",	"ao4" => "ào",	
		"ei1" => "ēi",	"ei2" => "éi",	"ei3" => "ĕi",	"ei4" => "èi",	
		"ai1" => "āi",	"ai2" => "ái",	"ai3" => "ăi",	"ai4" => "ài",	
		"er1" => "ēr",	"er2" => "ér",	"er3" => "ĕr",	"er4" => "èr",
		"uue1" => "üe",	"uue2" => "ǘe",	"uue3" => "ǚe",	"uue4" => "ǜe", 
		"uu1" => "ü",	"uu2" => "ǘ",	"uu3" => "ǚ",	"uu4" => "ǜ", 
		"u:e1" => "üe",	"u:e2" => "ǘe",	"u:e3" => "ǚe",	"u:e4" => "ǜe", 
		"u:1" => "ü",	"u:2" => "ǘ",	"u:3" => "ǚ",	"u:4" => "ǜ", 
		"a1" => "ā",	"a2" => "á",	"a3" => "ă",	"a4" => "à",
		"e1" => "ē",	"e2" => "é",	"e3" => "ĕ",	"e4" => "è",
		"i1" => "ī",	"i2" => "í",	"i3" => "ĭ",	"i4" => "ì",
		"o1" => "ō",	"o2" => "ó",	"o3" => "ŏ",	"o4" => "ò",
		"u1" => "ū",	"u2" => "ú",	"u3" => "ŭ",	"u4" => "ù" // no comma here 
																	// last one out closes the door
		);
		
	return $tone_marks_array; // array containing tonemarks - tonenumbers
	} // end FUNCTION pzpinyin_tonemarks_load
	
	
	
	
	function pzpinyin_tonedisplay_change($inputPhrase, $conversion_mode = 'tone_mark') {

		// conversion_mode = 'tone_number' 	- convert from tone mark to tone number
		// conversion_mode = 'tone_mark' 	- convert from tone number to tone mark

		$eachWord = explode(" ",$inputPhrase);
	
		$tone_marks_array = $this->pzpinyin_tonemarks_loadindex();

		foreach($eachWord as $dummy_key => &$word){

			foreach( $tone_marks_array as $tone_number => $tone_mark  ) {

				if($conversion_mode == 'tone_mark'){
		
		 			$word = str_replace ($tone_number, $tone_mark, $word);
				
				} elseif($conversion_mode == 'tone_number') {
		
		 			$word = str_replace ($tone_mark, $tone_number, $word);	

				}
	
			} // end FOREACH tone_marks_array				

		$converted_words[] = $word;
		} // end foreach WORD

		$converted_phrase = implode(" ",$converted_words);

	return $converted_phrase; // string of converted pinyin phrase
	} // end FUNCTION pzpinyin_tonedisplay_change
		
		
		
		
	function pzpinyin_pinyintype_loadindex($pinyinFileLocation = "idx55.txt"){ 

		$py_table = array();
		$str_from_file = @file_get_contents($pinyinFileLocation);

		$lines_from_txt 		= explode("\n",$str_from_file);

		foreach($lines_from_txt as $lines){
			$py_table[] 	= explode("\t",trim($lines));

		}	

		foreach($py_table as &$t){
	
		 	array_walk($t, array($this,'pzpinyin_pinyintable_trimwalk'));
	
		}

	
	return $py_table; // array containing pinyin table 
	} // end FUNCTION loadPinyinIndex
	
	
	
	

	function pzpinyin_pinyintype_array_load($inputpinyin = "yale"){

		$py_table 			= $this->pzpinyin_pinyintype_loadindex("idx55.txt");
		$pinyintype_array 	= array();
		
		// IDX55.TXT - CHEATSHEET 
		// 0 - zhuyin; 1 - wade_giles; 2 - mps2; 3 - yale; 4 - tongyong pinyin; 
		// 5 - hanyu pinyin; 6 - gyr1; 7 - gyr2; 8 - gyr3; 9 - gyr4;

		switch($inputpinyin){

			case "yale":
	
				foreach($py_table as $key=>$hole)
				{

					$pinyintype_array[trim($hole[5].'4')] 	= trim($hole[3]. "4");
					$pinyintype_array[trim($hole[5].'3')] 	= trim($hole[3]. "3");
					$pinyintype_array[trim($hole[5].'2')] 	= trim($hole[3]. "2");	
					$pinyintype_array[trim($hole[5].'1')] 	= trim($hole[3]. "1");						

				}
	
			break; // end case yale

			case "tongyong_pinyin":
		
				foreach($py_table as $key=>$hole)
				{

					$pinyintype_array[trim($hole[5].'4')] 	= trim($hole[4]. "4");
					$pinyintype_array[trim($hole[5].'3')] 	= trim($hole[4]. "3");
					$pinyintype_array[trim($hole[5].'2')] 	= trim($hole[4]. "2");	
					$pinyintype_array[trim($hole[5].'1')] 	= trim($hole[4]. "1");						

				}
			
			break; // end case tongyong_pinyin

			case "zhuyin":
	
				// hole[5] - hanyu_pinyin ... hole[0] - zhuyin
				foreach($py_table as $key=>$hole)
				{

					$pinyintype_array[trim($hole[5].'4')] 	= trim($hole[0]. "ˋ");
					$pinyintype_array[trim($hole[5].'3')] 	= trim($hole[0]. "ˇ");
					$pinyintype_array[trim($hole[5].'2')] 	= trim($hole[0]. "ˊ");	
					$pinyintype_array[trim($hole[5].'1')] 	= trim($hole[0]);						
					$pinyintype_array[trim($hole[5])] 		= trim($hole[0]);	
				}
	
			break;

			default:
			echo "--";
			break;
	
		}

	
	return $pinyintype_array; // returns pinyintype - hanyupinyin
	} // end FUNCTION pzpinyin_pinyintype_array_load	





	function pzpinyin_pinyintype_change($inputPhrase, $conversion_mode = 'hanyu_pinyin', $conversion_set = 'yale' ) {

		$eachWord = explode(" ",$inputPhrase);
		$pinyintype_array = $this->pzpinyin_pinyintype_array_load($conversion_set);
		
		//print_r($pinyintype_array);

		foreach($eachWord as $dummy_key => &$word){

			// uasort works better for zhuyin->pinyin			
			/** !! experimental block **/
			if($conversion_mode == 'hanyu_pinyin') {

				// uasort works better for zhuyin->pinyin
				uasort($pinyintype_array, array($this,"pzpinyin_cmp"));

			} else {
				
				// uksort works better for pinyin->zhuyin
				uksort($pinyintype_array, array($this,"pzpinyin_cmp"));
					
			}

			foreach( $pinyintype_array as $hanyu_pinyin => $otherpinyintype  ) {

				if($conversion_mode == 'hanyu_pinyin') {

		 			$word = str_replace ($otherpinyintype, $hanyu_pinyin, $word);	
					
				} else{
					
					$word = str_replace ($hanyu_pinyin, $otherpinyintype, $word);
					
				}

			} // end FOREACH tongyong_array

		$converted_words[] = $word;
		} // end foreach WORD

		$converted_phrase = implode(" ",$converted_words);

	return $converted_phrase; // string of converted pinyin phrase
	} // end FUNCTION pzpinyin_pinyintype_change
	
	
	
	## !! EXPERIMENTAL ## MAYBE NO LONGER NEEDED ?!
	// pzpinyin_gethanzi_by_pinyin
	// convert numerals or pinyin to Hanzi
	function pzpinyin_gethanzi_by_pinyin( $inputPinyin ){

		// numbers 0 - 9 --> Hanzi
		$decoder_ring['0'] 			= urldecode("%E3%80%87"); //0 
		$decoder_ring['1'] 			= urldecode("%E4%B8%80"); //1
		$decoder_ring['2'] 			= urldecode("%E4%BA%8C"); //2
		$decoder_ring['3'] 			= urldecode("%E4%B8%89"); //3
		$decoder_ring['4'] 			= urldecode("%E5%9B%9B"); //4
		$decoder_ring['5'] 			= urldecode("%E4%BA%94"); //5
		$decoder_ring['6'] 			= urldecode("%E5%85%AD"); //6
		$decoder_ring['7'] 			= urldecode("%E4%B8%83"); //7
		$decoder_ring['8'] 			= urldecode("%E5%85%AB"); //8
		$decoder_ring['9'] 			= urldecode("%E4%B9%9D"); //9


		// no-tone pinyin for numbers 0 - 9 --> Hanzi
		$decoder_ring['ling'] 		= urldecode("%E3%80%87"); //0
		$decoder_ring['yi'] 		= urldecode("%E4%B8%80"); //1
		$decoder_ring['er'] 		= urldecode("%E4%BA%8C"); //2
		$decoder_ring['san'] 		= urldecode("%E4%B8%89"); //3
		$decoder_ring['si'] 		= urldecode("%E5%9B%9B"); //4
		$decoder_ring['wu'] 		= urldecode("%E4%BA%94"); //5
		$decoder_ring['liu'] 		= urldecode("%E5%85%AD"); //6
		$decoder_ring['qi'] 		= urldecode("%E4%B8%83"); //7
		$decoder_ring['ba'] 		= urldecode("%E5%85%AB"); //8
		$decoder_ring['jiu']		= urldecode("%E4%B9%9D"); //9


		#size delimiters / classifiers
		#biggie = bigyi = 100 million
		$decoder_ring['biggie'] 	= urldecode("%E5%84%84"); // 100,000,000 ONE HUNDRED MILLION
		$decoder_ring['wan'] 		= urldecode("%E8%90%AC"); // 10,000 TEN THOUSAND
		$decoder_ring['qian'] 		= urldecode("%E5%8D%83"); // 1,000 THOUSAND
		$decoder_ring['bai'] 		= urldecode("%E7%99%BE"); // 100 HUNDRED
		$decoder_ring['shi'] 		= urldecode("%E5%8D%81"); // 10 TEN

		# fractional
		$decoder_ring['zhi'] 		= urldecode("%E4%B9%8B"); // delimiter qty fractional

		# qty of 2 TRADITIONAL CHINESE CHARACTERS
		$decoder_ring['liang'] 		= urldecode("%E5%85%A9"); // a pair of ...
		
		# AM and PM for clock times
		$decoder_ring['am']			= urldecode("%E4%B8%8A%E5%8D%88"); // 00:00 AM
		$decoder_ring['pm']			= urldecode("%E4%B8%8B%E5%8D%88"); // 00:00 PM


		# negative number flag
		$decoder_ring['nian'] 		= urldecode("%E5%B9%B4"); // year
		$decoder_ring['yue'] 		= urldecode("%E6%9C%88"); // month
		$decoder_ring['ri'] 		= urldecode("%E6%97%A5"); // formal
		// $decoder_ring['hao'] 		= urldecode("%E5%8F%B7"); // informal

		# negative number flag
		$decoder_ring['fu'] 		= urldecode("%E8%B2%A0"); // negative number

		# clock time delimiters TRADITIONAL CHARACTERS (LONG-FORM) CHINESE
		$decoder_ring['zhong'] 		= urldecode("%E9%8D%BE"); // clock end-delimiter
		$decoder_ring['dian'] 		= urldecode("%E9%BB%9E"); // hour classifier
		$decoder_ring['fen'] 		= urldecode("%E5%88%86"); // minute classifier 
															  // OR money classifier (1/100 yuan)
		$decoder_ring['ke'] 		= urldecode("%E5%88%BB"); // quarter-hour classifer

		# clock time delimiters TRADITIONAL CHARACTERS (LONG-FORM) CHINESE
		$decoder_ring['mao'] 		= urldecode("%E6%AF%9B"); // money classifer (1/10 yuan)
		$decoder_ring['kuai'] 		= urldecode("%E5%A1%8A"); // money classifer (1 yuan)



		if(@$decoder_ring[(string)$inputPinyin]){

			// simply returns hanzi string. no spills. no mess.
			return $decoder_ring[(string)$inputPinyin];

		} // end IF 


	return $inputPinyin;	
	} // end FCN pzpinyin_gethanzi_by_pinyin
	
	
	
	// pzpinyin_hearpinyin
	## !!! EXPERIMENTAL !!! ## 	
	function pzpinyin_hearpinyin( $qc, $pacer = "8t88" ){

	/***

	 @SUPPORT FILES: Quicktime methods
	 @SUPPORT FILES: Pinyin Tone Bank
	 @SUPPORT FILES: README file (optional, of course)

	*/

	// ** REQUIRES CLEANUP ** //

	// if root, sounds_directory = ""
	// if subdirectory, just enter name "voz" or "sounds" 
	$sounds_directory = ""; 

	// pacer appears to be a counter for each iteration in a while/for loop situation
	// qc appears to be pinyin query string 
	if ($qc == ""){
		return FALSE;
	}



	$ggx = $pacer;

	$uc = explode(" ", $qc);

	$uu = 1;
	$pp	= 5;

	foreach($uc as $g){

			$peanut = rand( 1 , 3000 ) * 3 ;
			$cashew = rand( 2 , $peanut );				

			$gx = $g;
			$pacer++;
			$pacelap = $g."_".$pacer."_".md5($g.$pacer.$cashew);

			// in windows, you need to have a max height/width of 0 (zero) for "invisble player"
			// cannot get rid of the player dots in Mac OS X yet ...
			$osxdims = 0;		

			if( strpos($_SERVER['HTTP_USER_AGENT'], 'Mac OS X') ) {

				$osxdims = 1; 	
				// must have a minimum height/width of 1 for the EMBED in Mac OS X, otherwise will not work
				// yes, the dot is there, but it is better than the sound not work, right?
				// and yes, i talk to myself whilst i code teh_poetry

			}

			// 0.8.4 // fixes issue ISSUE 84
			if( strpos(strtolower($_SERVER['HTTP_USER_AGENT']),"msie 7") ){

				$pinyin_sound_embed_tags[] = "
				<div style='position:absolute;'>
				<OBJECT width=$osxdims height=$osxdims
				classid='clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B'
				codebase='http://www.apple.com/qtactivex/qtplugin.cab'
				id=$pacelap>
				<param name='src' value='". $sounds_directory ."$gx.mp3'>	
				<param name='autoplay' value='false'>
				<param name='controller' value='false'>					

				<EMBED 
				cache=true 
				bgcolor=#ffffe0 
				width=$osxdims
		       	height=$osxdims
				src=". $sounds_directory ."$gx.mp3 
				TYPE='video/quicktime'
				qtsrc=". $sounds_directory ."$gx.mp3
				PLUGINSPAGE=www.apple.com/quicktime/download
				name=$pacelap
				id=$pacelap					
				enablejavascript=true 
				autostart=false>
	            </EMBED>

				</OBJECT></div>";

			} else {

			// version 0.4 no more echoed output
			// resolves issues of printing prior to sending headers by PHP
			$pinyin_sound_embed_tags[] = "
						<EMBED 
						cache=true 
						bgcolor=#ffffe0 
						width=$osxdims
				       	height=$osxdims
						src=". $sounds_directory ."$gx.mp3 
						TYPE='video/quicktime'
						qtsrc=". $sounds_directory ."$gx.mp3
						PLUGINSPAGE=www.apple.com/quicktime/download
						name='$pacelap'
						id='$pacelap'
						enablejavascript=true 
						autostart=false>
			            </EMBED>";

					// 0.8.4 opera issue?
					// opera cannot see past the DIV arrh! need reference
					// maybe getElementByID?

			} // END IF FOR BROWSER EMBED OR EMBED/OBJECT DETECTION


			$pinyin_sound_links[] = "<a class=pinyinsound 
				href='http://www.primezero.com/chinese/find?q=$g' 
				onmouseover=PlayPY('$pacelap'); 
				onmouseout=StopPY('$pacelap');>". 
				$this->pzpinyin_tones_number_to_mark($g)."</a>";


	} // foreach

	// need the <BR> between the EMBEDS and the pinyin sounds links ... 
	$output = 	"". implode(" ",$pinyin_sound_embed_tags) . 
				"<font size=1><br></font>". implode(" ",$pinyin_sound_links);


	return trim($output);
	}

	 function pzhanzi_hanzi_pinyin_index_build($filename){ 
		
		// maybe will need to change this to build Hanzi-Pinyin Index
		// maybe will change if anyone cares about the naming

		#suppose your handle opens a useful character index
		$handle = fopen(DATA_DIR."/".$filename , "r");

				#if you can grip the handle
				if ($handle)
				{
	
				#while you haven't finished gripping the handle
					while (!feof($handle))
					{
	
					#grease the handle with buffer
					$buffer = fgets($handle);
	
					#chop the buffer into edible slices
					$slices = explode("\t", $buffer);
	
					# slice-0 is chinese character (bread)
					$bread = $slices[0];
		
					# slice-1 is hanyu pinyin in numerical notation (cheese)
					@$open_faced_pinyin_sandwich[$bread] = $slices[1];
		
					} // end WHILE
	
				} // end IF


		#TADA! your open faced pinyin sandwich
		return $open_faced_pinyin_sandwich;

	} // end FCN pzhanzi_hanzi_pinyin_index_build



	# cleans up a lot of messy characters when we are talking about converting inside articles.
	# perhaps this needs its own class to also provide error-handling features
	function pzhanzi_input_cleanup($qx){ 


			$qx = $this->pzpinyin_tonedisplay_stripnumbers($qx);
			$qx = str_replace("(","",$qx);
			$qx = str_replace(")","",$qx);
			$qx = str_replace(".","",$qx);
			$qx = str_replace(" ","",$qx);
			$qx = str_replace(",","",$qx);
			$qx = str_replace("'","",$qx);
			$qx = str_replace("\"","",$qx);
			$qx = str_replace(";","",$qx);
			$qx = str_replace(":","",$qx);				
			#now let's see how we can remove some chinese punctuation
			$qx = urlencode($qx);


			$qx = str_replace("%EF%BC%8C","",$qx);
			$qx = str_replace("%E3%80%81","",$qx);
			$qx = str_replace("%7C","",$qx);
			$qx = str_replace("%3A","",$qx);
			$qx = str_replace("%EF%BC%9A","",$qx);
			$qx = str_replace("+","",$qx);
			$qx = str_replace("%E3%80%82","",$qx);
			$qx = str_replace("%E3%80%8A","",$qx);
			$qx = str_replace("%E3%80%8B","",$qx);
			$qx = str_replace("%EF%BC%89","",$qx);
			$qx = str_replace("%EF%BC%88","",$qx);


			$qx = urldecode($qx);


		return $qx;
	} // end FCN pzhanzi_query_cleanup


	## !! EXPERIMENTAL ##
	// throwback to Java style of storing data in String databases :-)
	function pzhanzi_daxie_xiaoxie_loadindex(){


		$x1 = "零壹貳參肆伍陸柒捌玖拾佰仟萬億";
		$x2 = "〇一二三四五六七八九十百千萬億";

		$daxie = str_split($x1, 3);
		$xiaoxie = str_split($x2, 3);

		$daxie_array = array_combine($xiaoxie,$daxie);

	return $daxie_array;
	}


	## !! EXPERIMENTAL ##
	// throwback to Java style of storing data in String databases :-)
	function pzhanzi_xiaoxie_to_daxie($daxie){

		$daxie_array = $this->pzhanzi_daxie_xiaoxie_loadindex();

		foreach($daxie_array as $k=>$da){

			$daxie = str_replace($k,$da,$daxie);

		}

	return $daxie;
	}
	
	


	// combining hanzi_to_pinyin and add ruby display - maybe also try hanzi to zhuyin
	// @param: 	(string) inputHanzi
	// @param: 	(string) rubydisplay (optional.. sorta)
	// @return:	(string) converted_phrase
	function pzhanzi_hanzi_to_pinyin($inputHanzi, $rubydisplay = FALSE) {

			// does not always clean up the string
			$inputHanzi 		= $this->pzhanzi_input_cleanup($inputHanzi); 		
			$eachWord = explode(" ",$inputHanzi);

			# COMBINE both open-face sandwiches (traditional and simplified chinese hanzi2pinyin indexes)
			$combined_index 	= $this->pzhanzi_hanzi_pinyin_index_build("idx88.txt") + $this->pzhanzi_hanzi_pinyin_index_build("idx99.txt");
			
		foreach($eachWord as $dummy_key => &$word){

			uasort($combined_index, array($this,"pzpinyin_cmp"));

			foreach( $combined_index as $hanziside => $pinyinside  ) {
						
				
				if($rubydisplay === TRUE){


					// http://en.wikipedia.org/wiki/Ruby_character
					// <ruby><rb>**ruby**base</rb><rp>(</rp><rt>**rubytext**</rt><rp>)</rp></ruby> 		
					$pinyinside = "<ruby><rb><font size=6 color=navy> $hanziside </font></rb>
					<rp>(</rp><rt><font size=3 color=gray> $pinyinside </font></rt><rp>)</rp></ruby>";
					
					$word = str_replace ($hanziside, $pinyinside, $word);	

				} else {
					
						$word = str_replace ($hanziside, $pinyinside, $word);	
										
				}

			} // end FOREACH hanzi side versus pinyin side

		$converted_words[] = $word;
		} // end foreach WORD

		$converted_phrase = implode(" ",$converted_words);
		
		
		// fixes issue 128 .. 
		$converted_phrase = str_replace("\n", " ", $converted_phrase);

	return $converted_phrase; // string of converted pinyin phrase
	} // end FUNCTION pzpinyin_pinyintype_change






	function pzhanzi_traditional_to_simplified($inputHanzi) {

				// does not always clean up the string
				$inputHanzi 		= $this->pzhanzi_input_cleanup($inputHanzi); 		
				$eachWord = explode(" ",$inputHanzi);

				# COMBINE both open-face sandwiches (traditional and simplified chinese hanzi2pinyin indexes)
				$combined_index 	= $this->pzhanzi_hanzi_pinyin_index_build("idx33.txt");

			foreach($eachWord as $dummy_key => &$word){

				uasort($combined_index, array($this,"pzpinyin_cmp"));

				foreach( $combined_index as $tradside => $simpside  ) {

							$word = str_replace ($tradside, $simpside, $word);	

				} // end FOREACH hanzi side versus pinyin side

			$converted_words[] = $word;
			} // end foreach WORD

			$converted_phrase = implode(" ", $converted_words);
			
			// fixes issue 128 .. 
			$converted_phrase = str_replace("\n", "", $converted_phrase);
			

		return $converted_phrase; // string of converted pinyin phrase
		} // end FUNCTION pzpinyin_pinyintype_change
		
		

	// ** EXPERIMENTAL ** ... STILL *SIGH* ...//
	function pzcedict_entry_getvalues($m, $def_array = FALSE){


		# four delimiters, later can be function arguments, maybe?
		$py_start 		= "[";	// pinyin starts here
		$py_end 		= "]";	// pinyin ends here
		$defs_separator = "/"; 	// separates multiple definitions /this/that/the_other/
		$part_separator = " "; 	// separates part of each entry CHINESE, PINYIN, THEN DEFINITIONS


		# LINE-SKIPPING CODE HERE ... *LALLY DAAAAA *LALLY DAAAAA *LALLY DAAAAA 
		if( ord($m) == 35 ){ // if this is a comment line .. just FOGHETABOUT-IT ... 
			return NULL; // return nothing
		}



	      //  ** maybe ... NEED A SEPARATE CLASS FOR HANDLING OF LANGUAGE PROCESSING ERRORS ** // 
	      try{

	         # no args?  AAH!!!
	         if(!func_get_arg(0))
	         {
	         throw new Exception ("the ONE AND ONLY parameter is missing! 
						PLEASE INPUT A SINGLE LINE FROM CEDICT 8809<p>");
	         }

	            try{

	            # we are missing any of the 3 important delimiters... AAH!!!
	            if( !strpos($m,$py_start) || !strpos($m,$py_end) || !strpos($m,$defs_separator))
	            {
	            throw new Exception ("ah!");
	            }

	               try
	               {


			//	echo ord($m); // checks for leading characters ... looks ok

	                     # line doesn't start with traditional character?... AAH!!!

						// the last character of the first character set
						$back_m =  substr(substr($m,0,strpos($m," ")),-3,3); 

						// does the first character or the last character of the first character set look like
						if( ord(urlencode($m)) === 37 ||  ord(urlencode($back_m)) == 37 ) {

						} else  {

	                      throw new Exception ("ahh!");

	                     }

	                     } catch (Exception $e)
	                     {

	                        return 	"CEDICT format error -- CHARACTER syntax error 8802 -- 
									FIRST OR LAST CHARACTER OF THE FIRST CHARACTER SET 
									DOES NOT TO APPEAR TO BE CHINESE<p>";

	                     }  // end TRYCATCH character

	               } catch (Exception $e)
	               {
	                      return 	"CEDICT format error -- DELIMITER syntax error 8801 -- 
									MUST FOLLOW CEDICT LINE SYNTAX<p>";
	               } // end TRYCATCH delimiter

	         } catch (Exception $e)
	         {


	            return "PLEASE PROVIDE A LINE FROM CEDICT -- LINE input error 8808";

	         } // end TRYCATCH no parameters



	            # this grabs the traditional characters before the first space
	            $trad_chars = substr($m,0,strpos($m,$part_separator) );
	            $trad_chars = trim($trad_chars);    // groom output, remove whitespace from in front and in back



	            # this grabs the simplified characters between the first space and the space touching pinyin
	            $simp_chars = substr($m,strpos($m,$part_separator),strpos($m,$py_start) - strpos($m,$part_separator) );
	            $simp_chars = trim($simp_chars);    // groom output // groom output, remove whitespace from in front and in back



	            # this grabs the pin1 yin1 sounds within the [ ] brackets
	            $pinyin = substr($m, (strpos($m,$py_start) + 1), strpos($m,$py_end) - strpos($m,$py_start) - 1) ;
	            $pinyin = trim(strtolower($pinyin)); // groom output // groom output, remove whitespace from in front and in back


	            # this is the remaining portion of the string until the end of the line... pal...
	            $definitionExtract = substr($m,strpos($m,$py_end) + 1);


				## returns array from the Land of Make Believe! DING! DING!
	            $trolley['trad'] 	= (string) $trad_chars;
	            $trolley['simp'] 	= (string) $simp_chars;
	            $trolley['py'] 		= (string) $pinyin;


	            if ( $def_array === TRUE ) {

	            # each of the definitions are delimited by a / 
	            $eachDef = explode($defs_separator,$definitionExtract);



	            		## the following algorithm may be faster than array_walk,
			            ## i have no idea yet ( i don't know why i care yet )

	                    #brings order to galaxy of defintions (A-Z sorting)
	                    sort($eachDef);                
	                    // #print_r($eachDef);  // just checking output prior to array_shift



	                    # cleans front of the array, if there are any NULL elements, front since blank is front side 
	                    while(trim($eachDef[0]) == ""){

							array_shift($eachDef); // chop!!

	                    }

	                    // #print_r($eachDef);  // just checking output again, looks good




	             $trolley['defs'] 	= (array) $eachDef;    
				// var_dump($trolley); // one for the road. one last check.



				} else {


					$trolley['defs']	= (string) $definitionExtract;

				}


	     		return $trolley; // returns the ARRAY of ENGLISH, CHINESE, and DEFINITIONS .. as promised.


	    } // end FCN pzcedict_entry_get_values

	
	## !!! EXPERIMENTAL !!! ## 
	function pzcedict_metadata_describe($filename){
	
		$metadata = array();
		
		$handle = fopen( $filename, "r"); // USES raw unchanged CEDICT flatfile

		$row = 1;
		if ($handle) {

		    while (!feof($handle)) {

		        $line = fgets($handle, 9000);		

				if(strstr($line, "#!")) {
				
					$metadataline = explode("=",$line);
					$key = str_replace("#!","",$metadataline[0]);
				
					$metadata[$key] =  $metadataline[1];
				
			}

		    } // end WHILE
		
		    fclose($handle);
		
		} // end IF
		

	return $metadata;
	} // end FCN get_metadata	





## ______________________________________________________________________________________________
## PrimezeroTools - pznumbers_ -- processing numbers and quantities into Chinese
## Puny Methods below this line ... 



	function pznumbers_convert($n){
	
		// n is actual number
		// m is the switch for the operation
	
		$m = $this->pznumbers_detex($n);
	
		switch($m){ 
		
			case 'regex_fraction':	
				return $this->pznumbers_fraction_calculate($n);
			break;
		
			case 'regex_percent':
				return $this->pznumbers_fraction_calculate($n,1);	// toggling percent
			break;

			case 'regex_time':
				return $this->pznumbers_time_calculate($n);
			break;


			case 'regex_time_am_pm':
				return $this->pznumbers_time_calculate($n);
			break;
		
			case 'regex_decimal':
				return $this->pznumbers_decimal_calculate($n);
			break;

			case 'regex_whole_number':
				return $this->pznumbers_bignumber_calculate($n);
			break;

			case 'regex_yearonly':
				return $this->pznumbers_yearonly_calculate($n);
			break;

			case 'regex_money':
				return $this->pznumbers_money_calculate($n);
			break;

			case 'regex_money_commas':
				return $this->pznumbers_money_calculate($n);
			break;

			case 'regex_whole_commas':
				return $this->pznumbers_bignumber_calculate($n);
			break;

			case 'regex_decimal_commas':
				return $this->pznumbers_decimal_calculate($n);
			break;

			case 'regex_date_long_1':
				return $this->pznumbers_date_calculate($n);
			break;

			case 'regex_date_short_1':
				if($n = $this->pznumbers_checkdate_short_isvalid($n)){
					return $this->pznumbers_date_calculate($n);
				} else {
					return FALSE;
				}
			break;	
		
			case 'regex_date_short_2':
				if($n = $this->pznumbers_checkdate_short_isvalid($n)){
					return $this->pznumbers_date_calculate($n);
				} else {
					return FALSE;
				}
			break;	
					
			case 'regex_dur_weeks':
				return $this->pznumbers_duration_weeks($n);
			break;

			case 'regex_dur_months':
				return $this->pznumbers_duration_months($n);
			break;

			case 'regex_dur_days':
				return $this->pznumbers_duration_days($n);
			break;

			case 'regex_dur_hours':
				return $this->pznumbers_duration_hours($n);
			break;

			case 'regex_dur_minutes':
				return $this->pznumbers_duration_minutes($n);
			break;
	
			case 'regex_ordinal':
				return $this->pznumbers_ordinal($n);
			break;
	
			default:
				return FALSE;		// just returns FALSE if not converted
			break;
		
		} //end SWITCH statement
	
	} // end FCN pznumbers_convert	



	function pznumbers_detex($q){

		$q = strtolower($q);

		$delimit_date 			= "([-/_\.])";
		$any_month_longname 	= "(january|february|march|april|may|june|july|august|september|october|november|december)";
		$any_month_shortname 	= "(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)";



		$regex_array['regex_dur_minutes'] 		= 	"^([0-9]{1,12}) minutes[s]?";



		$regex_array['regex_dur_hours'] 		= 	"^([0-9]{1,12}) hour[s]?";



		$regex_array['regex_dur_days'] 		= 	"^([0-9]{1,12}) day[s]?";



		$regex_array['regex_ordinal'] 		= 	"^([2-9]*(1st|2nd|3rd|[4-9]th)|[2-9]0th|1[0-9]th)";		



		$regex_array['regex_dur_weeks'] 		= 	"^([0-9]{1,12}) week[s]?";



		$regex_array['regex_dur_months'] 		= 	"^([0-9]{1,12}) month[s]?";


		// July 29, 2005
		$regex_array['regex_date_long_1'] 		= 	"^". $any_month_longname . " [0-9]{1,2}, [0-9]{4,4}$";





		// MM-DD-YYYY
		$regex_array['regex_date_short_1']	 	= 	"^[0-9]{1,2}" . $delimit_date . "[0-9]{1,2}" . $delimit_date . "[0-9]{4}$";			

		// YYYY-MM-DD
		$regex_array['regex_date_short_2']	 	= 	"^[0-9]{4}" . $delimit_date . "[0-9]{1,2}" . $delimit_date . "[0-9]{1,2}$";			





		$regex_array['regex_yearonly']	 		= "^(year) ([0-9]{1,4})$";	

		$regex_array['regex_money']		 		= "^[$]+([0-9]{1,12})(\.([0-9]{2}))?$";	

		$regex_array['regex_money_commas']		= "^[$]+[0-9]{1,3}(\,[0-9]{3})*(\.([0-9]{2}))?$";			



		$regex_array['regex_fraction'] 			= "^[-]?([0-9]{1,12})/([0-9]{1,12})$"; 

		$regex_array['regex_percent'] 			= "^[-]?([0-9]{1,12})%";

		$regex_array['regex_time'] 				= "^([1-9]|[1][0-9]|[2][0-3]|[0][0]):([0-5][0-9])$";

		$regex_array['regex_time_am_pm'] 		= "^ *(1[0-2]|[1-9]):[0-5][0-9] *(a|p|A|P)(m|M) *$";		

		$regex_array['regex_decimal'] 			= "^[-]?([0-9]{1,12})\.([0-9]{1,12})$";

		$regex_array['regex_decimal_commas'] 	= "^[-]?[0-9]{1,3}(\,[0-9]{3})*\.([0-9]{1,12})$";				

		$regex_array['regex_whole_number'] 		= "^[-]?([0-9]{1,12})$";		

		$regex_array['regex_whole_commas'] 		= "^[-]?[0-9]{1,3}(\,[0-9]{3})*$";				





			foreach($regex_array as $regex_key => $regex_value){

				if ( eregi ($regex_value, $q, $regs ) ) {

					return $regex_key;

				} // end IF REGEX

			} // end FOREACH



	} // end FCN detexNumbers



	function pznumbers_ordinal($n){

		# husking the ordinal suffixes


		$n = str_replace( "st" , "" , $n );	
		$n = str_replace( "nd" , "" , $n );			
		$n = str_replace( "rd" , "" , $n );
		$n = str_replace( "th" , "" , $n );				

		$n = $this->pzpinyin_gethanzi_by_pinyin('di') . $this->pznumbers_bignumber_calculate($n);

	return $n;	
	}	


	function pznumbers_duration_minutes($n){

		# husking minutes
		$n = str_replace( " minutes" , "" , $n );
		$n = str_replace( " minute" , "" , $n );		

		$n = $this->pznumbers_bignumber_calculate($n) . 
		$this->pzpinyin_gethanzi_by_pinyin('fen') . 
		$this->pzpinyin_gethanzi_by_pinyin('zhong');

	return $n;	
	}


	function pznumbers_duration_hours($n){

		# husking hours
		$n = str_replace( " hours" , "" , $n );
		$n = str_replace( " hour" , "" , $n );		

		$n = $this->pznumbers_bignumber_calculate($n) . 
		$this->pzpinyin_gethanzi_by_pinyin('ge').
			$this->pzpinyin_gethanzi_by_pinyin('xiaoshi');	

	return $n;	
	}

	function pznumbers_duration_days($n){

		# husking days
		$n = str_replace( " days" , "" , $n );
		$n = str_replace( " day" , "" , $n );		

		$n = $this->pznumbers_bignumber_calculate($n) . 
		$this->pzpinyin_gethanzi_by_pinyin('tian');

	return $n;	
	}	


	function pznumbers_duration_weeks($n){

		# husking weeks
		$n = str_replace( " weeks" , "" , $n );
		$n = str_replace( " week" , "" , $n );		

		$n = $this->pznumbers_bignumber_calculate($n) . 
		$this->pzpinyin_gethanzi_by_pinyin('ge') . 
		$this->pzpinyin_gethanzi_by_pinyin('xingqi');

	return $n;	
	}	

	function pznumbers_duration_months($n){

		# husking months
		$n = str_replace( " months" , "" , $n );
		$n = str_replace( " month" , "" , $n );		

		$n = $this->pznumbers_bignumber_calculate($n) . 
		$this->pzpinyin_gethanzi_by_pinyin('ge') . 
		$this->pzpinyin_gethanzi_by_pinyin('yue');

	return $n;	
	}	

	function pznumbers_delimiters_normalize($n,$m){

		$n = str_replace("-",$m,$n);
		$n = str_replace("_",$m,$n);	
		$n = str_replace(".",$m,$n);	

	return $n;	
	}


	function pznumbers_date_calculate( $inputDate ) {

		$year 			= $this->pzpinyin_hanyu_pinyin_transcribe_to_hanzi(date("Y",strtotime($inputDate)));
		$month 			= $this->pznumbers_bignumber_calculate(date("n",strtotime($inputDate)));
		$day 			= $this->pznumbers_bignumber_calculate(date("j",strtotime($inputDate)));

		$myChineseDate 	= $year. $this->pzpinyin_gethanzi_by_pinyin('nian');		// YEAR
		$myChineseDate .= $month.  $this->pzpinyin_gethanzi_by_pinyin('yue');		// MONTH
		$myChineseDate .= $day. $this->pzpinyin_gethanzi_by_pinyin('ri');			// DAY (RI or HAO)

	return $myChineseDate;
	}




	function pznumbers_checkdate_short_isvalid($inputDate){

			$calendar_year 	= "";
			$calendar_month = "";
			$calendar_day 	= "";

			$month_array 	= array(

				1 => "January",
				2 => "February",		
				3 => "March",				
				4 => "April",						
				5 => "May",							
				6 => "June",								
				7 => "July",
				8 => "August",		
				9 => "September",				
				10 => "October",						
				11 => "November",							
				12 => "December"

				);

			$mon_array = array();
			foreach($month_array as $ma){

				$mon_array[] = strtolower(substr($ma,0,3));

			}				

			$check_array = explode("/",$this->pznumbers_delimiters_normalize($inputDate,"/"));

		//	print_r($check_array);



			// MUST BE A TRIPLET
			if(count($check_array) !== 3){
				return FALSE;
			}


			// // YYYY-MM-DD
			if(  strlen($check_array[0]) == 4 )
			{
				$calendar_year 	= (int)$check_array[0];
				array_shift($check_array);


			// // MM-DD-YYYY
			} elseif(  strlen($check_array[2]) == 4 ) {

				$calendar_year 	= (int)$check_array[2];
				array_pop($check_array);	

			}




			$calendar_month = (int)$check_array[0];	

				if($calendar_month > 12){

					return FALSE;

				}



			$calendar_day 			= (int)$check_array[1];
			$calendar_month_name 	= $month_array[$calendar_month];

			$month_days_possible 	= date("t",strtotime("$calendar_month_name 1, $calendar_year"));


			if($calendar_day > $month_days_possible){

				return FALSE;
			}


			$date_to_check 			= $calendar_month_name . " " . $calendar_day . ", " . $calendar_year;

			// NOTE: LOOK AT THE FIRST DAY OF THE MONTH, TO FIND THE CORRECT MONTH.
			// IF YOU HAVE A CALENDAR OF 32, IT WILL BLEED TO THE NEXT DAY BECAUSE OF THE UNIX TIMESTAMP


	return $date_to_check;
	}	


	function pznumbers_yearonly_calculate($n){

	$n = str_replace("year ","",$n);	


	return $this->pzpinyin_hanyu_pinyin_transcribe_to_hanzi($n) . $this->pzpinyin_gethanzi_by_pinyin('nian');	
	}


	function pznumbers_money_calculate($n){

		$n = str_replace(",","",$n);

		$kuai_count = 0; // 1 yuan
		$mao_count 	= 0; // 1/10 of yuan
		$fen_count 	= 0; // 1/100 of yuan

		$dollars 	= "";
		$cents 		= "";		

		$kuai		= "";
		$mao 		= "";
		$fen 		= "";

		$mao_unit 	= "";
		$kuai_unit 	= "";
		$fen_unit 	= "";

		$money_only = explode("$",$n);

		if(strpos($n,".")) {

			$dollars_and_cents 	= explode(".",$money_only[1]);
			$dollars			= $kuai_count =  $dollars_and_cents[0];
		 	$kuai 				= $this->pznumbers_bignumber_calculate($dollars);
			$cents 				= $dollars_and_cents[1];	

		} else {

			$dollars 	= $kuai_count =	$money_only[1];
		 	$kuai 		= $this->pznumbers_bignumber_calculate($dollars);			

		}


		if($dollars == 2){
			$kuai = $this->pzpinyin_gethanzi_by_pinyin('liang');
		}


		if($kuai) {

			$kuai_unit 	= $this->pzpinyin_gethanzi_by_pinyin('kuai');
		}



		while ($cents >= 10) 
		{
			$cents 		= $cents - 10;
			$mao_count++;
			$mao_unit 	= $this->pzpinyin_gethanzi_by_pinyin('mao');
		}

		if($mao_count){

			if($mao_count == 2){

				$mao	= $this->pzpinyin_gethanzi_by_pinyin('liang');

			} else {

				$mao 	= $this->pznumbers_bignumber_calculate($mao_count);

			}


		}





		$fen_count = (int)$cents;	

		if($fen_count){


			if($cents == 2){

				$fen 	= $this->pzpinyin_gethanzi_by_pinyin('liang');

			} else {

				$fen 	= $this->pznumbers_bignumber_calculate($fen_count);				
			}


			$fen_unit 	= $this->pzpinyin_gethanzi_by_pinyin('fen');
		}


		// if there are only dollars ... play this tune
		if(!$fen_count && !$mao_count){

			return $kuai . $kuai_unit;
		}

		// if we have between 0 and 1 .. (only cents) .. play this tun
		if(!$kuai_count && ($mao_count || $fen_count))
		{
			return $mao. $mao_unit . $fen . $fen_unit;
		}


		// otherwise, play all of this ... 
		return $kuai .  $kuai_unit . $mao . $mao_unit . $fen . $fen_unit;

	}



	// PERCENT + FRACTION ARE TOGETHER ...
	// DECIMAL WILL ADDED AGAIN AS OF VERSION 0.5.3
	function pznumbers_decimal_calculate($n){

		$t	 			= explode(".", $n);

		$leftSide 		= $t[0];			// left side of the decimal
		$riteSide 		= $t[1];			// right side of the decimal

		$leftSide 		= $this->pznumbers_bignumber_calculate($leftSide);

		$riteSide 		= $this->pzpinyin_hanyu_pinyin_transcribe_to_hanzi($riteSide);

		$myDecimal 		= $leftSide . $this->pzpinyin_gethanzi_by_pinyin('dian') . $riteSide;

	return $myDecimal;
	}







	function pznumbers_fraction_calculate( $n, $forcePercent = 0){


		if($forcePercent){

			$t = explode("%", $n);	// delimiter is % sign

		} else {

			$t = explode("/", $n); 	// delimiter is / sign

		}


		$leftSide = $t[0];


		$riteSide = $t[1];


		$negativeNumber = 0;

		if($leftSide < 0 ){

			$negativeNumber = 1;

			$leftSide = $leftSide / -1;

		}


		 $leftSide = $this->pznumbers_bignumber_calculate($leftSide);
		 $riteSide = $this->pznumbers_bignumber_calculate($riteSide);

		if ($riteSide == 100 || $forcePercent) {

			// precisely BAI , not YI BAI 
			$riteSide = $this->pzpinyin_gethanzi_by_pinyin('bai'); 

		}

		//** flips the values around and orders for presentation 
		$myFraction = $riteSide . $this->pzpinyin_gethanzi_by_pinyin('fen') . $this->pzpinyin_gethanzi_by_pinyin('zhi') . $leftSide;

		if($negativeNumber)
		{
			$myFraction = $this->pzpinyin_gethanzi_by_pinyin('fu'). $myFraction;
		}		

	return $myFraction;
	}



	function pznumbers_time_calculate($inputTime) {

		$am_pm 		= 'am';
		$hours 		= "";	// converted hours
		$minutes 	= "";	// converted minutes
		$timex 		= "";


		$inputTime 	= strtolower(trim($inputTime));


		//** peel off AM or PM from the input string
	 	if(substr($inputTime, -2, 2) == "am"){ 

			$t 		= explode("am",$inputTime);
			$timex 	= trim($t[0]);				


		} else if(substr($inputTime, -2, 2) == "pm") {

			$t 		= explode("pm",$inputTime);
			$timex 	= trim($t[0]);
			$am_pm 	= 'pm';


		} else {

			$timex 	= $inputTime;

		}


		//** split the clock time into hours and minutes 
		$timeSplit 	= explode(":", $timex);

		$h 			= (int)$timeSplit[0];	// hours
		$m 			= (int)$timeSplit[1];	// minutes


		if($h > 11)
		{
			$am_pm = 'pm';
			$h = $h - 12;
		}

		// if in the midnight hour
		if ($h == 0)
		{
			$h = 12;
		}

		$hours = $this->pznumbers_number_calculate($h);
		$hours = $this->pznumbers_time_hours_cleanup($hours);		

		if ($m == 0) {

			return $this->pzpinyin_gethanzi_by_pinyin($am_pm). $hours . $this->pzpinyin_gethanzi_by_pinyin('dian');	

		} else if ($m < 10) {


			$minutes = $this->pznumbers_time_minutes_cleanup($m);	

		} else {


			$minutes = $this->pznumbers_number_calculate($m);


		}


		$myResult = $this->pzpinyin_gethanzi_by_pinyin($am_pm) . $hours . $this->pzpinyin_gethanzi_by_pinyin('dian') . $this->pzpinyin_gethanzi_by_pinyin($minutes). $this->pzpinyin_gethanzi_by_pinyin('fen');

	return $myResult;
	}











 	function pznumbers_number_calculate( $n ) {

		$n = str_replace(",","",$n);

	// n = number
	// g = graphical output

		$Qian 				= 0;
		$Bai 				= 0;
		$Shi 				= 0;
		$leftOver 			= 0;

		$wanUnit 			= "";
		$qianUnit 			= "";
		$baiUnit 			= "";
		$shiUnit 			= "";

		// this value is total number of grasshoppers left to be counted
		$grassHopper = $n;

		while ($grassHopper >= 1000)
		{
			$grassHopper 	= $grassHopper - 1000;
			$Qian++;
			$qianUnit 		= $this->pzpinyin_gethanzi_by_pinyin('qian');
		}

		while ($grassHopper >= 100)
		{
			$grassHopper 	= $grassHopper - 100;
			$Bai++;
			$baiUnit 		= $this->pzpinyin_gethanzi_by_pinyin('bai');
		}

		while ($grassHopper >= 10)
		{
			$grassHopper 	= $grassHopper - 10;
			$Shi++;
			$shiUnit 		= $this->pzpinyin_gethanzi_by_pinyin('shi');
		}

		$leftOver = $grassHopper;


		// for 120, 130, 150 ....
		if ($Bai > 0 && $Shi > 0 && $leftOver == 0)
		{
			$shiUnit		 = "";
		}

		// for 1200,
		if ($Qian > 0 && $Bai > 0 && $Shi == 0 && $leftOver == 0)
		{
			$shiUnit 		= "";
			$baiUnit 		= "";
		}


		if ($Qian > 0 && $Bai == 0 && $Shi == 0 && $leftOver == 0)
		{
			$baiUnit 		= "";
			$Bai 			= "";
			$shiUnit 		= "";
			$Shi 			= "";
		}


		// for 1000, 2000, 3000 ....
		if ($Qian > 0 && $Bai == 0 && $Shi == 0 && $leftOver == 0)
		{
			$baiUnit 		= "";
			$Bai 			= "";
			$shiUnit 		= "";
			$Shi 			= "";
		}


		// for 100, 200, 300....
		if ($Bai > 0 && $Shi == 0 && $leftOver == 0)
		{
			$shiUnit 		= "";
			$Shi 			= "";
		}


		// * shi yi, shi er, shi san ...
	 	// * 12.24.2003
		if ($n < 20)
		{
			$Shi 			= "";
		}



		if ($n < 100)
		{
			$Bai			= "";
		}

		if ($n < 1000)
		{
			$Qian 			= "";
		}


		// remove the redundant lings in the case of 1001, 2002,
		if ($Qian > 0 && $Bai == 0 && $Shi == 0)
		{
			$Shi 			= "";
		}


		// if it ends with zero ... 10, 20, 30 ...
		if ($leftOver == 0 || $leftOver == "0")
		{

			$leftOver 		= "";

		} else {

			$leftOver 		= $this->pzpinyin_gethanzi_by_pinyin($leftOver);			

		}

		/// trivial case
		if ($n == 0)
		{
			$leftOver 	= $this->pzpinyin_gethanzi_by_pinyin('0');
		}

		$Qian 		= $this->pzpinyin_gethanzi_by_pinyin($Qian);
		$Bai 		= $this->pzpinyin_gethanzi_by_pinyin($Bai);
		$Shi 		= $this->pzpinyin_gethanzi_by_pinyin($Shi);

		$shiUnit 	= $this->pzpinyin_gethanzi_by_pinyin($shiUnit);
		$baiUnit 	= $this->pzpinyin_gethanzi_by_pinyin($baiUnit);		
		$qianUnit 	= $this->pzpinyin_gethanzi_by_pinyin($qianUnit);				



		$myResult 	= $Qian . $qianUnit . $Bai .  $baiUnit . $Shi .  $shiUnit .  $leftOver;


	return $myResult;
	}



	function pznumbers_bignumber_calculate($n){



		$n = str_replace(",","",$n);

		$negativeNumber = 0;

		if($n < 0 ){

			$negativeNumber = 1;
			$n = $n / -1;

		}

		// this value is total number of grasshoppers left to be counted
		$grassHopper = $n;

		if ($n > 99999999) {

			$BigYi = 0;

				while ($grassHopper >= 100000000)
				{
				$grassHopper = $grassHopper - 100000000;
					$BigYi++;
					$bigyiUnit = $this->pzpinyin_gethanzi_by_pinyin('biggie'); // bigyi -> biggie ^_^
				}


				$BigYiPinyin = $this->pznumbers_number_calculate($BigYi);
				$myBigYi = $BigYiPinyin.$bigyiUnit;


		}


		if ($n > 9999) {

			$Wan = 0;
			$wanUnit = "";

				while ($grassHopper >= 10000)
				{
					$grassHopper = $grassHopper - 10000;
					$Wan++;
					$wanUnit = $this->pzpinyin_gethanzi_by_pinyin('wan');
				}

					$WanPinyin = $this->pznumbers_number_calculate($Wan);
					$myWan = $WanPinyin.$wanUnit;

		}


			$myBaseUnit = $this->pznumbers_number_calculate($grassHopper);
			$myBigNumber = $myBaseUnit;


		if ($n > 9999) {


			if ($grassHopper < 999)
			{
				$myBaseUnit = $this->pzpinyin_gethanzi_by_pinyin('ling').$myBaseUnit;
			}


			// if you get  #,000,00#
			if ($grassHopper == 0)
			{
				$myBaseUnit ="";
			}


			if ($Wan == 0 && $grassHopper != 0)
			{
				$myWan = $this->pzpinyin_gethanzi_by_pinyin('ling');
			}


			if ($Wan == 0 && $grassHopper != 0)
			{
				$myWan = $this->pzpinyin_gethanzi_by_pinyin('ling');
			}

			if ($Wan == 0 && $grassHopper == 0)
			{
				$myWan = "";
			}


		$myBigNumber = $myWan.$myBaseUnit;
		}


		if ($n > 99999999) {


				if ($grassHopper == 0)
				{
					$myBaseUnit ="";
				}

				if ($Wan == 0 && $grassHopper > 10000)
				{
					$myWan = $this->pzpinyin_gethanzi_by_pinyin('ling');
				}



		$myBigNumber = $myBigYi.$myWan.$myBaseUnit;
		}

	$myBigNumber = $this->pznumbers_ling_reduce($myBigNumber);


	if($negativeNumber)
	{
		$myBigNumber = $this->pzpinyin_gethanzi_by_pinyin('fu'). $myBigNumber;
	}

	return $myBigNumber;
	}


	// patch for duplicate lings in MILLION (BIG CHINESE NUMBER CALCULATIONS)
	function pznumbers_ling_reduce($n){

		// LING LING --> LING
		$n = str_replace( $this->pzpinyin_gethanzi_by_pinyin('ling').$this->pzpinyin_gethanzi_by_pinyin('ling'),$this->pzpinyin_gethanzi_by_pinyin('ling') , $n);

	return $n;
	}


	//** removes lings and converts er->liang where needed 
	//** clock time cases for --> HH:02 
	function pznumbers_time_minutes_cleanup($m) {


//		$minutes ="$minutes";
//		$minutes = str_replace($this->pzpinyin_gethanzi_by_pinyin('2'), "", $minutes);

		//** groom the data 
		$m = trim($m);


		//** If there are only 2 minutes....
		if ($m == 2)
		{
			$m  = $this->pzpinyin_gethanzi_by_pinyin('liang');
		}

	return $m;
	}




	//** removes excess white space, and converts er->liang where needed 
	//** clock time cases for --> 2:MM 
	function pznumbers_time_hours_cleanup($hours) {

		$hours = trim($hours);

		if ($hours == $this->pzpinyin_gethanzi_by_pinyin('2'))
		{
			$hours = $this->pzpinyin_gethanzi_by_pinyin('liang');
		}

	return $hours;
	}



## ______________________________________________________________________________________________
## PrimezeroTools - pzkelvin_ -- Understands and converts temperatures within thermodynamic rules
## Puny Methods below this line ...



	const absolute_zero_error_msg 	= 'no temperature exists below absolute zero';

	# based on what the listener/detector has detected
	# this function will provide the appropriate conversion
	function pzkelvin_temperature_convert( $q , $displayConversion = FALSE ){
		
		
		$fcn_num = $this->pzkelvin_temperature_detex($q);

		if(!isset($fcn_num))
		{
			return "no conversions available";
		}
		
		$fcn_list = array(

			0=> 'c2f',
			1=> 'f2c',	
			2=> 'c2k',
			3=> 'k2c',
			4=> 'c2r',
			5=> 'r2c',
			6=> 'r2k',
			7=> 'k2r',	
			8=> 'r2f',
			9=> 'f2r',
			10=> 'k2f',
			11=> 'f2k'

			);
	
		$scale_list = array(
			
			'c'=>'celsius',
			'r'=>'rankine',
			'k'=>'kelvin',
			'f'=>'fahrenheit'
			
			);

		
					
		$to_from = explode("2",$fcn_list[$fcn_num-1]);
		$b 		= explode(" ", $q);
		$n 		= $this -> pzkelvin_temperature_cleanup($b[0]);
		


		if($displayConversion){
	
		echo "<font size=5><b>$n " . ucfirst ( $scale_list[$to_from[0]] ) . " = " . number_format($this->$fcn_list[$fcn_num-1]($n), 2, '.', ',') . " " .  ucfirst( $scale_list[$to_from[1]] )  . " </b></font><P>";
		
	}
		

	
	return $this->$fcn_list[$fcn_num-1]($n);

	}
	
	
	# possibly the listener/detector that will tell me what i'm looking at
	function pzkelvin_temperature_detex($q){
		
		$q = strtolower($q);

		$regex_num_input_pi = "^[-]?([1-9]{1}[0-9]{0,}(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|\.[0-9]{1,2}) ?|(pi )";
		$regex_num_input = "^[-]?([1-9]{1}[0-9]{0,}(\.[0-9]{0,2})?|0(\.[0-9]{0,2})?|\.[0-9]{1,2}) ?";

		// can refactor more to make make variables comprise the regex
		
		$regex_c2f = ".*( celsius | degc |c )(in|at|to)( fahrenheit| degf| f)$"; //1
		$regex_f2c = ".*( fahrenheit | degf |c )(in|at|to)( celsius| degc| c)$"; //2

		$regex_c2k = ".*( celsius | degc |c )(in|at|to)( kelvin| degk| k)$"; //3
		$regex_k2c = ".*( kelvin | degk |k )(in|at|to)( celsius| degc| c)$"; //4		

		$regex_c2r = ".*( celsius | degc |c )(in|at|to)( fahrenheit| degf| f)$"; //5
		$regex_r2c = ".*( rankine | degr |r )(in|at|to)( celsius| degc| c)$"; //6
	
		$regex_r2k = ".*( rankine | degr|r )(in|at|to)( kelvin| degk| k)$"; //7
		$regex_k2r = ".*( kelvin | degk|k )(in|at|to)( rankine| degr| r)$"; //8
		
		$regex_r2f = ".*( rankine | degr |r )(in|at|to)( fahrenheit| degf| f)$"; //9
		$regex_f2r = ".*( fahrenheit | degf |f )(in|at|to)( rankine| degr| r)$"; //10	

		$regex_k2f = ".*( kelvin | degk |k )(in|at|to)( fahrenheit| degf| f)$"; //11
		$regex_f2k = ".*( fahrenheit | degf |f )(in|at|to)( kelvin| degk| k)$"; //12	


		$regex_array = array(
	
			0=> $regex_c2f,
			1=> $regex_f2c,	
			2=> $regex_c2k,
			3=> $regex_k2c,
			4=> $regex_c2r,
			5=> $regex_r2c,
			6=> $regex_r2k,
			7=> $regex_k2r,	
			8=> $regex_r2f,
			9=> $regex_f2r,
			10=> $regex_k2f,
			11=> $regex_f2k
	
			);
	
	
	foreach($regex_array as $regex_key => $regex_value){

			if ( (eregi ($regex_value, $q, $regs))  &&  
				 (ereg ($regex_num_input, $q, $regs)) ) {

				return ($regex_key + 1);

			} // end IF REGEX
			
	} // end FOREACH




	} //end FCN
	

	
	/**
	*
	* PLEASE NOTE THAT NO TEMPERATURE ACTUALLY EXISTS BELOW ABSOLUTE ZERO
	* COMMENTS SHOW THE ABS_ZERO TEMPERATURE FOR EACH FUNCTION
	* ALSO, ERROR HANDLING WILL BE ADDED LATER
	*
	*/

	function c2k($n){	// abs_zero = 0 K
		
		$n = $n + 273.15;

		try{

			if( $n < 0 ){

				 throw new Exception (PZKelvin::absolute_zero_error_msg);

			} // end if

		} catch( Exception $e ) {

			return (string) $e->getMessage();
			
		}  // end TRY-CATCH
		


	return $n;
	}

	function k2c($n){	// abs_zero = 0 K
		
		try{

			if( $n < 0 ){

				 throw new Exception (PZKelvin::absolute_zero_error_msg);

			} // end if

		} catch( Exception $e ) {

			return (string) $e->getMessage();
			
		}  // end TRY-CATCH
		
		$n = $n - 273.15;

	return $n;
	}

	function r2k($n){	// abs_zero = 0 R
		
		try{

			if( $n < 0 ){

				 throw new Exception (PZKelvin::absolute_zero_error_msg);

			} // end if

		} catch( Exception $e ) {

			return (string) $e->getMessage();
			
		}  // end TRY-CATCH		
		$n = $n / 1.8;
		
	return $n;
	}

	function k2r($n){	// abs_zero = 0 K
		
		try{

			if( $n < 0 ){

				 throw new Exception (PZKelvin::absolute_zero_error_msg);

			} // end if

		} catch( Exception $e ) {

			return (string) $e->getMessage();
			
		}  // end TRY-CATCH
		
		$n = $n * 1.8;
		
	return $n;
	}


	function f2r($n){	// abs_zero = 0 R
		
		$n = $n + 459.67;
		
		
		try{

			if( $n < 0 ){

				 throw new Exception (PZKelvin::absolute_zero_error_msg);

			} // end if

		} catch( Exception $e ) {

			return (string) $e->getMessage();
			
		}  // end TRY-CATCH
		
	return $n;
	}


	function r2f($n){	// abs_zero = 0 R
		
		try{

			if( $n < 0 ){

				 throw new Exception (PZKelvin::absolute_zero_error_msg);

			} // end if

		} catch( Exception $e ) {

			return (string) $e->getMessage();
			
		}  // end TRY-CATCH
		
		$n = $n - 459.67;
		
	return $n;
	}


	function c2r($n){	// abs_zero = 0 R

		$n = $this -> c2k($n);
		
		try{

			if( $n < 0 ){

				 throw new Exception (PZKelvin::absolute_zero_error_msg);

			} // end if

		} catch( Exception $e ) {

			return (string) $e->getMessage();
			
		}  // end TRY-CATCH
		
		$n = $this -> k2r($n);



	return $n;
	}


	function r2c($n){	// abs_zero = 0 R

		try{

			if( $n < 0 ){

				 throw new Exception (PZKelvin::absolute_zero_error_msg);

			} // end if

		} catch( Exception $e ) {

			return (string) $e->getMessage();
			
		}  // end TRY-CATCH


		$n = $this -> r2k($n);
		$n = $this -> k2c($n);

	return $n;
	}

	function k2f($n){	// abs_zero = 0 K
		
		try{

			if( $n < 0 ){

				 throw new Exception (PZKelvin::absolute_zero_error_msg);

			} // end if

		} catch( Exception $e ) {

			return (string) $e->getMessage();
			
		}  // end TRY-CATCH
		

		$n = $this -> k2r($n);
		$n = $this -> r2f($n);

	return $n;
	}


	function f2k($n){	// abs_zero = 0 K

		$n = $this -> f2r($n);
		$n = $this -> r2k($n);
		
		try{

			if( $n < 0 ){

				 throw new Exception (PZKelvin::absolute_zero_error_msg);

			} // end if

		} catch( Exception $e ) {

			return (string) $e->getMessage();
			
		}  // end TRY-CATCH
		

	return $n;
	}




	function c2f($n){	// abs_zero = -273.15 deg C .. 0 K

		$n = $this -> c2k($n);
		
		try{

			if( $n < 0 ){

				 throw new Exception (PZKelvin::absolute_zero_error_msg);

			} // end if

		} catch( Exception $e ) {

			return (string) $e->getMessage();
			
		}  // end TRY-CATCH
		
		
		$n = $this -> k2r($n);
		$n = $this -> r2f($n);

	return $n;
	}


	function f2c($n){ 	// abs_zero = -273.15 deg C .. 0 K

		$n = $this -> f2r($n);
		
		try{

			if( $n < 0 ){

				 throw new Exception (PZKelvin::absolute_zero_error_msg);

			} // end if

		} catch( Exception $e ) {

			return (string) $e->getMessage();
			
		}  // end TRY-CATCH
		
		
		$n = $this -> r2k($n);
		$n = $this -> k2c($n);

	return $n;
	}
	
	
	
	function pzkelvin_temperature_cleanup($n){

	$n = strtoupper($n);

	$n = str_replace("C","",$n);
	$n = str_replace("F","",$n);
	$n = str_replace("R","",$n);
	$n = str_replace("K","",$n);



	return $n;
	}




	## ____________ added in 0.9.4
	## EXPERIMENTAL ~~ .. icky algorithm
	function pzpinyin_analyze($give_me_one_word){

		$pinyin_types = array();

		$romans 	= $this -> pzpinyin_pinyintype_listall();
		$py_table 	= $this -> pzpinyin_pinyintype_loadindex();
	
		// doc, what type of romanziation is this?
		foreach($py_table as $t){

			//	var_dump($t);
			if($give_me_one_word){
				$op = trim($give_me_one_word);
				$op = urldecode($op);
				$keys_for_matches = array_keys($t,$op);
			} // end IF
			
			if(count($keys_for_matches)){ 
					foreach($keys_for_matches as $y){
							$pinyin_types[] = $romans[$y]; // <-- ok					
					} // end FOREACH romans
			} // end IF
		
		} // end FOREACH pinyin table

		// if no pinyintype is found, then state the obvious
		if(!count($pinyin_types))
		return $pinyin_types = "not a valid pinyintype";

	// returns the array telling what types of pinyin
	return $pinyin_types; 

	} // end FUNCTION pinyinTeller

} // end CLASS PrimezeroTools



?>