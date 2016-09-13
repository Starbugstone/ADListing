<?php
function getOr(&$var, $default) {
    if (isset($var)) {
        return $var;
    } else {
        return $default;
    }
}

function explodeCN($cn){
  $res1 = explode(",",$cn,2);
  $res2 = explode("=",$res1[0]);
  if (isset($res2[1])){
    return $res2[1];
  }
  else{
    return $res2[0];
  }
}

function sortByCn($a, $b) {
   return strcmp($a['cn'], $b['cn']);
 }

function sortBySam($a, $b) {
  return strcmp($a['sam'], $b['sam']);
}

function debugToConsole( $data ) {

    if ( is_array( $data ) )
        $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
    else
        $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";

    echo $output;
}


//check if the element's CN is blacklisted againsed a $refused list.
//$refused is configured in config file
function blacklistedDistinguishedname($distName, $refused=""){
  $blacklisted = FALSE;
  foreach ($refused as $refusedOU) {
    $pattern = '/'.preg_quote($refusedOU, '/') . '/';
    if (preg_match($pattern, $distName)){
      $blacklisted = TRUE;
    }
  }
  return $blacklisted;
}


function escapeLdapFilter($str = '') {

// The characters that need to be escape.
//
// NOTE: It’s important that the slash is the first character replaced.
// Otherwise the slash added by other replacements will then be
// replaced as well, resulted in double-escaping all characters
// replaced before the slashes were replaced.
//
$metaChars = array(
chr(0x5c), // \
chr(0x2a), // *
chr(0x28), // (
chr(0x29), // )
chr(0x00) // NUL
);

// Build the list of the escaped versions of those characters.
$quotedMetaChars = array ();
foreach ($metaChars as $key => $value) {
$quotedMetaChars[$key] = '\\' .
str_pad(dechex(ord($value)), 2, '0', STR_PAD_LEFT);
}

// Make all the necessary replacements in the input string and return
// the result.
return str_replace($metaChars, $quotedMetaChars, $str);
}


function removeAccents($str = ''){
  $normalizeChars = array(
      'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
      'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
      'Ï'=>'I', 'Ñ'=>'N', 'Ń'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
      'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
      'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
      'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ń'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
      'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f',
      'ă'=>'a', 'î'=>'i', 'â'=>'a', 'ș'=>'s', 'ț'=>'t', 'Ă'=>'A', 'Î'=>'I', 'Â'=>'A', 'Ș'=>'S', 'Ț'=>'T',
  );
  return strtr($str, $normalizeChars);
}


?>
