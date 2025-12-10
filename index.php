<?php

//phpinfo(); die();

require("config.php");

try {
    initApplication();
} catch (Exception $e) { 
    $results['errorMessage'] = $e->getMessage();
    global $TEMPLATE_PATH;
    require($TEMPLATE_PATH . "/viewErrorPage.php");
}


function initApplication()
{
    $action = isset($_GET['action']) ? $_GET['action'] : "";

    switch ($action) {
        case 'archive':
          archive();
          break;
        case 'viewArticle':
          viewArticle();
          break;
        default:
          homepage();
    }
}

function archive() 
{
    $results = [];
    
    $categoryId = ( isset( $_GET['categoryId'] ) && $_GET['categoryId'] ) ? (int)$_GET['categoryId'] : null;
    $subcategoryId = ( isset( $_GET['subcategoryId'] ) && $_GET['subcategoryId'] ) ? (int)$_GET['subcategoryId'] : null;
    
    $results['category'] = Category::getById( $categoryId );
    $results['subcategory'] = Subcategory::getById( $subcategoryId );
    
    // If both category and subcategory are specified, filter by subcategory
    if ($subcategoryId && $categoryId) {
        // Get articles by both category and subcategory
        $data = Article::getListBySubcategory($categoryId, $subcategoryId, 100000, 'publicationDate DESC', true);
    } else if ($categoryId) {
        // Get articles by category only
        $data = Article::getList( 100000, $results['category'] ? $results['category']->id : null, 'publicationDate DESC', true);
    } else {
        // Get all articles
        $data = Article::getList( 1000, null, 'publicationDate DESC', true);
    }
    
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    
    $data = Category::getList();
    $results['categories'] = array();
    
    foreach ( $data['results'] as $category ) {
        $results['categories'][$category->id] = $category;
    }
    
    if ($results['subcategory']) {
        $results['pageHeading'] = $results['subcategory']->name . " (" . ($results['category'] ? $results['category']->name : "All Categories") . ")";
    } else if ($results['category']) {
        $results['pageHeading'] = $results['category']->name;
    } else {
        $results['pageHeading'] = "Article Archive";
    }
    
    $results['pageTitle'] = $results['pageHeading'] . " | Widget News";
    
    global $TEMPLATE_PATH;
    require( $TEMPLATE_PATH . "/archive.php" );
}

/**
 * Загрузка страницы с конкретной статьёй
 * 
 * @return null
 */
function viewArticle()
{
    if ( !isset($_GET["articleId"]) || !$_GET["articleId"] ) {
      homepage();
      return;
    }

    $results = array();
    $articleId = (int)$_GET["articleId"];
    $results['article'] = Article::getById($articleId);
    
    if (!$results['article']) {
        throw new Exception("Статья с id = $articleId не найдена");
    }
    
    $results['category'] = Category::getById($results['article']->categoryId);
    
    // Получаем информацию о подкатегории, если она установлена
    $results['subcategory'] = null;
    if ($results['article']->subcategoryId) {
        $results['subcategory'] = Subcategory::getById($results['article']->subcategoryId);
    }
    
    $results['pageTitle'] = $results['article']->title . " | Простая CMS";
    
    global $TEMPLATE_PATH;
    require($TEMPLATE_PATH . "/viewArticle.php");
}

/**
 * Вывод домашней ("главной") страницы сайта
 */
function homepage() 
{
    $results = array();
    global $HOMEPAGE_NUM_ARTICLES;
    $data = Article::getList($HOMEPAGE_NUM_ARTICLES);
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    
    $data = Category::getList();
    $results['categories'] = array();
    foreach ( $data['results'] as $category ) { 
        $results['categories'][$category->id] = $category;
    } 
    
    $results['pageTitle'] = "Простая CMS на PHP";
    
//    echo "<pre>";
//    print_r($data);
//    echo "</pre>";
//    die();
    
    global $TEMPLATE_PATH;
    require($TEMPLATE_PATH . "/homepage.php");
    
}