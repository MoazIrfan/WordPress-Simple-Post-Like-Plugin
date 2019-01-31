jQuery(document).ready(function() {
 
    jQuery(".post-like a").click(function(){
     
        like = jQuery(this);
     
        // Retrieve post ID from data attribute
        post_id = like.data("post_id");
         
        // Ajax call
        jQuery.ajax({
            type: "post",
            url: ajax_var.url,
            data: "action=gppl_post_like&nonce="+ajax_var.nonce+"&post_like=&post_id="+post_id,
            success: function(count){
                // If vote successful
                if(count != "already")
                {
                    like.addClass("voted");
                    like.siblings(".count").text(count);
                }
            }
        });
         
        return false;
    })
})