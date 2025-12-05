<?php

////////////////////////////////////////////////////
//                                                //
//                                                //
//                  api pelote                    //
//                                                //
//                                                //
////////////////////////////////////////////////////


// Obtenir le numéro de semaine actuel
$date_jour = new DateTime();
#$date_jour->modify('-1 day');
$date_jour = $date_jour->format('Y-m-d');


if (isset($_GET['heure'], $_GET['pair'])){
  $heure = $_GET['heure'];
  $pair = $_GET['pair'];

// récupération de l'article selectionné
require '../assets/config.php';
define("WEB_EOL","<br/>");

// connexion bdd

// define("WEB_EOL","<br/>");
try { // --Connexion de la base de données --
$bdd = new PDO("mysql:host=$servname;dbname=$dbname", $user, $pass);
$bdd->exec("SET CHARACTER SET utf8");// Codage en UTF8
//-- Execution de la requête --

$reponse = $bdd->query("SELECT * FROM partie_de_demain WHERE date = '$date_jour' AND Heure = '$heure' AND id % 2 = $pair");
if (!$reponse){ // Traitement des erreurs de retours
throw new Exception('Problème de requête sur la table.');
}

$boucle = 0;
//-- Boucle de traitement de chaque personne --
while ($une_personne = $reponse->fetch()){

  $id = $une_personne['id'];
  $num_equipe = $une_personne['Numéro Equipe']; // Correction de la variable Kilométrage à Kilometrage
  $tel = $une_personne['Telephone']; // Correction de la variable Dénivelé à Denivele
  $heure = $une_personne['Heure'];
  $date = $une_personne['date'];
  $boucle += 1;

  if($boucle % 2 == 0){
    echo "$tel";

  }else{
    echo "$tel";
  }
}

$reponse->closeCursor();// Fermeture de la requête 
$bdd = NULL; //Fermeture de la connexion 
}
catch(Exception $e) {
	echo "<fieldset>";
	echo"<legend>Erreur d'accès à la base de données :</legend>".WEB_EOL;
	echo 'Erreur : '.$e->getMessage().WEB_EOL;
	echo"</fieldset>";
}
}else{
  echo "L'heure n'est pas présente dans les paramètres";
}
?>

