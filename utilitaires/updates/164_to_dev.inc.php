<?php
/**
 * Fichier de mise à jour de la version 1.6.4 à la version 1.6.5 par défaut
 *
 *
 * Le code PHP présent ici est exécuté tel quel.
 * Pensez à conserver le code parfaitement compatible pour une application
 * multiple des mises à jour. Toute modification ne doit être réalisée qu'après
 * un test pour s'assurer qu'elle est nécessaire.
 *
 * Le résultat de la mise à jour est du html préformaté. Il doit être concaténé
 * dans la variable $result, qui est déjà initialisé.
 *
 * Exemple : $result .= msj_ok("Champ XXX ajouté avec succès");
 *
 * @copyright Copyright 2001, 2013 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun
 * @license GNU/GPL,
 * @package General
 * @subpackage mise_a jour
 * @see msj_ok()
 * @see msj_erreur()
 * @see msj_present()
 */

$result .= "<h3 class='titreMaJ'>Mise à jour vers la version 1.6.5(dev) :</h3>";

/*
// Section d'exemple

$result .= "&nbsp;-> Ajout d'un champ 'tel_pers' à la table 'eleves'<br />";
$test_champ=mysqli_num_rows(mysqli_query($mysqli, "SHOW COLUMNS FROM eleves LIKE 'tel_pers';"));
if ($test_champ==0) {
	$query = mysqli_query("ALTER TABLE eleves ADD tel_pers varchar(255) NOT NULL default '';");
	if ($query) {
			$result .= msj_ok("Ok !");
	} else {
			$result .= msj_erreur();
	}
} else {
	$result .= msj_present("Le champ existe déjà");
}

$result .= "<br />";
$result .= "<strong>Ajout d'une table 'droits_acces_fichiers' :</strong><br />";
$test = sql_query1("SHOW TABLES LIKE 'droits_acces_fichiers'");
if ($test == -1) {
	$result_inter = traite_requete("CREATE TABLE IF NOT EXISTS droits_acces_fichiers (
	id INT(11) unsigned NOT NULL auto_increment,
	fichier VARCHAR( 255 ) NOT NULL ,
	identite VARCHAR( 255 ) NOT NULL ,
	type VARCHAR( 255 ) NOT NULL,
	PRIMARY KEY ( id )
	) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
	if ($result_inter == '') {
		$result .= msj_ok("SUCCES !");
	}
	else {
		$result .= msj_erreur("ECHEC !");
	}
} else {
	$result .= msj_present("La table existe déjà");
}
*/

$result .= "<br />";
$result .= "<strong>Ajout d'une table 'd_dates_evenements' :</strong><br />";
$test = sql_query1("SHOW TABLES LIKE 'd_dates_evenements'");
if ($test == -1) {
	$result_inter = traite_requete("CREATE TABLE d_dates_evenements (
			id_ev int(11) NOT NULL AUTO_INCREMENT, 
			type varchar(50) NOT NULL default '', 
			texte_avant TEXT NOT NULL default '', 
			texte_apres TEXT NOT NULL default '', 
			date_debut TIMESTAMP NOT NULL, 
			PRIMARY KEY  (id_ev)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
	if ($result_inter == '') {
		$result .= msj_ok("SUCCES !");
	}
	else {
		$result .= msj_erreur("ECHEC !");
	}
} else {
	$result .= msj_present("La table existe déjà");
}

$result .= "<br />";
$result .= "<strong>Ajout d'une table 'd_dates_evenements_classes' :</strong><br />";
$test = sql_query1("SHOW TABLES LIKE 'd_dates_evenements_classes'");
if ($test == -1) {
	$result_inter = traite_requete("CREATE TABLE d_dates_evenements_classes (
			id_ev_classe int(11) NOT NULL AUTO_INCREMENT, 
			id_ev int(11) NOT NULL, 
			id_classe int(11) NOT NULL default '0', 
			date_evenement TIMESTAMP NOT NULL, 
			PRIMARY KEY  (id_ev_classe)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
	if ($result_inter == '') {
		$result .= msj_ok("SUCCES !");
	}
	else {
		$result .= msj_erreur("ECHEC !");
	}
} else {
	$result .= msj_present("La table existe déjà");
}

$result .= "<br />";
$result .= "<strong>Ajout d'une table 'd_dates_evenements_utilisateurs' :</strong><br />";
$test = sql_query1("SHOW TABLES LIKE 'd_dates_evenements_utilisateurs'");
if ($test == -1) {
	$result_inter = traite_requete("CREATE TABLE d_dates_evenements_utilisateurs (
			id_ev int(11) NOT NULL, 
			statut varchar(20) NOT NULL, 
			KEY id_ev_u (id_ev,statut)) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
	if ($result_inter == '') {
		$result .= msj_ok("SUCCES !");
	}
	else {
		$result .= msj_erreur("ECHEC !");
	}
} else {
	$result .= msj_present("La table existe déjà");
}

?>