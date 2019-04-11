<?php
	//récupération de la liste des catégories pour générer le menu
	$query = $db->query('SELECT  name ,id FROM category ');
	$categoriesList = $query->fetchAll();
?>
<nav class="col-3 py-2 categories-nav">
    <!-- Si une session utilisateur existe (utilisateur connécté) on affiche son prénom et un boutton pour se déconnecter -->
    <?php if (isset($_SESSION['user'])): ?>
        <p class="h2 text-center">Salut <?= $_SESSION['user']['first_name']; ?> !</p>
        <!-- ici le boutton de déconnexion est un lien allant vers l'index qui envoie le paramètre "logout" via URL -->
        <p>
            <a class="d-block btn btn-danger mb-4 mt-2" href="index.php?logout">Déconnexion</a>
            <?php if ($_SESSION['user']['is_admin'] != 1): ?>
                <!-- pour ne pas afficher le bouton profile quand on est sur la page user_profile !-->
                <?php if ($_SERVER['SCRIPT_FILENAME'] != $_SERVER['DOCUMENT_ROOT'].='/user_profile.php' ): ?>
                    <a class="d-block btn btn-warning mb-4 mt-2" href="user_profile.php">Profile</a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($_SESSION['user']['is_admin'] == 1): ?>
                <a class="d-block btn btn-warning mb-4 mt-2" href="admin/index.php">Administration</a>
            <?php endif; ?>
        </p>
    <!-- Sinon afficher un boutton de connexion -->
    <?php else: ?>
        <a class="d-block btn btn-primary mb-4 mt-2" href="login_register.php">Connexion / inscription</a>
    <?php endif; ?>
	<b>Catégories :</b>
	<ul>
		<li><a href="article_list.php">Tous les articles</a></li>
		<!-- liste des catégories -->
		<?php foreach($categoriesList as $key => $category): ?>
		    <li><a href="article_list.php?category_id=<?= $category['id']; ?>"><?= $category['name']; ?></a></li>
		<?php endforeach; ?>
	</ul>
</nav>