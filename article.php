<?php
require_once '_tools.php';

//si j'ai reçu article_id ET que c'est un nombre
if(isset($_GET['article_id']) AND ctype_digit($_GET['article_id'])){
    $query = $db->prepare('SELECT a.*, c.name as category_name 
        FROM article a INNER JOIN articles_categories a_c
        ON a.id = a_c.article_id
        INNER JOIN category c
        ON a_c.category_id = c.id
        WHERE a.id = ? ');
    $query->execute([
            $_GET['article_id']
    ]);
    $article = $query->fetch();
}
//si aucun article n'a été trouvé je redirige
if(!$article OR !ctype_digit($_GET['article_id'])){
    header('location:index.php');
    exit;
}
?>
<?php $title = $article['title'] . ' - Mon premier blog !'; ?>
<?php ob_start(); ?>
<body class="article-body">
    <div class="container-fluid">
        <?php require 'partials/header.php'; ?>
        <div class="row my-3 article-content">
            <?php require 'partials/nav.php'; ?>
            <main class="col-9">
                <article>
                    <?php if( !empty($article['image'])): ?>
                        <img class="pb-4 img-fluid" src="../assets/img/<?= $article['image']; ?>" alt="" />
                    <?php endif; ?>
                    <h1><?= $article['title']; ?></h1>
                    <b class="article-category">[<?= $article['category_name']; ?>]</b>
                    <span class="article-date">
                        <!-- affichage de la date de l'article selon le format %A %e %B %Y -->
                        <?= strftime("%A %e %B %Y", strtotime($article['published_at'])); ?>
                    </span>
                    <div class="article-content">
                        <?= $article['content']; ?>
                    </div>
                </article>
            </main>
        </div>
        <?php require 'partials/footer.php'; ?>
    </div>
</body>
<?php $content = ob_get_clean(); ?>
<?php require 'template.php'; ?>