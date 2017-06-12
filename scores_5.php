<?php

/*
Бот для подсчета очков в летнем пятиборье.
Формат сообщения: стрельба - плавание - метание гранаты - бег 100м - бег на выносливость
Пример: 94 1:03.5 47.4 12.2 10:18.4
*/

function checkForCorrectInput( $message ) {
	/* 
	Функция проверки сообщения на корректный формат. Возвращает итоговый результат проверки сообщения.

	Примеры

	# слишком мало результатов
	var_dump( checkForCorrectInput( '94 1:03.5 47.4 12.2' ) );
	array(1) {
	  [0]=>
	  array(2) {
	    ["type"]=>
	    string(6) "global"
	    ["status"]=>
	    string(181) "Слишком мало значений. Пожалуйста, введите пять результатов через пробел. Например: 94 1:03.5 47.4 12.2 10:18.4"
	  }
	}

	# слишком много результатов
	var_dump( checkForCorrectInput( '94 1:03.5 47.4 12.2 10:18.4 47.4' ) );
	array(1) {
	  [0]=>
	  array(2) {
	    ["type"]=>
	    string(6) "global"
	    ["status"]=>
	    string(183) "Слишком много значений. Пожалуйста, введите пять результатов через пробел. Например: 94 1:03.5 47.4 12.2 10:18.4"
	  }
	}

	# результат стрельбы меньше 0 или больше 100
	var_dump( checkForCorrectInput( '942 1:03.5 47.4 12.2 10:18.4' ) );
	array(1) {
	  [0]=>
	  array(2) {
	    ["type"]=>
	    string(8) "shooting"
	    ["status"]=>
	    string(73) "Результат стрельбы должен быть от 0 до 100"
	  }
	}

	# результат стрельбы введен некоректно
	var_dump( checkForCorrectInput( '94.9 1:03.5 47.4 12.2 10:18.4' ) );
	array(1) {
	  [0]=>
	  array(2) {
	    ["type"]=>
	    string(8) "shooting"
	    ["status"]=>
	    string(75) "Некорректный формат результата стрельбы"
	  }
	}

	# результат плавания (медленнее минуты) введен некорректно
	var_dump( checkForCorrectInput( '94 1:03.5ё 47.4 12.2 10:18.4' ) );
	array(1) {
	  [0]=>
	  array(2) {
	    ["type"]=>
	    string(4) "swim"
	    ["status"]=>
	    string(75) "Некорректный формат результата плавания"
	  }
	}

	# результат плавания (медленнее минуты) не укладывается в лимиты по минутам
	var_dump( checkForCorrectInput( '94 4:01.0 47.4 12.2 10:18.4' ) );
	array(1) {
	  [0]=>
	  array(2) {
	    ["type"]=>
	    string(4) "swim"
	    ["status"]=>
	    string(79) "Результат плавания должен быть от 49.0 до 3:32.0"
	  }
	}

	# результат плавания (быстрее минуты) не укладывается в лимиты по секундам
	var_dump( checkForCorrectInput( '94 48.99 47.4 12.2 10:18.4' ) );
	array(1) {
	  [0]=>
	  array(2) {
	    ["type"]=>
	    string(4) "swim"
	    ["status"]=>
	    string(79) "Результат плавания должен быть от 49.0 до 3:32.0"
	  }
	}

	# результат плавания (быстрее минуты) введен некорректно
	var_dump( checkForCorrectInput( '94 58.ё4 47.4 12.2 10:18.4' ) );
	array(1) {
	  [0]=>
	  array(2) {
	    ["type"]=>
	    string(4) "swim"
	    ["status"]=>
	    string(75) "Некорректный формат результата плавания"
	  }
	}

	# результат гранаты слишком низкий
	var_dump( checkForCorrectInput( '94 58.4 7.4 12.2 10:18.4' ) );
	array(1) {
	  [0]=>
	  array(2) {
	    ["type"]=>
	    string(7) "grenade"
	    ["status"]=>
	    string(99) "Результат метания гранаты должен быть от 11 до 76 метров"
	  }
	}

	# некорректный результат гранаты с указанием сантиметров
	var_dump( checkForCorrectInput( '94 58.4 47ё.4 12.2 10:18.4' ) );
	array(1) {
	  [0]=>
	  array(2) {
	    ["type"]=>
	    string(4) "swim"
	    ["status"]=>
	    string(88) "Некорректный формат результата метания гранаты"
	  }
	}

	# слишком большое значение для метания
	var_dump( checkForCorrectInput( '94 58.4 147 12.2 10:18.4' ) );
	array(1) {
	  [0]=>
	  array(2) {
	    ["type"]=>
	    string(7) "grenade"
	    ["status"]=>
	    string(99) "Результат метания гранаты должен быть от 11 до 76 метров"
	  }
	}

	# некорректное значение результат спринта
	var_dump( checkForCorrectInput( '94 58.4 47 12ё.2 10:18.4' ) );

	array(1) {
	  [0]=>
	  array(2) {
	    ["type"]=>
	    string(6) "sprint"
	    ["status"]=>
	    string(73) "Некорректный формат результата спринта"
	  }
	}

	# слишком большое значение результата спринта
	var_dump( checkForCorrectInput( '94 58.4 47 22.2 10:18.4' ) );

	array(1) {
	  [0]=>
	  array(2) {
	    ["type"]=>
	    string(6) "sprint"
	    ["status"]=>
	    string(88) "Результат спринта должен быть от 10.5 до 20.0 секунд"
	  }
	}

	# слишком медленный результат в беге на выносливость
	var_dump( checkForCorrectInput( '94 58.4 47 12.2 17:18' ) );

	array(1) {
	  [0]=>
	  array(2) {
	    ["type"]=>
	    string(5) "cross"
	    ["status"]=>
	    string(100) "Результат бега на выносливость должен быть от 5:56 до 15:00"
	  }
	}

	# некорректный результат в беге на выносливость
	var_dump( checkForCorrectInput( '94 58.4 47 12.2 10:18ё.4' ) );

	array(1) {
	  [0]=>
	  array(2) {
	    ["type"]=>
	    string(5) "cross"
	    ["status"]=>
	    string(97) "Некорректный формат результата бега на выносливость"
	  }
	}
	*/

	# результат проверки введенных результатов
	$checkTotal = array();

	# массив результатов
	$dec = explode( " ", $message );

	# сколько должно быть видов
	$correctResultsNumber = 5;

	# проверка, что результатов пять
	if ( count( $dec ) == $correctResultsNumber ) {

		# проверка первого результата (стрельба) на корректность
		# array_push( $checkTotal, checkShootingResult( $dec[0] ) );

		# проверка второго результата (плавание) на корректность
		# array_push( $checkTotal, checkSwimResult( $dec[1] ) );

		# проверка третьего результата (граната) на корректность
		# array_push( $checkTotal, checkGrenadeResult( $dec[2] ) );

		# проверка четвертого результата (спринт) на корректность
		# array_push( $checkTotal, checkSprintResult( $dec[3] ) );

		# пятый результат (выносливость) и корректность
		array_push( $checkTotal, checkCrossResult( $dec[4] ) );

	} elseif ( count( $dec ) < $correctResultsNumber ) {
		array_push( $checkTotal, array( 'type' => 'global', 'status' => 'Слишком мало значений. Пожалуйста, введите пять результатов через пробел. Например: 94 1:03.5 47.4 12.2 10:18.4' ) );

	} elseif ( count( $dec ) > $correctResultsNumber ) {
		array_push( $checkTotal, array( 'type' => 'global', 'status' => 'Слишком много значений. Пожалуйста, введите пять результатов через пробел. Например: 94 1:03.5 47.4 12.2 10:18.4' ) );
	}

	return $checkTotal;
}

function checkShootingResult( $shootingResult ) {
	/*
	Проверка первого результата на корректность. 
	Тесты приведены в описании функции checkForCorrectInput
	*/

	# проверка результата стрельбы на целочисленность
	if ( ctype_digit( $shootingResult ) ) {
			
		# проверка первого результата на значение
		if ( $shootingResult >= 0 and $shootingResult <=100 ) {

			# ок, проверка стрельбы успешна
			return array( 'type' => 'shooting', 'status' => 'Ok' );

		} else {
			return array( 'type' => 'shooting', 'status' => 'Результат стрельбы должен быть от 0 до 100' );
		}

	} else {
		return array( 'type' => 'shooting', 'status' => 'Некорректный формат результата стрельбы' );
	}
}

function checkSwimResult( $swimResult ) {
	/*
	Проверка второго результата на корректность. 
	Тесты приведены в описании функции checkForCorrectInput
	*/

	$swim = preg_split( "/(\.|,|:|;)/" , $swimResult );

	# введен результат в формате 1:03.5
	if ( count( $swim ) == 3 ) {

		# проверка на целочисленность
		if ( ctype_digit( $swim[0] ) and ctype_digit( $swim[1] ) and ctype_digit( $swim[2] ) ) {

			# проверка результатов на значения
			# количество минут - от 0 до 2 (например, 0:59.4 и 2:08.3)
			# количество секунд от 0 до 59
			# количество сотых до 0 до 99
			if ( $swim[0] >= 0 and $swim[0] <= 3 and $swim[1] >= 0 and $swim[1] <= 59 and $swim[2] >= 0 and $swim[2] <= 99  ) {
				return array( 'type' => 'swim', 'status' => 'Ok' );
			} else {
				return array( 'type' => 'swim', 'status' => 'Результат плавания должен быть от 49.0 до 3:32.0' );
			}

		} else {
			return array( 'type' => 'swim', 'status' => 'Некорректный формат результата плавания' );
		}
	
	# введен результат в формате 59.3
	} elseif ( count( $swim ) == 2 ) {

		# проверка на целочисленность
		if ( ctype_digit( $swim[0] ) and ctype_digit( $swim[1] ) ) {

			# проверка результатов на значения
			# количество секунд от 49 до 59
			# количество сотых до 0 до 99
			if ( $swim[0] >= 49 and $swim[0] <= 59 and $swim[1] >= 0 and $swim[1] <= 99  ) {
				return array( 'type' => 'swim', 'status' => 'Ok' );
			} else {
				return array( 'type' => 'swim', 'status' => 'Результат плавания должен быть от 49.0 до 3:32.0' );
			}

		} else {
			return array( 'type' => 'swim', 'status' => 'Некорректный формат результата плавания' );
		}

	} else {
		return array( 'type' => 'swim', 'status' => 'Некорректный формат результата плавания' );
	}
}

function checkGrenadeResult( $grenadeResult ) {
	/*
	Проверка третьего результата на корректность. 
	Тесты приведены в описании функции checkForCorrectInput
	*/

	$grenade = preg_split( "/(\.|,|:|;)/" , $grenadeResult );

	# введен результат в формате 43.50
	if ( count( $grenade ) == 2 ) {
		
		# проверка на целочисленность
		if ( ctype_digit( $grenade[0] ) and ctype_digit( $grenade[1] ) ) {

			# проверка результатов на значения
			# количество метров от 11 до 76
			# количество сантиметров от 0 до 99
			if ( $grenade[0] >= 11 and $grenade[0] <= 76 and $grenade[1] >= 0 and $grenade[1] <= 99 ) {
				return array( 'type' => 'grenade', 'status' => 'Ok' );
			} else {
				return array( 'type' => 'grenade', 'status' => 'Результат метания гранаты должен быть от 11 до 76 метров' );
			}			
		} else {
			return array( 'type' => 'grenade', 'status' => 'Некорректный формат результата метания гранаты' );
		}
	
	# введен результат в формате 43
	} else if ( count( $grenade ) == 1 ) {

		# проверка на целочисленность
		if ( ctype_digit( $grenade[0] ) ) {

			# проверка результатов на значения
			# количество метров от 11 до 76
			# количество сантиметров от 0 до 99
			if ( $grenade[0] >= 11 and $grenade[0] <= 76 ) {
				return array( 'type' => 'grenade', 'status' => 'Ok' );
			} else {
				return array( 'type' => 'grenade', 'status' => 'Результат метания гранаты должен быть от 11 до 76 метров' );
			}			
		} else {
			return array( 'type' => 'grenade', 'status' => 'Некорректный формат результата метания гранаты' );
		}
	} else {
		return array( 'type' => 'grenade', 'status' => 'Некорректный формат результата метания гранаты' );
	}
}

function checkSprintResult( $springResult ) {
	/*
	Проверка четвертого результата на корректность. 
	Тесты приведены в описании функции checkForCorrectInput
	*/

	$sprint = preg_split( "/(\.|,|:|;)/" , $springResult );

	# введен результат в формате 12.2
	if ( count( $sprint ) == 2 ) {
		
		# проверка на целочисленность
		if ( ctype_digit( $sprint[0] ) and ctype_digit( $sprint[1] ) ) {

			# проверка результатов на значения
			# количество секунд от 10 до 20
			# количество сотых от 0 до 99
			if ( $sprint[0] >= 10 and $sprint[0] <= 20 and $sprint[1] >= 0 and $sprint[1] <= 99 ) {
				return array( 'type' => 'sprint', 'status' => 'Ok' );
			} else {
				return array( 'type' => 'sprint', 'status' => 'Результат спринта должен быть от 10.5 до 20.0 секунд' );
			}			
		} else {
			return array( 'type' => 'sprint', 'status' => 'Некорректный формат результата спринта' );
		}
	
	} else {
		return array( 'type' => 'sprint', 'status' => 'Некорректный формат результата спринта' );
	}
}

function checkCrossResult( $crossResult ) {
	/*
	Проверка пятого результата на корректность. 
	Тесты приведены в описании функции checkForCorrectInput
	*/

	$cross = preg_split( "/(\.|,|:|;)/" , $crossResult );

	# введен результат в формате 1:03.5
	if ( count( $cross ) == 3 ) {

		# проверка на целочисленность
		if ( ctype_digit( $cross[0] ) and ctype_digit( $cross[1] ) and ctype_digit( $cross[2] ) ) {

			# проверка результатов на значения
			# количество минут - от 5 до 15
			# количество секунд от 0 до 59
			# количество сотых до 0 до 99
			if ( $cross[0] >= 5 and $cross[0] <= 15 and $cross[1] >= 0 and $cross[1] <= 59 and $cross[2] >= 0 and $cross[2] <= 99  ) {
				return array( 'type' => 'cross', 'status' => 'Ok' );
			} else {
				return array( 'type' => 'cross', 'status' => 'Результат бега на выносливость должен быть от 5:56 до 15:00' );
			}

		} else {
			return array( 'type' => 'cross', 'status' => 'Некорректный формат результата бега на выносливость' );
		}
	
	# введен результат в формате 59.3
	} elseif ( count( $cross ) == 2 ) {

		# проверка на целочисленность
		if ( ctype_digit( $cross[0] ) and ctype_digit( $cross[1] ) ) {

			# проверка результатов на значения
			# количество секунд от 5 до 15
			# количество сотых до 0 до 99
			if ( $cross[0] >= 5 and $cross[0] <= 15 and $cross[1] >= 0 and $cross[1] <= 99  ) {
				return array( 'type' => 'cross', 'status' => 'Ok' );
			} else {
				return array( 'type' => 'cross', 'status' => 'Результат бега на выносливость должен быть от 5:56 до 15:00' );
			}

		} else {
			return array( 'type' => 'cross', 'status' => 'Некорректный формат результата бега на выносливость' );
		}

	} else {
		return array( 'type' => 'cross', 'status' => 'Некорректный формат результата бега на выносливость' );
	}
}

# $message = '94 1:03.5 47.4 12.2 10:18.4';
var_dump( checkForCorrectInput( '94 58.4 47 12.2 10:18ё.4' ) );