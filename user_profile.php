<?php
require_once '_tools.php';

// On declare un tableau warning pour stocker les eventuellescmessages d'erreur
$warnings = [];

// pour aficher les champs existant pour le update
if (isset($_SESSION['user']['id'])){
    $query_users = $db->prepare('SELECT * FROM user WHERE id = ?');
    $query_users->execute([
        $_SESSION['user']['id']
    ]);
    $user = $query_users->fetch();
}

// si le formulaire insertion utilisateur/admin a ete envoyer
if(isset($_POST['update'])) {
    // on verifie que les champs obligatoires sont remplie
    if (empty($_POST['first_name']) OR empty($_POST['email'])) {
        $warnings['empty'] = 'Tous les chams sont obligatoire !';
    }
    else{
        //début de la chaîne de caractères de la requête de mise à jour
        $query_string = 'UPDATE user SET first_name = :first_name, last_name = :last_name, mail = :mail, biography = :biography';
        //début du tableau de paramètres de la requête de mise à jour
        $query_parameters = [
            'first_name' => htmlspecialchars(ucfirst($_POST['first_name'])),
            'last_name' => htmlspecialchars(strtoupper($_POST['last_name'])),
            'mail' => htmlspecialchars($_POST['email']),
            'biography' => htmlspecialchars($_POST['bio']),
            'id' => $_POST['user_id']
        ];

        // si le champs password n'est pas vide
        if (!empty($_POST['password'])) {
            // on verifie aue les champs password et password_confirm sont identiques
            if ($_POST['password'] != $_POST['password_confirm']){
                $warnings['no_match'] = 'Les mots de passe ne sonts pas identiques !';
            }
            else{
                //concaténation du champ password à mettre à jour
                $query_string .= ', password = :password ';
                //ajout du paramètre password à mettre à jour
                $query_parameters['password'] = md5($_POST['password']);
            }
        }

        //on fait le update uniquement dans les cas ou le champ password est vide ou qu'il n'est pas vide et qu'il soit identique au champ password_confirm
        if (empty($_POST['password']) OR !empty($_POST['password'] AND $_POST['password'] == $_POST['password_confirm'])){

            //fin de la chaîne de caractères de la requête de mise à jour
            $query_string .= ' WHERE id = :id';

            //préparation et execution de la requête avec la chaîne de caractères et le tableau de données
            $query = $db->prepare($query_string);
            $result = $query->execute($query_parameters);
        }
        //si enregistrement ok
        if(isset($result) AND $result){
            $_SESSION['user']['first_name'] = $_POST['first_name'];
            $_SESSION['message']['updated'] = 'Mise a jour efféctuée avec succes !';
            header('location:index.php');
            exit;
        }
    }
    // on pré remplie tout les input en ecrasant les variables $user pour pas que l'utilisateur doit tout re'ecrire
    $user['first_name'] = $_POST['first_name'];
    $user['last_name'] = $_POST['last_name'];
    $user['mail'] = $_POST['email'];
    $user['biography'] = $_POST['bio'];
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
                <!-- on verifie si le tableau $warnings n'est pas vide pour afficher les massages a l'interieur d'une condition pour gagner en performance-->
                <?php if (!empty($warnings)): ?>
                    <?php foreach($warnings as $key => $warning): ?>
                        <div class="bg-danger text-white p-2 mb-4">
                            <?= $warning; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <form action="user_profile.php" method="post" class="p-4 row flex-column">
                    <h4 class="pb-4 col-sm-8 offset-sm-2">Mise à jour des informations utilisateur</h4>
                    <div class="form-group col-sm-8 offset-sm-2">
                        <label for="first_name">Prénom <b class="text-danger">*</b></label>
                        <input class="form-control" value="<?= isset($user) ? htmlentities($user['first_name']) : '' ;?>" type="text" placeholder="Prénom" name="first_name" id="first_name" />
                    </div>
                    <div class="form-group col-sm-8 offset-sm-2">
                        <label for="last_name">Nom de famille</label>
                        <input class="form-control" value="<?= isset($user) ? htmlentities($user['last_name']) : '' ;?>" type="text" placeholder="Nom de famille" name="last_name" id="last_name" />
                    </div>
                    <div class="form-group col-sm-8 offset-sm-2">
                        <label for="email">Email <b class="text-danger">*</b></label>
                        <input class="form-control" value="<?= isset($user) ? htmlentities($user['mail']) : '';?>" type="email" placeholder="Email" name="email" id="email" />
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
                        <textarea class="form-control" name="bio" id="bio" placeholder="Ta vie Ton oeuvre..."><?= isset($user) ? htmlentities($user['biography']) : '';?></textarea>
                    </div>
                    <div class="text-right col-sm-8 offset-sm-2">
                        <p class="text-danger">* champs requis</p>
                        <input class="btn btn-success" type="submit" name="update" value="Valider" />
                    </div>
                    <!-- Si $user existe, on ajoute un champ caché contenant l'id de l'utilisateur à modifier pour la requête UPDATE -->
                    <?php if(isset($user)): ?>
                        <input type="hidden" name="user_id" value="<?= $user['id']?>" />
                    <?php endif; ?>
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