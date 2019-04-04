<?php
require_once '../_tools.php';

// pour afficher les categories du menu select
$query = $db->query('SELECT * FROM category');
$categories = $query->fetchAll();

// on crée 2 tableu pour les messages messages en vert et warnings en rouge
$messages = [];
$warnings = [];

// de base tous les les input ont la valeur null = vide ( pour eviter le message d'erreur undefined variable .... )
$title = NULL;
$category_id = NULL;
$published_at = NULL;
$summary = NULL;
$content = NULL;
$image = NULL;
$is_published = NULL;

// pour aficher les champs existant pour le update
if (isset($_GET['article_id'])){
    $query_articles = $db->prepare('SELECT * FROM article WHERE id = ?');
    $query_articles->execute([
            $_GET['article_id']
    ]);
    $articles = $query_articles->fetch();

    $title = $articles['title'];
    $category_id = $articles['category_id'];
    $published_at = $articles['published_at'];
    $summary = $articles['summary'];
    $content = $articles['content'];
    $current_image = $articles['image'];
    $is_published = $articles['is_published'];
    $article_id = $articles['id'];
}

// si le formulaire insertion ou de mise a jour d'article a ete envoyer
if(isset($_POST['save']) OR isset($_POST['update'])) {
    // on verifie que tout les champs obligatoires ne sont pas vide ( $_POST['title'] $_POST['date'] $_POST['categories'] )
    if (empty($_POST['title']) OR empty($_POST['published_at']) OR empty($_POST['categories'])) {
        $warnings['empty'] = 'Tous les chams sont obligatoire !';
    }
    else{
        // si files existe et qu'il retourne l'erreur 0 ( qu'il a bien ete uploaded )
        if (isset($_FILES['image']) AND ($_FILES['image']['error'] === 0)) {
            // si le fichiers est diferent de type image
            if(pathinfo($_FILES['image']['type'])['dirname'] != 'image'){
                $warnings['type'] = "Le type de fichier n'est pas conforme !";
            }
            //si le fichier est trop lourd
            elseif ($_FILES['image']['size'] > 1500000){
                $warnings['size'] = 'Votre fichier est trop lourd !';
            }
            else{
                // on re'nome le fichier et on insert le le nouveau nom dans le dossier img
                $rename_img = time() . $_FILES['image']['name'];
                move_uploaded_file($_FILES['image']['tmp_name'], '../assets/img/' . basename($rename_img));
            }
        }
        // si le tableau $warnings est vide ( qu'il ny a aucune erreur )
        if (empty($warnings)){
            // si $_POST['update'] existe il s'agit d'un nouvelle article donc on insert l'article en db
            if (isset($_POST['update'])){
                // si $rename_img est vide on met pas a jour la photo et on garde l'ancienne
                if (empty($rename_img)){
                    $query = $db->prepare('UPDATE article 
                        SET category_id = ?, published_at = ?, title = ?, summary = ?, content = ?, is_published = ? 
                        WHERE id = ?');
                    $query->execute([
                        htmlspecialchars($_POST['categories']),
                        htmlspecialchars($_POST['published_at']),
                        htmlspecialchars(ucfirst($_POST['title'])),
                        htmlspecialchars($_POST['summary']),
                        htmlspecialchars($_POST['content']),
                        htmlspecialchars($_POST['is_published']),
                        $_POST['article_id']
                    ]);
                }
                // sinon une nouvelle photo a ete inserer est on met a jour aussi l'image
                else{
                    $query = $db->prepare('UPDATE article 
                        SET category_id = ?, published_at = ?, title = ?, summary = ?, content = ?, image = ?, is_published = ? 
                        WHERE id = ?');
                    $query->execute([
                        htmlspecialchars($_POST['categories']),
                        htmlspecialchars($_POST['published_at']),
                        htmlspecialchars(ucfirst($_POST['title'])),
                        htmlspecialchars($_POST['summary']),
                        htmlspecialchars($_POST['content']),
                        htmlspecialchars($rename_img),
                        htmlspecialchars($_POST['is_published']),
                        $_POST['article_id']
                    ]);
                }
                $messages['updated'] = 'Mise a jour efféctuée avec succes !';
            }
            // sinon il s'agit de $_POST['save'] donc on insert l'article en db
            else{
                $query = $db->prepare('INSERT INTO article (category_id, published_at, title, summary, content, image, is_published) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)');
                $query->execute([
                    htmlspecialchars($_POST['categories']),
                    htmlspecialchars($_POST['published_at']),
                    htmlspecialchars(ucfirst($_POST['title'])),
                    htmlspecialchars($_POST['summary']),
                    htmlspecialchars($_POST['content']),
                    htmlspecialchars($rename_img),
                    htmlspecialchars($_POST['is_published'])
                ]);
                $messages['inserted'] = 'Insertion efféctuée avec succes !';
            }
        }
    }
// on enrengistre tout les input pour preremplir le formulaire pour pas que l'admin doit tout r'ecrire en cas d'erreur
    $title = $_POST['title'];
    $published_at = $_POST['published_at'];
    $summary = $_POST['summary'];
    $content = $_POST['content'];
    $image = $_FILES['image'];
    $is_published = $_POST['is_published'];
// pour plus tard
//if (isset($_GET['article_id'])){
//    $category_id = $_POST['categories'];
//}
}
?>
<?php $title = 'Administration des articles - Mon premier blog !'; ?>
<?php ob_start(); ?>
<body class="index-body">
    <div class="container-fluid">
        <?php require 'partials/header.php'; ?>
        <div class="row my-3 index-content">
            <?php require 'partials/nav.php'; ?>
            <section class="col-9">
                <header class="pb-3">
                    <!-- Si $article existe, on affiche "Modifier" SINON on affiche "Ajouter" -->
                    <h4><?= (isset($_GET['article_id'])) ? 'Modifier un article' : 'Ajouter un article' ;?></h4>
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
                <ul class="nav nav-tabs justify-content-center nav-fill" role="tablist">
                    <?php if (isset($_GET['article_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#infos" role="tab">Infos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " data-toggle="tab" href="#images" role="tab">Images</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#infos" role="tab">Infos</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane container-fluid active" id="infos" role="tabpanel">
                        <!-- Si $article existe, chaque champ du formulaire sera pré-remplit avec les informations de l'article -->
                        <?php if (isset($_GET['article_id'])): ?>
                            <form action="article-form.php?article_id=<?= $article_id; ?>&action=edit" method="post" enctype="multipart/form-data">
                        <?php else: ?>
                            <form action="article-form.php" method="post" enctype="multipart/form-data">
                        <?php endif; ?>
                            <div class="form-group">
                                <label for="title">Titre : <b class="text-danger">*</b></label>
                                <input class="form-control"  type="text" placeholder="Titre" name="title" id="title" value="<?= $title; ?>" />
                            </div>
                            <div class="form-group">
                                <label for="published_at">Date de publication: <b class="text-danger">*</b></label>
                                <input class="form-control"  type="date" name="published_at" id="published_at" value="<?= $published_at; ?>" />
                            </div>
                            <div class="form-group">
                                <label for="summary">Résumé :</label>
                                <input class="form-control"  type="text" placeholder="Résumé" name="summary" id="summary" value="<?= $summary; ?>" />
                            </div>
                            <div class="form-group">
                                <label for="content">Contenu :</label>
                                <textarea class="form-control" name="content" id="content" placeholder="Contenu" ><?= $content; ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="image">Image :</label>
                                <input class="form-control" type="file" name="image" id="image" value="<?= $image; ?>"/>
                                <?php if (isset($_GET['article_id']) AND !empty($current_image)): ?>
                                    <img class="img-fluid py-4" src="../assets/img/<?= $current_image; ?>" alt="" />
                                    <input type="hidden" name="current-image" value="<?= $current_image; ?>" />
                                <?php endif;?>
                            </div>
                            <div class="form-group">
                                <label for="categories"> Catégorie <b class="text-danger">*</b></label>
                                <select class="form-control" name="categories" id="categories" multiple="multiple">
                                    <?php foreach($categories as $key => $category): ?>
                                            <option value="<?= $category['id']; ?>" ><?= $category['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="is_published"> Publié ?</label>
                                <select class="form-control" name="is_published" id="is_published">
                                    <?php if (isset($_GET['article_id'])): ?>
                                            <?php if ($articles['is_published'] == 0): ?>
                                                <option selected="selected" value="<?= $articles['is_published']; ?>">Non</option>
                                                <option value="1">Oui</option>
                                            <?php else: ?>
                                                <option value="0">Non</option>
                                                <option selected="selected" value="<?= $articles['is_published']; ?>">Oui</option>
                                            <?php endif;?>
                                    <?php else: ?>
                                        <option selected="selected" value="0" >Non</option>
                                        <option value="1" >Oui</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <!-- Si $article existe, on ajoute un champ caché contenant l'id de l'article à modifier pour la requête UPDATE -->
                            <?php if (isset($_GET['article_id'])): ?>
                                <div class="form-group">
                                    <input class="form-control" type="hidden" name="article_id" id="article_id" value="<?= $article_id; ?>"/>
                                </div>
                            <?php endif;?>
                            <!-- Si $article existe, on affiche un lien de mise à jour -->
                            <div class="text-right">
                                <?php if (isset($_GET['article_id'])): ?>
                                    <p class="text-danger">* champs requis</p>
                                    <input class="btn btn-success" type="submit" name="update" value="Mettre a jour" />
                                <!-- Si $article n'existe pas, on affiche un lien pour enrengister -->
                                <?php else: ?>
                                    <p class="text-danger">* champs requis</p>
                                    <input class="btn btn-success" type="submit" name="save" value="Enregistrer" />
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <?php if (isset($_GET['article_id'])): ?>
                        <div class="tab-pane container-fluid " id="images" role="tabpanel">
                            <h5 class="mt-4">Ajouter une image :</h5>
                            <form action="article-form.php?article_id=8&action=edit" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="caption">Légende :</label>
                                    <input class="form-control" type="text" placeholder="Légende" name="caption" id="caption" />
                                </div>
                                <div class="form-group">
                                    <label for="image">Fichier :</label>
                                    <input class="form-control" type="file" name="image" id="image" />
                                </div>
                                <input type="hidden" name="article_id" value="8" />
                                <div class="text-right">
                                    <input class="btn btn-success" type="submit" name="add_image" value="Enregistrer" />
                                </div>
                            </form>
                            <div class="row">
                                <h5 class="col-12 pb-4">Liste des images :</h5>
                            </div>
                        </div>
                    <?php endif;?>
                </div>
            </section>
        </div>
    </div>
</body>
<?php $content = ob_get_clean(); ?>
<?php require 'template.php'; ?>