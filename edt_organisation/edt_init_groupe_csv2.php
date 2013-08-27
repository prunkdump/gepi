
<?php

/**
 * Fichier qui permet de faire l'import des groupes depuis un logiciel propriétaire
 *

Copyright 2001, 2011 Thomas Belliard, Laurent Delineau, Edouard Hue, Eric Lebrun, Julien Jocal

This file is part of GEPI.

GEPI is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

GEPI is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with GEPI; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/



$titre_page = "Groupes - Initialisation";
$affiche_connexion = 'yes';
$niveau_arbo = 1;
// Initialisations files
require_once("../lib/initialisations.inc.php");
// fonctions edt
require_once("./fonctions_edt.php");
require_once("./edt_init_fonctions.php");
// Resume session
$resultat_session = $session_gepi->security_check();
if ($resultat_session == 'c') {
    header("Location:utilisateurs/mon_compte.php?change_mdp=yes&retour=accueil#changemdp");
    die();
} else if ($resultat_session == '0') {
    header("Location: ../logout.php?auto=1");
    die();
}


// Sécurité
/*
if (!checkAccess()) {
    header("Location: ../logout.php?auto=2");
    die();
}
*/

// Sécurité supplémentaire par rapport aux paramètres du module EdT / Calendrier
if (param_edt($_SESSION["statut"]) != "yes") {
    Die(ASK_AUTHORIZATION_TO_ADMIN);
}


// CSS et js particulier à l'EdT
$javascript_specifique = "edt_organisation/script/fonctions_edt";
$style_specifique = "templates/".NameTemplateEDT()."/css/style_edt";
// ==============PROTOTYPE===============
$utilisation_prototype = "ok";
// ============fin PROTOTYPE=============
// On insère l'entête de Gepi
require_once("../lib/header.inc.php");
// On ajoute le menu EdT
require_once("./menu.inc.php");
// +++++++++++++++++++GESTION DU RETOUR vers absences+++++++++++++++++
$_SESSION["retour"] = "edt_init_groupe_csv2";
// +++++++++++++++++++FIN GESTION RETOUR vers absences++++++++++++++++
//debug_var();
//$debug_init="n";



?>


<br />
<!-- la page du corps de l'EdT -->

<div id="lecorps">
  
  

  <?php
  
  // Initialisation des variables
  $csv_group_file = isset($_FILES["csv_group_file"]) ? $_FILES["csv_group_file"] : null;
  $recharger = isset($_POST["recharger"]) ? $_POST["recharger"] : null;
  $csv_group_file_found = null;
  $csv_group_file_opened = null;

  // On récupère le répertoire temporaire de l'admin
  $tempdir = get_user_temp_directory();
  if (!$tempdir) {
    // On crée alors le répertoire adéquat
    $creer_rep = check_user_temp_directory();
    if (!$creer_rep) {
      trigger_error('Impossible d\'enregistrer le fichier sur le serveur, veuillez vérifier les droits en écriture sur le répertoire /temp', E_USER_ERROR);
    }
  }


  // On efface le fichier si l'utilisateur le demande
  if ($recharger == 'oui' AND file_exists("../temp/".$tempdir."/g_group_2.csv")) {
    // On efface le fichier
    unlink("../temp/".$tempdir."/g_group_2.csv");
  }



  // On vérifie si on trouve le fichier d'export 
  if (file_exists("../temp/".$tempdir."/g_group_2.csv")) {
    // On peut initialiser les groupes 
    $csv_group_file_found = "oui";
    
  } else {
    //sinon  on essaye de le copier depuis le formulaire
    if($csv_group_file AND $csv_group_file['tmp_name'] != null ){
      $source_file = ($csv_group_file['tmp_name']);
      $dest_file = "../temp/".$tempdir."/g_group_2.csv";
      $res_copy = copy("$source_file" , "$dest_file");
      
      if($res_copy){
        $csv_group_file_found = "oui";
      }
    }
  }
  
  ?>
          
      


 
          
  <h3 class="red">Initialiser les groupes de Gepi &agrave; partir d'un export csv d'un logiciel propri&eacute;taire.</h3>

  <?php
  //-------------------------------------
  // Si le fichier n'est pas trouvé 
  // on affiche le formulaire de saisie
  //-------------------------------------
  if ($csv_group_file_found == "oui") {
    echo '<div style="display: none;">';
  }
  ?> 
  

  <p style="font-weight: bold;">UDT(profil concepteur) > Recherche > Emploi du temps > Rechercher. </p>
<p>
En cochant l'option "<b>El&egrave;ves en cours</b>" pour "<b>Une heure</b>", et en choisissant les divisions dont les groupes doivent être mis &agrave; jour.
<b> Attention </b> &agrave; ne pas mettre trop de divisions dans le fichier d'export car celui-ci sera rapidement tr&egrave;s volumineux. Vous pourrez de toute
 façon le recharger autant de fois que n&eacute;cessaire avec le formulaire ci-dessous. <b>Seul</b> les &eacute;l&egrave;ves pr&eacute;sents dans l'export
 seront replac&eacute;s dans les diff&eacute;rents groupes.
<p>
<br />

<p>
<b> Attention !!!</b> Les &eacute;l&egrave;ves qui apparaissent dans le fichier d'export seront extrait de tous les groupes avant replacement.
Il est donc indispensable que l'emplois du temps soit compl&egrave;tement renseign&eacute;. Chaque erreur conduira l'&eacute;l&egrave;ve
&agrave; ne pas être plac&eacute; dans le groupe correspondant.  
</p>
 
<br />

  <p>Il faut un export au format CSV sans la ligne d'ent&ecirc;te, avec les champs ci-dessous pr&eacute;sents, dans l'ordre, <b>s&eacute;par&eacute;s
	par un point-virgule et encadr&eacute;s par des guillemets ""</b>. Il peut être int&eacute;ressant &eacute;galement d'enlever les lignes contenants
les heures de repas et de permanence pour &eacute;viter des erreurs inutiles.</p><br />
  <p> &nbsp; &nbsp; <b>Jour;Heure;Div;Matière;El&egrave;ve;Salle;Groupe;Regroup;Eff;Mo;Freq;Aire;</b> </p>
    
<br />

   <p>Veuillez pr&eacute;ciser le nom complet du fichier d'export.</p>
  <form enctype="multipart/form-data" action="edt_init_groupe_csv2.php" method="post">
    <p><input type="file" size="80" name="csv_group_file" /></p>
    <p><input type="submit" value="Valider" /></p>
  </form>

															  
  <?php
  if ($csv_group_file_found == "oui") {
    echo '</div>';
  }
  ?>
 

  
  <?php 
  //----------------------------------------------
  // Si le fichier est trouvé on essaye de l'ouvrir 
  // et on affiche la coche de "recharge"
  //---------------------------------------------
  if ($csv_group_file_found == "oui") {
    echo '<p>';
    echo 'Le fichier a &eacute;t&eacute; upload&eacute; dans Gepi ';

    //on essaye de l'ouvrir 
    $exportFile = fopen("../temp/".$tempdir."/g_group_2.csv", "r");

    if (!$exportFile) {
      echo "mais n'a pas pu être ouvert. V&eacute;rifier les droits et la validit&eacute; de votre fichier d'export.";
    } else {
      $csv_group_file_opened = 'oui';
      echo "et a &eacute;t&eacute; correctement charg&eacute; pour l'initialisation des groupes.";
    }

    echo '</p>';
    echo '<p>';
    echo 'Vous pouvez si n&eacute;cessaire recharger le fichier d\'export avec le champs ci-dessous.'; 

    echo '</p> <br />';

    //On laisse la possibilité a l'utilisateur de changer de fichier 
    echo '<form name="recharger" action="edt_init_groupe_csv2.php" method="post">';
    echo '<p style="font-weight: bold;" title="Cochez pour recharger le fichier d\'export">';
    echo '<label for="rechargerExport">Recharger le fichier d\'export </label>';
    echo '<input type="checkbox" id="rechargerExport" name="recharger" value="oui" Recharger />';
    echo '<input type="submit" name="rechargerSend" value="Recharger" />';
    echo '</p>';
    echo '</form>';
    echo '</br >';
  }
      
  ?>
  



<?php
// On propose de lancer l'initialisation des groupes 
// si le fichier a été correstement chargé 
if($csv_group_file_opened == 'oui'){
  echo '<div class="mode_gr">';
  echo '<p> Vous pouvez maintenant lancer l\'initialisation des groupes &agrave; partir des informations ';
  echo 'contenues dans le fichier upload&eacute;.';
  
  echo '<form name="edtInitGroupCsv2" action="edt_init_groupe2.php" method="post">';
  echo '<input type="submit" name="initialiser" value="Initialiser les groupes" />';
  echo '</form>';

  echo '</div>';
}
?> 

</div>  

  






<?php
// inclusion du footer
require("../lib/footer.inc.php");
?>
															 
















