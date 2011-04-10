<?php

$q = $_GET["q"];
if (!$q) return;

$items = Admin_Core::getObjectDatabase()->selectCol('SELECT id AS ARRAY_KEY,name FROM tags WHERE name LIKE ?',"%$q%");

foreach ($items as $key=>$value) {
	echo "$value\n";
}
