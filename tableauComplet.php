<? php
header("X-UA-Compatible: IE=Edge");
if(!isset($_SESSION))
{
  session_start();
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
<link href="dataTables/datatables.min.css">
<link rel="stylesheet" href="font-awesome-4.6.3/css/font-awesome.min.css">
<title>Comptes AD</title>
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

<?php include 'favicon.php'; ?>
<link href="css/ripple.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
</head>
<body>
<?php include 'navBar.php'; ?>


<div class="container-fluid text-center">
  <div class="row content">
    <div class="col-xs-12">

      <div id="userTable">
        <?php include 'ajax/loading.php'; ?>
      </div>

    </div>
  </div>
</div>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="js/jquery-2.2.4.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="js/bootstrap.min.js"></script>
<script src="js/table2csv.js"></script>

<script src="dataTables/datatables.min.js"></script>

<script src="js/script.js"></script>
<script>
$(document).ready(function() {
  //grabbing our ajax request
  $("#userTable").load("ajax/comptesComplet-req.php", function() {
    constructUserTable('#tableComptes');

  });

  //taking care of navBar
  $('#nav4').addClass("active");

});

</script>
</body>
</html>
