<?php
header("X-UA-Compatible: IE=Edge");
if(!isset($_SESSION))
{
  session_start();
}
include 'php/config.php';
include 'php/functions.php';

// connect
$ldapconn = ldap_connect($ldapserver) or die("Could not connect to LDAP server.");

if($ldapconn) {
  // Adding options
  ldap_set_option ($ldapconn, LDAP_OPT_REFERRALS, 0);
  ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
  // binding to ldap server
  $ldapbind = ldap_bind($ldapconn, $ldapuser, $ldappass) or die ("Error trying to bind: ".ldap_error($ldapconn));
  // verify binding
  if ($ldapbind) {

    //recupere variable passé dans URL
    //on fait un choix de filtre en fonction de ceux qu'on passe comme paramettre. Le resultat est toujours un seul utilisateurs car ces données doivent etre uniques dans AD
    if (isset($_GET['dn'])){
      $dn=$_GET['dn'];
      $dn=escapeLdapFilter($dn);
      $filter = "(&(objectCategory=group)(distinguishedname=$dn))";
    }elseif (isset($_GET['id'])){
      $id=$_GET['id'];
      //filter on ID
      $filter = "(&(objectCategory=group)(sAMAccountName=$id))";
    }elseif (isset($_GET['dispName'])){
      $dispName = $_GET['dispName'];
      $filter = "(&(objectCategory=group)(displayname=$dispName))";
    }
    else{
      echo("<h1>erreur de filtre</h1>");
    }

    //Getting results
    $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
    $data = ldap_get_entries($ldapconn, $result);

    if (!isset($data[0])){
      //bug in IE with utf-8 encoding
      $filter = utf8_encode($filter);
      $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
      $data = ldap_get_entries($ldapconn, $result);
      //echo $filter;
    }

    //grab all our required info
    //1st pannel
    $samaccountname = $data[0]['samaccountname'][0];
    $cn = getOr($data[0]['cn'][0],$samaccountname);
    $fullDn = $data[0]['dn'];
    //$mail = getOr($data[0]['mail'][0],"Aucun mail");
    if (isset($data[0]['mail'][0])){
      $mail = $data[0]['mail'][0]."<a href=\"mailto:".$data[0]['mail'][0]."\"><i class='fa fa-envelope-o secIcon' aria-hidden='true' title='Envoyer Mail'></i></a>";
    }else{
      $mail = "Aucun mail";
    }
    if(isset($data[0]['managedby'][0])){
      $managedby = "<a href=\"detailCompte.php?dn=".$data[0]['managedby'][0]."\">".explodeCN($data[0]['managedby'][0])."</a>";
    }
    else{
      $managedby ="Pas de Gestionnaire";
    }
    $description = getOr($data[0]["description"][0], "Pas de description");


    //2nd pannel
    if (isset($data[0]['member'])){
      $members = $data[0]['member'];
      array_shift($members); //get rid of dead line
      $memberCount = "( ".$data[0]['member']['count']." )";
      //asort($members); //alphanum sorting
      natcasesort($members);
      $memberNoError = TRUE;

    }
    else{
      $members = "Aucun Membre";
      $memberCount = "";
      $memberNoError = FALSE;
    }




  } else {
    echo "LDAP bind failed...";
  }
}
 ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
<link href="css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="font-awesome-4.6.3/css/font-awesome.min.css">
<title>Compte - <?php echo($cn); ?></title>
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

<?php include 'favicon.php'; ?>
<link href="css/ripple.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
<link href="css/print.css" rel="stylesheet">
</head>
<body>

<?php
  echo '<h1>Dump all data</h1><pre>';
  print_r($data);
  echo '</pre>';
?>
</body>
</html>
