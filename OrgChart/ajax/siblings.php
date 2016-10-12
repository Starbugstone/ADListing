<?php
/* set out document type to text/javascript instead of text/html */
header("Content-type: text/javascript");

//initialise our array to be returned
$arr = array();
$arr['siblings'] = array();
$relationshipSiblings = "110"; //we are getting siblings so must have siblings and manager

include '../../php/config.php';
include '../../php/functions.php';

$ldapconn = ldap_connect($ldapserver);

if($ldapconn) {
  // Adding options
  ldap_set_option ($ldapconn, LDAP_OPT_REFERRALS, 0);
  ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
  // binding to ldap server
  $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass);
  // verify binding and adding link for redirection
  if ($ldapbind) {
    if (isset($_GET['dn'])){
      $dn=$_GET['dn'];
      $dn=escapeLdapFilter($dn);
      $filter = "(&(objectCategory=*)(distinguishedname=$dn))";
    }elseif (isset($_GET['id'])){
      $id=$_GET['id'];
      $filter = "(&(objectCategory=*)(sAMAccountName=$id))";
    }elseif (isset($_GET['dispName'])){
      $dispName = $_GET['dispName'];
      $filter = "(&(objectCategory=*)(displayname=$dispName))";
    }
    else{
      $arr['isError']="1";
      $arr['errorMessage']="Erreur de filtre";
      //echo("<h1>erreur de filtre</h1>");
    }
    //get the selfDN to not return self as siblings
    if(isset($_GET['selfDn'])){
      $selfDn = $_GET['selfDn'];
    }

    $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
    $data = ldap_get_entries($ldapconn, $result);

    if (!isset($data[0])){
      //bug in IE with utf-8 encoding
      $filter = utf8_encode($filter);
      $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
      $data = ldap_get_entries($ldapconn, $result);
      //echo $filter;
    }

      //now we get sibling data (parent children)

      //get rid of the first line
      if (isset($data[0]['directreports'])){
        $directReports = $data[0]['directreports'];
        $directReports = nonBlacklistedDistunguishednameArray($directReports,$refusedOU); //eliminating restricted OU's
        array_shift($directReports);
        asort($directReports);

        if(count($directReports)>0){

          foreach ($directReports as $dn) {
            $relationship = $relationshipSiblings; //resetting to avoid polution

            $dn=escapeLdapFilter($dn);
            $filter = "(&(objectCategory=*)(distinguishedname=$dn))";
            $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
            $data = ldap_get_entries($ldapconn, $result);

            if (!isset($data[0])){
              //bug in IE with utf-8 encoding
              $filter = utf8_encode($filter);
              $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
              $data = ldap_get_entries($ldapconn, $result);
              //echo $filter;
            }
            //check if we have results AND we are not returning the same user
            //the results should be good and the isset is probably not necessary. Just habit !!
            if(isset($data[0]) AND removeAccents($data[0]['dn']) != $selfDn){
              //checking for children
              if (isset($data[0]['directreports'])){
                $children = $data[0]['directreports'];
                array_shift($children);
                //asort($directReports);

                //taking care of the children
                if(count($children)>0){
                  $relationship[2]="1";
                }
              }

              $rowArray['name']=getOr($data[0]['displayname'][0], "Aucun Nom");
              $rowArray['title']=getOr($data[0]['title'][0],"Aucun Titre");
              $rowArray['relationship']=$relationship;
              $rowArray['DN']=removeAccents($data[0]['dn']);
              $rowArray['managerDn']=removeAccents($data[0]['manager'][0]);
              array_push($arr['siblings'],$rowArray);

              //$arr['children']['id':$count,'name':$test1,'title':$test2,'relationship':'111'];
            }

          }
        }
      }





    //return our json
    echo json_encode($arr);

  } else{
    $arr['isError']="1";
    $arr['errorMessage']="Erreur Bind LDAP";
  }
} else {
  $arr['isError']="1";
  $arr['errorMessage']="Erreur connexion LDAP";
}


?>
