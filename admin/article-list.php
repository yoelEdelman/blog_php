<?php
require_once '../_tools.php';

// si le param article_id existe en url on suprime tout de la table article la ou id est egale a l'id recu en get
if (isset($_GET['article_id'])){
    $query = $db->prepare('DELETE FROM article WHERE id = ?');
    $result = $query->execute([
            $_GET['article_id']
    ]);
}
// on recupere les infos de la table article a afficher
$query = $db->query('SELECT id, title, is_published FROM article ORDER BY published_at DESC ');
$articles = $query->fetchAll();
?>
<?php $title = 'Administration des articles - Mon premier blog !'; ?>
<?php ob_start(); ?>
    <body class="index-body">
        <div class="container-fluid">
            <?php require 'partials/header.php'; ?>
            <div class="row my-3 index-content">
                <?php require 'partials/nav.php'; ?>
                <section class="col-9">
                    <header class="pb-4 d-flex justify-content-between">
                        <h4>Liste des articles</h4>
                        <a class="btn btn-primary" href="article-form.php">Ajouter un article</a>
                    </header>
                    <!-- si on a recu le parametre action en url-->
                    <?php if ( isset($_GET['action'])): ?>
                        <div class="bg-success text-white p-2 mb-4">Suppression efféctuée.</div>
                    <?php endif; ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Titre</th>
                                <th>Publié</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($articles as $key => $article): ?>
                                <tr>
                                    <th><?= $article['id']; ?></th>
                                    <td><?= $article['title']; ?></td>
                                    <td><?= ($article['is_published'] == 0) ? 'non' : 'oui' ;?></td>
                                    <td>
                                        <a href="article-form.php?article_id=<?= $article['id']; ?>&action=edit" class="btn btn-warning">Modifier</a>
                                        <a onclick="return confirm('Are you sure?')" href="article-list.php?article_id=<?= $article['id']; ?>&action=delete" class="btn btn-danger">Supprimer</a>
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