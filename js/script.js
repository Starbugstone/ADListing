//Function to construct the DataTable and add the copy button
function constructUserTable($tableId){
  $($tableId).DataTable({
    "language": {
        "url": "dataTables/dataTables.french.lang"
    },
    paging: false
  });

  //construct export to excel button after ajax call and wait for the dataTable calls init so we can use the search ID.
  //called in doc ready will fail because the ID isn't present
  var tableExport = $($tableId).DataTable();
  tableExport.on( 'init', function(){
    var $tableIdButton = $tableId + "_filter>label";
    //construct export button after the search bar
    $($tableIdButton).after("<a href='#' id='csvExportButton' class='btn btn-default exportButton' title='Exporter vers CSV'><span class='glyphicon glyphicon-save-file'></span></a>");
    //add the on click to execute export
    $("#csvExportButton").on('click', function (event) {
        exportTableToCSV.apply(this, [$($tableId), 'ExportAD.csv']);
        // IF CSV, don't do event.preventDefault() or return false
        // We actually need this to be a typical hyperlink
    });
  });
}

//login ajax request
function submitLogin(){
  var $data = $("#login-nav").serialize();
  var $response = null;
  //console.log($data);
  $.ajax({
    type : 'POST',
    url : 'ajax/login-req.php',
    data : $data,
    beforeSend : function(){
      $("#btn-login").html('<i class="fa fa-spinner fa-pulse" aria-hidden="true"></i> &nbsp; Signing In ...');
    },
    success : function ($responseJSON){
      $response = jQuery.parseJSON($responseJSON);
      if ($response.state){
        $("#btn-login").html('<i class="fa fa-question-circle-o" aria-hidden="true"></i> &nbsp; Ok ...');
        $("#loginErrorMessage").html("");
        $("#loginDropdown").addClass("hidden");
        $("#logoutDropdown").removeClass("hidden");
        $("#nonUpdatedUser").html($response.fullName);
        $("#name").html($response.fullNameLink);
        $("#manager").html($response.manager);
        $("#mail").html($response.mail);
        $("#phone").html($response.phone);
        $("#mobile").html($response.mobile);
        $("#fax").html($response.fax);
      }else{
        $("#btn-login").html('<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> &nbsp; erreur ...');
        $("#loginErrorMessage").html('<p>'+$response.error+'</p>');
      }
    }
  });
  return false;
}


//common JS to all pages
$(document).ready(function() {
  //log in
  $("#login-nav").submit(function(e){
    submitLogin();
    e.preventDefault();
  });

  //logout
  $("#logout").click(function(){
    $.post("ajax/logout-req.php");
    var $response = null;
    $("#btn-login").html('<span class="glyphicon glyphicon-log-in"></span> &nbsp;Connexion AD');
    $("#sAMAccountName").val("");
    $("#AD_Password").val("");
    $("#loginDropdown").removeClass("hidden");
    $("#logoutDropdown").addClass("hidden");
  });

});
