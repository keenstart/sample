// Flex Admin Custom JavaScript Document

// Messages Pop ups
$("#composeMessageBtn").click(function() {
    $("#writeMessage").toggleClass('showMessagesPopUps', 'hideMessagesPopUps')
})

//Sidebar Toggle
$("#sidebar-toggle").click(function(e) {
    e.preventDefault();
    $(".navbar-side").toggleClass("collapsed");
    $("#page-wrapper").toggleClass("collapsed");
});
// TalkWall Toggle 
$("#talkWall-toggle").click(function(e) {
    e.preventDefault();
    $("#talkWall").toggleClass('toggleTalkWall')
    $("#talkWall-toggle").toggleClass('rotateTalkWallIcon')
    $('.page-content').toggleClass('col-sm-9', 'col-sm-12')
    $('.page-content').toggleClass('col-md-9', 'col-md-12')
});

$("#mobileToggleTalkWall").click(function(e){
                    $("#talkWall").toggleClass('toggleTalkWall')
                    $("#talkWall-toggle").toggleClass('rotateTalkWallIcon')
                    $('.page-content').toggleClass('col-sm-9', 'col-sm-12')
                    $('.page-content').toggleClass('col-md-9', 'col-md-12')
})
var mq = window.matchMedia("(min-width: 768px)");
    mq.addListener(WidthChange);
    WidthChange(mq) 
    function WidthChange(mq) {
        if (!mq.matches) {
            $("#talkWall").toggleClass('toggleTalkWall')
            $("#talkWall").css ('width', "100%")
            $("#talkList").css('width', '100%')
            $('.userMessageInputContainer').css('width', '100%')
            $("#talkWall-toggle").css("display", "none")
            // $('.desktopWagerBtn').css("display", "none")
            // $('.mobileWagerBtn').css("display", "inline-block")
            $("#talkWall-toggle").toggleClass('rotateTalkWallIcon')
            $('.page-content').toggleClass('col-sm-9', 'col-sm-12')
            $('.page-content').toggleClass('col-md-9', 'col-md-12')
        }
        else {
            $("#talkWall").css('width', '18%')
            $("#talkList").css('width', '17%')
            $('.userMessageInputContainer').css('width', '18%')
            $("#talkWall-toggle").css("display", "inline-block")
            //$('.desktopWagerBtn').css("display", "inline-block")
            // $('.mobileWagerBtn').css("display", "none") 
        }
    }
// Accept Rules before Make Wager is Clickable 
$('#acceptWagerRulesCheckbox').change(function() {
    if ($('#acceptWagerRulesCheckbox').is(':checked')) {
         $("#madewagerSubmit").removeAttr("disabled")
         
    }
    else {
        $("#madewagerSubmit").attr('disabled', 'disabled')
    }
})

$("#acceptWagerPopUpCheckbox").change(function() {
    if ($("#acceptWagerPopUpCheckbox").is(':checked')) {
        $("#acceptwager").removeAttr("disabled")
    }
    else {
         $("#acceptwager").attr('disabled', 'disabled')
    }
})


// Count Unread Messages in Inbox 
var inboxNumberMsg = $("#unreadMessages").html();
if (inboxNumberMsg == 0) {
    $("#unreadMsgs").addClass("noshow")    
    $("#unreadInboxMsgs").addClass("noshow")
}
else {
    $("#unreadMsgs").html(inboxNumberMsg)
    $("#unreadInboxMsgs").html(inboxNumberMsg)
}
// Count Pending Wagers Received 
var pendingWagersCount = $("#myWagersCount").html();
if (pendingWagersCount == 0) {
    $("#notSeenWagers").addClass("noshow")
}
else $("#notSeenWagers").html(pendingWagersCount)


//Portlet Icon Toggle
$(".portlet-widgets .fa-chevron-down, .portlet-widgets .fa-chevron-up").click(function() {
    $(this).toggleClass("fa-chevron-down fa-chevron-up");
});

//Portlet Refresh Icon
(function($) {

    $.fn.extend({

        addTemporaryClass: function(className, duration) {
            var elements = this;
            setTimeout(function() {
                elements.removeClass(className);
            }, duration);

            return this.each(function() {
                $(this).addClass(className);
            });
        }
    });

})(jQuery);

$("a i.fa-refresh").click(function() {
    $(this).addTemporaryClass("fa-spin fa-spinner", 2000);
});

//Slim Scroll
$(function() {
    $('#messageScroll, #alertScroll, #taskScroll').slimScroll({
        height: '200px',
        alwaysVisible: true,
        disableFadeOut: true,
        touchScrollStep: 50
    });
});

//Easing Script for Smooth Page Transitions
$(function() {
    $('.page-content').addClass('page-content-ease-in');
});

//Tooltips
$(function() {

    // Tooltips for sidebar toggle and sidebar logout button
    $('.tooltip-sidebar-toggle, .tooltip-sidebar-logout').tooltip({
        selector: "[data-toggle=tooltip]",
        container: "body"
    })

})

//HISRC Responsive Images
$(document).ready(function() {
    $(".hisrc").hisrc();
});

if ($(".pageView").html() == "theWagerWallView") {
    $("#theWagerWallView").css("background-color", "rgba(44,44,44,1)")
}
if ($(".pageView").html() == "pendingWagersView") {
    $("#pendingWagersView").css("background-color", "rgba(44,44,44,1)")
}
if ($(".pageView").html() == "wagerHistoryView") {
    $("#wagerHistoryView").css("background-color", "rgba(44,44,44,1)")
}
if ($(".pageView").html() == "messagesView") {
    $("#messagesView").css("background-color", "rgba(44,44,44,1)")
}
if ($(".pageView").html() == "walletView") {
    $("#walletView").css("background-color", "rgba(44,44,44,1)")
}
if ($(".pageView").html() == "profileView") {
    $("#profileView").css("background-color", "rgba(44,44,44,1)")
    $("#talkWall-toggle").css("display", "none")
}
