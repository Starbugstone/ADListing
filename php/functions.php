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

function debugToConsole( $data ) {

    if ( is_array( $data ) )
        $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
    else
        $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";

    echo $output;
}

function blacklistedDistinguishedname($distName){
  if (preg_match('/OU=Admins/',$distName) == TRUE){
    return TRUE;
  }
  elseif (preg_match('/OU=Compte de Service/',$distName) == TRUE){
    return TRUE;
  }
  elseif (preg_match('/CN=Users/',$distName) == TRUE){
    return TRUE;
  }
  elseif (preg_match('/OU=LDAP/',$distName) == TRUE){
    return TRUE;
  }
  elseif (preg_match('/CN=Builtin/',$distName) == TRUE){
    return TRUE;
  }
  elseif (preg_match('/CN=Microsoft Exchange System Objects/',$distName) == TRUE){
    return TRUE;
  }
  elseif (preg_match('/OU=Microsoft Exchange Security Groups/',$distName) == TRUE){
    return TRUE;
  }
  else{
    return FALSE;
  }
}
?>
