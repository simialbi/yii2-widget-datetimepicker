<?php
/**
 * Created by PhpStorm.
 * User: karlen
 * Date: 04.10.2017
 * Time: 15:31
 */

namespace simialbi\yii2\date\helpers;

use IntlDateFormatter;
use Yii;

class FormatConverter extends \yii\helpers\FormatConverter {
	protected static $_icuShortFormats = [
		'short'  => 3, // IntlDateFormatter::SHORT,
		'medium' => 2, // IntlDateFormatter::MEDIUM,
		'long'   => 1, // IntlDateFormatter::LONG,
		'full'   => 0, // IntlDateFormatter::FULL,
	];
	/**
	 * @var array the moment fallback definition to use for the ICU short patterns `short`, `medium`, `long` and `full`.
	 * This is used as fallback when the intl extension is not installed.
	 */
	public static $momentFallbackDatePatterns = [
		'short' => [
			'date' => 'M/D/YY',
			'time' => 'HH:mm',
			'datetime' => 'M/D/YY HH:mm',
		],
		'medium' => [
			'date' => 'MMM D, YYYY',
			'time' => 'h:mm:ss A',
			'datetime' => 'MMM D, YYYY h:mm:ss A',
		],
		'long' => [
			'date' => 'MMMM D, YYYY',
			'time' => 'h:mm:ssA',
			'datetime' => 'MMMM D, YYYY h:mm:ssA',
		],
		'full' => [
			'date' => 'dddd, MMMM D, YYYY',
			'time' => 'h:mm:ssA z',
			'datetime' => 'dddd, MMMM D, YYYY h:mm:ssA z',
		]
	];

	/**
	 * Converts a date format pattern from [ICU format][] to [Moment.js date format][].
	 *
	 * Pattern constructs that are not supported by the Moment.js format will be removed.
	 *
	 * [Moment.js date format]: http://momentjs.com/docs/#/displaying/format/
	 * [ICU format]: http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax
	 *
	 * @param string $pattern date format pattern in ICU format.
	 * @param string $type 'date', 'time', or 'datetime'.
	 * @param string $locale the locale to use for converting ICU short patterns `short`, `medium`, `long` and `full`.
	 * If not given, `Yii::$app->language` will be used.
	 * @return string The converted date format pattern.
	 */
	public static function convertDateIcuToMoment($pattern, $type = 'date', $locale = null) {
		if (isset(self::$_icuShortFormats[$pattern])) {
			if (extension_loaded('intl')) {
				if ($locale === null) {
					$locale = Yii::$app->language;
				}
				if ($type === 'date') {
					$formatter = new IntlDateFormatter($locale, self::$_icuShortFormats[$pattern], IntlDateFormatter::NONE);
				} elseif ($type === 'time') {
					$formatter = new IntlDateFormatter($locale, IntlDateFormatter::NONE, self::$_icuShortFormats[$pattern]);
				} else {
					$formatter = new IntlDateFormatter($locale, self::$_icuShortFormats[$pattern], self::$_icuShortFormats[$pattern]);
				}
				$pattern = $formatter->getPattern();
			} else {
				return static::$momentFallbackDatePatterns[$pattern][$type];
			}
		}
		// http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax
		// escaped text
		$escaped = [];
		if (preg_match_all('/(?<!\')\'.*?[^\']\'(?!\')/', $pattern, $matches)) {
			foreach ($matches[0] as $match) {
				$escaped[$match] = $match;
			}
		}
		return strtr($pattern, array_merge($escaped, [
			'G' => '',        // era designator like (Anno Domini)
			'Y' => 'GGGG',    // 4digit year of "Week of Year"
			'y' => 'YYYY',    // 4digit year e.g. 2014
			'yyyy' => 'YYYY', // 4digit year e.g. 2014
			'yy' => 'YY',     // 2digit year number eg. 14
			'u' => 'Y',       // extended year e.g. 4601
			'U' => '',        // cyclic year name, as in Chinese lunar calendar
			'r' => '',        // related Gregorian year e.g. 1996
			'Q' => 'Q',       // number of quarter
			'QQ' => '',       // number of quarter '02'
			'QQQ' => '',      // quarter 'Q2'
			'QQQQ' => 'Qo',   // quarter '2nd quarter'
			'QQQQQ' => 'Q',   // number of quarter '2'
			'q' => 'Q',       // number of Stand Alone quarter
			'qq' => '',       // number of Stand Alone quarter '02'
			'qqq' => '',      // Stand Alone quarter 'Q2'
			'qqqq' => 'Qo',   // Stand Alone quarter '2nd quarter'
			'qqqqq' => 'Q',   // number of Stand Alone quarter '2'
			'M' => 'M',       // Numeric representation of a month, without leading zeros
			'MM' => 'MM',     // Numeric representation of a month, with leading zeros
			'MMM' => 'MMM',   // A short textual representation of a month, three letters
			'MMMM' => 'MMMM', // A full textual representation of a month, such as January or March
			'MMMMM' => '',    //
			'L' => 'M',       // Stand alone month in year
			'LL' => 'MM',     // Stand alone month in year
			'LLL' => 'MMM',   // Stand alone month in year
			'LLLL' => 'MMMM', // Stand alone month in year
			'LLLLL' => '',    // Stand alone month in year
			'w' => 'W',       // ISO-8601 week number of year
			'ww' => 'WW',     // ISO-8601 week number of year
			'W' => '',        // week of the current month
			'd' => 'D',       // day without leading zeros
			'dd' => 'DD',     // day with leading zeros
			'D' => 'DDD',     // day of the year 0 to 365
			'F' => '',        // Day of Week in Month. eg. 2nd Wednesday in July
			'g' => '',        // Modified Julian day. This is different from the conventional Julian day number in two regards.
			'E' => 'ddd',     // day of week written in short form eg. Sun
			'EE' => 'ddd',
			'EEE' => 'ddd',
			'EEEE' => 'dddd', // day of week fully written eg. Sunday
			'EEEEE' => '',
			'EEEEEE' => 'dd',
			'e' => 'E',       // ISO-8601 numeric representation of the day of the week 1=Mon to 7=Sun
			'ee' => 'E',      // php 'w' 0=Sun to 6=Sat isn't supported by ICU -> 'w' means week number of year
			'eee' => 'ddd',
			'eeee' => 'dddd',
			'eeeee' => '',
			'eeeeee' => 'dd',
			'c' => 'E',       // ISO-8601 numeric representation of the day of the week 1=Mon to 7=Sun
			'cc' => 'E',      // php 'w' 0=Sun to 6=Sat isn't supported by ICU -> 'w' means week number of year
			'ccc' => 'ddd',
			'cccc' => 'dddd',
			'ccccc' => '',
			'cccccc' => 'dd',
			'a' => 'a',       // am/pm marker
			'h' => 'h',       // 12-hour format of an hour without leading zeros 1 to 12h
			'hh' => 'hh',     // 12-hour format of an hour with leading zeros, 01 to 12 h
			'H' => 'H',       // 24-hour format of an hour without leading zeros 0 to 23h
			'HH' => 'HH',     // 24-hour format of an hour with leading zeros, 00 to 23 h
			'k' => 'k',       // hour in day (1~24)
			'kk' => 'kk',     // hour in day (1~24)
			'K' => '',        // hour in am/pm (0~11)
			'KK' => '',       // hour in am/pm (0~11)
			'm' => 'm',       // Minutes without leading zeros, not supported by php but we fallback
			'mm' => 'mm',     // Minutes with leading zeros
			's' => 's',       // Seconds, without leading zeros, not supported by php but we fallback
			'ss' => 'ss',     // Seconds, with leading zeros
			'S' => 'S',       // fractional second
			'SS' => 'SS',     // fractional second
			'SSS' => 'SSS',   // fractional second
			'SSSS' => 'SSSS', // fractional second
			'A' => '',        // milliseconds in day
			'z' => 'z',       // Timezone abbreviation
			'zz' => 'zz',     // Timezone abbreviation
			'zzz' => 'zz',    // Timezone abbreviation
			'zzzz' => '',     // Timzone full name, not supported by php but we fallback
			'Z' => 'ZZ',      // Difference to Greenwich time (GMT) in hours
			'ZZ' => 'ZZ',     // Difference to Greenwich time (GMT) in hours
			'ZZZ' => 'ZZ',    // Difference to Greenwich time (GMT) in hours
			'ZZZZ' => '',     // Time Zone: long localized GMT (=OOOO) e.g. GMT-08:00
			'ZZZZZ' => '',    //  TIme Zone: ISO8601 extended hms? (=XXXXX)
			'O' => '',        // Time Zone: short localized GMT e.g. GMT-8
			'OOOO' => '',     //  Time Zone: long localized GMT (=ZZZZ) e.g. GMT-08:00
			'v' => '',        // Time Zone: generic non-location (falls back first to VVVV and then to OOOO) using the ICU defined fallback here
			'vvvv' => '',     // Time Zone: generic non-location (falls back first to VVVV and then to OOOO) using the ICU defined fallback here
			'V' => '',        // Time Zone: short time zone ID
			'VV' => '',       // Time Zone: long time zone ID
			'VVV' => '',      // Time Zone: time zone exemplar city
			'VVVV' => '',     // Time Zone: generic location (falls back to OOOO) using the ICU defined fallback here
			'X' => '',        // Time Zone: ISO8601 basic hm?, with Z for 0, e.g. -08, +0530, Z
			'XX' => '',       // Time Zone: ISO8601 basic hm, with Z, e.g. -0800, Z
			'XXX' => '',      // Time Zone: ISO8601 extended hm, with Z, e.g. -08:00, Z
			'XXXX' => '',     // Time Zone: ISO8601 basic hms?, with Z, e.g. -0800, -075258, Z
			'XXXXX' => '',    // Time Zone: ISO8601 extended hms?, with Z, e.g. -08:00, -07:52:58, Z
			'x' => '',        // Time Zone: ISO8601 basic hm?, without Z for 0, e.g. -08, +0530
			'xx' => '',       // Time Zone: ISO8601 basic hm, without Z, e.g. -0800
			'xxx' => 'Z',     // Time Zone: ISO8601 extended hm, without Z, e.g. -08:00
			'xxxx' => '',     // Time Zone: ISO8601 basic hms?, without Z, e.g. -0800, -075258
			'xxxxx' => '',    // Time Zone: ISO8601 extended hms?, without Z, e.g. -08:00, -07:52:58
		]));
	}
}