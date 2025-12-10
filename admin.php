<?php

require("config.php");
session_start();
$action = isset($_GET['action']) ? $_GET['action'] : "";
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";

if ($action != "login" && $action != "logout" && !$username) {
    login();
    exit;
}

switch ($action) {
    case 'login':
        login();
        break;
    case 'logout':
        logout();
        break;
    case 'newArticle':
        newArticle();
        break;
    case 'editArticle':
        editArticle();
        break;
    case 'deleteArticle':
        deleteArticle();
        break;
    case 'listCategories':
        listCategories();
        break;
    case 'newCategory':
        newCategory();
        break;
    case 'editCategory':
        editCategory();
        break;
    case 'deleteCategory':
        deleteCategory();
        break;
    case 'listSubcategories':
        listSubcategories();
        break;
    case 'newSubcategory':
        newSubcategory();
        break;
    case 'editSubcategory':
        editSubcategory();
        break;
    case 'deleteSubcategory':
        deleteSubcategory();
        break;
    default:
        listArticles();
}

/**
 * Авторизация пользователя (админа) -- установка значения в сессию
 */
function login() {

    $results = array();
    $results['pageTitle'] = "Admin Login | Widget News";

    if (isset($_POST['login'])) {

        // Пользователь получает форму входа: попытка авторизировать пользователя

        global $ADMIN_USERNAME, $ADMIN_PASSWORD;
        if ($_POST['username'] == $ADMIN_USERNAME
                && $_POST['password'] == $ADMIN_PASSWORD) {

          // Вход прошел успешно: создаем сессию и перенаправляем на страницу администратора
          global $ADMIN_USERNAME;
          $_SESSION['username'] = $ADMIN_USERNAME;
          header( "Location: admin.php");

        } else {

          // Ошибка входа: выводим сообщение об ошибке для пользователя
          $results['errorMessage'] = "Неправильный пароль, попробуйте ещё раз.";
          global $TEMPLATE_PATH;
          require( $TEMPLATE_PATH . "/admin/loginForm.php" );
        }

    } else {

      // Пользователь еще не получил форму: выводим форму
      global $TEMPLATE_PATH;
      require($TEMPLATE_PATH . "/admin/loginForm.php");
    }

}


function logout() {
    unset( $_SESSION['username'] );
    header( "Location: admin.php" );
}


function newArticle() {
	  
    $results = array();
    $results['pageTitle'] = "New Article";
    $results['formAction'] = "newArticle";

    if ( isset( $_POST['saveChanges'] ) ) {
//            echo "<pre>";
//            print_r($results);
//            print_r($_POST);
//            echo "<pre>";
//            В $_POST данные о статье сохраняются корректно
        // Пользователь получает форму редактирования статьи: сохраняем новую статью
        $article = new Article();
        $article->storeFormValues( $_POST );
//            echo "<pre>";
//            print_r($article);
//            echo "<pre>";
//            А здесь данные массива $article уже неполные(есть только Число от даты, категория и полный текст статьи)          
        $article->insert();
        header( "Location: admin.php?status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // Пользователь сбросил результаты редактирования: возвращаемся к списку статей
        header( "Location: admin.php" );
    } else {

        // Пользователь еще не получил форму редактирования: выводим форму
        $results['article'] = new Article;
        $data = Category::getList();
        $results['categories'] = $data['results'];
        
        // Load subcategories for the form
        $subcatData = Subcategory::getList();
        // Group subcategories by category
        $groupedSubcategories = array();
        foreach ($subcatData['results'] as $subcategory) {
            $categoryId = $subcategory->categoryId;
            if (!isset($groupedSubcategories[$categoryId])) {
                $groupedSubcategories[$categoryId] = array();
            }
            $groupedSubcategories[$categoryId][] = $subcategory;
        }
        $results['subcategories'] = $subcatData['results'];
        $results['groupedSubcategories'] = $groupedSubcategories;
        
        global $TEMPLATE_PATH;
        require( $TEMPLATE_PATH . "/admin/editArticle.php" );
    }
}


/**
 * Редактирование статьи
 * 
 * @return null
 */
function editArticle() {
	  
    $results = array();
    $results['pageTitle'] = "Edit Article";
    $results['formAction'] = "editArticle";

    if (isset($_POST['saveChanges'])) {

        // Пользователь получил форму редактирования статьи: сохраняем изменения
        if ( !$article = Article::getById( (int)$_POST['articleId'] ) ) {
            header( "Location: admin.php?error=articleNotFound" );
            return;
        }

        $article->storeFormValues( $_POST );
        $article->update();
        header( "Location: admin.php?status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // Пользователь отказался от результатов редактирования: возвращаемся к списку статей
        header( "Location: admin.php" );
    } else {

        // Пользвоатель еще не получил форму редактирования: выводим форму
        $results['article'] = Article::getById((int)$_GET['articleId']);
        $data = Category::getList();
        $results['categories'] = $data['results'];
        
        // Load subcategories for the form
        $subcatData = Subcategory::getList();
        // Group subcategories by category
        $groupedSubcategories = array();
        foreach ($subcatData['results'] as $subcategory) {
            $categoryId = $subcategory->categoryId;
            if (!isset($groupedSubcategories[$categoryId])) {
                $groupedSubcategories[$categoryId] = array();
            }
            $groupedSubcategories[$categoryId][] = $subcategory;
        }
        $results['subcategories'] = $subcatData['results'];
        $results['groupedSubcategories'] = $groupedSubcategories;
        
        global $TEMPLATE_PATH;
        require($TEMPLATE_PATH . "/admin/editArticle.php");
    }

}


function deleteArticle() {

    if ( !$article = Article::getById( (int)$_GET['articleId'] ) ) {
        header( "Location: admin.php?error=articleNotFound" );
        return;
    }

    $article->delete();
    header( "Location: admin.php?status=articleDeleted" );
}


function listArticles() {
    $results = array();
    
    $data = Article::getList(1000000,null,"publicationDate DESC",true);
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    
    $data = Category::getList();
    $results['categories'] = array();
    foreach ($data['results'] as $category) {
        $results['categories'][$category->id] = $category;
    }
    
    // Load subcategories for display
    $subcatData = Subcategory::getList();
    $results['subcategories'] = array();
    foreach ($subcatData['results'] as $subcategory) {
        $results['subcategories'][$subcategory->id] = $subcategory;
    }
    
    $results['pageTitle'] = "Все статьи";

    if (isset($_GET['error'])) { // вывод сообщения об ошибке (если есть)
        if ($_GET['error'] == "articleNotFound")
            $results['errorMessage'] = "Error: Article not found.";
    }

    if (isset($_GET['status'])) { // вывод сообщения (если есть)
        if ($_GET['status'] == "changesSaved") {
            $results['statusMessage'] = "Your changes have been saved.";
        }
        if ($_GET['status'] == "articleDeleted")  {
            $results['statusMessage'] = "Article deleted.";
        }
    }

    global $TEMPLATE_PATH;
    require($TEMPLATE_PATH . "/admin/listArticles.php" );
}

function listCategories() {
    $results = array();
    $data = Category::getList();
    $results['categories'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $results['pageTitle'] = "Article Categories";

    if ( isset( $_GET['error'] ) ) {
        if ( $_GET['error'] == "categoryNotFound" ) $results['errorMessage'] = "Error: Category not found.";
        if ( $_GET['error'] == "categoryContainsArticles" ) $results['errorMessage'] = "Error: Category contains articles. Delete the articles, or assign them to another category, before deleting this category.";
    }

    if ( isset( $_GET['status'] ) ) {
        if ( $_GET['status'] == "changesSaved" ) $results['statusMessage'] = "Your changes have been saved.";
        if ( $_GET['status'] == "categoryDeleted" ) $results['statusMessage'] = "Category deleted.";
    }

    global $TEMPLATE_PATH;
    require( $TEMPLATE_PATH . "/admin/listCategories.php" );
}
	  
	  
function newCategory() {

    $results = array();
    $results['pageTitle'] = "New Article Category";
    $results['formAction'] = "newCategory";

    if ( isset( $_POST['saveChanges'] ) ) {

        // User has posted the category edit form: save the new category
        $category = new Category;
        $category->storeFormValues( $_POST );
        $category->insert();
        header( "Location: admin.php?action=listCategories&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // User has cancelled their edits: return to the category list
        header( "Location: admin.php?action=listCategories" );
    } else {

        // User has not posted the category edit form yet: display the form
        $results['category'] = new Category;
        global $TEMPLATE_PATH;
        require( $TEMPLATE_PATH . "/admin/editCategory.php" );
    }

}


function editCategory() {

    $results = array();
    $results['pageTitle'] = "Edit Article Category";
    $results['formAction'] = "editCategory";

    if ( isset( $_POST['saveChanges'] ) ) {

        // User has posted the category edit form: save the category changes

        if ( !$category = Category::getById( (int)$_POST['categoryId'] ) ) {
          header( "Location: admin.php?action=listCategories&error=categoryNotFound" );
          return;
        }

        $category->storeFormValues( $_POST );
        $category->update();
        header( "Location: admin.php?action=listCategories&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // User has cancelled their edits: return to the category list
        header( "Location: admin.php?action=listCategories" );
    } else {

        // User has not posted the category edit form yet: display the form
        $results['category'] = Category::getById( (int)$_GET['categoryId'] );
        global $TEMPLATE_PATH;
        require( $TEMPLATE_PATH . "/admin/editCategory.php" );
    }

}


function deleteCategory() {

    if ( !$category = Category::getById( (int)$_GET['categoryId'] ) ) {
        header( "Location: admin.php?action=listCategories&error=categoryNotFound" );
        return;
    }

    $articles = Article::getList( 1000000, $category->id );

    if ( $articles['totalRows'] > 0 ) {
        header( "Location: admin.php?action=listCategories&error=categoryContainsArticles" );
        return;
    }

    $category->delete();
    header( "Location: admin.php?action=listCategories&status=categoryDeleted" );
}

function listSubcategories() {
    $results = array();
    $data = Subcategory::getList();
    $results['subcategories'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $results['pageTitle'] = "Article Subcategories";

    if ( isset( $_GET['error'] ) ) {
        if ( $_GET['error'] == "subcategoryNotFound" ) $results['errorMessage'] = "Error: Subcategory not found.";
    }

    if ( isset( $_GET['status'] ) ) {
        if ( $_GET['status'] == "changesSaved" ) $results['statusMessage'] = "Your changes have been saved.";
        if ( $_GET['status'] == "subcategoryDeleted" ) $results['statusMessage'] = "Subcategory deleted.";
    }

    global $TEMPLATE_PATH;
    require( $TEMPLATE_PATH . "/admin/listSubcategory.php" );
}
	  
	  
function newSubcategory() {

    $results = array();
    $results['pageTitle'] = "New Article Subcategory";
    $results['formAction'] = "newSubcategory";

    if ( isset( $_POST['saveChanges'] ) ) {

        // User has posted the subcategory edit form: save the new subcategory
        $subcategory = new Subcategory;
        $subcategory->storeFormValues( $_POST );
        $subcategory->insert();
        header( "Location: admin.php?action=listSubcategories&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // User has cancelled their edits: return to the subcategory list
        header( "Location: admin.php?action=listSubcategories" );
    } else {

        // User has not posted the subcategory edit form yet: display the form
        $results['subcategory'] = new Subcategory;
        $data = Category::getList();
        $results['categories'] = $data['results'];
        global $TEMPLATE_PATH;
        require( $TEMPLATE_PATH . "/admin/editSubcategory.php" );
    }

}


function editSubcategory() {

    $results = array();
    $results['pageTitle'] = "Edit Article Subcategory";
    $results['formAction'] = "editSubcategory";

    if ( isset( $_POST['saveChanges'] ) ) {

        // User has posted the subcategory edit form: save the subcategory changes

        if ( !$subcategory = Subcategory::getById( (int)$_POST['subcategoryId'] ) ) {
          header( "Location: admin.php?action=listSubcategories&error=subcategoryNotFound" );
          return;
        }

        $subcategory->storeFormValues( $_POST );
        $subcategory->update();
        header( "Location: admin.php?action=listSubcategories&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // User has cancelled their edits: return to the subcategory list
        header( "Location: admin.php?action=listSubcategories" );
    } else {

        // User has not posted the subcategory edit form yet: display the form
        $results['subcategory'] = Subcategory::getById( (int)$_GET['subcategoryId'] );
        $data = Category::getList();
        $results['categories'] = $data['results'];
        global $TEMPLATE_PATH;
        require( $TEMPLATE_PATH . "/admin/editSubcategory.php" );
    }

}


function deleteSubcategory() {

    if ( !$subcategory = Subcategory::getById( (int)$_GET['subcategoryId'] ) ) {
        header( "Location: admin.php?action=listSubcategories&error=subcategoryNotFound" );
        return;
    }

    $subcategory->delete();
    header( "Location: admin.php?action=listSubcategories&status=subcategoryDeleted" );
}


        