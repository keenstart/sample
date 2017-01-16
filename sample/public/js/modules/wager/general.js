    $(document).ready(function() {

           var wagerInterval = setInterval(expiredWager, 60000 * 5);
           expiredWager();

        $('#sendHelpEmail').click(function() {
            HelpQuestion();
            
            $("#getHelpPopup").popup("hide");
        }); 
    
    });
    
    // Remove expired wager //
    function expiredWager() {
        var id, userAskId, status;    
        var user = $("#userId").html();
        
        //alert("safari");
        
        if($("#pageOn").html() === "mywager" || $("#pageOn").html() === "openwager") {
                    //alert("safari.....");
            $( "#wagerList > li" ).each(function(i) {
                 id = $( this ).attr("wagerid");
                 userAskId = $( this ).attr("ur");
                 status = $("#wager-status-" + id).html(); 
        
                if(userAskId == user) {  
                                        //alert("safari.....userAskId");
                    if(status == 1) {
                        //alert("safari.....status");
                        var created = $("#wager-created-" + id).html();
                        
                        
                        //var d = new Date(created + ' UTC');
                        var d = new Date(created.replace(/-/g, "/"));
                        d.setTime(d.getTime() - d.getTimezoneOffset()*60*1000)
     
                        
                        var now = new Date();
                        var expired = new Date(d.getTime() + ((1000 * 60) * 60) * 2);
                        if(now > expired) {
                            //alert("safari.....ExpireWager");
                            ExpireWager(id, "/wager/index/expirewager/");
                            $("#listEvtMatch" + id).remove();
                        }
                    }
                }
            });                
        }
        
        if($("#pageOn").html() === "mywager") {
                    //alert("safari.....");
            $( "#wagerList > li" ).each(function(i) {
                 id = $( this ).attr("wagerid");
                 userAskId = $( this ).attr("ur");
                 status = $("#wager-status-" + id).html(); 

                var gamestarttime = $("#wager-gamestarttime-" + id).html();
                var gameresulttime = $("#wager-gameresulttime-" + id).html();                
                if(gamestarttime) {  
                                        //alert("safari.....userAskId");
                    if(status == 2) {
                        var gamesdates = new Date(gamestarttime.replace(/-/g, "/"));
                        
                        if(gameresulttime) {
                            var gameresulttime = new Date(gameresulttime.replace(/-/g, "/"));
                            if(jQuery.type(gameresulttime) === "date") {
                               gamesdates = gameresulttime;
                            }
                        }

                        gamesdates.setTime(gamesdates.getTime() - gamesdates.getTimezoneOffset()*60*1000)
     
                        
                        var now = new Date();

                        
                        if(jQuery.type(gameresulttime) === "date") {
                            var expired = new Date(gamesdates.getTime() + ((1000 * 60) * 60) * 1);// 1 hour expiration
                        } else {
                            var expired = new Date(gamesdates.getTime() + ((1000 * 60) * 60) * 2);// 2 hour expiration
                        }                        
                        
                        if(now > expired) {
                            //alert("safari.....ExpireWager");
                            if(jQuery.type(gameresulttime) === "date") {
                                ExpireWager(id, "/wager/index/resultexpires/");
                                //alert("safari.....ExpireWager");
                            } else {
                                ExpireWager(id, "/wager/index/matchexpires/");
                            }
                            $("#listEvtMatch" + id).remove();
                        }
                    }
                }
            }); 
        }
    }
    
    
    function ExpireWager(wagerid, url) {

        //var url = "/wager/index/expirewager/";
        $.ajax({
                type: "POST",
                url: url,
                data: {wagerId:wagerid},
                success: function(data)
                {
                    if(data.success) {
                        if(data.type === 9) {
                            $("#mycredits").html('<h3>$' + data.credits + '</h3>');
                        }
                    } 
                }
        });
    }    
    

    
    function getGameWagerForm(id) {
        var list, addoption, gameConsoleid;

        gameConsoleid = $(id).val();

        var url = "/wager/general/getgames/";
        $.ajax({
                type: "POST",
                url: url,
                data: {consoleId:gameConsoleid},
                success: function(data)
                {
                    if(data.success) {
                       if(data.count) {
                           $.each(data.consoleGame, function( i,d ) {
                               list =  "<option value='"+ i +"'>"+ d +"</option>";
                               addoption = addoption + list;
                           });
                           $('#wager-games-id').html(addoption);
                           if(data.whichConsole != 0) {
                                if(data.whichConsole == 1) {
                                    $('#consoleUsername').val($('#xboxGamertag').html());
                                } else {
                                    $('#consoleUsername').val($('#pSNUsername').html());
                                }
                           } else {
                               $('#consoleUsername').val("");
                           }
                       }
                    } 
                }
        });
    }

    function getGameConsole(id) {
        var list, addoption;
        var gameConsoleid = $(id).val();
        var url = "/wager/general/getgames/";
        $.ajax({
                type: "POST",
                url: url,
                data: {consoleId:gameConsoleid},
                success: function(data)
                {
                    if(data.success) {
                       if(data.count && $("#pageOn").html() === data.pageon) {
                           $.each(data.consoleGame, function( i,d ) {
                               list =  "<option value='"+ i +"'>"+ d +"</option>";
                               addoption = addoption + list;
                           });
                           $('#games-id').html(addoption);
                           openWagerFilter($("#console-id").val(), $("#games-id").val());
                       } else {
                           if($("#console-id").val() == 1) {
                               openWagerFilter($("#console-id").val(), $("#games-id").val());
                           }
                           $('#games-id').html("<option value='0'>All Games</option>");
                       }
                    } 
                }
        });
    }
    
    function openWagerFilter(consoleid, gamesid) {
        var list, addlist;

        var url = "/wager/general/openwagerfilter/";
        $.ajax({
                type: "POST",
                url: url,
                data: {consoleId:consoleid, gameId:gamesid},
                success: function(data)
                {
                    if(data.success) {
                        if($("#pageOn").html() === data.pageon) {
                           if(data.count) {
                               $.each(data.openwager, function( i,d ) {
                                   list =  getOpenwagerList(d);
                                   addlist = addlist + list;
                               });
                               $('#wagerList').html(addlist);
                           } else {
                               $('#wagerList').html("");
                           }
                        }
                    } 
                }
        });
    }
    
    function getOpenwagerList(data) 
    {
        var button, wagerType;
        var user = $("#userId").html();
        
        if(data.userAskId === user) {
                button = " <a id = 'cancelmatch' type='button' value1 =  'cancelwager' value2 = " + data.id + 
                        " class='btn btn-danger cancelWager_open' href='#cancelWager'>Cancel</a>";            
        } else {
                button = '<a id = "acceptmatch" type="button" value1 = "acceptwager" value2 = "' + data.id + '"' +
                        ' class="btn btn-success  acceptWager_open" href="#acceptWager">Accept</a>';            
        }
        
        if(data.typeId == 1) {
            wagerType = "<div>User: " + data.askusername + "<small> have made an Open Wager</small></div> ";
        } else {

            wagerType = "<div><small>You have made a challenge</small></div>" +
                        "<div><small> Opponent:  " +  data.askusername + "</small></div>"
        }
                
        var list = '<li class="list-group-item"  id="listEvtMatch' + data.id + '"> ' +
                "<div> <div><h3>" + data.consoleName + " - " + data.gameName +
                ' - $' + data.riskAmount + '</h3></div>' +
                        wagerType +
                '</div> <div class="individualWager" id="buttEvtMatchResult' + data.id + '"> ' +
                '<div id = "wagerid" style="display:none;">' + data.id + '</div>' +
                '<div role="group" aria-label="...">' + 
                 button +                               
                '</div></div></li>';
        
        return list;

    }
  
    function HelpQuestion() {

       var url = "/wager/general/helpemail/";
       var  helpquestion = $("#helpquestion").val();
        
        $.ajax({
                type: "POST",
                url: url,
                data: {question:helpquestion},
                success: function(data)
                {
                    if(data.success) {
                        $('#messagewager').html("Question was sent.");
                        $('#messageWager').popup("show");
                    } 
                }
        });
    }






