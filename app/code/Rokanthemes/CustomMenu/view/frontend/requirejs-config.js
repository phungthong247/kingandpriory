/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
	"shim": {
		"rokanthemes/custommenu": ["jquery"]
	},
	'paths': {
		'rokanthemes/custommenu': 'Rokanthemes_CustomMenu/js/custommenu'
	},
	"map":{
		"*":{
			'Magento_Catalog/js/product/breadcrumbs': 'Rokanthemes_CustomMenu/js/product/breadcrumbs'
		}
	}
};
