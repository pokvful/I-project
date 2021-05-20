<?php
class StringHelper {
	public static function cut(string $string, int $maxLength, $useEllipsis = false) {
		$cutString = substr($string, 0, $maxLength);

		return $useEllipsis && strlen($string) > $maxLength ? $cutString . '…' : $cutString;
	}
}
