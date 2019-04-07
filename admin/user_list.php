<?php
require_once '../_tools.php';

// si user_id est existe on delete tout de la table user ou id est egale a l'id recu en get
if (isset($_GET['user_id'])){
    $query = $db->prepare('DELETE FROM user WHERE id = ?');
    $result = $query->execute([
            $_GET['user_id']
    ]);
}
// on recupere les infos de la table user pour les afficher
$query = $db->query('SELECT id, first_name, last_name, mail, is_admin FROM user');
$users = $query->fetchAll();
?>
<?php $title = 'Administration des utilisateurs - Mon premier blog !'; ?>
<?php ob_start(); ?>
<body class="index-body">
    <div class="container-fluid">
        <?php require 'partials/header.php'; ?>
        <div class="row my-3 index-content">
            <?php require 'partials/nav.php'; ?>
            <section class="col-9">
                <header class="pb-4 d-flex justify-content-between">
                    <h4>Liste des utilisateurs</h4>
                    <a class="btn btn-primary" href="user_form.php">Ajouter un utilisateur</a>
                </header>
                <!-- si on a recu le parametre action en url-->
                <?php if ( isset($_GET['action'])): ?>
                    <div class="bg-success text-white p-2 mb-4">Suppression efféctuée.</div>
                <?php endif; ?>
                <?php if(isset($_SESSION['message'])) :?>
                    <?php foreach($_SESSION['message'] as $message): ?>
                        <div class="bg-success text-white p-2 mb-4">
                            <?= $message; ?>
                            <?php unset($_SESSION['message']) ;?>
                        </div>
                    <?php endforeach; ?>
                <?php endif ;?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Admin</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $key => $user): ?>
                            <tr>
                                <th><?= $user['id']; ?></th>
                                <td><?= $user['first_name']; ?></td>
                                <td><?= $user['last_name']; ?></td>
                                <td><?= $user['mail']; ?></td>
                                <td><?= ($user['is_admin'] == 0) ? 'non' : 'oui' ; ?></td>
                                <td>
                                    <a href="user_form.php?user_id=<?= $user['id']; ?>&action=edit" class="btn btn-warning">Modifier</a>
                                    <a onclick="return confirm('Are you sure?')" href="user_list.php?user_id=<?= $user['id']; ?>&action=delete" class="btn btn-danger">Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</body>
<?php $content = ob_get_clean(); ?>
<?php require 'template.php'; ?>