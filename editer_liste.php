<?php
/**
 * Created by PhpStorm.
 * User: RoseRP
 * Date: 2017-11-16
 * Time: 13:01
 */
$niveau="./";

//Inclusion du fichier de connexion à la BD
include($niveau."inc/scripts/config.inc.php");
$strFichierTexte=file_get_contents($niveau.'js/objJSONMessages.json');
$jsonMessagesErreur=json_decode($strFichierTexte);

//Code erreur
$intCodeErreur='00000';
//Variable des messages d'erreur
$strMessage="";
//Liste de types d'erreurs
$arrTypeErreur=array();
//Liste de champs fautifs
$arrErreur=array();
//Liste d'erreurs à afficher
$arrMessagesErreur=array();
$arrMessagesErreur['nomListe']="";
$arrMessagesErreur['couleurs']="";

//Variable querystring de la liste
$id_liste="";
if(isset($_GET['id_liste'])){
    $id_liste=$_GET['id_liste'];
}
else {
    $id_liste = -1;
}
//Code présence en querystring
$strCodeOperation="";
if(isset($_GET['btn_modifier'])){
    $strCodeOperation="modifier";
}

///////////////////////////
//Requête de modification//
///////////////////////////
if($strCodeOperation=="modifier"){
    //Chercher les valeurs modifiés en querystring
    $arrInfoListe['id_liste']=$_GET['id_liste'];
    $arrInfoListe['nom_liste']=$_GET['nom_liste'];
    if($_GET['nom_liste']!=""){
        $arrInfoListe['nom_liste']=$_GET['nom_liste'];

        $regexNomListe="/^[A-Za-zÀ-ÿ0-9 '\-#]{1,50}$/";
        if(!preg_match($regexNomListe, $arrInfoListe['nom_liste'])){
            $intCodeErreur=-1;
            array_push($arrTypeErreur, "motif");
            array_push($arrErreur,"nomListe");
        }
    }else if($arrInfoListe['nom_liste']==""){
        $arrInfoListe['nom_liste']="";
        $intCodeErreur=-1;
        array_push($arrTypeErreur, "vide");
        array_push($arrErreur,"nomListe");
    }

    if(isset($_GET['id_couleur'])) {
        $arrInfoListe['id_couleur']=$_GET['id_couleur'];
    } else{
        $intCodeErreur=-1;
        $arrInfoListe['id_couleur']="";
        array_push($arrTypeErreur, "vide");
        array_push($arrErreur, "couleurs");
    }
    if($strCodeOperation=='modifier' && $intCodeErreur=='00000')
    {
        $strRequete="UPDATE t_liste SET nom_liste=:nom_liste, id_couleur=:id_couleur WHERE id_liste=:id_liste";
        $pdosResultat=$pdoConnexion->prepare($strRequete);
        $pdosResultat->bindParam(':nom_liste', $arrInfoListe['nom_liste']);
        $pdosResultat->bindParam(':id_couleur', $arrInfoListe['id_couleur']);
        $pdosResultat->bindParam(':id_liste', $id_liste);
        $pdosResultat->execute();
        //Récupération du CodeErreur
        $intCodeErreur=$pdosResultat->errorCode();
    }
}else{
    //Affichage informations de la liste
    $strRequete="SELECT id_liste, nom_liste, id_couleur FROM t_liste WHERE id_liste=:id_liste";

    $pdosResultat=$pdoConnexion->prepare($strRequete);
    $pdosResultat->bindParam(':id_liste', $id_liste);
    $pdosResultat->execute();

    $arrInfoListe=array();
    $ligne=$pdosResultat->fetch();
    $arrInfoListe['id_liste']=$ligne['id_liste'];
    $arrInfoListe['nom_liste']=$ligne['nom_liste'];
    $arrInfoListe['id_couleur']=$ligne['id_couleur'];
}

if($intCodeErreur!="00000"){
    for($cpt=0;$cpt<count($arrErreur);$cpt++){
        $champErreur=$arrErreur[$cpt];
        $arrMessagesErreur[$champErreur]=$jsonMessagesErreur->{$champErreur}->{'erreurs'}->{$arrTypeErreur[$cpt]};
    }
} else if ($strCodeOperation!= ""){
    header("Location:index.php?strCodeOperation=".$strCodeOperation);
}

//Information toutes couleurs pour une liste
$strRequete="SELECT id_couleur, nom_couleur_fr, hexadecimale
          FROM t_couleur";

$pdosResultat=$pdoConnexion->prepare($strRequete);
$pdosResultat->execute();

$arrCouleurs=[];
$ligneCouleurs=$pdosResultat->fetch();

for($cpt=0;$cpt<$pdosResultat->rowCount();$cpt++){
    $arrCouleurs[$cpt]['id_couleur']=$ligneCouleurs['id_couleur'];
    $arrCouleurs[$cpt]['nom_couleur_fr']=$ligneCouleurs['nom_couleur_fr'];
    $arrCouleurs[$cpt]['hexadecimale']=$ligneCouleurs['hexadecimale'];
    $ligneCouleurs=$pdosResultat->fetch();
}
$pdosResultat->closeCursor();


function ecrireChecked($valeurRadio,$nomRadio){
    $strChecked="";
    global $arrInfoListe;
    if($valeurRadio==$arrInfoListe[$nomRadio]){
        $strChecked='checked="checked"';
    }
    return $strChecked;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width"/>
    <title>Editer une liste - N'oublie pas!</title>
    <!--URL de base pour la navigation -->
    <base href="<?php echo $niveau?>"/>
    <link rel="stylesheet" href="css/styles_a.css">
</head>
<body>
<!--http://webaim.org/techniques/skipnav/-->
<a href="#contenu" class="lienEvitement focusable visuallyhidden">Allez au contenu</a>
<header>
    <?php include('inc/fragments/zone_utilisateur.php'); ?>
</header>
<main class="wrap">
    <?php include('inc/fragments/header.inc.php'); ?>
    <div class="contenu div_editerliste">
        <h1 class="#contenu">Éditer une liste</h1>
        <p class="nom_liste_courante"><?php //echo $arrInfoListe['nom_liste']; ?></p>
        <?php if($strCodeOperation!="modifier" || $intCodeErreur!="00000"){ ?>
            <form action="editer_liste.php" method="get" class="form_editerliste">
                <input type="hidden" name="id_liste" value="<?php echo $arrInfoListe['id_liste']; ?>">
                <div class="conteneurElementAValider">
                    <label for="nom_liste">Nom de la liste </label>
                    <input type="text" name="nom_liste" id="nom_liste" class="nom_liste" value="<?php echo $arrInfoListe['nom_liste']; ?>" pattern="^[A-Za-zÀ-ÿ0-9 '\-#]{1,50}$" required>
                    <span class="erreur">
                    <?php echo $arrMessagesErreur['nomListe']; ?>
                </span>
                </div>

                <fieldset class="conteneurElementAValider choixcouleurs">
                    <legend>Sélectionner la couleur de la liste <span class="ico_validation"></span></legend>
                    <ul class="couleurs">
                        <?php for($cpt=0;$cpt<count($arrCouleurs);$cpt++){ ?>
                            <li>
                                <input type="radio" name="id_couleur" class="visuallyhidden"
                                       id="rb_couleur<?php echo $arrCouleurs[$cpt]['id_couleur']; ?>"
                                       value="<?php echo $arrCouleurs[$cpt]['id_couleur']; ?>"
                                    <?php echo ecrireChecked($arrCouleurs[$cpt]['id_couleur'], 'id_couleur'); ?> required>
                                <label for="rb_couleur<?php echo $arrCouleurs[$cpt]['id_couleur'] ?>"
                                       data-couleur="<?php echo $arrCouleurs[$cpt]['nom_couleur_fr']; ?>" class="focusable">
                                    <img src="images/couleurs/<?php echo $arrCouleurs[$cpt]['hexadecimale'] ?>.png"
                                         alt="<?php echo $arrCouleurs[$cpt]['nom_couleur_fr'] ?>" class="imgRadius">
                                </label>
                            </li>
                        <?php } ?>
                    </ul>
                    <span class="erreur">
                    <span class="ico_erreur"></span>
                        <?php echo $arrMessagesErreur['couleurs']; ?>
                </span>
                </fieldset>
                <div class="boutons">
                    <button type="submit" id="btn_modifier" class="btn_modifier" name="btn_modifier">Enregistrer</button>
                    <a href="index.php" class="lien_annuler">Annuler</a>
                </div>
            </form>
        <?php }else{ ?>
            <a href="index.php">Retourner à l'accueil</a>
        <?php } ?>
    </div>
</main>
<?php
include ('inc/fragments/footer.inc.html');
include('inc/scripts/scripts.inc.php'); ?>
<script src="js/validationsMandatA.js"></script>
<script>
    $(document).ready(validationsMandatA.initialiser.bind(validationsMandatA));
</script>
</body>
</html>


