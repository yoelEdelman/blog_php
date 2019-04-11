<?php
require_once '../_tools.php';

if(!isset($_SESSION['user']) OR $_SESSION['user']['is_admin'] == 0){
    header('location:../index.php');
    exit;
}

// pour afficher les categories du menu select
$query = $db->query('SELECT * FROM category');
$categories = $query->fetchAll();

$warnings = [];

// pour aficher les champs existant pour le update
if (isset($_GET['article_id'])){
    $query_articles = $db->prepare('SELECT * FROM article WHERE id = ?');
    $query_articles->execute([
            $_GET['article_id']
    ]);
    $article = $query_articles->fetch();
}

// si le formulaire insertion ou de mise a jour d'article a ete envoyer
if(isset($_POST['save']) OR isset($_POST['update'])) {
    // on verifie que tout les champs obligatoires ne sont pas vide ( $_POST['title'] $_POST['date'] $_POST['categories'] )
    if (empty($_POST['title']) OR empty($_POST['published_at']) OR empty($_POST['categories'])) {
        $warnings['empty'] = 'Tous les chams sont obligatoire !';
    }
    else{
        if (isset($_FILES['image']) AND ($_FILES['image']['error'] === 0)) {

            $allowed_extensions = ['jpg', 'jpeg', 'gif', 'png'];

            $my_file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

            if(!$my_file_extension){
                $warnings['type'] = "Le type de fichier n'est pas conforme !";
            }
            elseif ($_FILES['image']['size'] > 1500000){
                $warnings['size'] = 'Votre fichier est trop lourd !';
            }
            else{
                do{
                    $new_file_name = rand().time() . $_FILES['image']['name'];
                    $destination = '../assets/img/' . $new_file_name;
                }while(file_exists($destination));

                $result = move_uploaded_file($_FILES['image']['tmp_name'], $destination);
            }
        }

        // si le tableau $warnings est vide ( qu'il ny a aucune erreur )
        if (empty($warnings)){
            // si $_POST['update'] existe il s'agit d'un nouvelle article donc on insert l'article en db
            if (isset($_POST['update'])){

                $query = $db->prepare('DELETE FROM articles_categories WHERE article_id = :article_id');
                $query->execute([
                    'article_id' => $_POST['article_id']
                ]);
                
                //début de la chaîne de caractères de la requête de mise à jour
                $query_string = 'UPDATE article SET published_at = :published_at, title = :title, summary = :summary, content = :content, is_published = :is_published ';
                //début du tableau de paramètres de la requête de mise à jour
                $query_parameters = [
                    'published_at' => htmlspecialchars($_POST['published_at']),
                    'title' => htmlspecialchars(ucfirst($_POST['title'])),
                    'summary' => htmlspecialchars($_POST['summary']),
                    'content' => htmlspecialchars($_POST['content']),
                    'is_published' => htmlspecialchars($_POST['is_published']),
                    'id' => $_POST['article_id']
                ];

                //uniquement si l'admin souhaite mettre a jour l'image
                if( isset($result) AND !empty($result)) {
                    //concaténation du champ image à mettre à jour
                    $query_string .= ', image = :image ';
                    //ajout du paramètre password à mettre à jour
                    $query_parameters['image'] = htmlspecialchars($new_file_name);
                    unlink('../assets/img/'.$article['image']);
                }

                //fin de la chaîne de caractères de la requête de mise à jour
                $query_string .= 'WHERE id = :id';

                //préparation et execution de la requête avec la chaîne de caractères et le tableau de données
                $query = $db->prepare($query_string);
                $result = $query->execute($query_parameters);

                $query = $db->prepare('INSERT INTO articles_categories (article_id, category_id)
                  VALUES (:article_id, :category_id)');
                foreach ($_POST['categories'] as $category){
                    $query->execute([
                        'article_id' => $_POST['article_id'],
                        'category_id' => htmlspecialchars($category)
                    ]);
                }

                if($result){
                    $_SESSION['message']['updated'] = 'Mise a jour efféctuée avec succes !';
                    header('location:article_list.php');
                    exit;
                }
            }
            // sinon il s'agit de $_POST['save'] donc on insert l'article en db
            else{
                $query_string = 'INSERT INTO article (published_at, title, summary, content, is_published ';
                $query_values = 'VALUES (:published_at, :title, :summary, :content, :is_published';
                $query_parameters = [
                    'published_at' => htmlspecialchars($_POST['published_at']),
                    'title' => htmlspecialchars(ucfirst($_POST['title'])),
                    'summary' => htmlspecialchars($_POST['summary']),
                    'content' => htmlspecialchars($_POST['content']),
                    'is_published' => htmlspecialchars($_POST['is_published'])
                ];

                //uniquement si l'admin souhaite ajouter une image
                if( isset($result) AND !empty($result)) {
                    //concaténation du champ image à ajouter
                    $query_string .= ', image ';
                    $query_values .= ', :image';
                    $query_parameters['image'] = htmlspecialchars($new_file_name);
                }

                $query_string .= ') ';
                $query_values .= ')';

                $query_string .= $query_values;

                //préparation et execution de la requête avec la chaîne de caractères et le tableau de données
                $query = $db->prepare($query_string);
                $result = $query->execute($query_parameters);

                $last_insert = $db -> lastInsertId();

                $query = $db->prepare('INSERT INTO articles_categories (article_id, category_id)
                  VALUES (:article_id, :category_id)');
                foreach ($_POST['categories'] as $category){
                    $query->execute([
                        'article_id' => $last_insert,
                        'category_id' => htmlspecialchars($category)
                    ]);
                }

                if($result){
                    $_SESSION['message']['inserted'] = 'Insertion efféctuée avec succes !';
                    header('location:article_list.php');
                    exit;
                }
            }
        }
    }
// on enrengistre tout les input pour preremplir le formulaire pour pas que l'admin doit tout r'ecrire en cas d'erreur
    $article['title'] = $_POST['title'];
    $article['published_at'] = $_POST['published_at'];
    $article['summary'] = $_POST['summary'];
    $article['content'] = $_POST['content'];
    $image = $_FILES['image'];
    $article['is_published'] = $_POST['is_published'];
// pour plus tard
//if (isset($_GET['article_id'])){
//    $category['id'] = $_POST['categories'];
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
                <!-- on verifie si le tableau $warnings n'est pas vide pour afficher les massages a l'interieur d'une condition pour gagner en performance-->
                <?php if (!empty($warnings)): ?>
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
                            <form action="article_form.php?article_id=<?= $article['id']; ?>&action=edit" method="post" enctype="multipart/form-data">
                        <?php else: ?>
                            <form action="article_form.php" method="post" enctype="multipart/form-data">
                        <?php endif; ?>
                            <div class="form-group">
                                <label for="title">Titre : <b class="text-danger">*</b></label>
                                <input class="form-control"  type="text" placeholder="Titre" name="title" id="title" value="<?= isset($article) ? htmlentities($article['title']) : '';?>" />
                            </div>
                            <div class="form-group">
                                <label for="published_at">Date de publication: <b class="text-danger">*</b></label>
                                <input class="form-control"  type="date" name="published_at" id="published_at" value="<?= isset($article) ? htmlentities($article['published_at']) : '';?>" />
                            </div>
                            <div class="form-group">
                                <label for="summary">Résumé :</label>
                                <input class="form-control"  type="text" placeholder="Résumé" name="summary" id="summary" value="<?= isset($article) ? htmlentities($article['summary']) : '';?>" />
                            </div>
                            <div class="form-group">
                                <label for="content">Contenu :</label>
                                <textarea class="form-control" name="content" id="content" placeholder="Contenu" ><?= isset($article) ? htmlentities($article['content']) : '';?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="image">Image :</label>
                                <input class="form-control" type="file" name="image" id="image" value="<?= $image; ?>"/>
                                <?php if (isset($_GET['article_id']) AND !empty($article['image'])): ?>
                                    <img class="img-fluid py-4" src="../assets/img/<?= $article['image']; ?>" alt="" />
                                    <input type="hidden" name="current-image" value="<?= $article['image'] ?>" />
                                <?php endif;?>
                            </div>
                            <div class="form-group">
                                <label for="categories"> Catégorie <b class="text-danger">*</b></label>
                                <select multiple class="form-control" name="categories[]" id="categories" multiple="multiple">
                                    <?php foreach($categories as $key => $category): ?>
                                        <?php
                                        $query = $db->prepare('SELECT * FROM articles_categories WHERE article_id = ? AND category_id = ?');
                                        $query->execute([
                                            $_GET['article_id'],
                                            $category['id']
                                        ]);
                                        $selected = $query->fetch();
                                        ?>
                                        <option value="<?= $category['id'];?>" <?= (isset($_GET['article_id']) AND !empty($selected)) ? "selected" : "" ; ?>><?= $category['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="is_published"> Publié ?</label>
                                <select class="form-control" name="is_published" id="is_published">
                                    <?php if (isset($_GET['article_id'])): ?>
                                            <?php if ($article['is_published'] == 0): ?>
                                                <option selected="selected" value="<?= $article['is_published']; ?>">Non</option>
                                                <option value="1">Oui</option>
                                            <?php else: ?>
                                                <option value="0">Non</option>
                                                <option selected="selected" value="<?= $article['is_published']; ?>">Oui</option>
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
                                    <input class="form-control" type="hidden" name="article_id" id="article_id" value="<?= $article['id']; ?>"/>
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
                            <form action="article_form.php?article_id=8&action=edit" method="post" enctype="multipart/form-data">
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