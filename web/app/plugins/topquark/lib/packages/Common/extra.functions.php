<?php
if (!function_exists('json_encode'))
{
  function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}

if (!function_exists('str_word_count_utf8')){
    define("WORD_COUNT_MASK", "/\p{L}[\p{L}\p{Mn}\p{Pd}'\x{2019}]*/u");

    function str_word_count_utf8($string, $format = 0)
    {
        switch ($format) {
        case 1:
            preg_match_all(WORD_COUNT_MASK, $string, $matches);
            return $matches[0];
        case 2:
            preg_match_all(WORD_COUNT_MASK, $string, $matches, PREG_OFFSET_CAPTURE);
            $result = array();
            foreach ($matches[0] as $match) {
                $result[$match[1]] = $match[0];
            }
            return $result;
        }
        return preg_match_all(WORD_COUNT_MASK, $string, $matches);
    }
	
}

if (!function_exists('strcasecmp_utf8')){
	function strcasecmp_utf8($a,$b){
		return strcasecmp(
		    iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $a),
		    iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $b));
	}
}
?>