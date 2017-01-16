  $(document).ready(function() {
    //var stopScroll = true;
    var pusher = new Pusher('xxxxxxxxxxxxx'); 
    
//    pusher.connection.bind('connected', function() {
//        var socketId = pusher.connection.socket_id;
//        $('#socketId').attr("value",socketId);
//    });

    var channel = pusher.subscribe('talkevents');
    
    channel.bind('talkchannel', function(data) {
        if($("#talkChannel").val() === data.channelId) {
            var user = $("#userId").html();
            if(data.userId !== user) {
                $('#talkList').append(returnTalkHtml(data));
                var scrollWall = $("#talkList")
                scrollWall.scrollTop(scrollWall[0].scrollHeight);
                console.log("receiving new tag")
            }
        }
    });
    
    $("#talkMsg").on('keydown', function(e) {
        if (e.keyCode === 13) {
            sendTalk("/wager/message/talk/");
            $("#talkMsg").val("");
        }
        else console.log("didn't work");
    })

    $('#sendTalk').click(function() {
        sendTalk("/wager/message/talk/");
            $("#talkMsg").val("");
    });

    $("#talkList").on("scroll", function() {
        var listcount;
        var scrollPosition =  $("#talkList").scrollTop();
        //var scrollHeight = $("#talkList").prop("scrollHeight");

        //$("#inboxMessageBtn").html(" scrollTop():"+$("#messageList").scrollTop() + "px. " + " scrollPosition : "+scrollPosition +
                //" scrollHeight: "+scrollHeight);

        if (scrollPosition == 0) {
            var listcount = $("#talkList > li").length;
            getTalkChannel(listcount);
        }
    });
  });

function getTalkChannel(listcount)
{
    var url = "/wager/message/gettalk/";
    var list = "", addlist = "";
    var channel = $("#talkChannel").val();
    
    
    $.ajax({
            type: "POST",
            url: url,
            data: {channelId:channel,offset:listcount},
            success: function(data)
            {
                if(data.count) { 
                    if(data.success) {
                       $.each(data.talk, function( i,d ) {
                           list =  returnTalkHtml(d);
                           addlist = addlist + list;
                       });
                       
                       if(data.append) {
                            $('#talkList').prepend(addlist);
                       } else {
                           $('#talkList').html(addlist);
                       } 
                    }
                } 
//                else {
//                     $('#talkList').html("");
//                }
            }
    }); 
}


function sendTalk(url) {

    var d = new Date();
    var minutes = d.getMinutes()
    if (minutes <= 9) {
        minutes = "0" + minutes
    }
    var time = d.getHours() + ":" + minutes
    
  
    var list = '<li>' +
            '<div class="talkWallMessage">' +
            '<p class="talkWallUser">' + $(".last-name").html() + '</p>' +
            '<p>' + $("#talkMsg").val() + '</p>' +
            '</div>' +
            '<div class="talkWallMessageTime">'+
            '<p>' + time + '</p>'
            '</div>' +
            '</li>'
        
    $('#talkList').append(list);
    
    var channel = $("#talkChannel").val();
    $.ajax({
        type: "POST",
        url: url,
        data: {channelId:channel,talk:$("#talkMsg").val()},
        success: function(data)
        {
              if(data.success) {
              } 
        }
    });
}


function returnTalkHtml(data) {
     //var d = new Date(data.created+ ' UTC');
     var timing = new Date(data.created.replace(/-/g, "/"));
     timing.setTime(timing.getTime() - timing.getTimezoneOffset()*60*1000)

     //var s = d.toString();
     //var r = new Date();
     //var time = d.toLocaleTimeString();
    //var time = (d.toString()).substring(16,21) //(data.created).substring(11,16)

    var minutes = timing.getMinutes()
    if (minutes <= 9) {
        minutes = "0" + minutes
    }
    var time = timing.getHours() + ":" + minutes
    

    var list = '		<li>' +
			'<div class="talkWallMessage">' +
				'<p class="talkWallUser">' + data.username+ '</p>' +
				'<p>' + data.talk + '</p>' +
			'</div>' +
			'<div class="talkWallMessageTime">'+
				'<p>' + time + '</p>'
			'</div>' +
		'</li>';
    
    return list;
}

// Auto scroll feature 
$("#talkMsg").on('keydown', function(e) {
        if (e.keyCode === 13) {
            var scrollWall = $("#talkList")
            scrollWall.scrollTop(scrollWall[0].scrollHeight);

            setTimeout(function autoScroll() {
                scrollWall.scrollTop(scrollWall[0].scrollHeight);
            }, 10)

        }
        else console.log("didn't work")
})

$("#sendTalk").click(function() {
    var scrollWall = $("#talkList");
    scrollWall.scrollTop(scrollWall[0].scrollHeight);
    
    setTimeout(function autoScroll() {
        var scrollWall = $("#talkList");
        scrollWall.scrollTop(scrollWall[0].scrollHeight);
        return false 
    }, 10)

})


