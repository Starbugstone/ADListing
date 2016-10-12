<?php
header("X-UA-Compatible: IE=Edge");
if(!isset($_SESSION))
{
  session_start();
}
include 'php/config.php';
include 'php/functions.php';
$relationship = "000";
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

    $displayName = getOr($data[0]['displayname'][0], "Aucun Nom");
    $title = getOr($data[0]['title'][0],"Aucun Titre");
    $fullDn = removeAccents($data[0]['dn']);

    if (isset($data[0]['manager'][0]) AND blacklistedDistinguishedname($data[0]['manager'][0],$refusedOU) == FALSE){
      $manager = explodeCN($data[0]['manager'][0]);
      $managerDn = removeAccents($data[0]['manager'][0]);
      //has parent
      $relationship[0]="1";
      //check if has siblings
      $dn=$data[0]['manager'][0];
      $dn=escapeLdapFilter($dn);
      $filter = "(&(objectCategory=*)(distinguishedname=$dn))";
      $result = ldap_search($ldapconn,$ldaptree, $filter) or die ("Error in search query: ".ldap_error($ldapconn));
      $dataParent = ldap_get_entries($ldapconn, $result);
      if (isset($dataParent[0]['directreports'])){
        $children = $dataParent[0]['directreports'];
        $children = nonBlacklistedDistunguishednameArray($children,$refusedOU); //eliminating restricted OU's
        array_shift($children);


        //taking care of the siblings
        if(count($children)>1){
          $relationship[1]="1";
        }
      }
    }
    else{
      $manager="";
      $managerDn="";
    }

    if (isset($data[0]['directreports'])){
      $directReports = $data[0]['directreports'];
      $directReports = nonBlacklistedDistunguishednameArray($directReports,$refusedOU); //eliminating restricted OU's
      array_shift($directReports);
      asort($directReports);
      //has children
      if(count($directReports)>0){
        $relationship[2]="1";
      }
    }else{
      unset($directReports);
    }


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
<title>Compte - <?php echo($nomPrenom); ?></title>
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

<?php include 'favicon.php'; ?>
<link href="css/style.css" rel="stylesheet">
<link href="OrgChart/css/jquery.orgchart.css" rel="stylesheet">
<link href="css/print.css" rel="stylesheet">
</head>
<body>
<?php include 'navBar.php'; ?>

<div class="container-fluid text-center">
  <div class="row content equal">
    <div class="col-md-12">
      <div id="chart-container"></div>
    </div>
  </div>
</div>



<!-- jQuery (necessary for Bootstrap's JavaScript plugins) upgraded to 3.1.1 for orgchart compatability. cross fingers that bootstrap behaves-->
<script src="js/jquery-3.1.1.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>
<script src="OrgChart/js/jquery.orgchart.js"></script>


<script src="js/script.js"></script>
<script>
  // Create the orgchart
  var datasource = {
    'relationship': '<?php echo($relationship); ?>',
    'name': '<?php echo($displayName); ?>',
    'title': '<?php echo($title); ?>',
    'DN': '<?php echo($fullDn); ?>',
    'managerDn': '<?php echo($managerDn); ?>'
  };

  var ajaxURLs = {
    'children': function(nodeData){
      return 'OrgChart/ajax/children.php?dn=' + nodeData.DN;
     },
    'parent': function(nodeData){
      return 'OrgChart/ajax/parent.php?dn=' + nodeData.managerDn;
     },
    'siblings': function(nodeData) {
      return 'OrgChart/ajax/siblings.php?dn=' + nodeData.managerDn+'&selfDn='+nodeData.DN;
    },
   'families': function(nodeData) {
     return 'OrgChart/ajax/families.php?dn=' + nodeData.managerDn+'&selfDn='+nodeData.DN;
   }
  };

  $('#chart-container').orgchart({
    'data' : datasource,
    'ajaxURL': ajaxURLs,
    'nodeContent': 'title',
    'pan': true,
    'zoom': true,
    'createNode': function($node, data) {
      //add an "i" icon to return to main info page
      var secondMenuIcon = $('<i>', {
        'class': 'fa fa-info-circle second-menu-icon',
        click: function() {
          var rep = window.confirm("Voir le detail de "+data.name);
          if(rep == true){
            window.location = 'detailCompte.php?dn='+data.DN;
          }
        }
      });
      $node.append(secondMenuIcon);
    }
  });

//console.log($.fn.jquery);

</script>
</body>
</html>
