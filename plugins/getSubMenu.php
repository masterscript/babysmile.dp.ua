<?php

/**
 * @param current_page $page
 */
function getSubMenu($page) {

	$currentItem = $page;
	$menu = array();
	do {
		$currentItem = array_pop($currentItem->getParents());
		if (!$currentItem) {
			$menu[] = $page->getChildren('menu_item');
			break;
		}
		$children = $currentItem->getChildren('menu_item');
		if ($children) {
			$menu[] = $children;
		}
	} while ($currentItem->pid != 1);
	
	$menu = array_reverse($menu);
	
	if ($page->pid != 1) {
		$children = $page->getChildren('menu_item');
		if ($children) {
			$menu[] = $children;
		}
	}
	return $menu;
}