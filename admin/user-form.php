<?php
require_once '../_tools.php';

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
if(isset($_POST['save']) OR isset($_POST['update'])) {
    // on verifie que les champs obligatoires sont remplie
    if (empty($_POST['first_name']) OR empty($_POST['last_name']) OR empty($_POST['email']) OR empty($_POST['password']) ) {
        $warnings['empty'] = 'Tous les chams sont obligatoire !';
    }
    else{
        //  si $_POST['update'] existe donc on met a jour l'utilisateur en db
        if (isset($_POST['update'])){
            $query = $db->prepare('UPDATE user 
                SET first_name = ?, last_name = ?, mail = ?, password = ?, biography = ?, is_admin = ? 
                WHERE id = ?');
            $query->execute([
                htmlspecialchars(ucfirst($_POST['first_name'])),
                htmlspecialchars(strtoupper($_POST['last_name'])),
                htmlspecialchars($_POST['email']),
                htmlspecialchars(md5($_POST['password'])),
                htmlspecialchars($_POST['bio']),
                htmlspecialchars($_POST['is_admin']),
                $_POST['user_id']
            ]);
            $messages['updated'] = 'Mise a jour efféctuée avec succes !';
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
                $messages['inserted'] = 'Insertion efféctuée avec succes !';
            }
        }
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
                <!-- Si $user existe, chaque champ du formulaire sera pré-remplit avec les informations de l'utilisateur -->
                <form <?= (isset($_GET['user_id'])) ? 'action="user-form.php?user_id='.$user_id.'&action=edit"' : 'action="user-form.php"'; echo 'method="post" enctype="multipart/form-data"';?>>
<!--                --><?php //if (isset($_GET['user_id'])): ?>
<!--                    <form action="user-form.php?user_id=--><?//= $user_id; ?><!--&action=edit" method="post" enctype="multipart/form-data">-->
<!--                --><?php //else: ?>
<!--                    <form action="user-form.php" method="post" enctype="multipart/form-data">-->
<!--                --><?php //endif; ?>
                    <div class="form-group">
                        <label for="first_name">Prénom : <b class="text-danger">*</b></label>
                        <input class="form-control"  type="text" placeholder="Prénom" name="first_name" id="first_name" value="<?= $first_name; ?>"/>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Nom de famille : <b class="text-danger">*</b></label>
                        <input class="form-control"  type="text" placeholder="Nom de famille" name="last_name" id="last_name" value="<?= $last_name; ?>"/>
                    </div>
                    <div class="form-group">
                        <label for="email">Email : <b class="text-danger">*</b></label>
                        <input class="form-control"  type="email" placeholder="Email" name="email" id="email" value="<?= $mail; ?>"/>
                    </div>
                    <div class="form-group">
                        <label for="password">Password : <b class="text-danger">*</b></label>
                        <input class="form-control" type="password" placeholder="Mot de passe" name="password" id="password" value=""/>
                    </div>
                    <div class="form-group">
                        <label for="bio">biography :</label>
                        <textarea class="form-control" name="bio" id="bio" placeholder="Sa vie son oeuvre..."><?= $biography; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="is_admin"> Admin ?</label>
                        <select class="form-control" name="is_admin" id="is_admin">
                            <?php if (isset($_GET['user_id'])): ?>
                                    <?php if ($users['is_admin'] == 0): ?>
                                        <option selected="selected" value="<?= $users['is_admin']; ?>" >Non</option>
                                        <option value="1">Oui</option>
                                    <?php else: ?>
                                        <option value="0" >Non</option>
                                        <option selected="selected" value="<?= $users['is_admin']; ?>">Oui</option>
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
                            <input class="form-control" type="hidden" name="user_id" id="user_id" value="<?= $user_id; ?>"/>
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