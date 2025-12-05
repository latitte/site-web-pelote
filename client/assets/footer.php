<!-- footer -->

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Footer Design</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>

  <footer style="padding-block: 80px !important;background-color: #383A3A!important" class="footer">
     <div class="container" style="max-width: 10000px;background-color: #383A3A!important;box-shadow: none!important;display: block;text-align: left;margin-left: 25px;width: 90%;">
      <div class="row">
        <div class="footer-col">
          <h4>navigation</h4>
          <ul>
            <li><a href="./index.php">accueil</a></li>
            <li><a href="./equipes.php">les équipes</a></li>
            <li><a href="./calendrier.php">calendrier</a></li>
            <li><a href="./arbre_tournoi.php">classement</a></li>
            <li><a href="./inscription_joueur.php">s'inscrire</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>information</h4>
          <ul>
            <li><a href="#">site & logiciel réalisé par</a></li>
            <li><a href="https://latitte.titoanlalanne.fr">Titoan Lalanne</a></li>
            <li><a href="#"></a></li>
            <li><a href="#">Copyright ©</a></li>
            <li><a href="#">2025 tournoi-pelote.com</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>réseaux sociaux</h4>
          <div class="social-links">
            <!-- <a href="https://www.facebook.com/profile.php?id=100071389555409&locale=fr_FR"><i class="fab fa-facebook-f"></i></a> -->
            <a href="https://www.instagram.com/ilharreko_gazteria?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=="><i class="fab fa-instagram"></i></a>
            <!-- <a href="https://www.strava.com/clubs/usspa-cyclisme"><i class="fab fa-strava"></i></a> -->
            <a href="mailto:admin@tournoi-pelote.com"><i class="fa fa-envelope"></i></a>
          </div>
        </div>

        <div class="footer-col">
          <h4>information</h4>
          <ul>
            <li><a href="https://tournoi-pelote.com">je veux créer mon tournoi</a></li>
          </ul>
        </div>


      </div>
     </div>
  </footer>

</body>
</html>


<style>




.row{
  display: flex;
  flex-wrap: wrap;
}
ul{
  list-style: none;
}
.footer{
  background-color: #383A3A;
    padding: 70px 0;
}
.footer-col{
   width: 25%;
   padding: 0 15px;
}
.footer-col h4{
  font-size: 18px;
  color: #ffffff;
  text-transform: capitalize;
  margin-bottom: 35px;
  font-weight: 500;
  position: relative;
}
.footer-col h4::before{
  content: '';
  position: absolute;
  left:0;
  bottom: -10px;
  background-color: #09407b;
  height: 2px;
  box-sizing: border-box;
  width: 50px;
}
.footer-col ul li:not(:last-child){
  margin-bottom: 10px;
}
.footer-col ul li a{
  font-size: 16px;
  text-transform: capitalize;
  color: #ffffff;
  text-decoration: none;
  font-weight: 300;
  color: #bbbbbb;
  display: block;
  transition: all 0.3s ease;
}
.footer-col ul li a:hover{
  color: #ffffff;
  padding-left: 8px;
}
.footer-col .social-links a{
  display: inline-block;
  height: 40px;
  width: 40px;
  background-color: rgba(255,255,255,0.2);
  margin:0 10px 10px 0;
  text-align: center;
  line-height: 40px;
  border-radius: 50%;
  color: #ffffff;
  transition: all 0.5s ease;
}
.footer-col .social-links a:hover{
  color: #24262b;
  background-color: #ffffff;
}

/*responsive*/
@media(max-width: 767px){
  .footer-col{
    width: 50%;
    margin-bottom: 30px;
}
}
@media(max-width: 574px){
  .footer-col{
    width: 100%;
}
}

</style>