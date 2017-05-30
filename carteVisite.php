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
      $filter = "(&(objectCategory=person)(distinguishedname=$dn))";
    }elseif (isset($_GET['id'])){
      $id=$_GET['id'];
      //filter on ID
      $filter = "(&(objectCategory=person)(sAMAccountName=$id))";
    }elseif (isset($_GET['dispName'])){
      $dispName = $_GET['dispName'];
      $filter = "(&(objectCategory=person)(displayname=$dispName))";
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
    if (isset($data[0]['thumbnailphoto'][0])){
      $thumbnailRaw = $data[0]['thumbnailphoto'][0];
      $thumbnailImg = '<img class="thumb" src="data:image/jpeg;base64,'. base64_encode($thumbnailRaw).'" /><br>';
    }else{
      $thumbnailRaw='';
      $thumbnailImg='<img class="thumb" src="img/user-icon.png" /><br>';
    }
    $displayName = getOr($data[0]['displayname'][0], "Aucun Nom");
    $samaccountname = getOr($data[0]['samaccountname'][0], "Aucun nom de compte");
    $nom = getOr($data[0]['sn'][0], "Aucun nom de famille");
    $prenom = getOr($data[0]['givenname'][0], "Aucun Prenom");
    $nomPrenom = getOr($data[0]['sn'][0],"")." ".getOr($data[0]['givenname'][0],"");
    //$mail = getOr($data[0]['mail'][0],"Aucun mail");
    if (isset($data[0]['mail'][0])){
      $mail = $data[0]['mail'][0];
      $mail_link = "<a href=\"mailto:".$data[0]['mail'][0]."\"><i class='fa fa-envelope-o secIcon' aria-hidden='true' title='Envoyer Mail'></i></a>";
    }else{
      $mail = "Aucun mail";
      $mail_link = "";
    }
    $Matricule = getOr($data[0]["employeeid"][0],"");
    $description = getOr($data[0]["description"][0],"Pas de description");
    if ($customRPPSField){$RPPS = getOr($data[0]["rpps"][0],"");}

    if (accountIsNotActive($data[0]["useraccountcontrol"][0])){
      $accountState = "<p><span class='glyphicon glyphicon-warning-sign secIcon'></span>Compte Desactive</p>";
    }else{
      $accountState = "";
    }

    //2nd pannel
    $userGroupError = "Aucun groupe";
    if (isset($data[0]['memberof'])){
      $userGroup = $data[0]['memberof'];
      array_shift($userGroup);
      natcasesort($userGroup);
    }else{
      $userGroup = $userGroupError;
    }

    //3rd pannel

    if (isset($data[0]['manager'][0])){
      //get display name from AD
      $managerDN = $data[0]['manager'][0];
      $filter1 = "(&(objectCategory=person)(distinguishedname=$managerDN))";
      $result1 = ldap_search($ldapconn,$ldaptree, $filter1) or die ("Error in search query: ".ldap_error($ldapconn));
      $data1 = ldap_get_entries($ldapconn, $result1);
      $managerDisplayName = getOr($data1[0]["displayname"][0],$data1[0]["cn"][0]);
      $manager = "<a href=\"detailCompte.php?dn=".removeAccents($data[0]['manager'][0])."\">".$managerDisplayName."</a>";
      $managerDn = removeAccents($data[0]['manager'][0]);
    }
    else{
      $manager = "Aucun Gestionnaire";
      $managerDn = "";
    }
    $directReportsError = "Aucun Colaborateur";
    if (isset($data[0]['directreports'])){
      $directReports = $data[0]['directreports'];
      array_shift($directReports);
      $directReports = nonBlacklistedDistunguishednameArray($directReports,$refusedOU);
      natcasesort($directReports);
    }else{
      $directReports = $directReportsError;
    }
    $title = getOr($data[0]['title'][0],"Aucun Titre");
    $department = getOr($data[0]['department'][0],"Aucun Service");
    $company = getOr($data[0]['company'][0],"Aucun Societe");
    $telephone = getOr($data[0]['telephonenumber'][0],"Aucun Telephone");
    $mobile = getOr($data[0]['mobile'][0],"Aucun mobile");
    $fax = getOr($data[0]['facsimiletelephonenumber'][0],"Aucun Fax");
    $office = getOr($data[0]['physicaldeliveryofficename'][0],"Aucun Bureau");
    $ville = getOr($data[0]['l'][0],"Aucun ville");
    $fullDn = removeAccents($data[0]['dn']); //echo $fullDn;
    $urlOrganigramme = "organigramme.php?id=".$samaccountname;

  } else {
    echo "LDAP bind failed...";
    //$data=null;
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
<title>Carte de visite - <?php echo($nomPrenom); ?></title>
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
<?php include 'navBar.php'; ?>

<div class="container-fluid text-center">
  <div class="jumbotron">
    <img src="img/LOGO_AS_CLINIQUES.jpg" class="img-responsive" alt="Logo">
  </div>
  <div class="row content equal">

    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-heading">
        <h3 class="panel-title"><?php echo($nomPrenom); ?></h3>
        </div>
        <div class="panel-body panelIcons">

          <?php echo $thumbnailImg;?>
          <p><b>Nom&nbsp;:</b> <span id='Nom'><?php echo($nom); ?></span></p>
          <p><b>Prenom&nbsp;:</b> <span id='Prenom'><?php echo($prenom); ?></span></p>
          <!--<p><b>Nom&nbsp;Affiche&nbsp;:</b> <span id='NomPrenom'><?php echo($displayName); ?></span></p>-->
          <p><b>Mail&nbsp;:</b> <span id='e-mail'><?php echo($mail); ?></span></p>

          <?php
          if ($customRPPSField AND $RPPS != ""){
          echo("<p><b>RPPS&nbsp;:</b> <span id='RPPS'>".$RPPS."</span></p>");
          }
          ?>


        </div>
      </div>
    </div>


    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-heading">
        <h3 class="panel-title">Organisation</h3>
        </div>
        <div class="panel-body">
          <p><b>Fonction&nbsp;:</b> <?php echo($title); ?></p>
          <p><b>Service&nbsp;:</b> <?php echo($department); ?></p>
          <p><b>Bureau&nbsp;:</b> <?php echo($office); ?></p>
          <p><b>Ville&nbsp;:</b> <?php echo($ville); ?></p>
          <p><b>Telephone&nbsp;:</b> <?php echo($telephone); ?></p>
          <p><b>Mobile&nbsp;:</b> <?php echo($mobile); ?></p>
          <p><b>Fax&nbsp;:</b> <?php echo($fax); ?></p>
          <p><b>Societe&nbsp;:</b> <?php echo($company); ?></p>

        </div>
      </div>
    </div>


  </div>
</div>

<script src="js/jquery-2.2.4.min.js"></script>
<script>
$( document ).ready(function() {
  //setTimeout(function () { window.print(); }, 500);
  //setTimeout(window.close, 0);
  var document_focus = false; // var we use to monitor document focused status.
  // Now our event handlers.
  $(document).focus(function() { document_focus = true; });
  $(document).ready(function() { window.print(); });
  setInterval(function() { if (document_focus === true) { window.close(); }  }, 500);
});

</script>
</body>
</html>
