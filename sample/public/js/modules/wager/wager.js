$(document).ready(function(){

    //Initailize the radio buttons//
    $(":radio").each(function() {
        $(this).attr("id",$(this).attr("name")+$(this).attr("value"));
    });
    // WagerForm
    $("#typeId0").attr("checked","checked");

    
    $(":radio").click(function(){
        if($("#typeId1").prop('checked') === true) {
            $("#opponentgroup").hide();//attr("style","display:none;");
        } else {
            $("#opponentgroup").show();//attr("style","display:block;");
        }
    });
    //---report match--//
    $('#wagerList').on('click', '#reportmatch', function() {
        var value = $( this ).attr("value");
        var id = $( this ).attr("value1");
        
        $('#matchdetail').html("<h2>"+value+"</h2>");
        $('#matchid').val(id);

        $('#p-wager-id').html($("#shown-wagerid-"+id).html());
        $('#span-disputer').html($("#username").html());
        $('#span-disputer-against').html($("#wager-opponentname-"+id).html());
        
        $("#reportMatch").popup("show");
    });
    
    $("#MatchForm").submit(function() {
            event.preventDefault();  
    }); 
    
    $('#submitMatch').click(function() {
        $("#reportMatch").popup("hide");
        Reportmatch();
    });     
    //---- Accept ---//
    $('#wagerList').on('click', '#acceptmatch', function() {
        var value2 = $( this ).attr("value2");
        $("#acceptwager").attr("value1","/wager/index/acceptwager");
        $("#acceptwager").attr("value2",value2);

        $("#wager-consoleUsername").html($("#wager-consoleusername-"+value2).html());
        $("#wager-askRules").html($("#wager-askrules-"+value2).html());
        $("#wager-opponent").html($("#wager-opponentname-"+value2).html());
        $("#wager-game").html($("#wager-gametobeplayed-"+value2).html());
        $("#wager-wagerAmount").html("$"+$("#wager-riskamount-"+value2).html());
        $("#wager-MatchId").html($("#wager-matchid-"+value2).html());
        
        var whichConsole = $("#wager-whichconsole-"+value2).html();

        if (whichConsole == 1) {
            $("#wager-consoleName").html("XBOX");
        }
        if (whichConsole == 2) {
            $("#wager-consoleName").html("Playstation");
        }
        if (whichConsole == 0) {
            $("#wager-consoleName").html("PC");
        }

        if(whichConsole != 0) {
            if(whichConsole == 1) {
                $('#accept-consoleusername').val($('#xboxGamertag').html());
            } else {
                $('#accept-consoleusername').val($('#pSNUsername').html());
            }
        } else {
            $('#accept-consoleusername').val("");
        }        
    });
    
    
    $('#acceptwager').click(function(){
        var url = $('#acceptwager').attr("value1");
        var id = $('#acceptwager').attr("value2");
            
        Wagers(url,id);
        $('#acceptWager').popup("hide");
    });
    
    //---view match details after Wager has been accepted---//
    $('#wagerList').on('click',"#viewWagerDetailsBtn", function() {
        var value1 = $( this ).attr("value1");
        $("#wagerDetailsView").popup("show");

        $("#wagerDetails-consoleUsername").html($("#wager-consoleusername-"+value1).html());
        $("#wagerDetails-askRules").html($("#wager-askrules-"+value1).html());
        $("#wagerDetails-opponent").html($("#wager-opponentname-"+value1).html());
        $("#wagerDetails-game").html($("#wager-gametobeplayed-"+value1).html());
        $("#wagerDetails-wagerAmount").html("$" + $("#wager-riskamount-"+value1).html());
        $("#wagerAcceptorDetails-consoleUsername").html($("#wager-acceptor-"+value1).html()); 
        $("#wagerDetails-MatchId").html($("#wagerMatchId-"+value1).html());

        var whichConsole = $("#wager-whichconsole-"+value1).html();

        if (whichConsole == 1) {
            $("#wagerDetails-consoleName").html("XBOX");
        }
        if (whichConsole == 2) {
            $("#wagerDetails-consoleName").html("Playstation");
        }
        if (whichConsole == 0) {
            $("#wagerDetails-consoleName").html("PC");
        }
        $("#exit_wagerDetails").on('click', function() {
            $("#wagerDetailsView").popup("hide");
        })
    })


    //---- Decline ---//
    $('#wagerList').on('click', '#declinematch', function() {
        var value2 = $( this ).attr("value2");
        
        $("#declinewager").attr("value1","/wager/index/declinewager");
        $("#declinewager").attr("value2",value2);
    });
    
    $('#declinewager').click(function(){
        var url = $('#declinewager').attr("value1");
        var id = $('#declinewager').attr("value2");
            
        Wagers(url,id);
        $('#declineWager').popup("hide");
    });
    
    //----Cancel ---//
    $('#wagerList').on('click', '#cancelmatch', function() {
        var value2 = $( this ).attr("value2");
        
        $("#cancelwager").attr("value1","/wager/index/cancelwager");
        $("#cancelwager").attr("value2",value2);
    });
         
    $('#cancelwager').click(function(){
        var url = $('#cancelwager').attr("value1");
        var id = $('#cancelwager').attr("value2");
            
        Wagers(url,id);
        
        $('#cancelWager').popup("hide");
    });
    

    //---dispute match--//
    $('#wagerList').on('click', '#pendingdispute', function() {
        var id = $( this ).attr("value");
        
        $('#p-disputerid').html($("#disputeid-"+id).html());
        $('#p-wagerid').html($("#shown-wagerid-"+id).html());
        $('#span_disputer').html($("#username").html());
        $('#span_disputer-against').html($("#wager-opponentname-"+id).html());

//var t = $("#linkurl-"+id).html();
//var k= $("#disputedetails-"+id).html();
        $('#disputer-linkurl').val($("#linkurl-"+id).html());
        $('#dispute-details').val($("#disputedetails-"+id).html());
        $('#dispute-wagerid').html(id); 
        
//        var y = $("#dispute-askresult-"+id).html()
//        var x = $("#dispute-useraskid-"+id).html()
// 
//        var w = $("#dispute-acceptresult-"+id).html()
//        var z = $("#dispute-useracceptid-"+id).html()
        
        var user = $("#userId").html();
        if(($("#dispute-useraskid-"+id).html() == user && $("#dispute-askresult-"+id).html() != 3) ||
            ($("#dispute-useracceptid-"+id).html() == user && $("#dispute-acceptresult-"+id).html() != 3)) {
            
            $("#disputer-linkurl").prop("disabled",true);
            $("#dispute-details").prop("disabled",true);
        } else {
            $("#disputer-linkurl").prop("disabled",false);
            $("#dispute-details").prop("disabled",false);           
        }
        
        //$("#reportMatch").popup("show");
    });

    
    $('#submitDispute').click(function() {
        $("#disputeWager").popup("hide");
        EditDispute();
    });     
    
    //--Make a wager --//
    $("#WagersForm").submit(function() {
          return false;
    });

    $("#submitWager").click(function(){

        if ($("#typeId0").prop('checked') === true) {
            $("#WagersForm").validate({
                rules: {
                    userAccept: {
                        required: true
                    },
                    riskAmount: {
                        required: true,
                        number: true
                    },
                    consoleId: {
                        required: true
                    },
                    gameId: {
                        required: true
                    },
                    consoleUsername: {
                        required: true
                    }
                },
                messages: {
                    userAccept: {
                        required: "Enter username"
                    },
                    riskAmount: {
                        required: "Enter Wager Amount"
                    },
                    consoleUsername: {
                        required: "Enter your console username"
                    }
                },
                submitHandler: function (form) { 
                        $("#postWager").popup("hide");
                        $("#madeWager").popup("show"); 
                        return false; 
                }
            });
        }
        else if ($("#typeId1").prop('checked') === true) {
                $("#WagersForm").validate({
                    rules: {
                        riskAmount: {
                            required: true,
                            number: true
                        },
                        consoleId: {
                            required: true
                        },
                        gameId: {
                            required: true
                        },
                        consoleUsername: {
                            required: true
                        }
                    },
                    messages: {
                        riskAmount: {
                            required: "Enter Wager Amount"
                        },
                        consoleUsername: {
                            required: "Enter your console username"
                        }
                    },
                    submitHandler: function (form) { 
                        $("#postWager").popup("hide");
                        $("#madeWager").popup("show");                               
                        return false; 
                    }
                });
        } 
    });
    
    $('#madewagerSubmit').click(function() {
        $("#madeWager").popup("hide");
        if($("#typeId1").prop('checked') === true) {
            //$("#opponent").attr("value","$validate$");
            $("#opponent").val("$validate$");
        }
        Makewager();
        $("#opponent").val("");
    }); 
    
    $('#madewagerCancel').click(function(){
        $("#madeWager").popup("hide");
    });
    
    $('#exit_wager').click(function(){
      $('#postWager').popup("hide");
      defaultDirectwager();
    });
    $('#close_wager').click(function(){
      $('#postWager').popup("hide");
      defaultDirectwager();
    }); 
    
    $('#exit_rules').click(function(){
      $('#madeWager').popup("hide");
      defaultDirectwager();
    });   
    
    // Filter direct wager -- //
     $('#mywager-All').click(function(){
        $('#mywager-All').addClass('activePill');
        $('#mywager-Received').removeClass('activePill');
        $('#mywager-Sent').removeClass('activePill');
        $( "#wagerList > li" ).each(function(i) {
            $( this ).show();
        });
    }); 
    
    $('#mywager-Received').click(function(){
        $('#mywager-All').removeClass('activePill');
        $('#mywager-Received').addClass('activePill');
        $('#mywager-Sent').removeClass('activePill');

        $( "#wagerList > li" ).each(function(i) {
            var ur = $( this ).attr("ur");
            var user = $("#userId").html();
            $( this ).show();
            if(ur === user) {
                $( this ).hide();
            }
        });
    });    
    
    $('#mywager-Sent').click(function(){
         $('#mywager-All').removeClass('activePill');
        $('#mywager-Received').removeClass('activePill');
        $('#mywager-Sent').addClass('activePill');

        $( "#wagerList > li" ).each(function(i) {
            var ur = $( this ).attr("ur");
            var user = $("#userId").html();
            $( this ).show();
            if(ur !== user) {
                $( this ).hide();
            }
        });
    });

    $("#wagerHistory-All").click(function() {
        $("#wagerHistory-Wins").removeClass('activePill')
        $("#wagerHistory-Losses").removeClass('activePill')
        $("#wagerHistory-All").addClass('activePill')
        $("#wagerHistoryList > li").each(function(i) {
            $(this).show();
        })
    })
    $("#wagerHistory-Wins").click(function() {
        $("#wagerHistory-Wins").addClass('activePill')
        $("#wagerHistory-Losses").removeClass('activePill')
        $("#wagerHistory-All").removeClass('activePill')
        $("#wagerHistoryList > li").each(function(i) {
            var id = $(this).attr("wagerid");
            var status = parseInt($("#listEvtMatch"+id).find(".historyStatus").html())
            if (status == 1) {
                 $(this).show();
            }
            else {
                $(this).hide();
            }
        })
    })
    $("#wagerHistory-Losses").click(function() {
        $("#wagerHistory-Wins").removeClass('activePill')
        $("#wagerHistory-Losses").addClass('activePill')
        $("#wagerHistory-All").removeClass('activePill')
        $("#wagerHistoryList > li").each(function(i) {
            var id = $(this).attr("wagerid");
            var status = parseInt($("#listEvtMatch"+id).find(".historyStatus").html())
            if (status == 2) {
                 $(this).show();
            }
            else {
                $(this).hide();
            }
        })
    })


});

function defaultDirectwager() {
   //$("#typeId0").attr("checked",true); 
   $("#typeId0").prop("checked", true);
   $("#opponentgroup").show();
}

function Wagers(url,id){
    
    $('#messagewager').html("Please wait while we send your wager...");
    $('#messageWager').popup("show");
    var acceptconsoleusername = $("#accept-consoleusername").val();
    $.ajax({
            type: "POST",
            url: url,
            data: {wagerId:id, pageOn:$("#pageOn").html(), consoleUsernameAccept:acceptconsoleusername},
            success: function(data)
            {
                  if(data.success){
                    console.log(data.consoleUsernameAccept)
                    if(data.type === 4) {
                        $("#mycredits").html('<h3>$' + data.credits + '</h3>');
                    }
                    if(data.type === 2) {

                        var root = $(location).attr('hostname');
                        var protocol = $(location).attr('protocol');
                        var url = protocol + "//"+root + "/wager/index/mywager"; 
                        $(location).attr('href',url);
                    }
                    $('#messageWager').popup("hide");
                  } else {
                      $('#messagewager').html(data.error);
                      $('#messageWager').popup("show");
                  } 
            }
    });

}


function Makewager() {
    var url = "/wager/index/createwager/";
    var wagerType;
    
    $('#messagewager').html("Please wait while we send your wager...");
    $('#messageWager').popup("show");
    var d = $('#WagersForm').serialize();
    $.ajax({
            type: "POST",
            url: url,
            data: $('#WagersForm').serialize(),
            success: function(data)
            {
                if(data.success){
                    
                    if(data.typeid == 1) {
                        wagerType = "<div>User: " + data.username + "<small> have made an Open Wager</small></div> ";
                    } else {
                        
                        wagerType = "<div><small>You have made a Wager</small></div>" +
                                    "<div><small> Opponent:  " +  data.username + "</small></div>";
                    }
                    
                    var html = "<li class='list-group-item' id='listEvtMatch" + data.id +"' wagerid='" + data.id +"' ur='" + data.userask +"' >" +   
                    "<div> <div><h3>" + data.consoleName + " - " + data.gameName +
                    " - $" + data.riskAmount + "</h3></div>" +
                    "<div><small>Match Id: " + data.wagerId + "</small></div>" +
                    '<div id = "wagerid" style="display:none;">' + data.id + '</div>' +
                    '<div id = "shown-wagerid-' + data.id + '" style="display:none;">' + data.wagerId + '</div>' +
                    '<div id = "wager-consoleusername-' + data.id + '" style="display:none;">' + data.consoleUsername + '</div>'+
                    '<div id = "wager-askrules-' + data.id + '" style="display:none;">' + data.askRules + '</div>'+   
                    '<div id = "wager-riskamount-' + data.id + '" style="display:none;">' + data.riskAmount + '</div>'+ 
                    '<div id = "wager-status-' + data.id + '" style="display:none;">' + data.status + '</div>'+ 
                    '<div id = "wager-gametobeplayed-' + data.id + '" style="display:none;">' + data.gameName + '</div>'+ 
                    '<div id = "wager-opponentname-' + data.id + '" style="display:none;">' + data.username + '</div>'+
                    '<div id = "wager-type-' + data.id + '" style="display:none;">' + data.typeid + '</div>' +                  
                    '<div id = "wager-whichconsole-' + data.id + '" style="display:none;">' + data.whichConsole + '</div>'+ 
                    '<div id = "wager-created-' + data.id + '" style="display:none;">' + data.created + '</div>'+  
                    '<div id = "wager-gamestarttime-' + data.id + '" style="display:none;">' + data.gameStartTime + '</div>'+ 
                    '<div id = "wager-gameresulttime-' + data.id + '" style="display:none;">' + data.gameResultTime + '</div>'+                     
                    
                       wagerType  +
                    "</div> <div class='individualWager' id='buttEvtMatchResult" + data.id + "'> " +
                    "<div id = 'wagerid' style='display:none;'>" + data.id + "</div>" +
                    "<div role='group' aria-label='...'>" + 
                    "<a id = 'pendingmatch' type='button' value2 = '"+ data.id +
                    "' class='btn btn-default' disabled='disabled'>Pending</a>" +
                    " <a id = 'cancelmatch' type='button' value1 =  'cancelwager' value2 = " + data.id + 
                        " class='btn btn-danger cancelWager_open' href='#cancelWager'>Cancel</a>" +                               
                    "</div></div></li>";
            
           

                    if($("#pageOn").html() === data.pageOn) {
                        $("#wagerList").prepend(html);
                    }
                    
                     $("#mycredits").html('<h3>$' + data.credits + '</h3>');
                    $('#messageWager').popup("hide");
                  } else {
                      $('#messagewager').html(data.messages);
                      $('#messageWager').popup("show");
                  } 
            }
    });
    document.getElementById("WagersForm").reset(); 
    getGameWagerForm("#wager-console-id");
}

function Reportmatch() {
    var url = "/wager/index/match/";
    
    $('#messagewager').html("Please wait while we report match result...");
    $('#messageWager').popup("show");
    var d = $('#WagersForm').serialize();
    $.ajax({
            type: "POST",
            url: url,
            data: $('#MatchForm').serialize(),
            success: function(data)
            {
                  if(data.success){
//                      
//                    $("#mycredits").html('<h3>$' + data.credits + '</h3>');
//                    $('#messageWager').popup("hide");
//                    $('#listEvtMatch'+ data.id).html("");
//                    $('#listEvtMatch'+ data.id).hide();

                    //Replace with dynamic html
                    var root = $(location).attr('hostname');
                    var protocol = $(location).attr('protocol');
                    var url = protocol + "//"+root + "/wager/index/mywager"; 
                    $(location).attr('href',url);
                    
                  } else {
                      $('#messagewager').html(data.messages);
                      $('#messageWager').popup("show");
                  } 
             }
    });
    document.getElementById("MatchForm").reset(); 
}

function EditDispute() {
    
    $('#messagewager').html("Please wait while we send your wager...");
    $('#messageWager').popup("show");
    
    var url = "/wager/index/dispute/";
    var wagerid = $('#dispute-wagerid').html();  
    $.ajax({
            type: "POST",
            url: url,
            data: {id:$("#dispute-id-"+wagerid).html(),linkurl:$("#disputer-linkurl").val(),
                details:$("#dispute-details").val(),wagerid:wagerid},
            success: function(data)
            {
                  if(data.success) {
                        $("#linkurl-"+data.id).html(data.linkurl);
                        $("#disputedetails-"+data.wagerid).html(data.disputedetails);                        
                  } else {
//                      $('#messagewager').html(data.error);
//                      $('#messageWager').popup("show");

                  } 
                  $('#messageWager').popup("hide");
                  //location.reload();
            }
    });    
    
}

$("[name='matchresult']").change(function() {
    if ($("[name='matchresult']").val() == 3) {
        $(".disputeWagerInformation").css("display", "block")
         $(".disputeWagerInformation").outerWidth();
        $(".disputeWagerInformation").addClass("showDispute")
    }
    else {
        $(".disputeWagerInformation").css("display", "none")
        $(".disputeWagerInformation").removeClass("showDispute")
    }
});


