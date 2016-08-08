<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="dataTables/datatables.min.css">
<title>Comptes AD</title>
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
  $("#userTable").load("ajax/comptes-req.php", function() {
    //initialise dataTables on the data
    $('#tableComptes').DataTable({
      "language": {
          "url": "dataTables/dataTables.french.lang"
      },
      paging: false
    });

    //construct export to excel button after ajax call and wait for the dataTable calls init so we can use the search ID.
    //called in doc ready will fail because the ID isn't present
    var tableExport = $('#tableComptes').DataTable();
    tableExport.on( 'init', function(){
      //construct export button after the search bar
      $("#tableComptes_filter>label").after("<a href='#' id='csvExportButton' class='btn btn-default exportButton' title='Exporter vers CSV'><span class='glyphicon glyphicon-save-file'></span></a>");
      //add the on click to execute export
      $("#csvExportButton").on('click', function (event) {
          exportTableToCSV.apply(this, [$('#tableComptes'), 'Users.csv']);
          // IF CSV, don't do event.preventDefault() or return false
          // We actually need this to be a typical hyperlink
      });
    });



  });

  //add handler



});

</script>
</body>
</html>
