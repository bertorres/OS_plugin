<?php

function plugin_os_install(){
	
	global $DB, $LANG;
	
          $query_conf = "CREATE TABLE IF NOT EXISTS `glpi_plugin_os_config` (
          `ID` int(1) unsigned NOT NULL default '1',
          `name` varchar(255) NOT NULL default '0',
          `address` varchar(50) NOT NULL default '0',
          `phone` varchar(255) NOT NULL default '0',
          `city`  varchar(255) NOT NULL default '0',
          `textcolor`  varchar(7) NOT NULL default '#FFFFFF',
          `color`  varchar(7) NOT NULL default '#000000',
          `tarefas`  int(1) NOT NULL default '0',
          `custos`  int(1) NOT NULL default '0',
          `moeda`  varchar(1) NOT NULL default '€',
	  PRIMARY KEY (`id`))
	  ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

          $DB->query($query_conf) or die("error creating table glpi_plugin_os_config " . $DB->error());

        return true;
}
function plugin_os_uninstall(){

	global $DB;
	$drop_config = "DROP TABLE glpi_plugin_os_config";
	$DB->query($drop_config);
	return true;
}

?>