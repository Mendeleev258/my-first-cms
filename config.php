<?php
try {
    // Включаем полное отображение ошибок
    ini_set("display_errors", true);
    error_reporting(E_ALL);
    
    date_default_timezone_set("Europe/Moscow");  // http://www.php.net/manual/en/timezones.php
    
    // Настройки БД и остальных параметров будем хранить в переменных
    $DB_DSN = "mysql:host=" . (getenv('DB_HOST') ?: 'localhost') . ";dbname=" . (getenv('DB_NAME') ?: 'cms') . ";charset=utf8;";
    $DB_USERNAME = getenv('DB_USER') ?: 'root';
    $DB_PASSWORD = getenv('DB_PASS') ?: 'qwe123';
    
    // Объявление переменных, используемых в проекте
    $CLASS_PATH = "classes";
    $TEMPLATE_PATH = "templates";
    $HOMEPAGE_NUM_ARTICLES = 5;
    $ADMIN_USERNAME = "admin";
    $ADMIN_PASSWORD = "mypass";
    
    
    /* config-local.php больше не используется, все конфигурации теперь в config.php */
    
    // Подключаем Классы моделей (классы, отвечающие за работу с сущностями базы данных)
    require($CLASS_PATH . "/Article.php");
    require($CLASS_PATH . "/Category.php");

} catch (Exception $ex) {
    echo "При загрузке конфигураций возникла проблема!<br><br>";
    error_log($ex->getMessage());
}