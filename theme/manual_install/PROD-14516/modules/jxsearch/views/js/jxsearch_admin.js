/*
 * 2017-2018 Zemez
 *
 * JX Search
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the General Public License (GPL 2.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/GPL-2.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the module to newer
 * versions in the future.
 *
 * @author     Zemez (Alexander Grosul)
 * @copyright  2017-2018 Zemez
 * @license    http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

$(document).ready(function() {
	jxsearch_ajax_switch();
	jxsearch_instant_switch();
	jxsearch_navigation_switch();

	$(document).on('change', 'input[name="PS_JXSEARCH_AJAX"]', function() {
		jxsearch_ajax_switch();
	});

	$(document).on('change', 'input[name="PS_JXINSTANT_SEARCH"]', function() {
		jxsearch_instant_switch();
	});

	$(document).on('change', 'input[name="PS_JXSEARCH_NAVIGATION"]', function() {
		jxsearch_navigation_switch();
	});
});

function jxsearch_check_status(setting_name) {
	return $('input[name="'+setting_name+'"]:checked').val();
}

function jxsearch_ajax_switch() {
	if (jxsearch_check_status('PS_JXSEARCH_AJAX')) {
		if (!jxsearch_check_status('PS_JXSEARCH_NAVIGATION')) {
			$('.form-group.ajax-block').not('.navigation-block').removeClass('hidden');
		} else {
			$('.form-group.ajax-block').removeClass('hidden');
		}
	} else {
		if (jxsearch_check_status('PS_JXINSTANT_SEARCH')) {
			$('.form-group.ajax-block').not('.instant-block').addClass('hidden');
		} else {
			$('.form-group.ajax-block').addClass('hidden');
		}
	}
}

function jxsearch_instant_switch() {
	if (jxsearch_check_status('PS_JXINSTANT_SEARCH')) {
		if (jxsearch_check_status('PS_JXSEARCH_AJAX')) {
			$('.form-group.instant-block').not('.ajax-block').removeClass('hidden');
		} else {
			$('.form-group.instant-block').removeClass('hidden');
		}
	} else {
		if (jxsearch_check_status('PS_JXSEARCH_AJAX')) {
			$('.form-group.instant-block').not('.ajax-block').addClass('hidden');
		} else {
			$('.form-group.instant-block').addClass('hidden');
		}
	}
}

function jxsearch_navigation_switch() {
	if (jxsearch_check_status('PS_JXSEARCH_AJAX') && jxsearch_check_status('PS_JXSEARCH_NAVIGATION')) {
		$('.form-group.navigation-block').removeClass('hidden');
	} else {
		$('.form-group.navigation-block').addClass('hidden');
	}
}