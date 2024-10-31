function textextShow() {
    jQuery('.revenue-box:not(.hide-box) #excluded-pages').textext({
        plugins : 'tags focus autocomplete ajax',
        tagsItems : excludedPosts ? excludedPosts : false,
        ext: {
            itemManager: {
                items: [],  // A custom array that will be used to lookup id    
                stringToItem: function(str)
                {
                    //Lookup id for the given str from our custom array
                    for (var i=0;i<this.items.length;i++)
                        if (this.items[i].name == str) {
                            id = this.items[i].id;
                            break;
                        }
                    
                    if(str.length > 10) {
                        str = jQuery.trim(str).substring(0, 10)
                            .split(" ").join(" ") + "...";
                    }
                        
                   return { name: str, id: id };
                },

                itemToString: function(item)
                {
                    //Push items to our custom object
                    this.items.push(item);
                    
                    if($('.error-exclude-page').hasClass('show')) {
                        $('.error-exclude-page').removeClass('show');
                    }
                    
                    
                    return item.name;

                },
                compareItems: function(item1, item2)
                {
                    return item1.name == item2.name;
                }    
            }
        },
        ajax : {
            url : ajaxurl + '?action=get_excluded_posts',
            dataType : 'json',
            cacheResults : true 
        }
    }).bind('isTagAllowed', function(e, data){
        var formData = $(e.target).textext()[0].tags()._formData,
            list = eval(formData);

        for(var i = 0; i < list.length; i++) {
            for(key in list[i]) {
                if(list[i]['id'] == data.tag.id) {
                    $('.error-exclude-page').addClass('show');
                    data.result = false;
                }
            }
        }    
    });

}

jQuery(document).ready(function(){
    jQuery('input.revenuehits_show').on('change', function(){
         if(jQuery(this).val() == 2) {
            jQuery('tr.revenue-box').addClass('hide-box');
         } else {
            jQuery('tr.revenue-box').removeClass('hide-box');
            textextShow();
         }
    });
    
    jQuery('.check').on('change', function(){
        if($('.check:checked').length > 1) {
            $('.check').not($(this)).prop('checked', false);
        }        
    });
    
    textextShow();
});





