$('.top_menu_available_credits').tooltip({
    selector: "[data-toggle=tooltip]",
    container: "body"
});
// var mobileHome = window.matchMedia("(min-width: 768px");
var mobileHome = window.matchMedia("(min-width: 950px");
    mobileHome.addListener(WidthChange);
    WidthChange(mobileHome) 
    function WidthChange(mobileHome) {
        if (!mobileHome.matches) {
            $('.homepageWagerList').css('display', 'none')
            $('#homeTalkWallDisplay').css('display', 'none')
        }
        else {
            //console.log ("desktop view")
            $('.homepageWagerList').css('display', 'block')
            $('#homeTalkWallDisplay').css('display', 'block') 
        }
    }