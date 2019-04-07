<?php
$query = $db->query('SELECT COUNT(*) FROM user');
$users_quantity = $query->fetch();

$query = $db->query('SELECT COUNT(*) FROM category');
$categories_quantity = $query->fetch();

$query = $db->query('SELECT COUNT(*) FROM article');
$articles_quantity = $query->fetch();
?>
<nav class="col-3 py-2 categories-nav">
    <p class="h2 text-center">Salut <?= $_SESSION['user']['first_name']; ?> !</p>
    <a class="d-block btn btn-danger mb-4 mt-2" href="../index.php?logout">Déconnexion</a>
    <a class="d-block btn btn-warning mb-4 mt-2" href="../index.php">Site</a>
    <ul>
        <li><a href="user_list.php">Gestion des utilisateurs (<?= $users_quantity[0]; ?>)</a></li>
        <li><a href="category_list.php">Gestion des catégories (<?= $categories_quantity[0]; ?>)</a></li>
        <li><a href="article_list.php">Gestion des articles (<?= $articles_quantity[0]; ?>)</a></li>
    </ul>
</nav>