$(function(){
    $('ul.spy').simpleSpy();
});

(function($){
$.fn.simpleSpy = function (limit, interval) {
    limit = limit || 4;
    interval = interval || 7000;

    return this.each(function () {
        var $list = $(this),
            items = [],
            currentItem = limit,
            total = 0,
            height = $list.find('> li:first').height();
        $list.find('> li').each(function () {
            items.push('<li>' + $(this).html() + '</li>');
        });

        total = items.length;
        $list.wrap('<div class="spyWrapper" />').parent().css({ height : height * limit });
        $list.find('> li').filter(':gt(' + (limit - 1) + ')').remove();

        function spy() {
            var $insert = $(items[currentItem]).css({height : 0, opacity : 0}).prependTo($list);
            $list.find('> li:last').animate({opacity : 0}, 1000, function () {
            	$insert.animate({ height : height }, 1000).animate({ opacity : 1 }, 1000);
            	$(this).remove();
            });

            currentItem++;
            if (currentItem >= total) {
                currentItem = 0;
            }
            setTimeout(spy, interval)
        }
        spy();
    });
};  
})(jQuery);