<?php
	/* Add context-menu for StockCenter, to be reused in multiple pages */

	print "  <nav id='context-menu' class='context-menu'>\n";
	print "    <ul class='context-menu__items'>\n";
	print "      <li>\n";
	print "        <span class='fa fa-header primary-header primary-header__title' id='contextheader'>Menu</span>\n";
	print "      </li>\n";
	print "      <li class='context-menu__item'>\n";
	print "        <a href='#' class='context-menu__link' data-action='manage'><i class='fa fa-manage'>Manage</i></a>\n";
	print "      </li>\n";
	print "      <li class='context-menu__item'>\n";
	print "        <a href='#' class='context-menu__link' data-action='yahoo'><i class='fa fa-yahoo'>Yahoo</i></a>\n";
	print "      </li>\n";
	print "      <li class='context-menu__item'>\n";
	print "        <a href='#' class='context-menu__link' data-action='compinvestor'><i class='fa fa-compinvestor'>Investor</i></a>\n";
	print "      </li>\n";
	print "      <li class='context-menu__item'>\n";
	print "        <a href='#' class='context-menu__link' data-action='compwebsite'><i class='fa fa-compwebsite'>Website</i></a>\n";
	print "      </li>\n";
	print "    </ul>\n";
	print "  </nav>\n";
	print "  <script src='javascript/contextmenu4.js'></script>\n";
?>
