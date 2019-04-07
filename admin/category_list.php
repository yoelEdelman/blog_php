<?php
require_once '../_tools.php';

if(!isset($_SESSION['user']) OR $_SESSION['user']['is_admin'] == 0){
    header('location:../index.php');
    exit;
}

// si le param category_is existe on suprime tout de la table category la ou id est egale a l'id recu en get
if (isset($_GET['category_id'])){
    $query = $db->prepare('DELETE FROM category WHERE id = ?');
    $result = $query->execute([
            $_GET['category_id']
    ]);
}
// on recupere les infos de la table category pour les afficher
$query = $db->query('SELECT id, name, description FROM category');
$categories = $query->fetchAll();
?>
<?php $title = 'Administration des catégories - Mon premier blog !'; ?>
<?php ob_start(); ?>
    <body class="index-body">
        <div class="container-fluid">
            <?php require 'partials/header.php'; ?>
            <div class="row my-3 index-content">
                <?php require 'partials/nav.php'; ?>
                <section class="col-9">
                    <header class="pb-4 d-flex justify-content-between">
                        <h4>Liste des catégories</h4>
                        <a class="btn btn-primary" href="category_form.php">Ajouter une catégorie</a>
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
                                <th>Name</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($categories as $key => $category): ?>
                                <tr>
                                    <th><?= $category['id']; ?></th>
                                    <td><?= $category['name']; ?></td>
                                    <td><?= $category['description']; ?></td>
                                    <td>
                                        <a href="category_form.php?category_id=<?= $category['id']; ?>&action=edit" class="btn btn-warning">Modifier</a>
                                        <a onclick="return confirm('Are you sure?')" href="category_list.php?category_id=<?= $category['id']; ?>&action=delete" class="btn btn-danger">Supprimer</a>
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