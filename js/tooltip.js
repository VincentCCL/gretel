/* GrETEL 2.0 tooltip JavaScript */
/* written by Jonas Doumen (c) 2014 */
/* for the GrETEL2.0 project */

$(document).ready(function(){

    $(".clickMe").click(function(e){
        // prevent link event from firing
        e.preventDefault(); 
        e.stopPropagation();
        // remove all open tips
        closeTips();
        // create tooltip
        if ($(this).hasClass("clickMe")){
            var content = $(this).attr("tooltip");
            $(this).after( '<div class="tip"><p>'+content+'</p><div id="close">x</div></div>' );
            $("#close").click(function(event){
                event.stopPropagation();
                closeTips();
            });
            $(".tip").click(function(event){
                event.stopPropagation();
                //don't do anything
            });
            $(this).removeClass("clickMe").addClass("clicked");
        } 
    });
    $('html').click(function(e){
        closeTips();
    });
});

function closeTips(){
    $( ".tip" ).prev().removeClass("clicked").addClass("clickMe");
    $( ".tip" ).remove();   
}