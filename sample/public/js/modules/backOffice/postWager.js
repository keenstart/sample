var laddaNext;
var laddaPrevoius;
var url;

$(document).ready(function(){
  setLadda();
  setupView();    
});

$('.wagerDisplay').tooltip({
  selector: "[data-toggle=tooltip]",
  container: "body"
});

$('.wagerMaker').tooltip({
  selector: "[data-toggle=tooltip]",
  container: "body"
});

function setupView(){
  $('.wagerNext').click(function(){
    stopLadda();
    laddaNext[$(this).attr('rel')].start();

    loadAjax(this, 'wagerForm');
  });

  $('.wagerPrevious').click(function(){
    laddaPrevious.start();

    loadAjax(this, 'wagerPrevious');
  });

  $('#wagerForm').submit(function(){
    return false;
  });

  $('#wagerPrevious').submit(function(){
    return false;
  });
}

function loadAjax(thisObj, formName){  
  var formData = $('#' + formName).serializeArray();
  if(thisObj){
    formData.push({ name: thisObj.name, value: thisObj.value });
  }
    
  $.ajax({
    type: "POST",
    url: url,
    data: formData,
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
}

function setLadda(){
  laddaNext = [];
  
  $('.wagerNext').each(function(){
    laddaNext[$(this).attr('rel')] = Ladda.create( document.querySelector( '.wagerNext' + $(this).attr('rel')));
  });

  if($('.wagerPrevious').length){
    laddaPrevious = Ladda.create( document.querySelector( '.wagerPrevious'));
  }
}

function stopLadda(){
  Ladda.stopAll();
}

function takeWager(wagerIdVal){
	var url = "/backoffice/wager/acceptwager";
	
	$('.wagerAcceptance').html('<div class="portlet-heading login-heading">' +
	    '<div class="portlet-title">' +
	      '<h4><strong>Take a Wager: Preview</strong></h4>' +
	    '</div>' +
	    '<div class="clearfix"></div>' +
	  '</div>' +
	  '<div class="portlet-body">' +
        '<h4>Loading:</h4>' +
        '<p><i class="fa fa-spinner fa-spin"></i> Loading wager details. Please wait one moment.</p>' +
        '<div class="clearfix"></div>' +
	  '</div>');
	  
	$('#takeWager').popup('show');
	
	$.ajax({
		type: "POST",
		url: url,
		data: {wagerId:wagerIdVal, step:'BeginAccept'},
		success: function(data)
		{
		  if(data.success){
		    //Change Out the Form
		    $('.wagerAcceptance').html(data.html);
		    setLadda();
	        setupView();
		  }
		}
	});
}