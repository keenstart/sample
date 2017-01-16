  $(document).ready(function() {
    //var stopScroll = true;
    var pusher = new Pusher('xxxxxxxxxxxxx'); 
    
    pusher.connection.bind('connected', function() {
        var socketId = pusher.connection.socket_id;
        $('#socketId').attr("value",socketId);
    });

    var channel = pusher.subscribe('messageevents');
    
    channel.bind('msgsent', function(data) {
      var user = $("#userId").html();
      
        if(data.touserId == user) {
            // if inbox tab
            if($("#pageOn").html() == 'inbox') {
                $('#messageList').prepend(returnMsgHtml(data, data.direction));
            }
        }
    });

    $('#inboxMessageBtn').click(function() {
        $("#pageOn").html('inbox');
        $('#inboxMessageBtn').css('background', '#5cb85c')
        $('#outboxMessageBtn').css('background', 'transparent')
        getMsg("/wager/message/getmsg/", 'inbox', -1);
    });
    
    $('#outboxMessageBtn').click(function() {
        $("#pageOn").html('outbox');
        $('#outboxMessageBtn').css('background', '#5cb85c')
        $('#inboxMessageBtn').css('background', 'transparent')
        getMsg("/wager/message/getmsg/",'outbox', -1);
    });    
    
    //--delete ---//
    $('#deleteMsg').click(function() {
        var id = $(this).attr("value");
        alaert(id);
//        deleteMsg("/wager/message/delete/", id);
    });    
    
    //--Send message --//
    $('#send_message').click(function() {
        $("#writeMessage").popup("hide");
        //debug
//        $("#pageOn").html('outbox');
//        getMsg("/wager/message/getmsg/",'outbox', -1);
        
        SendMsg("/wager/message/send/");
    }); 

    $('#messageList').on('click', '#readmessage', function() {
        var id = $(this).attr("value1");

        $("#userMsg").html($("#listEvtMsg"+id+" :nth-child(1)").html());
        $("#subjectMsg").html($("#listEvtMsg"+id+" :nth-child(2)").html());
        $("#messageMsg").html($("#listEvtMsg"+id+" :nth-child(3)").html());
    });
    
    $('#replyMessageBtn').click(function() {
        
        var t = $("#userMsg").html();
        $("#touserId").val($("#userMsg").html());
        $("#readMessage").popup("hide");
    });
    
    $('#exit_write').click(function(){
        $("#writeMessage").popup("hide");
    });
    
    $('#exit_read').click(function(){ 
        $("#readMessage").popup("hide");
    });
    
    $('#close_reply').click(function(){ 
        $("#readMessage").popup("hide");
    });
    
    $('#close_message').click(function(){ 
        $("#writeMessage").popup("hide");
    });    

    $("#messageList").on("scroll", function() {
        var listcount;
            var scrollPosition = $("#messageList").height() + $("#messageList").scrollTop();
            var scrollHeight = $("#messageList").prop("scrollHeight");

            //$("#inboxMessageBtn").html(" scrollTop():"+$("#messageList").scrollTop() + "px. " + " scrollPosition : "+scrollPosition +
                    //" scrollHeight: "+scrollHeight);


            if ((scrollHeight - scrollPosition) === 0) {
                listcount = $("#messageList > li").length;
                getMsg("/wager/message/getmsg/",'outbox', listcount);
            }
    });
  });


    function deleteMsg(url,id) {
        $('#messagewager').html("Please wait while we Delete your Message...");
        $('#messageWager').popup("show");

        $.ajax({
                type: "POST",
                url: url,
                data: {id:id},
                success: function(data)
                {
                      if(data.success) {
                        $('#listEvtMsg'+ data.id).html("");
                        $('#listEvtMsg'+ data.id).hide(); 
                      } 
                      $('#messagewager').html(data.messages);
                      $('#messageWager').popup("show");

                }
        });
        //document.getElementById("DepositForm").reset();
        //$('#ccredit').hide();
    }
  
    function SendMsg(url) {
        $('#messagewager').html("Please wait while we send your Message...");
        $('#messageWager').popup("show");
        var d = $('#MessageForm').serialize();
        $.ajax({
                type: "POST",
                url: url,
                data: $('#MessageForm').serialize(),
                success: function(data)
                {
                      if(data.success) {
                           if($("#pageOn").html() == 'outbox') { 
                               $('#messageList').prepend(returnMsgHtml(data, data.direction));
                           }
                      } 
                      $('#messagewager').html(data.messages);
                      $('#messageWager').popup("show");

                }
        });
        //document.getElementById("DepositForm").reset();
        //$('#ccredit').hide();
    }


function getMsg(url, type, listcount) {
        var list, addlist,direction;
        $('#messagewager').html("Please wait while we retreive your Messages...");
        $('#messageWager').popup("show");

        $.ajax({
                type: "POST",
                url: url,
                data:  {type:type,offset:listcount},
                success: function(data)
                {
                     var t = $("#pageOn").html();
                     $('#messageWager').popup("hide"); 
                     
                     if(data.success) {
                       if(data.count && $("#pageOn").html() === data.pageon) {
                           direction =data.direction;
                           $.each(data.msg, function( i,d ) {
                               list =  returnMsgHtml(d, direction);
                               addlist = addlist + list;
                           });
                           $('#messageList').html(addlist);
                       }
                        $('#messageWager').popup("hide");
                        
                    } else {
                        $('#messagewager').html(data.messages);
                        $('#messageWager').popup("show");
                    }
                }
        });
    }

function returnMsgHtml(data, direction) {
     
    var list = '<li class="list-group-item"  id="listEvtMsg' + data.id + '"> ' +
    '<div id = "theuserMsg" style="display:none;">' + data.username + '</div>' +
    '<div id = "thesubjectMsg" style="display:none;">' + data.subject + '</div>' +
    '<div id = "themessageMsg" style="display:none;">' + data.messages + '</div>' +
    "<div><h4><small>Subject: </small>" + data.subject +  '</h4></div>' +
    '<div><small>' + direction +  data.username + '</small></div>' +
    '<div class="individualWager"> ' +
    '<div role="group" aria-label="...">' + 
    '<a id = "readmessage" type="button" value1 = "' + data.id + '"' +
    ' class="btn btn-success  readMessage_open" href="#readMessage">Read</a>' +
    ' <input id="listDeleteMsg" type="checkbox">' +
    '</div></div></li>';
    
    return list;
}



