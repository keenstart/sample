  $(document).ready(function() {

    var pusher = new Pusher('ceb26085fe5ecfd5442e'); 
    
    pusher.connection.bind('connected', function() {
        var socketId = pusher.connection.socket_id;
        $('#socketId').attr("value",socketId);
    });

    var channel = pusher.subscribe('buttonevents');

    Notification.requestPermission(); 
    
    channel.bind('acceptwager', function(data) {
      var user = $("#userId").html();
        $('#buttEvtMatchResult'+data.id).html(
               '<a id="viewWagerDetailsBtn" '+
              'type="button" value = "' + data.consoleName + ' - ' + data.gameName +
              ' - $' +  data.riskAmount + '" value1 = "' + data.id +'" '+ 
              'class="btn btn-success">'+
              'View Details'+
              '</a>'   +            
               
                '<a id="reportmatch" '+
              'type="button" value = "' + data.consoleName + ' - ' + data.gameName +
              ' - $' +  data.riskAmount + '" value1 = "' + data.id +'" '+ 
              'class="btn btn-success  reportMatch_open" href="#reportMatch">'+
              'Report Wager Result'+
              '</a>');
      
        $('#wager-gamestarttime-'+data.id).html(data.gameStartTime);
        $('#wager-gameresulttime-'+data.id).html(data.gameResultTime);        
        $('#wager-status-'+data.id).html(data.status);

        if(data.user == user) {
            $("#mycredits").html('<h3>$' + data.credits + '</h3>');
        }
        if($("#pageOn").html() === "openwager") {
                var root = $(location).attr('hostname');
                var protocol = $(location).attr('protocol');
                var url = protocol + "//"+root + "/wager/index/mywager"; 
                $(location).attr('href',url);            
        }        
        
        if (user == data.wagerOriginator) {
            var notificationInformation = {
                msg: data.wagerAcceptor + " has accepted your wager"
            }
            var notice = new Notification("Wager Accepted", {
                body: notificationInformation.msg 
            })
            notice.onclick = function() {
                var root = $(location).attr('hostname');
                var protocol = $(location).attr('protocol');
                var url = protocol + "//"+root + "/wager/index/mywager"; 
                $(location).attr('href',url);
            }
            return notice 
        }
    });
    
    channel.bind('createbutton', function(data) {
        var user = $("#userId").html();
        // Notification 
        if (data.userAccept == user && (data.typeid == 0)) {
            var notificationInformation = {
            msg: "You have received a new wager",
            user: "From: " + data.username
            }
            var notice = new Notification("New Wager", {
                body: notificationInformation.msg + "\n" + notificationInformation.user 
            })
            notice.onclick = function() {
                var root = $(location).attr('hostname');
                var protocol = $(location).attr('protocol');
                var url = protocol + "//"+root + "/wager/index/mywager"; 
                $(location).attr('href',url);
            }
            if ($("#notSeenWagers").html() == "") {
                var addOneToWagerCount = 1 
                $("#notSeenWagers").removeClass("noshow")
                $("#notSeenWagers").html(addOneToWagerCount)
            }
            else {
                var addOneToWagerCount = parseInt($("#notSeenWagers").html()) + 1
                $("#notSeenWagers").html(addOneToWagerCount)
            }
            
        }

        if($("#pageOn").html() === data.pageOn) {
            if(data.userAccept == user || (data.typeid == 1 && data.userask != user)) {
                var wagerType, decline = "";
                if(data.typeid != 1){
                    decline = '<a id = "declinematch" type="button" value1 = "declinewager" value2 = "' + data.id + '"' + 
                                ' class="btn btn-danger declineWager_open" href="#declineWager" data-popup-ordinal="0">Decline</a>';
                }
                
                if(data.typeid == 1) {
                    wagerType = "<div>User: " + data.username + "<small> have made an Open Wager</small></div> ";
                } else {

                    wagerType = "<div><small>You have receive a Wager</small></div>" +
                                "<div><small> Opponent:  " +  data.username + "</small></div>";
                }

                $('#wagerList').prepend('<li class="list-group-item"  id="listEvtMatch' + data.id + '" wagerid="' + data.id +'" ur="'+ data.userask +'"> ' + 
                "<div> <div><h3>" + data.consoleName + " - " + data.gameName +
                ' - $' + data.riskAmount + '</h3></div>' +
                "<div><small>Match Id: " + data.wagerId + "</small></div>" +
                '<div id = "wagerid" style="display:none;">' + data.id + '</div>' +
                '<div id = "shown-wagerid-' + data.id + '" style="display:none;">' + data.wagerId + '</div>' +
                '<div id = "wager-consoleusername-' + data.id + '" style="display:none;">' + data.consoleUsername + '</div>'+
                '<div id = "wager-askrules-' + data.id + '" style="display:none;">' + data.askRules + '</div>'+   
                '<div id = "wager-riskamount-' + data.id + '" style="display:none;">' + data.riskAmount + '</div>'+ 
                '<div id = "wager-status-' + data.id + '" style="display:none;">' + data.status + '</div>'+                  
                '<div id = "wager-gametobeplayed-' + data.id + '" style="display:none;">' + data.gameName + '</div>'+ 
                '<div id = "wager-opponentname-' + data.id + '" style="display:none;">' + data.username + '</div>'+                 
                '<div id = "wager-whichconsole-' + data.id + '" style="display:none;">' + data.whichConsole + '</div>'+ 
                '<div id = "wager-created-' + data.id + '" style="display:none;">' + data.created + '</div>'+          
                '<div id = "wager-gamestarttime-' + data.id + '" style="display:none;">' + data.gameStartTime + '</div>'+ 
                '<div id = "wager-gameresulttime-' + data.id + '" style="display:none;">' + data.gameResultTime + '</div>'+                 
                
                
                wagerType +
                '</div> <div class="individualWager" id="buttEvtMatchResult' + data.id + '"> ' +
                '<div role="group" aria-label="...">' + 
                '<a id = "acceptmatch" type="button" value1 = "acceptwager" value2 = "' + data.id + '"' +
                ' class="btn btn-success  acceptWager_open" href="#acceptWager" data-popup-ordinal="0">Wager Details</a>' + 
                 decline +                               
                '</div></div></li>');
            }
        }
        return notice 
    });
    
    
    channel.bind('cancelwager', function(data) {
//        $('#listEvtMatch'+ data.id).html("");
        $('#listEvtMatch'+ data.id).remove(); //attr("style","display:none;");       
    });
    
    channel.bind('delcinewager', function(data) {
//        $('#listEvtMatch'+ data.id).html("");
        $('#listEvtMatch'+ data.id).remove(); //attr("style","display:none;"); 
        
        var user = $("#userId").html();
        if(data.userAskId === user){
            $("#mycredits").html('<h3>$' + data.credits + '</h3>');
        }
    });
    
    
    channel.bind('matchresult', function(data) {
        var user = $("#userId").html();
        if(data.status != 9) {
            $('#listEvtMatch'+ data.id).remove();

            if(data.userId === user) {
                $("#mycredits").html('<h3>$' + data.credits + '</h3>');
                if(data.status == 7) {
                         //Replace with dynamic html
                        var root = $(location).attr('hostname');
                        var protocol = $(location).attr('protocol');
                        var url = protocol + "//"+root + "/wager/index/mywager"; 
                        $(location).attr('href',url);               
                }
            }
        } 
        
        if(data.status == 9) {
            if(data.userId === user) {            
                $('#wager-gameresulttime-'+data.id).html(data.gameResultTime);  
            }
        }
    });    
    
     channel.bind('message', function(data) {
        var user = $("#userId").html();
        if(data.userId === user){
          $('#messagewager').html(data.messages);
          $('#messageWager').popup("show");
        }
    });   
  });
