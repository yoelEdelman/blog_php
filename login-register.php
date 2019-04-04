<?php
require_once '_tools.php';

// on crée 2 tableu pour les messages messages en vert et warnings en rouge
$messages = [];
$warnings = [];

// de base toutes les variables sonts null pour ne pas avoir de message undefined variable
$first_name = NULL;
$last_name = NULL;
$mail = NULL;
$password = NULL;
$password_confirm = NULL;
$biography = NULL;

// si le formulaire de connexion a ete envoyer
if(isset($_POST['login']) ) {
    // on verifie que les champs obligatoires sont remplie
    if (empty($_POST['email']) OR empty($_POST['password'])) {
        $warnings['empty'] = 'Tous les chams sont obligatoire !';
    }
    else{
        // on recupere toutes les infos de la table user la ou le mail et pwd est egale a celui recu en post
        $query = $db->prepare('SELECT * FROM user WHERE mail = ? AND password = ?');
        $query->execute([
                $_POST['email'],
            md5($_POST['password'])
        ]);
        $login_info = $query->fetch();

        // si l'email ou pwd ne correnspondent a celui de la db
        if (!$login_info) {
            $warnings['error_login'] = 'Mauvaise identifiant !';
        }
        // si l'email et pwd recu en post correnspondent a celui de la db on connecte l'utilisateur et on le redirige a la page d'acceuil
        else{
            $_SESSION['user']['first_name'] = $login_info['first_name'];
            $_SESSION['user']['is_admin'] = $login_info['is_admin'];
            $_SESSION['user']['id'];
            header('location:index.php');
            exit;
        }
    }
// on stock les infos rempli dans le formulaire pour preremplire le form en cas d'erreur
$mail = $_POST['email'];
$password = $_POST['password'];
}

// si le formulaire inscription utilisateur a ete envoyer
if(isset($_POST['register'])) {
    // on recupere l'email pour verifier que le mail n'est pas deja dans la db
    $query = $db->prepare('SELECT mail FROM user WHERE mail = ?');
    $query->execute([
            $_POST['email']
    ]);
    $mail_exist = $query->fetch();

    // on verifie que les champs obligatoires sont remplie
    if (empty($_POST['first_name']) OR empty($_POST['email']) OR empty($_POST['password']) OR empty($_POST['password_confirm']) ) {
        $warnings['empty'] = 'Tous les chams sont obligatoire !';
    }
    // si l'email existe en db
    elseif ($mail_exist){
        $warnings['exist'] = 'Cette email existe deja !';
    }
    // pwd et confirm_pwd ne sont pas egale
    elseif ($_POST['password'] != $_POST['password_confirm']){
        $warnings['no_match'] = 'Les mots de passe ne sonts pas identiques !';
    }
    else{
        // on insert en bdd
        $query_user = $db->prepare('INSERT INTO user (first_name, last_name, mail ,password, biography) VALUES (?, ?, ?, ?, ?)');
        $query_user->execute([
                htmlspecialchars(ucfirst($_POST['first_name'])),
            htmlspecialchars(strtoupper($_POST['last_name'])),
            htmlspecialchars($_POST['email']),
            htmlspecialchars(md5($_POST['password'])),
            htmlspecialchars($_POST['bio'])
        ]);
        $messages['inserted'] = 'Inscription efféctuée avec succes !';
        $_SESSION['user']['is_admin'] = 0; //PAS ADMIN !
        $_SESSION['user']['first_name'] = $_POST['first_name'];
        $messages['valid_login'] = 'Vous etes connecté ! ' . $_SESSION['first_name'];
        header('location:index.php');
        exit;
    }
// on stock les infos rempli dans le formulaire pour preremplire le form en cas d'erreur
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$mail = $_POST['email'];
$password = $_POST['password'];
$password_confirm = $_POST['password_confirm'];
$biography = $_POST['bio'];
}
?>
<?php $title = 'Login - Mon premier blog !'; ?>
<?php ob_start(); ?>
<!DOCTYPE html>
    <body class="article-body">
        <div class="container-fluid">
            <?php require 'partials/header.php'; ?>
            <div class="row my-3 article-content">
                <?php require('partials/nav.php'); ?>
                <main class="col-9">
                    <!-- on verifie si les 2 tableaux ne sont pas vide pour afficher les massages a l'interieur d'une condition pour gagner en performance-->
                    <?php if (!empty($messages)): ?>
                        <?php foreach($messages as $key => $message): ?>
                            <div class="bg-success text-white p-2 mb-4">
                                <?= $message; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif (!empty($warnings)): ?>
                        <?php foreach($warnings as $key => $warning): ?>
                            <div class="bg-danger text-white p-2 mb-4">
                                <?= $warning; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <!-- par defaut le form login s'affiche et si post_register existe alors on affiche le form register et pas le login (la meme chose en en bas)-->
                    <ul class="nav nav-tabs justify-content-center nav-fill" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link <?php if (!isset($_POST['register'])): ?>active<?php endif; ?>" data-toggle="tab" href="#login" role="tab">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if (isset($_POST['register'])): ?>active<?php endif; ?>" data-toggle="tab" href="#register" role="tab">Inscription</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane container-fluid <?php if (!isset($_POST['register'])): ?>active<?php endif; ?>" id="login" role="tabpanel">
                            <form action="login-register.php" method="post" class="p-4 row flex-column">
                                <h4 class="pb-4 col-sm-8 offset-sm-2">Connexion</h4>
                                <div class="form-group col-sm-8 offset-sm-2">
                                    <label for="email">Email <b class="text-danger">*</b></label>
                                    <input class="form-control" value="<?= $mail ; ?>" type="email" placeholder="Email" name="email" id="email" />
                                </div>
                                <div class="form-group col-sm-8 offset-sm-2">
                                    <label for="password">Mot de passe <b class="text-danger">*</b></label>
                                    <input class="form-control" value="<?= $password ; ?>" type="password" placeholder="Mot de passe" name="password" id="password" />
                                </div>
                                <div class="text-right col-sm-8 offset-sm-2">
                                    <p class="text-danger">* champs requis</p>
                                    <input class="btn btn-success" type="submit" name="login" value="Valider" />
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane container-fluid <?php if (isset($_POST['register'])): ?>active<?php endif; ?>" id="register" role="tabpanel">
                            <form action="login-register.php" method="post" class="p-4 row flex-column">
                                <h4 class="pb-4 col-sm-8 offset-sm-2">Inscription</h4>
                                <div class="form-group col-sm-8 offset-sm-2">
                                    <label for="first_name">Prénom <b class="text-danger">*</b></label>
                                    <input class="form-control" value="<?= $first_name; ?>" type="text" placeholder="Prénom" name="first_name" id="first_name" />
                                </div>
                                <div class="form-group col-sm-8 offset-sm-2">
                                    <label for="last_name">Nom de famille</label>
                                    <input class="form-control" value="<?= $last_name; ?>" type="text" placeholder="Nom de famille" name="last_name" id="last_name" />
                                </div>
                                <div class="form-group col-sm-8 offset-sm-2">
                                    <label for="email">Email <b class="text-danger">*</b></label>
                                    <input class="form-control" value="<?= $mail; ?>" type="email" placeholder="Email" name="email" id="email" />
                                </div>
                                <div class="form-group col-sm-8 offset-sm-2">
                                    <label for="password">Mot de passe <b class="text-danger">*</b></label>
                                    <input class="form-control" value="<?= $password; ?>" type="password" placeholder="Mot de passe" name="password" id="password" />
                                </div>
                                <div class="form-group col-sm-8 offset-sm-2">
                                    <label for="password_confirm">Confirmation du mot de passe <b class="text-danger">*</b></label>
                                    <input class="form-control" value="<?= $password_confirm; ?>" type="password" placeholder="Confirmation du mot de passe" name="password_confirm" id="password_confirm" />
                                </div>
                                <div class="form-group col-sm-8 offset-sm-2">
                                    <label for="bio">biography</label>
                                    <textarea class="form-control" name="bio" id="bio" placeholder="Ta vie Ton oeuvre..."><?= $biography; ?></textarea>
                                </div>
                                <div class="text-right col-sm-8 offset-sm-2">
                                    <p class="text-danger">* champs requis</p>
                                    <input class="btn btn-success" type="submit" name="register" value="Valider" />
                                </div>
                            </form>
                        </div>
                    </div>
                </main>
            </div>
            <?php require 'partials/footer.php'; ?>
            <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.3.1/jquery.fancybox.min.js"></script>
            <script src="assets/js/main.js"></script>
        </div>
    </body>
<?php $content = ob_get_clean(); ?>
<?php require 'template.php'; ?>