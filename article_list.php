<?php
require_once '_tools.php';

//si une catégorie est demandée
if (isset($_GET['category_id'])) {
	//selection des articles de la catégorie demandée
    $query_articles = $db->prepare('SELECT article.*, articles_categories.category_id
        FROM article INNER JOIN articles_categories
        ON article.id = articles_categories.article_id
        WHERE published_at <= NOW() AND articles_categories.category_id = ? AND is_published = 1 
        ORDER BY published_at DESC');
	$query_articles->execute([
	        $_GET['category_id']
    ]);

	//selection des informations de la catégorie demandée
	$query_category = $db->prepare('SELECT * FROM category WHERE id = ?');
    $query_category->execute([
            $_GET['category_id']
    ]);
	$selected_category = $query_category->fetch();

    //si l'id envoyé n'est pas un nombre entier, je redirige
	//si la catégorie n'a pas été trouvé je redirige
	if (!$selected_category OR !ctype_digit($_GET['category_id'])) {
		header('location:index.php');
		exit;
	}
}
else {
	//si pas decatégorie demandée j'affiche tous les articles
	$query_articles = $db->query('SELECT title, GROUP_CONCAT(name), published_at, summary, article.id, article.image 
        FROM article JOIN articles_categories
        ON article.id = articles_categories.article_id
        JOIN category 
        ON articles_categories.category_id = category.id
        WHERE published_at <= NOW() AND is_published = 1 
        GROUP BY article.id
        ORDER BY published_at DESC');
}
//puis je récupère les données selon la requête générée avant
$articles = $query_articles->fetchAll();
?>
<?php $title = (isset($_GET['category_id'])) ? $selected_category['name'] : 'Tous les articles'; $title .= ' - Mon premier blog !'; ?>
<?php ob_start(); ?>
<body class="article-list-body">
    <div class="container-fluid">
        <?php require 'partials/header.php'; ?>
        <div class="row my-3 article-list-content">
            <?php require('partials/nav.php'); ?>
            <main class="col-9">
                <section class="all_aricles">
                    <header>
                        <h1 class="mb-4"><?= (isset($_GET['category_id'])) ? $selected_category['name'] : 'Tous les articles' ;?></h1>
                    </header>
                    <?php if(isset($_GET['category_id'])): ?>
                        <div class="category-description mb-4">
                            <?= $selected_category['description']; ?>
                        </div>
                    <?php endif; ?>
                    <!-- s'il y a des articles à afficher -->
                    <?php if(!empty($articles)): ?>
                        <?php foreach($articles as $key => $article): ?>
                            <?php if( !isset($_GET['category_id']) OR ( isset($_GET['category_id']) AND $article['category_id'] == $_GET['category_id'] ) ): ?>
                            <article class="mb-4">
                                <h2><?php echo $article['title']; ?></h2>
                                <div class="row">
                                    <?php if( !empty($article['image'])): ?>
                                        <div class="col-12 col-md-4 col-lg-3">
                                            <img class="img-fluid" src="../assets/img/<?= $article['image']; ?>" alt="" />
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-12 col-md-8 col-lg-9">
                                        <?php if( !isset($_GET['category_id'])): ?>
                                            <b class="article-category">[<?= $article['GROUP_CONCAT(name)']; ?>]</b>
                                        <?php endif; ?>
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
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <!-- s'il n'y a pas d'articles à afficher -->
                    <?php else: ?>
                        <b class="article-category">Désolé il n’y a pas d’article pour le moment !!! 😔</b>
                    <?php endif; ?>
                </section>
            </main>
        </div>
        <?php require 'partials/footer.php'; ?>
    </div>
</body>
<?php $content = ob_get_clean(); ?>
<?php require 'template.php'; ?>