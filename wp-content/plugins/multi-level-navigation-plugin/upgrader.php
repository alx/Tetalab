<?php
/* CRUDELY HACKED SCRIPT TO MAKE SURE THE PLUGIN UPGRADES CORRECTLY - THIS SCRIPT WILL ONLY RUN WHEN YOU ENTER THE ADMIN PANEL SO THAT IT DOESN'T SLOW DOWN THE PLUGIN */

foreach($suckerfish_menuitem as $key=> $menuitem) {
	switch ($menuitem){
		case "Pages Dropdown":update_option('suckerfish_menuitem1', 'Pages (single dropdown)');break;
		case "Categories Dropdown":update_option('suckerfish_menuitem1', 'Categories (single dropdown)');;break;
		case "Blogroll":update_option('suckerfish_menuitem1', 'Links - no categories');;break;
		case "Blogroll Dropdown":update_option('suckerfish_menuitem1', 'Links - no categories (single dropdown)');break;
		case "Archives (months)":update_option('suckerfish_menuitem1', 'Archives - months');;break;
		case "Archives (years)":update_option('suckerfish_menuitem1', 'Archives - years');break;
		case "Archives (months) Dropdown":update_option('suckerfish_menuitem1', 'Archives - months (single dropdown)');;break;
		case "Archives (years) Dropdown":update_option('suckerfish_menuitem1', 'Archives - years (single dropdown)');;break;
		case "Recent Comments":update_option('suckerfish_menuitem1', 'Recent Comments (single dropdown)');break;
		}
}
?>
