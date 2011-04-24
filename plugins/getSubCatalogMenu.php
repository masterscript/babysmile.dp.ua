<?php

/**
 * @param current_page $page
 */
function getSubCatalogMenu($page) {

	// fetch children of parent pages up to page with 'catatalog' template
	$currentItem = $page;
	$menu = array();
	do {
		$currentItem = array_pop($currentItem->getParents());
		$menu[] = $currentItem->getChildren('menu_item');
	} while ($currentItem->getTemplate() !== 'catalog');
	// }}}
	
	$menu = array_reverse($menu);
	
	// add children of current page
	if ($page->getTemplate() !== 'catalog') {
		$childrenType = db::getDB()->selectCol('SELECT DISTINCT type FROM ?_items WHERE pid = ?d', $page->getId());
		if (!in_array('good', $childrenType)) {
			$menu[] = $page->getChildren('menu_item');
		}
	}
	return $menu;
    
}
