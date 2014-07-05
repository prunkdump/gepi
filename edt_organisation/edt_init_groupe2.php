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
  
  // On récupère le répertoire temporaire de l'admin
  $tempdir = get_user_temp_directory();
  if (!$tempdir) {
    // On crée alors le répertoire adéquat
    $creer_rep = check_user_temp_directory();
    if (!$creer_rep) {
      trigger_error('Impossible d\'enregistrer le fichier sur le serveur, veuillez v&eacute;rifier les droits en &eacute;criture sur le r&eacute;pertoire /temp', E_USER_ERROR);
    }
  }

  //on ouvre le fichier d'export  
  $exportFile = fopen("../temp/".$tempdir."/g_group_2.csv", "r");
  
  //on créé le tableau qui va recevoir les informations 
  //TEMPORARY 
  $sql="CREATE TABLE IF NOT EXISTS tempElvGrp (
        line INT NOT NULL PRIMARY KEY,
	login VARCHAR(50) NOT NULL,
        id_groupe INT NOT NULL,
        id_classe INT NOT NULL,
        UNIQUE INDEX eleve_groupe (login,id_groupe)
	) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
  
  $create_table=mysqli_query($GLOBALS["mysqli"], $sql);
  $sql="TRUNCATE tempElvGrp;";
  $menage=mysqli_query($GLOBALS["mysqli"], $sql);

  
  

  /*------------------------------*/
  /*                              */
  /*   LECTURE DE L'EXPORT        */
  /*                              */
  /*------------------------------*/
  $exportLineNumber = 0;

  while( $exportLine = fgetcsv($exportFile, 1024, ";")  ){
  
    $exportLineNumber++;

    //on recupère données nécéssaires 
    $jour_exp = $exportLine[0];
    $heure_exp = $exportLine[1];
    $classe_exp = remplace_accents($exportLine[2], 'all_nospace'); //necessaire car renvoi concordance n'aid me par les '
    $matiere_exp = remplace_accents($exportLine[3], 'all_nospace'); 

    //$eleve_names = explode(',', $exportLine[4],2);
    //$eleve_exp = $eleve_names[0];
    $eleve_exp = $exportLine[4];

    $salle_exp = $exportLine[5];
    $groupe_exp = $exportLine[6];
    $regroup_exp = $exportLine[7];
    $frequence_exp = $exportLine[10];
  
    /***********************************/
    /* on recupère les correspondances */
    /***********************************/
  
    //le jour
    $jour = my_strtolower($jour_exp);
    
    //le creneau
    $creneau_desc = rechercheCreneauCsv2($heure_exp);
    $creneau = $creneau_desc["id_creneau"];
  
    //la classe et la matiere 
    $classe = renvoiConcordances($classe_exp, 2);
    $matiere = renvoiConcordances($matiere_exp, 3);

    //salle
    $salle = renvoiIdSalle($salle_exp);
  
    //la semaine
    $type_semaine = renvoiConcordances($frequence_exp, 10);
    if ($type_semaine == '' OR $type_semaine == 'erreur') {
      $type_semaine = '0';
    }

    /*************************************/
    /* on détermine le groupe avec l'edt */
    /*************************************/
    $sqlreq = "SELECT DISTINCT edtc.id_groupe FROM edt_cours edtc, j_groupes_classes jgc, j_groupes_matieres jgm  WHERE
                                                   edtc.id_salle = '".$salle."' AND
                                                   edtc.jour_semaine = '".$jour."' AND
                                                   edtc.id_definie_periode <= ".$creneau." AND
                                                   edtc.id_definie_periode + edtc.heuredeb_dec + (0.5*edtc.duree) > ".$creneau." AND
                                                   edtc.id_semaine = '".$type_semaine."' AND
                                                   edtc.id_groupe = jgc.id_groupe AND
                                                   edtc.id_groupe = jgm.id_groupe AND
                                                   jgc.id_classe = '".$classe."' AND
                                                   jgm.id_matiere = '".$matiere."' ;";

    $sqlres = mysqli_query($GLOBALS["mysqli"], $sqlreq) OR DIE('erreur dans la requête '.$sqlreq.' : '.mysqli_error($GLOBALS["mysqli"]));
   
    // on vérifie le groupe obtenu
    $nbreGrp = mysqli_num_rows($sqlres);
   

    //si le nombre de groupes est supérieur a 1 c'est qu'il y a deux cours du 
    //même type avec un changement en milieu de créneau. 
    //il y aura toujours une ambiguité avec EDT qui ne donne pas les cours 
    //a la demi heure. On vérifie juste que chaque cours 
    //a un autre créneau qui lui est caractéristique.
    if($nbreGrp > 1){
      
      //on récupère la liste des cours ou il y ambiguité
      $listreq = "SELECT DISTINCT edtc.id_groupe, edtc.id_definie_periode, edtc.duree, edtc.heuredeb_dec 
                  FROM edt_cours edtc, j_groupes_classes jgc, j_groupes_matieres jgm  WHERE
                                                   edtc.id_salle = '".$salle."' AND
                                                   edtc.jour_semaine = '".$jour."' AND
                                                   edtc.id_definie_periode <= ".$creneau." AND
                                                   edtc.id_definie_periode + edtc.heuredeb_dec + (0.5*edtc.duree) > ".$creneau." AND
                                                   edtc.id_semaine = '".$type_semaine."' AND
                                                   edtc.id_groupe = jgc.id_groupe AND
                                                   edtc.id_groupe = jgm.id_groupe AND
                                                   jgc.id_classe = '".$classe."' AND
                                                   jgm.id_matiere = '".$matiere."' ;";
      
      $listres = mysqli_query($GLOBALS["mysqli"], $listreq) OR DIE('erreur dans la requête '.$listreq.' : '.mysqli_error($GLOBALS["mysqli"]));
      
      
      //pour chaque cours on cherche un créneau caractéristique
      // c'est a dire un créneau ou il n'y a plus ambiguité
      //sinon on affiche un message d'avertissement
      while( $coursTab = mysqli_fetch_array($listres) ){
          
        $coursResolvFlag = 0;
        $coursStartPer = $coursTab['id_definie_periode'];
        $coursEndPer = $coursStartPer + $coursTab['heuredeb_dec'] + (0.5 * $coursTab['duree']); 

        while($coursStartPer < $coursEndPer){
          
          $resolvReq =  "SELECT DISTINCT edtc.id_groupe FROM edt_cours edtc, j_groupes_classes jgc, j_groupes_matieres jgm  WHERE
                                                   edtc.id_salle = '".$salle."' AND
                                                   edtc.jour_semaine = '".$jour."' AND
                                                   edtc.id_definie_periode <= ".$coursStartPer." AND
                                                   edtc.id_definie_periode + edtc.heuredeb_dec + (0.5*edtc.duree) > ".$coursStartPer." AND
                                                   edtc.id_semaine = '".$type_semaine."' AND
                                                   edtc.id_groupe = jgc.id_groupe AND
                                                   edtc.id_groupe = jgm.id_groupe AND
                                                   jgc.id_classe = '".$classe."' AND
                                                   jgm.id_matiere = '".$matiere."' ;";
            
          $resolvRes = mysqli_query($GLOBALS["mysqli"], $resolvReq) OR DIE('erreur dans la requête '.$resolvReq.' : '.mysqli_error($GLOBALS["mysqli"]));
          $resolvn = mysqli_num_rows($sqlres);
          
          if(mysqli_num_rows($resolvRes) == 1){
            $coursResolvFlag = 1;
            break;
          }

          //creneau suivant 
          $coursStartPer++;
          
        }
          
        if($coursResolvFlag != 1){
          echo "<b>Attention :</b> Plusieus cours correspondent &agrave; la ligne $exportLineNumber ! <br />";
          echo "V&eacute;rifiez qu'il a une autre cours pour remplir les groupes correspondants. <br />";
          break;
        }
      }
       
    } elseif($nbreGrp != 1){
      echo "<b>Erreur :</b> Le groupe n'a pas pu &ecirc;tre d&eacute;termin&eacute; dans la ligne $exportLineNumber !<br />";
      echo "V&eacute;rifiez l'emplois du temps pour le cours correspondant. <br />";
      $groupe = null;
      echo $sqlreq."<br />";
    } else {
      $sqltab = mysqli_fetch_array($sqlres);
      $groupe = $sqltab["id_groupe"];
    }
      
            


    /**********************************/
    /* on détermine l'élève concerné  */
    /**********************************/
    $sqlreq = "SELECT DISTINCT login FROM eleves WHERE _utf8 \"".$eleve_exp."\" LIKE CONCAT(eleves.nom,'".' '."',eleves.prenom,'".'%'."')  COLLATE utf8_general_ci;";
  
    $sqlres = mysqli_query($GLOBALS["mysqli"], $sqlreq)  OR DIE('erreur dans la requête '.$sqlreq.' : '.mysqli_error($GLOBALS["mysqli"]));
    $sqltab = mysqli_fetch_array($sqlres);

    //on vérifie l'élève
    $nbreElv = mysqli_num_rows($sqlres);
  
    if($nbreElv != 1){
      echo "<b>Erreur :</b> L'&eacute;l&egrave;ve $eleve_exp de la ligne $exportLineNumber n'a pas pu &ecirc;tre trouv&eacute; dans la base Gepi !<br />";
      echo "V&eacute;rifiez le nom de l'&eacute;l&egrave;ve dans les bases. <br />";
      echo $sqlreq."</br>";
      $eleve = null;
    } else {
      $eleve = $sqltab["login"];
    }
  
  
    /*****************************/
    /* on sauvegarde le résultat */
    /*****************************/
    if($groupe AND $eleve){
      
      $sqlreq = "INSERT IGNORE INTO tempElvGrp VALUES ('".$exportLineNumber."','".$eleve."','".$groupe."','".$classe."');" ;
    
      $sqlres = mysqli_query($GLOBALS["mysqli"], $sqlreq) OR DIE('erreur dans la requête '.$sqlreq.' : '.mysqli_error($GLOBALS["mysqli"]));
    }
  
  }

  /*------------------------------*/
  /*                              */
  /*   TRAITEMENT DES GROUPES     */
  /*                              */
  /*------------------------------*/
  
  
  /**********************************************/
  /* on efface les entrées de groupe existantes */
  /* pour les éléves traité ici                 */
  /**********************************************/

  //!!! on efface uniquement pour les périodes de l'élève correspondant à la "classe"
  $sqlreq = "DELETE jeg
             FROM j_eleves_groupes jeg,
                  (SELECT DISTINCT teg.login, jec.periode FROM tempElvGrp teg, j_eleves_classes jec WHERE teg.login = jec.login AND teg.id_classe = jec.id_classe) elvp
             WHERE jeg.login = elvp.login AND jeg.periode = elvp.periode;" ;

  
  $sqlres = mysqli_query($GLOBALS["mysqli"], $sqlreq) OR DIE('erreur dans la requête '.$sqlreq.' : '.mysqli_error($GLOBALS["mysqli"]));
  
  

  /************************************/
  /* on insère a nouveau les groupes  */
  /************************************/
 
  //!!! on ajoute uniquement pour les periode correspondant à la classe
  $sqlreq = "INSERT j_eleves_groupes 
             SELECT teg.login, teg.id_groupe, jec.periode FROM tempElvGrp teg, j_eleves_classes jec WHERE teg.login = jec.login AND teg.id_classe = jec.id_classe;";


  $sqlres = mysqli_query($GLOBALS["mysqli"], $sqlreq) OR DIE('erreur dans la requête '.$sqlreq.' : '.mysqli_error($GLOBALS["mysqli"]));

  

  /************************************/
  /* on efface les elèves qui sont    */
  /* dans des groupes dont leur       */
  /* classe n'en fait pas partie      */
  /************************************/
  
  $sqlreq = "DELETE jeg 
             FROM j_eleves_groupes jeg, j_eleves_classes jec 
             WHERE jeg.login = jec.login AND jeg.periode = jec.periode 
                   AND NOT EXISTS (SELECT * FROM j_groupes_classes jgc WHERE jgc.id_groupe = jeg.id_groupe AND jgc.id_classe = jec.id_classe);" ;

  
  $sqlres = mysqli_query($GLOBALS["mysqli"], $sqlreq) OR DIE('Erreur dans la requête '.$sqlreq.' : '.mysqli_error($GLOBALS["mysqli"]));

    
   

  // On affiche un lien pour revenir à la page de départ
  echo '<p style="color: green;">L\'initialisation des groupes est termin&eacute;e.</p>';
  echo '<p><a href="edt_init_groupe_csv2.php" style="border: 1px solid black;">Retour</a></p>';
  
  ?>

</div>  
  

<?php
// inclusion du footer
require("../lib/footer.inc.php");
?>