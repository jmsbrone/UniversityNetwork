<?php
	/*******************************************
	function check_int
	Функция проверяет на наличие переменной и на определенный интервал
	@param int $var - Собственно число
	@param int  $lb - Left Border. Крайняя левая граница отрезка
	@param int $rb - Right Border. Крайняя правая граница отрезка
	@throws throw403() 
	@return $var
	*********************************************/
	function check_int($var,$lb,$rb) {
		if ($lb<$rb){
			if ((isset($var) && isset($rb) && isset($lb))){
				if ($var<$lb || $var>$rb) {
					throw403("Invalid range of integer"); 
				} else {
						return $var;
				};
				
			}else {
					throw403("No Data");
			}
			
		}else {throw403("leftborder>rightborder");}
	}
	/*******************************************
	function check_str_eng
	Функция проверяет строку на соответствие шаблону /[^a-zA-Z0-9_]/, только латинские символы + цифры + _
	@param string $var - Собственно проверяемая строка
	@throws throw403() 
	@return $var
	*********************************************/
	function check_str_eng($var) {
		if(isset($var)){
			if (preg_match("/[^a-zA-Z0-9_]/", $var)) {
				throw403("False");
			} else {
					return $var;
			};
		}
	}
	/*******************************************
	function check_str
	запрещает все, кроме пробела,_, букв русского и латинского алфавита, цифр
	@param string $var - Собственно проверяемая строка
	@throws throw403() 
	@return $var
	*********************************************/
	function check_str($var) {
		if(isset($var))
		{
			if (preg_match("/[^(\w)|(\x7F-\xFF)|(\s)]/", $var)) {
				throw403("False"); 
			} else 	{
					return $var;
			};
		}
		
	}	// запрещает все, кроме пробела,_, букв русского и латинского алфавита, цифр

/*******************************************
	function check_str
	проверяет на соответствие заданному шаблрну
	@param string $var - Собственно проверяемая строка
	@param string $pattern - Собственно шаблон
	@throws throw403() 
	@return $var
	*********************************************/
    function check_str_with_pattern($var,$pattern)
	{
		if((isset($var) && isset($pattern)))
		
		{
			if (preg_match("$pattern", $var)) {
				throw403("False"); 
			} else 	{
					return $var;
			};
		}
	}
	/*******************************************
	function check_str_len
	Проверяет на соответствие заданной длине, либо между какими-то значениями
	@param string $var - Собственно проверяемая строка
	@param string $minlen - Минимальная длина
	@param string $maxlen - Максимальная длина
	@throws throw403() 
	@return $var
	*********************************************/
	function check_str_len($var,$minlen,$maxlen){
		
		
		if ((isset($var) && isset($minlen) && isset($maxlen)))
		
		{
			if ($minlen<=$maxlen){
				if (strlen($var)<$minlen || strlen($var)>$maxlen) {
					throw403("Invalid range of strlen");
				} else 	{
					return $var;
				};
			} else {
				throw403('Error');
			}	
		}
		
		
	}
	
?>
