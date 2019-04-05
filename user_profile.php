<?php require_once '_tools.php'; ?>
<?php

$messages = [];
$warnings = [];

// de base les input sont null = vide
$first_name = NULL;
$last_name = NULL;
$mail = NULL;
$password = NULL;
$biography = NULL;
$is_admin = NULL;

// pour aficher les champs existant pour le update
if (isset($_GET['user_id'])){
    $query_users = $db->prepare('SELECT * FROM user WHERE id = ?');
    $query_users->execute([
        $_GET['user_id']
    ]);
    $users = $query_users->fetch();

    $first_name = $users['first_name'];
    $last_name = $users['last_name'];
    $mail = $users['mail'];
    $password = $users['password'];
    $biography = $users['biography'];
    $is_admin = $users['is_admin'];
    $user_id = $users['id'];
}
// si le formulaire insertion utilisateur/admin a ete envoyer
if(isset($_POST['update'])) {
    // on verifie que les champs obligatoires sont remplie
    if (empty($_POST['first_name']) OR empty($_POST['email'])) {
        $warnings['empty'] = 'Tous les chams sont obligatoire !';
    }
    else{
        $query_string = 'UPDATE user SET first_name = :first_name, last_name = :last_name, mail = :mail, biography = :biography';
        $query_parameters = [
            'first_name' => htmlspecialchars(ucfirst($_POST['first_name'])),
            'last_name' => htmlspecialchars(strtoupper($_POST['last_name'])),
            'mail' => htmlspecialchars($_POST['email']),
            'biography' => htmlspecialchars($_POST['bio']),
            'id' => $_POST['user_id']
        ];

        if (!empty($_POST['password'])) {
            //concaténation du champ password à mettre à jour
            $query_string .= ', password = :password ';
            //ajout du paramètre password à mettre à jour
            $query_parameters['password'] = md5($_POST['password']);
        }

        $queryString .= 'WHERE id = :id';

        $query = $db->prepare($query_string);
        $result = $query->execute($query_parameters);

        $messages['updated'] = 'Mise a jour efféctuée avec succes !';
    }

    // on enrengistre tout les input pour pas que l'admin doit tout r'ecrire
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $mail = $_POST['email'];
    $password = $_POST['password'];
    $biography = $_POST['bio'];
    $is_admin = $_POST['is_admin'];
}
?>
<?php $title = 'Profile - Mon premier blog !'; ?>
<?php ob_start(); ?>
<body class="article-body">
    <div class="container-fluid">
        <?php require 'partials/header.php'; ?>
        <div class="row my-3 article-content">
            <?php require('partials/nav.php'); ?>
            <main class="col-9">
                <form action="user_profile.php" method="post" class="p-4 row flex-column">
                    <h4 class="pb-4 col-sm-8 offset-sm-2">Mise à jour des informations utilisateur</h4>
                    <div class="form-group col-sm-8 offset-sm-2">
                        <label for="first_name">Prénom <b class="text-danger">*</b></label>
                        <input class="form-control" value="User" type="text" placeholder="Prénom" name="first_name" id="first_name" />
                    </div>
                    <div class="form-group col-sm-8 offset-sm-2">
                        <label for="last_name">Nom de famille</label>
                        <input class="form-control" value="TheBrickBox" type="text" placeholder="Nom de famille" name="last_name" id="last_name" />
                    </div>
                    <div class="form-group col-sm-8 offset-sm-2">
                        <label for="email">Email <b class="text-danger">*</b></label>
                        <input class="form-control" value="user@thebrickbox.net" type="email" placeholder="Email" name="email" id="email" />
                    </div>
                    <div class="form-group col-sm-8 offset-sm-2">
                        <label for="password">Mot de passe (uniquement si vous souhaitez modifier votre mot de passe actuel)</label>
                        <input class="form-control" value="" type="password" placeholder="Mot de passe" name="password" id="password" />
                    </div>
                    <div class="form-group col-sm-8 offset-sm-2">
                        <label for="password_confirm">Confirmation du mot de passe (uniquement si vous souhaitez modifier votre mot de passe actuel)</label>
                        <input class="form-control" value="" type="password" placeholder="Confirmation du mot de passe" name="password_confirm" id="password_confirm" />
                    </div>
                    <div class="form-group col-sm-8 offset-sm-2">
                        <label for="bio">Biographie</label>
                        <textarea class="form-control" name="bio" id="bio" placeholder="Ta vie Ton oeuvre...">utilisateur du blog</textarea>
                    </div>
                    <div class="text-right col-sm-8 offset-sm-2">
                        <p class="text-danger">* champs requis</p>
                        <input class="btn btn-success" type="submit" name="update" value="Valider" />
                    </div>
                </form>
            </main>
        </div>
        <?php require 'partials/footer.php'; ?>
        <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.3.1/jquery.fancybox.min.js"></script>
        <script src="js/main.js"></script>
    </div>
</body>
<?php $content = ob_get_clean(); ?>
<?php require 'template.php'; ?>