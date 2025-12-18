(function ($) {
	'use strict';

	$(document).ready(function () {

		// Sections
		$('.content-section').each(function () {
			var container = $(this);

			if (container.prop('style')) {
				var background = container.css('background-image');

				container.prop('style', '');
				container.css('background-image', container.css('background-image') + ',' + background);
			}
		});

		// Buttons
		$('.button, button, input[type="submit"], .wp-block-button__link').each(function () {
			var button = $(this),
				color = button.css('background-color');

			if (button.css('box-shadow') === 'none' && color.indexOf('rgb(') === 0) {
				color = color.replace('rgb(', 'rgba(').replace(')', ',.35)');

				button.css('box-shadow', '0 5px 21px ' + color);
			}
		});

		// Columns
		if ($(window).width() < 768) {
			$('.wp-block-column:empty').remove();
		}

		// Slider
		hivetheme.getComponent('slider').each(function () {
			var container = $(this),
				slider = container.children('div:first'),
				settings = {
					prevArrow: '<i class="slick-prev fas fa-chevron-left"></i>',
					nextArrow: '<i class="slick-next fas fa-chevron-right"></i>',
				};

			if (container.data('type') === 'carousel') {
				var slides = slider.children('div');

				$.extend(settings, {
					centerMode: true,
					slidesToShow: Math.ceil($(window).width() / 420),
					slidesToScroll: 1,
					responsive: [{
						breakpoint: 1025,
						settings: {
							slidesToShow: 3,
						},
					},
					{
						breakpoint: 769,
						settings: {
							slidesToShow: 2,
						},
					},
					{
						breakpoint: 481,
						settings: {
							slidesToShow: 1,
							centerMode: false,
						},
					},
					],
				});

				if (settings['slidesToShow'] > slides.length) {
					settings['slidesToShow'] = slides.length;
				}
			} else {
				$.extend(settings, {
					fade: true,
					dots: true,
					customPaging: function () {
						return '<div role="tab" tabindex="0"></div>';
					},
				});
			}

			slider.slick(settings);

			container.imagesLoaded(function () {
				slider.slick('resize');
			});

			var observer = new MutationObserver(function () {
				slider.slick('resize');
			}).observe(slider.get(0), {
				subtree: true,
				childList: true,
				attributes: true,
				attributeFilter: ['src'],
			});
		});

		// Rating
		hivetheme.getComponent('circle-rating').each(function () {
			var container = $(this);

			container.circleProgress({
				size: 26,
				emptyFill: 'transparent',
				fill: container.css('color'),
				thickness: 3,
				animation: false,
				startAngle: -Math.PI / 2,
				reverse: true,
				value: parseFloat(container.data('value')) / 5,
			});
		});
	});
})(jQuery);
