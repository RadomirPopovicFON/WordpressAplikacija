/*
*	jQuery handling of the events
*/
$(document).ready(function() {

    var ajaxUrl = $("form").attr("ajaxurl");
    
    $("#gridview").on("click", function(){ 

        $.ajax({  
            type: 'POST',  
            url: ajaxUrl,
            data: {  
                action: 'gridView'
            },  
            beforeSend: function(xhr) {
                $("#showgridview").fadeIn();
                var $imgloader = $("<img src='http://www.tirol.at/portal/img/preloader-large-transparent.gif' />");
                $("#showgridview").html($imgloader);
            },
            success: function(data, textStatus, XMLHttpRequest){  
                $("#showlistview").fadeOut();
                $("#showgridview").html(data);
            },  
            error: function(MLHttpRequest, textStatus, errorThrown){  
                alert(errorThrown);
            }  
        });  
    });

    $("#listview").on("click", function(){ 
        $("#showlistview").fadeIn();
        $("#showgridview").fadeOut();        
    });

});