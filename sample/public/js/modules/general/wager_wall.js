var wagerTable;
var wagerCurrent;
var wagerPast;
var tooltipListener;

function sortList(){
  $('.selection_row').each(function(index) {
    if ($(this).text().toLowerCase().indexOf($('#search_filter_field').val().toLowerCase()) >= 0) {
        $(this).closest('tr').show();
    } else{
        $(this).closest('tr').hide();
    }    
  });
};

$(document).ready(function(){
  initializeDataTables();
  
  $('#exit_post').click(function(){
	$('#postWager').popup("hide");
	$.ajax({
	  type: "POST",
	  url: '/backoffice/wager/reset',
	  success: function(data)
	  {
	    if(data.success){
	      //Stop all spinners
	      Ladda.stopAll();
	      
	      //Change Out the Form
	      $('.wagerSelection').html(data.html);
	      
	      //Run the setup on the view again now that we have new elements.
	      setLadda();
	      setupView();
	    }
	  }
	});
  });

  $('#exit_take').click(function(){
	$('#takeWager').popup("hide");
  });
});

function resetDataTables(){
  wagerTable.fnClearTable();
  wagerCurrent.fnClearTable();
  wagerPast.fnClearTable();
}

function initializeDataTables(){
  wagerTable = $('#wager_table').dataTable({
	"aaSorting": [],
    "bFilter" : false
  });
  wagerCurrent = $('#wager_table_current').dataTable({
	"aaSorting": [],
	"bFilter" : false
  });
  wagerPast = $('#wager_table_past').dataTable({
	"aaSorting": [],
	"bFilter" : false
  });
  setTooltips();
}

function setTooltips(){
  if(tooltipListener){
	  $('.dataTables_paginate li').unbind( "click", tooltipListener);
  }
  tooltipListener = $('.dataTables_paginate li').click(function(){
	setTooltips();
  });
  $('.wagerDisplay').tooltip({
    selector: "[data-toggle=tooltip]",
    container: "body"
  });

  $('.homeTeamColumn').tooltip({
    selector: "[data-toggle=tooltip]",
    container: "body"
  });
  
  $('.wager_wall_team_hover').tooltip({
    selector: "[data-toggle=tooltip]",
    container: "body"
  });
}