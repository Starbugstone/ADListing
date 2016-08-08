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
