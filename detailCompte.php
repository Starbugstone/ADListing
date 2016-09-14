<?php
header("X-UA-Compatible: IE=Edge");
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
    $RPPS = getOr($data[0]["rpps"][0],"");
    if ($data[0]["useraccountcontrol"][0]==514){
      $accountState = "<p><span class='glyphicon glyphicon-warning-sign secIcon'></span>Compte Desactive</p>";
    }else{
      $accountState = "";
    }

    //2nd pannel
    $userGroupError = "Aucun groupe";
    if (isset($data[0]['memberof'])){
      $userGroup = $data[0]['memberof'];
      array_shift($userGroup);
      debugToConsole($userGroup);
      debugToConsole("------");
      asort($userGroup);
      debugToConsole($userGroup);
    }else{
      $userGroup = $userGroupError;
    }

    //3rd pannel

    if (isset($data[0]['manager'][0])){
      $manager = "<a href=\"detailCompte.php?dn=".removeAccents($data[0]['manager'][0])."\">".explodeCN($data[0]['manager'][0])."</a>";
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
      asort($directReports);
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


  } else {
    echo "LDAP bind failed...";
    //$data=null;
  }
}
 ?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Compte - <?php echo($nomPrenom); ?></title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
<link href="css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="font-awesome-4.6.3/css/font-awesome.min.css">

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->


<link href="css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navBar.php'; ?>

<div class="container-fluid text-center">
  <div class="row content equal">

    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-heading">
        <h3 class="panel-title"><?php echo($nomPrenom); ?></h3>
        </div>
        <div class="panel-body panelIcons">
          <?php echo $accountState; ?>
          <p><b>Login&nbsp;:</b> <span id='login'><?php echo($samaccountname); ?></span><button class='btn clipBtn' data-clipboard-target='#login' title="Copier Login"><span class="glyphicon glyphicon-copy"></span></button></p>
          <p><b>Nom&nbsp;:</b> <span id='Nom'><?php echo($nom); ?></span><button class='btn clipBtn' data-clipboard-target='#Nom' title="Copier Nom"><span class="glyphicon glyphicon-copy"></span></button></p>
          <p><b>Prenom&nbsp;:</b> <span id='Prenom'><?php echo($prenom); ?></span><button class='btn clipBtn' data-clipboard-target='#Prenom' title="Copier Prenom"><span class="glyphicon glyphicon-copy"></span></button></p>
          <p><b>Nom&#8209;Prenom&nbsp;:</b> <span id='NomPrenom'><?php echo($nomPrenom); ?></span><button class='btn clipBtn' data-clipboard-target='#NomPrenom' title="Copier Nom-Prenom"><span class="glyphicon glyphicon-copy"></span></button></p>
          <p><b>Mail&nbsp;:</b> <span id='e-mail'><?php echo($mail); ?></span><?php echo($mail_link); ?><button class='btn clipBtn' data-clipboard-target='#e-mail' title="Copier Mail"><span class="glyphicon glyphicon-copy"></span></button></p>
          <p><b>Matricule&nbsp;:</b> <span id='Matricule'><?php echo($Matricule); ?></span><button class='btn clipBtn' data-clipboard-target='#Matricule' title="Copier Matricule"><span class="glyphicon glyphicon-copy"></span></button></p>
          <?php
          if ($RPPS != ""){
          echo("<p><b>RPPS&nbsp;:</b> <span id='RPPS'>".$RPPS."</span><button class='btn clipBtn' data-clipboard-target='#RPPS' title='Copier RPPS'><span class='glyphicon glyphicon-copy'></span></button></p>");
          }
          ?>
          <p><b>Description&nbsp;:</b> <span id='description'><?php echo($description); ?></span><button class='btn clipBtn' data-clipboard-target='#description' title="Copier description"><span class="glyphicon glyphicon-copy"></span></button></p>

        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-heading">
        <h3 class="panel-title">Groupes&nbsp;<?php if($userGroup!=$userGroupError){echo("(".count($userGroup).")");} ?></h3>
        </div>
        <div class="panel-body">

            <?php
            if($userGroup!=$userGroupError){
              foreach( $userGroup as $grp) {
                //Get rid of all the excess CN and OU
                echo ("<p><a href=\"detailGroupe.php?dn=".removeAccents($grp)."\">".explodeCN($grp) . "</a></p>");
              }
            }else{
              echo("<p>".$userGroupError."</p>");
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
          <p><b>Gestionnaire&nbsp;:</b> <?php echo($manager); ?></p>

          <div id="collegues"><i class='fa fa-spinner fa-pulse'></i></div>

          <?php
          if($directReports!=$directReportsError){
            echo("<p><b>Gestionnaire de&nbsp;:</b></p><ul class='colaboList'>");
            foreach( $directReports as $colabo) {
              //Get rid of all the excess CN and OU
              if (blacklistedDistinguishedname($colabo,$refusedOU) == FALSE){
                $colaboName = explodeCN($colabo);
                echo ("<li><a href=\"detailCompte.php?dn=".removeAccents($colabo)."\">".$colaboName."</a></li>");
              }
            }
            echo("</ul>");
          }
          ?>

          <div id="GestionGroupes"><i class='fa fa-spinner fa-pulse'></i></div>
        </div>
      </div>
    </div>


  </div>
</div>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="js/jquery-2.2.4.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>
<script src="js/clipboard.min.js"></script>

<script src="js/script.js"></script>
<script>
//load collegues via Ajax
$("#collegues").load("ajax/getColabo-req.php?manager=<?php echo(rawurlencode($managerDn)); ?>&user=<?php echo(rawurlencode($samaccountname)); ?>");
$("#GestionGroupes").load("ajax/getGroupsManagedBy-req.php?user=<?php echo(rawurlencode($fullDn)); ?>");

var clipboard = new Clipboard('.clipBtn');
clipboard.on('success', function(e) {
    //clear the selected text. Looks ugly
    e.clearSelection();
    //console.info('Action:', e.action);
    //console.info('Text:', e.text);
});

</script>
</body>
</html>
