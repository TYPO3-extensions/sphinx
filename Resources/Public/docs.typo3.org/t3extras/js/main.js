(function($) {
	$.fn.hAccordion = function(o) {

		var $ul = $("> ul", this),
		$ob = $(this),
		$li = $("> li", $ul),
		ulWidth = $ob.outerWidth(true),
		liLength = $li.length,
		midWidth = parseInt(ulWidth/liLength),
		minWidth = parseInt(midWidth/2),
		maxWidth = ulWidth - minWidth * (liLength -1);

		$li.css({width: midWidth}).last().addClass('last');

		o = $.extend({
			min: minWidth,
			max: maxWidth,
			mid: midWidth,
			speed: 500,
			easing: "easeOutCirc"
		}, o || {});

		$li.mouseenter(function() {
				running = true;
				$li.not(this).stop().animate({width: o.min}, o.speed);
				$(this).stop().animate({width: o.max}, o.speed);
		});

		$ul.mouseleave(function() {
			$li.stop().animate({width: o.mid}, o.speed);
		});
	};
})(jQuery);


(function($) {
	// Calculate fixed tabs width
	$.fn.calculateTabsWidth = function() {

	$(this).each(function() {

		var o = $(this);
		var list = $(o).find('li');
		var oWidth = o.outerWidth(true);

		o.parent().width(oWidth); // fix for FF4 :)

		var tabsNo = $(list).length;
		tabsNo = (tabsNo == 0) ? 1 : tabsNo;

		var tabsWidth = 0, addWidth = 0, mod = 0, counter = 0;

		$(list).each(function() {
			tabsWidth += $(this).outerWidth(true);
		});

		mod = (oWidth - tabsWidth) % tabsNo;
		addWidth = (oWidth - mod - tabsWidth) / tabsNo;

		$(list).each(function() {

			newWidth = (counter < mod) ? $(this).width() + addWidth + 1 : $(this).width() + addWidth;

			$(this).css({'width': newWidth});
			$(this).find('a').css({'width': newWidth-1}); // for IE7 fix

			counter++;
		});

	});

	}



	/**
	 * BEN (2011-04-17): navMain improvements
	 *
	 * @notes: Made it faster and fixed animation. Added z-index incrementation so the expanding layer is always on top
	 * @todo: Possibly not add up z-index infinetely but make sure the collapsing layers decrement. That's low priority.
	 * @added: (2011-04-18) Exclude list-items with class "f-r" (View Demo & Download)
	**/
	$.fn.navMain = function() {
		// Initialize the var for adding up z-index of the nav-sub-layers
		var zIndex = 9;
		$(this).find('>li').not('.f-r').hover(function() {
			zIndex++;
			$(this).css('zIndex',zIndex);
			$(this).find('.nav-sub').
			show().
			find('.animate').
			stop(true,false).
			animate({marginTop:'0'},750,'easeOutQuint');
		},function() {
			$(this).
			find('.animate').
			stop(true,false).
			animate({marginTop:'-285px'},500,'easeOutQuint',function() {
				$(this).parent().hide();
			});
		});
		// Don't break the chain :-)
		return this;
	}
	/* :NEB */



	// Smart label plugin: Input and label text inside span
	$.fn.smartLabel = function() {
		$(this).each(function(){
			// clearing values after reload
			if($(this).attr('value').length > 0 ) $(this).siblings('span').fadeOut('fast');

			// Show/hide label
			$(this).focus(function(){
				$(this).siblings('span').fadeOut('fast');
			}).blur(function(){
				if (!$(this).attr('value')) $(this).siblings('span').fadeIn('fast');
			});
		});
	}

})(jQuery);


(function($) {
	// Accordion
	$.fn.accordion = function() {

		$(this).find('> .a-body').each(function(){
			$(this).data('height', $(this).height());
		});

		$(this).find('> .a-h:not(.open)').addClass('closed')
			.next().hide().css('height',0);

		$(this).find('> .a-h').click(function(){
			if($(this).hasClass('closed')){
			var domCurrent = $(this);
			var intCurrentHeight = domCurrent.next().data('height');
			var domOpened = $(this).siblings('.open');

			domOpened.addClass('closed').removeClass('open')
				.next().animate({'height': 0}, function() {$(this).hide()});
			domCurrent.removeClass('closed').addClass('open')
				.next().show().animate({'height': intCurrentHeight});
			}
		});

	}
})(jQuery);

(function($) {
// Social box
$.fn.socialBox = function() {

	var o = $(this),
		$body = o.find('.b-social-body'),
		$opened = o.find('.b-social-opened'),
		$closed = o.find('.b-social-closed'),
		$toggle = o.find('.b-social-toggle'),
		o_height = $opened.height(),
		o_closed_height = $closed.height(),
		sOpenedClass = 'b-social-open',
		sClosedClass = 'b-social-close';

	$closed.css('position', 'absolute');

	$status = $.jCookies({ get : 'Social Box Status' });

	if(o.hasClass(sClosedClass) || ($status && $status.closed)) {
		$body.height(o_closed_height);
		$opened.hide();
		o.removeClass(sOpenedClass).addClass(sClosedClass);
	} else {
		$closed.hide();
	}

	$toggle.click(function(){
		if(o.hasClass(sOpenedClass)){
			$closed.fadeIn();
			$opened.fadeOut();

			$body.animate({'height': o_closed_height});
			o.removeClass(sOpenedClass).addClass(sClosedClass);

			// Remember status (save in cookie)
			$.jCookies({
				name : 'Social Box Status',
				value : { closed: true },
				days : 365
			});

		} else {
			$closed.fadeOut();
			$opened.fadeIn();

			$body.animate({'height': o_height});
			o.addClass(sOpenedClass).removeClass(sClosedClass);

			// Remember status (save in cookie)
			$.jCookies({
				name : 'Social Box Status',
				value : { closed: false },
				days : 365
			});
		}
	});

}
})(jQuery);



/**
 * BEN (2011-04-17): Small wrapper for logging
**/
jQuery.log = function(s) {
	if (window.console) {
		console.log(s);
	} else {
		// Only if really needed. It's quite annoying without a console...
		//alert(s);
	}
}
/*:NEB */

/**
 * init
 *     the twitter post sliding in the calendar
 * and
 *     the forge slider on the home page
 *
 * called when the tweets are loaded via AJAX
 */
function initSlideshows(){
	$('.b-twitter .slider-nav, .ticker-slider .slider-nav').tabs('.slide', {
		tabs: 'li',
		current: 'active',
		effect: 'fade',
		fadeInSpeed: 500,
		fadeOutSpeed: 500,
		rotate: true
	}).slideshow({
		autoplay: true,
		interval: 4000
	});
}


$(document).ready(function(){
		// redundant - already being done in the page template (for faster js-on/js-off class switch!)
	$('body').removeClass('js-off');

	$.tools.tabs.addEffect("default", function(tabIndex, done) {	// Removed display none for inactive tabs
		this.getPanes().removeClass('show-tab').addClass('hide-tab').eq(tabIndex).removeClass('hide-tab').addClass('show-tab');
		done.call();
	});

	$(".tabs:not(.js-off):not(.search-result)").tabs("> .tab-panes > div", {tabs: 'li', current: 'act', initialIndex: 0});
	$(".tabs:not(.lite-tabs)").calculateTabsWidth();

	//$(".h-accordion").hAccordion({min: 129, max: 446, mid: 235});
	$(".h-accordion").hAccordion();

	// Home page Main Scroller/ Tabs
	if ($('#top-slider').length > 0) {
		$('#top-slider .slider-nav').tabs('#top-slider .slides > .slide', {tabs: 'li', current: 'active', effect: 'fade', fadeInSpeed: 1000, fadeOutSpeed: 1000, rotate: true}).slideshow({autoplay:false,interval:8000});
		setTimeout(function(){$('#top-slider .slider-nav').data("slideshow").play();}, 1000);
	}

	// END Home page Main Scroller/ Tabs

	if ($('.small-slider').length > 0) {
		$('.small-slider:not(.auto-scroll) .slider-content').scrollable({circular: true, next: '.slider-nav .next', prev: '.slider-nav .prev'});
		$('.small-slider.auto-scroll .slider-content').scrollable({circular: true, next: '.slider-nav .next', prev: '.slider-nav .prev'}).autoscroll({interval:4000});
		$('.slider-nav .next, .slider-nav .prev').click(function() { return false; })
	}

	$('.accordion').accordion();

//	Cufon.replace('.b-numbers li, .b-numbers h4, .b-numbers p, .b-numbers h3, .b-communities-text, .b-social-closed, .b-find-out-link, .b-keyvisual h3, .quick-search-tip-body', { fontFamily: 'Share-Italic' });
	//Cufon.replace('h1, .d h2, .d h3, .d h4, #header .nav > li > a', { fontFamily: 'Share-Regular' });
//	Cufon.replace('h1, .d h2, .d h3, .d h4, .b-filter-head h4, #beta-status', { fontFamily: 'Share-Regular' });
//	Cufon.replace('.b-keyvisual h3, .b-404 h2, #top-slider .slide-content h3', { fontFamily: 'Share-Italic' });
//	Cufon.replace('.b-404 h1, .slide-content h1, #top-slider .slide-content h2', { fontFamily: 'Share-Bold' });

	/* BEN: Cufon for TER */
//	Cufon.replace('.tx-terfe-pi1 .ext-name', { fontFamily: 'Share-Regular' });
//	Cufon.replace('.tx-terfe-pi1 .state', { fontFamily: 'Share-Italic' });
	/* :NEB */

	// Social block on homepage
	$('.b-social').socialBox();

	// Main nav animation fix
	$('.nav-sub').wrapInner('<div class="animate"></div>');

	$('.nav-sub').each(function(){
		if($(this).find('.col:only-child').length) {
			$(this).css('width', '190px');

		} else {
			$(this).css('width', '388px');
		}
	});

	/**
	 * BEN (2011-04-17): navMain improvements
	 *
	 * @notes: It's a plugin now. See above
	 * @todo: Reorganize this file
	 *
	**/
	$('.nav').navMain();
	/* :NEB */

	/**
	 * BEN (2011-04-18): Scrollable for references box on home
	**/
	$('.scrollable').scrollable({
		circular: true,
		onBeforeSeek: function(e,i) {
			var title = $('.scrollable .items>div[class!="cloned"]').eq(i).find('a').attr('title');
			if(title!=undefined){$('#references').find('h5').text(title);}
		}
	});
	$('.scrollable').autoscroll({interval:4000});
	/* :NEB */


	/**
	 * CHRISTIAN Z. (2011-04-28): fix a bug where to fast clicking on buttons  marks some text
	 */

	$('#references .next, #references .prev').mousedown(function() { return false; })
	/* :YLGU SKOOL SDRAWKCAB EMAN YM */

	/**
	 * BEN (2011-04-19): forgeTicker AJAX-request
	 * @notes: Param is the attribute where the url is "stored" in
	 *
	 * CHRISTIAN (2011-04-21): also used for Twitter in UPCOMING view of the calendar
	 *  and other remotely loaded feeds later on
	**/
	$('#facebook-ticker').load($('#facebook-ticker').attr('data-uri'));
	$('#twitter-ticker').load($('#twitter-ticker').attr('data-uri'));
	$('#forge-ticker').load($('#forge-ticker').attr('data-uri'), initSlideshows);

	/**
	 * image gallery with fade effect
	 * Martin Tepper, 29.09.2011
	 */
	$('.csc-textpic .csc-textpic-imagewrap.fade').tabs('.csc-textpic-imagerow', { effect: 'fade', fadeOutSpeed: 'slow', rotate: true }).slideshow({ autoplay: true });

	/* :NEB */



	if (typeof $.fn.MultiFile == 'function') {
		if ($('.multi-file').length > 0) {
			var textWidth = $('.form-file-main-text').outerWidth(true);
			var inputWidth = $('.multi-file').outerWidth(true);
			var inputLeft = (inputWidth - textWidth) * (-1);
			$('.multi-file').css({'left': inputLeft})
		}
		$('.multi-file').MultiFile({
			onFileRemove: function(element, value, master_element){
				$(element).closest('.b-form-file').removeClass('disabled');
			},
			afterFileSelect: function(element, value, master_element){
				var disabled = $(element).next('input').attr('disabled');
				if (typeof disabled !== 'undefined' && disabled !== false) {
					$(element).closest('.b-form-file').addClass('disabled');
				}
			}
		});
	}

	if (typeof $.fn.prettyCheckboxes == 'function') {
		$('.b-form input[type=checkbox], .specialist-filter input[type=checkbox], .b-form input[type=radio]').prettyCheckboxes();
	}

	// Smart search
	if($('.smart-search').length) smartSearch();

	// Smart label
	if($('.s-input')) $('.s-input input[type=text]').smartLabel();

	// Gallery every third item fix
	if ($('.gallery').length) {
		$('.gallery li:nth-child(3n)').addClass('right');
		$('.gallery li:not(.nohover)').hover(function(){
				var item = $(this);
				hovered = setTimeout(function(){item.addClass('hovered')},500);
		}, function(){
				clearTimeout(hovered);
				$(this).closest('.gallery').find('li').removeClass('hovered');

		});

		// $('.gallery li:not(.nohover)').hover(function() {
		// 	    clearTimeout($(this).data('timeout'));
		// 	    $(this).hide();
		// 	}, function() {
		// 	    var t = setTimeout(function() {
		// 	        $(this).show();
		// 	    }, 1000);
		// 	    $(this).data('timeout', t);
		// 	});

	}

	// Equal tab height for FP
	equalTabHeight();

	// Insert print decoration
	/* Christian Zenker (2011-09-23): removed as it points to a non-existing resource and it does not seem to do anything usefull */
	//var sPrintHorizontalLine = '<img class="print-info" src="../typo3conf/ext/t3org_template/i/border.gif" width="100%" height="1" />'
	//$('#content').before(sPrintHorizontalLine).after(sPrintHorizontalLine);



	/* simulate placeholder functionality for browsers that don't support this
	 *
	 * @see http://www.hagenburger.net/BLOG/HTML5-Input-Placeholder-Fix-With-jQuery.html
	 * @see https://gist.github.com/379601
	 */

	var i = document.createElement("input");
	i.setAttribute("placeholder", "foo");
	if (!i.placeholder) {
		// if browser does not support placeholders
		$('[placeholder]').focus(function(){
			var input = $(this);
			if (input.val() == input.attr('placeholder')) {
				input.val('');
				input.removeClass('placeholder');
			}
		}).blur(function(){
			var input = $(this);
			if (input.val() == '' || input.val() == input.attr('placeholder')) {
				input.addClass('placeholder');
				input.val(input.attr('placeholder'));
			}
		}).blur().parents('form').submit(function(){
			$(this).find('[placeholder]').each(function(){
				var input = $(this);
				if (input.val() == input.attr('placeholder')) {
					input.val('');
				}
			})
		});
	}

    //td-classes for documentation tables
    //see ticket: http://forge.typo3.org/issues/35664
    //and second bugfix: https://forge.typo3.org/issues/37392
    $(".d .informaltable table td").each(function(){
        if($(this).parent("tr").children("td").length > 1)
            $(this).addClass("col"+($(this).index()+1));
    });

    //Beautify code blocks within the documentation
    //see ticket http://forge.typo3.org/issues/35278
    $('p + pre, div + pre').each(function(index,item){
        $(item).nextUntil('p', 'pre').each(function(i,sub){
            $(item).append("\n" + $(sub).html()); $(sub).remove();
        });
    });

});

/**
 * smartSearch, to switch the search depending on page content and
 * user interaction
 */
function smartSearch() {

		// init the selector
	$('.smart-search').click(function(event){
		return false;
		/*
		$(this).toggleClass('open');

		$('body').one('click',function() {
				$('.smart-search').removeClass('open');
		});

		event.stopPropagation();

		return false;
		*/

	});

	$('.smart-search ul a').click(function(){
		return false;
		/*
		switchSmartSearch($(this));
		return false;
		*/
	});

		// if a diff for the ter exists, switch to extension search
	if ($('.tx_terfe2').length) {
		return false;
		/*
		switchSmartSearch($('.smart-search').find('.i-ext').parent());
		return false;
		*/
	}


	}

/**
 * switch for the solr search
 *
 * @param currentSelection the element that should be selected
 */
function switchSmartSearch(currentSelection) {

		// change icon and selection
	currentSelection.parent().siblings().removeClass('current');
	currentSelection.parent().addClass('current');
	currentSelection.parents('.smart-search').find('.selector .ico').replaceWith(currentSelection.find('.ico').clone());


		// switch forms
	$('#solr-website-search').toggle();
	$('#solr-extension-search').toggle();

		// close selector
	$('.smart-search').removeClass('open');
}

function equalTabHeight() {
	$('.home-page-template .tab-panes').each(function(){
		var theight = 396;
		var padding = 36;

		if(($(this).outerHeight() - padding ) > theight) $('.home-page-template .tab-content').height($(this).outerHeight() - padding);
	});

	$('.h-a-block').each(function(){
		$(this).find('.h-a-item').height($(this).outerHeight());
	});
}
