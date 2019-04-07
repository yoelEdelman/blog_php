<?php
require_once '_tools.php';

// si on a recu le parametre logout en url
if (isset($_GET['logout']) AND isset($_SESSION['user'])){

    // on deconecte
    unset($_SESSION["user"]);

    // On redirige le visiteur vers la page d'accueil
    header('location:index.php');
    exit;
}

// on recupere les infos des tables article et category avec jointure pour les aficher
$query = $db->query('SELECT title, name, published_at, summary, article.id, article.image 
    FROM category INNER JOIN article 
    ON category.id = article.category_id 
    WHERE published_at <= NOW() AND is_published = 1 
    ORDER BY published_at DESC LIMIT 3');
$homeArticles=$query->fetchAll();
?>
<?php $title = 'Homepage - Mon premier blog !'; ?>
<?php ob_start(); ?>
<body class="index-body">
    <div class="container-fluid">
        <?php require 'partials/header.php'; ?>
        <div class="row my-3 index-content">
            <?php require 'partials/nav.php'; ?>
            <main class="col-9">
                <?php if(isset($_SESSION['message'])) :?>
                    <?php foreach($_SESSION['message'] as $message): ?>
                        <div class="bg-success text-white p-2 mb-4">
                            <?= $message; ?>
                            <?php unset($_SESSION['message']) ;?>
                        </div>
                    <?php endforeach; ?>
                <?php endif ;?>
                <section class="latest_articles">
                    <header class="mb-4"><h1>Les 3 derniers articles :</h1></header>
                    <!-- les trois derniers articles -->
                    <?php foreach($homeArticles as $key => $article): ?>
                    <article class="mb-4">
                        <h2><?php echo $article['title']; ?></h2>
                        <div class="row">
                            <?php if( !empty($article['image'])): ?>
                                <div class="col-12 col-md-4 col-lg-3">
                                    <img class="img-fluid" src="../assets/img/<?= $article['image']; ?>" alt="" />
                                </div>
                            <?php endif; ?>
                            <div class="col-12 col-md-8 col-lg-9">
                                <b class="article-category">[<?= $article['name']; ?>]</b>
                                <span class="article-date">
                                    <!-- affichage de la date de l'article selon le format %A %e %B %Y -->
                                    <?= strftime("%A %e %B %Y", strtotime($article['published_at'])); ?>
                                </span>
                                <div class="article-content">
                                    <?= $article['summary']; ?>
                                </div>
                                <a href="article.php?article_id=<?= $article['id']; ?>">> Lire l'article</a>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </section>
                <div class="text-right">
                    <a href="article_list.php">> Tous les articles</a>
                </div>
            </main>
        </div>
        <?php require 'partials/footer.php'; ?>
    </div>
</body>
<?php $content = ob_get_clean(); ?>
<?php require 'template.php'; ?>