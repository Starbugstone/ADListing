<?php
error_reporting(E_ERROR);
include '../php/config.php';
include '../php/functions.php';
//include '../php/vars.php';

//add a return data array to grab results
$returndata = array(
  'state' =>'',
  'error' =>'',
  'responseName' => ''
);

//grab all our posted variables



$samaccountname = $_POST["samaccountname"];


/*$userdata=array();
$userdata["sn"][0] = $sn = $_POST["sn"];
$userdata["givenname"][0] = $givenname = $_POST["givenname"];
//check if need space
if($sn!=""&&$givenname!=""){
  $userdata["cn"][0] = $sn.' '.$givenname;
}else{
  $userdata["cn"][0] = $sn.$givenname;
}
$userdata["employeeid"][0] = $_POST["employeeid"];
if ($customRPPSField){
  $userdata["rpps"][0] = $_POST["rpps"];
}
$userdata["description"][0] = $_POST["description"];
$userdata["telephonenumber"][0] = $_POST["telephonenumber"];
$userdata["mobile"][0] = $_POST["mobile"];
$userdata["facsimiletelephonenumber"][0] = $_POST["facsimiletelephonenumber"];
$userdata["company"][0] = $_POST["company"];
*/


if(!isset($_SESSION))
  {
    session_start();
  }
if (isset($_SESSION['domainsAMAccountName'])) {
  //check for session, no need to go further if no session and could be security risk
  $ldapconn = ldap_connect($ldapserver);
  if ($ldapconn) {

      ldap_set_option ($ldapconn, LDAP_OPT_REFERRALS, 0);
    	ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
      // binding to ldap server
      $ldapbind = ldap_bind($ldapconn, $_SESSION['domainsAMAccountName'], $_SESSION['password']);
      if ($ldapbind) {
        //ok we're in with the user's credentials. Let's update.

        //check if we go into super admin mode (damn feals bad doing this but no other way, have to go through ALL security)
        //At least we did a regular user connexion check and a session check before.
        if ($bypassUserRights){
          $ldapbind = ldap_bind($ldapconn, $ldapAdminuser, $ldapAdminpass);
        }
        $returndata['state']=true;

        //parse_str($_POST[$data], $theData);
        //error_log(print_r($theData,true));
        //error_log($_POST["sn"]);


        //1st grab the actual info so we can check later
        //$sessionSAM = $_SESSION['sAMAccountName'];
        $filter = "(&(objectCategory=person)(sAMAccountName=$samaccountname))";
        $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
        $data = ldap_get_entries($ldapconn, $result);

        $ldapParamDn = $data[0]['dn'];

        //construction on update
        //array of authorised updates. For security we check if allowed to update
        $RHUpdateKeys = array('sn','givenname','displayname','employeeid','rpps','description','telephonenumber','mobile','facsimiletelephonenumber','company');
        $userdata=array();
        //go through all that was posted


        foreach($_POST as $LDAPkey => $value){
          if(in_array($LDAPkey,$RHUpdateKeys)){
            //error_log('key : '.$LDAPkey.', value : '.$value);
            //check if value is diffrent in AD, if yes then add to update field
            if ($data[0][$LDAPkey][0] != $value){
              error_log('updateKey :'.$LDAPkey.'->'.$value.' | OldKey=>'.$data[0][$LDAPkey][0].' | LdapKey=>'.$LDAPkey);
              $userdata[$LDAPkey][0] = $value;
              //run update
              if($value != null){
                ldap_modify($ldapconn,$ldapParamDn,$userdata);
              }else{
                ldap_mod_del($ldapconn, $ldapParamDn, $userdata);
              }
            }
          }

        }

        /*
        foreach ($loggedinInfo as $row => $param){
          //loop over all our elements
          if($param['isVisableModify'] and $param['isModifiable']){
            //only grab the stuff that was shown in the modifiable form. We should only be getting info from there
            $ldapParamData = $data[0][$param['ldapName']][0];
            if ($_POST[$row] != $param['ldapErrorVal'] and $_POST[$row] != $ldapParamData) {
              //check if it's diffrent to the null value or AD value

              //construct the array for the php update
              $ldapParamDn = $data[0]['dn'];
              $userdata=array();
              $userdata[$param['ldapName']][0] = $_POST[$row];
              //update AD
              //check if not deleted entry
              if ($_POST[$row]!= null) {
                $userdata[$param['ldapName']][0] = $_POST[$row];
                ldap_modify($ldapconn,$ldapParamDn,$userdata);
                //Update the session and add to returndata to update the mod page
                $_SESSION[$row] = $returndata[$row]= $_POST[$row];
              }
              // If deleted, we must use ldap_mod_del
              else{
                //need to get the existing value. grab from session
                $userdata[$param['ldapName']][0] = $_SESSION[$row];
                ldap_mod_del($ldapconn, $ldapParamDn, $userdata);
                //Update the session and add to returndata to update the mod page
                $_SESSION[$row] = $returndata[$row]= $param['ldapErrorVal'];

              }


            }

          }
        }*/

      }
      else {
        //LDAP Bind failed
        $returndata['state']=false;
        $returndata['error']="Erreur Bind LDAP";
      }

  }
  else {
    //ldap connect failed
    $returndata['state']=false;
    $returndata['error']="Erreur Connect LDAP";
  }

}
else{
  #error no session
  $returndata['state']=false;
  $returndata['error']="aucun session";
}

ldap_close();
//return our result data in json for to be used by our ajax call
echo json_encode($returndata);
?>
