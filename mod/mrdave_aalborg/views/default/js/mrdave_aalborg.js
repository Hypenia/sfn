define('mrdave_aalborg', function(require){
	var $ = require('jquery');

	$(document).on('click', '.elgg-menu-item-profile > a', function () {
		$('.elgg-menu-item-profile').toggleClass('opened');
		return false;
	});

	$(document).on('click', '.elgg-menu-topbar .elgg-menu-item-administration', function () {
		var $gear = $('.developers-gear span');
		if ($gear.length) {
			$gear.trigger('click');
			return false;
		}
	});

	$(document).on('click', '.mrdave-aalborg-login a', function(e) {
		e.preventDefault();

		var offset = $(this).offset();

		$('.mrdave-aalborg-account a').trigger('click');
		var $box = $('#login-dropdown-box');
		var height = $box.height();
		var width = $box.width();

		$box.css({
				left: (offset.left - width/2) + 'px',
				top: (offset.top - height - 50) + 'px'
			})
			.find('[name="username"]')
			.focus();

		return false;
	});
});
