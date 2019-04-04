<?php
require_once '../_tools.php';

// on crée 2 tableu pour les messages messages en vert et warnings en rouge
$messages = [];
$warnings = [];

// de base tous les les input ont la valeur null = vide ( pour eviter le message d'erreur undefined variable .... )
$name = NULL;
$description = NULL;
$image = NULL;
$category_id = NULL;

// pour aficher les champs existant pour le update
if (isset($_GET['category_id'])){
    $query_categories = $db->prepare('SELECT * FROM category WHERE id = ?');
    $query_categories->execute([
            $_GET['category_id']
    ]);
    $categories = $query_categories->fetch();

    $name = $categories['name'];
    $description = $categories['description'];
    $current_image = $categories['image'];
    $category_id = $categories['id'];
}

// si le formulaire insertion ou update categorie a ete envoyer
if(isset($_POST['save']) OR isset($_POST['update'])) {
    // on verifie que les champs obligatoires sont remplie
    if (empty($_POST['name'])) {
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
        // si le tableau messages est vide ( qu'il ny a aucune erreur )
        if (empty($warnings)){
            //  si $_POST['update'] existe donc on met a jour la categorie en db
            if (isset($_POST['update'])){
                if (empty($rename_img)) {
                    $query = $db->prepare('UPDATE category SET name = ?, description = ?  WHERE id = ?');
                    $query->execute([
                        htmlspecialchars(ucfirst($_POST['name'])),
                        htmlspecialchars($_POST['description']),
                        $_POST['category_id']
                    ]);
                }
                else{
                    $query = $db->prepare('UPDATE category SET name = ?, description = ?, image = ? WHERE id = ?');
                    $query->execute([
                        htmlspecialchars(ucfirst($_POST['name'])),
                        htmlspecialchars($_POST['description']),
                        htmlspecialchars($rename_img),
                        $_POST['category_id']
                    ]);
                }
                $messages['updated'] = 'Mise a jour efectue avec succes !';
            }
            // sinon il s'agit d'une nouvelle categorie donc on insert la categorie en db
            else{
                // on verifie que le nom de categorie n'est pas deja dans la db
                $query = $db->prepare('SELECT name FROM category WHERE name = ?');
                $query->execute(array($_POST['name']));
                $category_exist = $query->fetch();
                if ($category_exist) {
                    $warnings['exist'] = 'Cette categorie existe deja !';
                }
                else{
                    // on insert en bdd
                    $query = $db->prepare('INSERT INTO category (name, description, image) VALUES (?, ?, ?)');
                    $query->execute([
                        htmlspecialchars(ucfirst($_POST['name'])),
                        htmlspecialchars($_POST['description']),
                        htmlspecialchars($rename_img)]);
                    $messages['inserted'] = 'Insertion efectue avec succes !';
                }
            }
        }
    }
    // on enrengistre tout les input pour preremplir le formulaire pour pas que l'admin doit tout r'ecrire en cas d'erreur
    $name = $_POST['name'];
    $description = $_POST['description'];
    $image = $_FILES['image'];
}
?>
<?php $title = 'Administration des catégories - Mon premier blog !'; ?>
<?php ob_start(); ?>
    <body class="index-body">
        <div class="container-fluid">
            <?php require 'partials/header.php'; ?>
            <div class="row my-3 index-content">
                <?php require 'partials/nav.php'; ?>
                <section class="col-9">
                    <header class="pb-3">
                        <!-- Si $category existe, on affiche "Modifier" SINON on affiche "Ajouter" -->
                        <h4><?= (isset($_GET['category_id'])) ? 'Modifier une catégorie' : 'Ajouter une catégorie'; ?></h4>
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
                    <!-- Si $category existe, chaque champ du formulaire sera pré-remplit avec les informations de la catégorie -->
                    <?php if (isset($_GET['category_id'])): ?>
                        <form action="category-form.php?category_id=<?= $category_id; ?>&action=edit" method="post" enctype="multipart/form-data">
                    <?php else: ?>
                        <form action="category-form.php" method="post" enctype="multipart/form-data">
                    <?php endif; ?>
                        <div class="form-group">
                            <label for="name">Nom : <b class="text-danger">*</b></label>
                            <input class="form-control"  type="text" placeholder="Nom" name="name" id="name" value="<?= $name ; ?>"/>
                        </div>
                        <div class="form-group">
                            <label for="description">Description : </label>
                            <input class="form-control"  type="text" placeholder="Description" name="description" id="description" value="<?= $description ; ?>"/>
                        </div>
                        <div class="form-group">
                            <label for="image">Image :</label>
                            <input class="form-control" type="file" name="image" id="image" value="<?= $image ; ?>"/>
                            <?php if (isset($_GET['category_id']) AND !empty($current_image)): ?>
                                <img class="img-fluid py-4" src="../assets/img/<?= $current_image; ?>" alt="" />
                                <input type="hidden" name="current-image" value="<?= $current_image; ?>" />
                            <?php endif;?>
                        </div>
                        <!-- Si $category existe, on ajoute un champ caché contenant l'id de la catégorie à modifier pour la requête UPDATE -->
                        <?php if (isset($_GET['category_id'])): ?>
                            <div class="form-group">
                                <input class="form-control" type="hidden" name="category_id" id="category_id" value="<?= $category_id ; ?>"/>
                            </div>
                        <?php endif;?>
                        <div class="text-right">
                            <!-- Si $category existe, on affiche un lien de mise à jour -->
<!--                            <p class="text-danger">* champs requis</p>-->
<!--                            <input class="btn btn-success" type="submit" name="update" value="--><?//= (isset($_GET['category_id'])) ? 'Mettre a jour' : 'Enregistrer' ;?><!--" />-->


                            <?php if (isset($_GET['category_id'])): ?>
                                <p class="text-danger">* champs requis</p>
                                <input class="btn btn-success" type="submit" name="update" value="Mettre a jour" />
                            <?php else: ?>
                                <p class="text-danger">* champs requis</p>
                                <input class="btn btn-success" type="submit" name="save" value="Enregistrer" />
                            <?php endif; ?>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </body>
<?php $content = ob_get_clean(); ?>
<?php require 'template.php'; ?>