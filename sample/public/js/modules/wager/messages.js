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
        
        var notificationInformation = {
                 msg: "You have received a new message",
                 user: "From: " + data.username
            }
            var notice = new Notification("New Message", {
                     body: notificationInformation.msg + "\n" + notificationInformation.user 
            })
            notice.onclick = function() {
                     var root = $(location).attr('hostname');
                     var protocol = $(location).attr('protocol');
                     var url = protocol + "//"+root + "/wager/message"; 
                     $(location).attr('href',url);
            }
            // Nofitication Numbers updated 
            if (($("#unreadInboxMsgs").html() == "") || ($("#unreadInboxMsgs").html() == 0)) {
              var unreadCount = 1 
              $("#unreadInboxMsgs").removeClass("noshow")
              $("#unreadMsgs").removeClass("noshow")
              $("#unreadMsgs").html(unreadCount)
              $('#unreadInboxMsgs').html(unreadCount)
            } else {
              var unreadCount = parseInt($('#unreadInboxMsgs').html()) + 1 
              $('#unreadInboxMsgs').html(unreadCount)
               $("#unreadMsgs").html(unreadCount)
            }
            // if inbox tab
            if ($("#pageOn").html() == 'inbox') {
                $('#messageList').prepend(returnMsgHtml(data, data.direction));
            }
        return notice
        }
    });

    $('#inboxMessageBtn').click(function() {
        $("#pageOn").html('inbox');
        $('#inboxMessageBtn').css('background', '#5cb85c')
        $('#outboxMessageBtn').css('background', 'transparent')
        getMsg("/wager/message/getmsg/", 'inbox', 0);
    });
    
    $('#outboxMessageBtn').click(function() {
        $("#pageOn").html('outbox');
        $('#outboxMessageBtn').css('background', '#5cb85c')
        $('#inboxMessageBtn').css('background', 'transparent')
        getMsg("/wager/message/getmsg/",'outbox', 0);
    });    
   
    //--Send message --//
    $('#send_message').click(function() {
        $("#writeMessage").popup("hide");

        
        SendMsg("/wager/message/send/");
    }); 

    $('#messageList').on('click', '#readmessage', function() {
        var id = $(this).attr("value1");
        $("#userMsg").html($("#listEvtMsg"+id+" :nth-child(1)").html());
        $("#subjectMsg").html($("#listEvtMsg"+id+" :nth-child(2)").html());
        $("#messageMsg").html($("#listEvtMsg"+id+" :nth-child(3)").text().replace(/\r?\n/g, '<br />'));
        $("#dateMsg").html($("#listEvtMsg"+id).find("#thedateMsg").html());

        IsReadMsg(id,"/wager/message/isread/"); 
    });
    
    $('#replyMessageBtn').click(function() {
        
        var t = $("#userMsg").html();
        $("#touserId").val($("#userMsg").html());
        var replySubject = $("#subjectMsg").html();
        if (replySubject.substring(0,3) == "RE:") {
          $("#subject").val($("#subjectMsg").html());
        }
        else {
          $("#subject").val("RE: " + $("#subjectMsg").html());
        }
        var pastDateStamp = $("#dateMsg").html();
        var pastConvo = $("#messageMsg").html();
        $('[name="messages"]').val(" \n" + " \n" + " On " +pastDateStamp +", " + $("#userMsg").html() + " wrote:" + "\n" + " " + pastConvo.replace(/<br\s*[\/]?>/gi, "\n"));
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


    //--delete ---//   
    
    $("#deleteMessageIcon").click(function() {
      $("#messageList > li").each(function(i) {
        var id = $(this).attr("id");
        var msgCheckbox = $("#"+id).find("#listDeleteMsg");
        if (msgCheckbox.prop("checked")) {
          id = id.substring(10)  
          DeleteMessage("/wager/message/deletemessage/", id);
        }
      });
    });


    $("#messageList").on("scroll", function() {
        var listcount;
            var scrollPosition = $("#messageList").height() + $("#messageList").scrollTop();
            var scrollHeight = $("#messageList").prop("scrollHeight");

            if ((scrollHeight - scrollPosition) === 0) {
                listcount = $("#messageList > li").length;
                getMsg("/wager/message/getmsg/",$("#pageOn").html(), listcount);
            }
    });
  });
    function changeMsgRead(url, id) {

        $.ajax ({
            type: "POST",
            url: url,
            data: {id:id},
            success: function(data) 
            {
                if (data.success) {
                    console.log("changeMsgRead", data)
                }
                else {
                    console.log("didn't work")
                }
            }
        });
    }

    function DeleteMessage(url,id) {
        $('#messagewager').html("Please wait while we delete your message...");
        $('#messageWager').popup("show");

        $.ajax({
                type: "POST",
                url: url,
                data: {id:id},
                success: function(data)
                {
                      if(data.success) {
                        $('#messageWager').popup("hide");
                        getMsg("/wager/message/getmsg/",$("#pageOn").html(), 0);
                      } 
                }
        });
    }
  
    function SendMsg(url) {
        $('#messagewager').html("Please wait while we send your message...");
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
    }


function getMsg(url, type, listcount) {
        var list,direction;
        var addlist = ''; 
        $('#messagewager').html("Please wait while we retrieve your messages...");
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
                        if(data.count) {
                           if($("#pageOn").html() === data.pageon) {
                               direction =data.direction;
                               $.each(data.msg, function( i,d ) {
                                   list =  returnMsgHtml(d, direction);
                                   addlist = addlist + list;
                               });
                               $('#messageList').html(addlist);
                           }
                       } else {
                           if(data.append){
                                $('#messageList').html("");
                           }
                       }
                        $('#messageWager').popup("hide");
                        
                    } else {
                        $('#messagewager').html(data.messages);
                        $('#messageWager').popup("show");
                    }
                }
        });
    }


   function IsReadMsg(id,url) {
        var readMsg = $("#listEvtMsg"+id).find("#isReadMsg").html();
        if (readMsg != 1 ) {
          $.ajax({
                  type: "POST",
                  url: url,
                  data: {id:id},
                  success: function(data)
                  {
                        if(data.success) {
                          //console.log("IsReadMsg", data)
                          var unreadCount = parseInt($('#unreadInboxMsgs').html()) - 1 
                          var unreadPopUpCount = parseInt($("#unreadMsgs").html()) - 1
                          var readIcon = $("#listEvtMsg"+id).find(".messageUnreadIcon")
                          readIcon.css("color","black")
                          readIcon.css("opacity", ".3")
                          $('#unreadInboxMsgs').html(unreadCount)
                          $("#unreadMsgs").html(unreadPopUpCount)
                          if (unreadCount == 0) {
                            $("#unreadMsgs").addClass("noshow")    
                            $("#unreadInboxMsgs").addClass("noshow")
                          }
                          
                        } 
                  }
          });
        }
    }


function returnMsgHtml(data, direction) {
     var messageCreated = data.created
     var dateReceived = messageCreated.substring(5,7) + "/" + messageCreated.substring(8,10)  + "/" + messageCreated.substring(0,4)
     var timeReceived = messageCreated.substring(11,13) + ":" + messageCreated.substring(14,16)
     if (data.isRead == 1) {
        var noIcon = "color:black;opacity:.3"
     }
     if (direction == "To:") {
        var noIcon = "display:none;"
     }
     if (data.isDeleted == 1) {
        var deletedMessage = "display:none;"
     }

    var list = '<li class="list-group-item" style=' + deletedMessage +' id="listEvtMsg' + data.id + '"> ' +
    '<div id = "theuserMsg" style="display:none;">' + data.username + '</div>' +
    '<div id = "thesubjectMsg" style="display:none;">' + data.subject + '</div>' +
    '<div id = "themessageMsg" style="display:none;">' + data.messages + '</div>' +
    '<div id = "thedateMsg" style="display:none;">' + dateReceived + ', at ' + timeReceived + '</div>' +
    '<div id = "thetimeMsg" style="display:none;">' + timeReceived + '</div>' +
    '<div id = "isReadMsg" style="display:none;">' + data.isRead + '</div>' +
    '<div class="messageDateTime messageOverview"><h4>' + data.username +  '<h5><span class="dateReceivedMsg"><small>' + dateReceived + '</small></span></h5></h4></div>' +
    '<div class="messageOverview"><h4><small>' +  data.subject + '</small></h4></div>' +
    '<div class="messageUnreadIcon" style='+ noIcon +'><i class="fa fa-circle"></i></div>' +
    '<div class="individualWager"> ' +
    '<div role="group" aria-label="...">' + 
    '<a id = "readmessage" type="button" value1 = "' + data.id + '"' +
    ' class="btn btn-success  readMessage_open" href="#readMessage">Read</a>' +
    ' <input id="listDeleteMsg" type="checkbox">' +
    '</div></div></li>';
    
    return list;
}



// Profile - username validation Front End 
$('#changeUsernameInput').keyup(function() {
    var $th = $(this);
    $th.val( $th.val().replace(/[^a-zA-Z0-9]/g, function(str) { 
        if ($('#changeUsernameInput').next().is('label')) {
            $('#changeUsernameInput').next('label').remove()
        }
        $('#changeUsernameInput').after("<label class='error'>You typed " + str + ". Please use only letters and numbers</label>");
        return ''; 
    }));
});

var playerWagerCount = $("#wagersHistoryCount").html();
var playerWins = $("#wagersHistoryCountWins").html();
var playerLosses = $("#wagersHistoryCountLosses").html();
$(".playerMatches").html("Total Wagers Played: " + playerWagerCount);
$(".playerWins").html("Total Wager Wins: " + playerWins);
$(".playerLosses").html("Total Wager Losses: " + playerLosses);




