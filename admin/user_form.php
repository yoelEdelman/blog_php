<?php
require_once '../_tools.php';

if(!isset($_SESSION['user']) OR $_SESSION['user']['is_admin'] == 0){
    header('location:../index.php');
    exit;
}

$warnings = [];

// pour aficher les champs existant pour le update
if (isset($_GET['user_id'])){
    $query_users = $db->prepare('SELECT * FROM user WHERE id = ?');
    $query_users->execute([
            $_GET['user_id']
    ]);
    $user = $query_users->fetch();
}

// si le formulaire insertion utilisateur/admin a ete envoyé
if(isset($_POST['save']) OR isset($_POST['update'])) {
    // on verifie que les champs obligatoires sont remplie
    if (empty($_POST['first_name']) OR empty($_POST['last_name']) OR empty($_POST['email'])) {
        $warnings['empty'] = 'Tous les chams sont obligatoire !';
    }
    else{
        //  si $_POST['update'] existe donc on met a jour l'utilisateur en db
        if (isset($_POST['update'])){

            //début de la chaîne de caractères de la requête de mise à jour
            $query_string = 'UPDATE user SET first_name = :first_name, last_name = :last_name, mail = :mail, biography = :biography, is_admin = :is_admin ';
            //début du tableau de paramètres de la requête de mise à jour
            $query_parameters = [
                'first_name' => htmlspecialchars(ucfirst($_POST['first_name'])),
                'last_name' => htmlspecialchars(strtoupper($_POST['last_name'])),
                'mail' => htmlspecialchars($_POST['email']),
                'biography' => htmlspecialchars($_POST['bio']),
                'is_admin' => htmlspecialchars($_POST['is_admin']),
                'id' => $_POST['user_id']
            ];

            //uniquement si l'admin souhaite modifier le mot de passe
            if( !empty($_POST['password'])) {
                //concaténation du champ password à mettre à jour
                $query_string .= ', password = :password ';
                //ajout du paramètre password à mettre à jour
                $query_parameters['password'] = htmlspecialchars(md5($_POST['password']));
            }

            //fin de la chaîne de caractères de la requête de mise à jour
            $query_string .= 'WHERE id = :id';

            //préparation et execution de la requête avec la chaîne de caractères et le tableau de données
            $query = $db->prepare($query_string);
            $result = $query->execute($query_parameters);

            if($result){
                $_SESSION['message']['updated'] = 'Mise a jour efféctuée avec succes !';
                header('location:user_list.php');
                exit;
            }
        }
        else{
            // on verifie que le mail n'est pas deja dans la db
            $query = $db->prepare('SELECT mail FROM user WHERE mail = ?');
            $query->execute( array( $_POST['email']));
            $mail_exist = $query->fetch();
            if ($mail_exist){
                $warnings['exist'] = 'Cette email existe deja !';
            }
            else{
                // on insert en bdd
                $query = $db->prepare('INSERT INTO user (first_name, last_name, mail ,password, biography, is_admin)
                    VALUES (?, ?, ?, ?, ?, ?)');
                $query->execute([
                    htmlspecialchars(ucfirst($_POST['first_name'])),
                    htmlspecialchars(strtoupper($_POST['last_name'])),
                    htmlspecialchars($_POST['email']),
                    htmlspecialchars(md5($_POST['password'])),
                    htmlspecialchars($_POST['bio']),
                    htmlspecialchars($_POST['is_admin'])
                ]);
                $_SESSION['message']['inserted'] = 'Insertion efféctuée avec succes !';
                header('location:user_list.php');
                exit;
            }
        }
    }
    // on enrengistre tout les input pour pas que l'admin doit tout r'ecrire
    $user['first_name'] = $_POST['first_name'];
    $user['last_name'] = $_POST['last_name'];
    $user['mail'] = $_POST['email'];
    $user['biography'] = $_POST['bio'];
    $user['is_admin'] = $_POST['is_admin'];
}
?>
<?php $title = 'Administration des utilisateurs - Mon premier blog !'; ?>
<?php ob_start(); ?>
<body class="index-body">
    <div class="container-fluid">
        <?php require 'partials/header.php'; ?>
        <div class="row my-3 index-content">
            <?php require 'partials/nav.php'; ?>
            <section class="col-9">
                <header class="pb-3">
                    <!-- Si $user existe, on affiche "Modifier" SINON on affiche "Ajouter" -->
                    <h4><?= (isset($_GET['user_id'])) ? 'Modifier un utilisateur' : 'Ajouter un utilisateur' ;?></h4>
                </header>
                <!-- on verifie si le tableau $warnings n'est pas vide pour afficher les massages a l'interieur d'une condition pour gagner en performance-->
                <?php if (!empty($warnings)): ?>
                    <?php foreach($warnings as $key => $warning): ?>
                        <div class="bg-danger text-white p-2 mb-4">
                            <?= $warning; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <!-- Si $user existe, chaque champ du formulaire sera pré-remplit avec les informations de l'utilisateur -->
                <form <?= (isset($_GET['user_id'])) ? 'action="user_form.php?user_id='.$user['id'].'&action=edit"' : 'action="user_form.php"'; echo 'method="post" enctype="multipart/form-data"';?>>
                    <div class="form-group">
                        <label for="first_name">Prénom : <b class="text-danger">*</b></label>
                        <input class="form-control"  type="text" placeholder="Prénom" name="first_name" id="first_name" value="<?= isset($user) ? htmlentities($user['first_name']) : '';?>"/>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Nom de famille : <b class="text-danger">*</b></label>
                        <input class="form-control"  type="text" placeholder="Nom de famille" name="last_name" id="last_name" value="<?= isset($user) ? htmlentities($user['last_name']) : '';?>"/>
                    </div>
                    <div class="form-group">
                        <label for="email">Email : <b class="text-danger">*</b></label>
                        <input class="form-control"  type="email" placeholder="Email" name="email" id="email" value="<?= isset($user) ? htmlentities($user['mail']) : '';?>"/>
                    </div>
                    <div class="form-group">
                        <label for="password">Password : <b class="text-danger"><?= (isset($_GET['user_id'])) ? '(uniquement si vous souhaitez modifier votre mot de passe actuel)' : '*'; ?></b></label>
                        <input class="form-control" type="password" placeholder="Mot de passe" name="password" id="password" value=""/>
                    </div>
                    <div class="form-group">
                        <label for="bio">biography :</label>
                        <textarea class="form-control" name="bio" id="bio" placeholder="Sa vie son oeuvre..."><?= isset($user) ? htmlentities($user['biography']) : '';?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="is_admin"> Admin ?</label>
                        <select class="form-control" name="is_admin" id="is_admin">
                            <?php if (isset($_GET['user_id'])): ?>
                                    <?php if ($user['is_admin'] == 0): ?>
                                        <option selected="selected" value="<?= $user['is_admin']; ?>" >Non</option>
                                        <option value="1">Oui</option>
                                    <?php else: ?>
                                        <option value="0" >Non</option>
                                        <option selected="selected" value="<?= $user['is_admin']; ?>">Oui</option>
                                    <?php endif;?>
                            <?php else: ?>
                                <option selected="selected" value="0" >Non</option>
                                <option value="1" >Oui</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <!-- Si $user_id existe, on ajoute un champ caché contenant l'id de l'utilisateur à modifier pour la requête UPDATE -->
                    <?php if (isset($_GET['user_id'])): ?>
                        <div class="form-group">
                            <input class="form-control" type="hidden" name="user_id" id="user_id" value="<?= $user['id']; ?>"/>
                        </div>
                    <?php endif;?>
                    <div class="text-right">
                        <!-- Si $user existe, on affiche un lien de mise à jour -->
                        <?php if (isset($_GET['user_id'])): ?>
                            <p class="text-danger">* champs requis</p>
                            <input class="btn btn-success" type="submit" name="update" value="Mettre a jour" />
                        <?php else: ?>
                            <p class="text-danger">* champs requis</p>
                            <input class="btn btn-success" type="submit" name="save" value="Enregistrer" />
                        <?php endif; ?>
                    </div>
                    <!-- Si $user existe, on ajoute un champ caché contenant l'id de l'utilisateur à modifier pour la requête UPDATE -->
                </form>
            </section>
        </div>
    </div>
</body>
<?php $content = ob_get_clean(); ?>
<?php require 'template.php'; ?>