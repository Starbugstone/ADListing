<?php
error_reporting(E_ERROR);
include '../php/config.php';
include '../php/functions.php';
include '../php/vars.php';

//add a return data array to grab results
$returndata = array(
  'state' =>'',
  'error' =>'',
  'responseName' => ''
);
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

        //1st grab the actual info so we can check later
        $sessionSAM = $_SESSION['sAMAccountName'];
        $filter = "(&(objectCategory=person)(sAMAccountName=$sessionSAM))";
        $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
        $data = ldap_get_entries($ldapconn, $result);


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
        }

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
