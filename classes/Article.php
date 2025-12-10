<?php

/**
 * Класс для обработки статей
 */
class Article
{
    // Свойства
    /**
    * @var int ID статей из базы данны
    */
    public $id = null;

    /**
    * @var int Дата первой публикации статьи
    */
    public $publicationDate = null;

    /**
    * @var string Полное название статьи
    */
    public $title = null;

     /**
     * @var int ID категории статьи
     */
     public $categoryId = null;
     
     /**
     * @var int ID подкатегории статьи
     */
     public $subcategoryId = null;

    /**
    * @var string Краткое описание статьи
    */
    public $summary = null;

    /**
    * @var string HTML содержание статьи
    */
    public $content = null;
    
    /**
    * @var int Активна ли статья (0 или 1)
    */
    public $active = 1;  // Добавляем новое поле со значением по умолчанию 1
    
    /**
     * @var array IDs of authors for this article
     */
    public $authorIds = array();
    
    /**
     * @var array Author information (id and login) for this article
     */
    public $authors = array();
    
    /**
     * Создаст объект статьи
     * 
     * @param array $data массив значений (столбцов) строки таблицы статей
     */
    public function __construct($data=array())
    {
        
      if (isset($data['id'])) {
          $this->id = (int) $data['id'];
      }
      
      if (isset( $data['publicationDate'])) {
          $this->publicationDate = (string) $data['publicationDate'];     
      }

      //die(print_r($this->publicationDate));

      if (isset($data['title'])) {
          $this->title = $data['title'];        
      }
      
      if (isset($data['categoryId'])) {
          $this->categoryId = (int) $data['categoryId'];
      }
      
      if (isset($data['subcategoryId']) && $data['subcategoryId'] !== null && $data['subcategoryId'] !== '') {
          $this->subcategoryId = (int) $data['subcategoryId'];
      } else {
          $this->subcategoryId = null;
      }
      
      if (isset($data['summary'])) {
          $this->summary = $data['summary'];
      }
      
      if (isset($data['content'])) {
          $this->content = $data['content'];  
      }
      
      // Добавляем обработку поля active
      if (isset($data['active'])) {
          $this->active = (int) $data['active'];  
      }
    }


    /**
    * Устанавливаем свойства с помощью значений формы редактирования записи в заданном массиве
    *
    * @param assoc Значения записи формы
    */
    public function storeFormValues($params) {
        // Сохраняем все параметры
        $this->__construct($params);

        // Разбираем и сохраняем дату публикации
        if (isset($params['publicationDate'])) {
            $publicationDate = explode('-', $params['publicationDate']);
            if (count($publicationDate) == 3) {
                list($y, $m, $d) = $publicationDate;
                $this->publicationDate = mktime(0, 0, 0, $m, $d, $y);
            }
        }
        
        // Обрабатываем поле active (для checkbox)
        if (!isset($params['active'])) {
            $this->active = 0; // Если checkbox не отмечен
        }
        
        // Обрабатываем поле subcategory
        // For now, we'll store the value but won't save it to DB until column exists
        if (isset($params['subcategoryId'])) {
            $subcategoryId = (int) $params['subcategoryId'];
            // If subcategoryId is 0 (meaning "(none)" was selected), set it to null
            $this->subcategoryId = ($subcategoryId > 0) ? $subcategoryId : null;
        }
    }


    /**
     * Возвращаем объект статьи соответствующий заданному ID статьи
     *
     * @param int ID статьи
     * @return Article|false Объект статьи или false, если запись не найдена или возникли проблемы
     */
     public static function getById($id) {
         global $DB_DSN, $DB_USERNAME, $DB_PASSWORD;
         $conn = new PDO( $DB_DSN, $DB_USERNAME, $DB_PASSWORD );
         $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) "
                 . "AS publicationDate FROM articles WHERE id = :id";
         $st = $conn->prepare($sql);
         $st->bindValue(":id", $id, PDO::PARAM_INT);
         $st->execute();

         $row = $st->fetch();
         
         if ($row) {
             $article = new Article($row);
             
             // Получаем авторов статьи
             $sql = "SELECT u.id, u.login FROM users u
                     INNER JOIN article_authors aa ON u.id = aa.user_id
                     WHERE aa.article_id = :articleId";
             $st = $conn->prepare($sql);
             $st->bindValue(":articleId", $id, PDO::PARAM_INT);
             $st->execute();
             
             $authorIds = array();
             $authors = array();
             while ($authorRow = $st->fetch()) {
                 $authorIds[] = $authorRow['id'];
                 $authors[] = $authorRow;
             }
             $article->authorIds = $authorIds;
             $article->authors = $authors;
             
             $conn = null;
             return $article;
         }
         
         $conn = null;
     }


    /**
    * Возвращает все (или диапазон) объекты Article из базы данных
    *
    * @param int $numRows Количество возвращаемых строк (по умолчанию = 10000)
    * @param int $categoryId Вернуть статьи только из категории с указанным ID
    * @param string $order Столбец, по которому выполняется сортировка статей (по умолчанию = "publicationDate DESC")
    * @return Array|false Двух элементный массив: results => массив объектов Article; totalRows => общее количество строк
    */
    public static function getList($numRows=100000, 
        $categoryId=null, $order="publicationDate DESC", $includeInactive=false) {
        global $DB_DSN, $DB_USERNAME, $DB_PASSWORD;
        $conn = new PDO($DB_DSN, $DB_USERNAME, $DB_PASSWORD);
        $fromPart = "FROM articles";
        $whereClause = "";
        $conditions = [];
        
        // Добавляем условие для категории если указано
        if ($categoryId) {
            $conditions[] = "categoryId = :categoryId";
        }
        
        // Добавляем условие для активных статей, если не запрошены все
        if (!$includeInactive) {
            $conditions[] = "active = 1";
        }
        
        if (!empty($conditions)) {
            $whereClause = "WHERE " . implode(" AND ", $conditions);
        }
        
        $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) 
                AS publicationDate
                $fromPart $whereClause
                ORDER BY  $order  LIMIT :numRows";
        
        $st = $conn->prepare($sql);
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
    
        if ($categoryId) {
            $st->bindValue( ":categoryId", $categoryId, PDO::PARAM_INT);
        }
        
        $st->execute();
        $list = array();

        while ($row = $st->fetch()) {
            $article = new Article($row);
            
            // Получаем авторов статьи
            $sql = "SELECT u.id, u.login FROM users u
                    INNER JOIN article_authors aa ON u.id = aa.user_id
                    WHERE aa.article_id = :articleId";
            $st_authors = $conn->prepare($sql);
            $st_authors->bindValue(":articleId", $article->id, PDO::PARAM_INT);
            $st_authors->execute();
            
            $authorIds = array();
            $authors = array();
            while ($authorRow = $st_authors->fetch()) {
                $authorIds[] = $authorRow['id'];
                $authors[] = $authorRow;
            }
            $article->authorIds = $authorIds;
            $article->authors = $authors;
            
            $list[] = $article;
        }

        // Получаем общее количество статей, которые соответствуют критерию
        $sql = "SELECT COUNT(*) AS totalRows $fromPart $whereClause";
        $st = $conn->prepare($sql);
        if ($categoryId) {
            $st->bindValue( ":categoryId", $categoryId, PDO::PARAM_INT);
        }
        $st->execute();                    
        $totalRows = $st->fetch();
        $conn = null;
        
        return (array(
            "results" => $list, 
            "totalRows" => $totalRows[0]
            ) 
        );
}

    /**
     * Возвращает все (или диапазон) объекты Article из базы данных, отфильтрованные по подкатегории
     *
     * @param int $numRows Количество возвращаемых строк (по умолчанию = 10000)
     * @param int $categoryId ID категории
     * @param int $subcategoryId ID подкатегории
     * @param string $order Столбец, по которому выполняется сортировка статей (по умолчанию = "publicationDate DESC")
     * @param bool $includeInactive Включать ли неактивные статьи (по умолчанию = false)
     * @return Array|false Двух элементный массив: results => массив объектов Article; totalRows => общее количество строк
     */
    public static function getListBySubcategory($categoryId, $subcategoryId, $numRows=1000,
        $order="publicationDate DESC", $includeInactive=false) {
        global $DB_DSN, $DB_USERNAME, $DB_PASSWORD;
        $conn = new PDO($DB_DSN, $DB_USERNAME, $DB_PASSWORD);
        $fromPart = "FROM articles";
        $whereClause = "WHERE categoryId = :categoryId AND subcategoryId = :subcategoryId";
        $conditions = [];
        
        // Добавляем условие для активных статей, если не запрошены все
        if (!$includeInactive) {
            $conditions[] = "active = 1";
            $whereClause .= " AND " . implode(" AND ", $conditions);
        }
        
        $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate)
                AS publicationDate
                $fromPart $whereClause
                ORDER BY  $order  LIMIT :numRows";
        
        $st = $conn->prepare($sql);
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
        $st->bindValue(":categoryId", $categoryId, PDO::PARAM_INT);
        $st->bindValue(":subcategoryId", $subcategoryId, PDO::PARAM_INT);
        
        $st->execute();
        $list = array();

        while ($row = $st->fetch()) {
            $article = new Article($row);
            
            // Получаем авторов статьи
            $sql = "SELECT u.id, u.login FROM users u
                    INNER JOIN article_authors aa ON u.id = aa.user_id
                    WHERE aa.article_id = :articleId";
            $st_authors = $conn->prepare($sql);
            $st_authors->bindValue(":articleId", $article->id, PDO::PARAM_INT);
            $st_authors->execute();
            
            $authorIds = array();
            $authors = array();
            while ($authorRow = $st_authors->fetch()) {
                $authorIds[] = $authorRow['id'];
                $authors[] = $authorRow;
            }
            $article->authorIds = $authorIds;
            $article->authors = $authors;
            
            $list[] = $article;
        }

        // Получаем общее количество статей, которые соответствуют критерию
        $sql = "SELECT COUNT(*) AS totalRows $fromPart $whereClause";
        $st = $conn->prepare($sql);
        $st->bindValue(":categoryId", $categoryId, PDO::PARAM_INT);
        $st->bindValue(":subcategoryId", $subcategoryId, PDO::PARAM_INT);
        $st->execute();
        $totalRows = $st->fetch();
        $conn = null;
        
        return (array(
            "results" => $list,
            "totalRows" => $totalRows[0]
            )
        );
    }
    
    /**
     * Возвращает все (или диапазон) объекты Article из базы данных, отфильтрованные по категории и без подкатегории
     *
     * @param int $numRows Количество возвращаемых строк (по умолчанию = 10000)
     * @param int $categoryId ID категории
     * @param string $order Столбец, по которому выполняется сортировка статей (по умолчанию = "publicationDate DESC")
     * @param bool $includeInactive Включать ли неактивные статьи (по умолчанию = false)
     * @return Array|false Двух элементный массив: results => массив объектов Article; totalRows => общее количество строк
     */
    public static function getListWithoutSubcategory($categoryId, $numRows=1000,
        $order="publicationDate DESC", $includeInactive=false) {
        global $DB_DSN, $DB_USERNAME, $DB_PASSWORD;
        $conn = new PDO($DB_DSN, $DB_USERNAME, $DB_PASSWORD);
        $fromPart = "FROM articles";
        $whereClause = "WHERE categoryId = :categoryId AND (subcategoryId IS NULL OR subcategoryId = 0)";
        $conditions = [];
        
        // Добавляем условие для активных статей, если не запрошены все
        if (!$includeInactive) {
            $conditions[] = "active = 1";
            $whereClause .= " AND " . implode(" AND ", $conditions);
        }
        
        $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate)
                AS publicationDate
                $fromPart $whereClause
                ORDER BY  $order  LIMIT :numRows";
        
        $st = $conn->prepare($sql);
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
        $st->bindValue(":categoryId", $categoryId, PDO::PARAM_INT);
        
        $st->execute();
        $list = array();

        while ($row = $st->fetch()) {
            $article = new Article($row);
            
            // Получаем авторов статьи
            $sql = "SELECT u.id, u.login FROM users u
                    INNER JOIN article_authors aa ON u.id = aa.user_id
                    WHERE aa.article_id = :articleId";
            $st_authors = $conn->prepare($sql);
            $st_authors->bindValue(":articleId", $article->id, PDO::PARAM_INT);
            $st_authors->execute();
            
            $authorIds = array();
            $authors = array();
            while ($authorRow = $st_authors->fetch()) {
                $authorIds[] = $authorRow['id'];
                $authors[] = $authorRow;
            }
            $article->authorIds = $authorIds;
            $article->authors = $authors;
            
            $list[] = $article;
        }

        // Получаем общее количество статей, которые соответствуют критерию
        $sql = "SELECT COUNT(*) AS totalRows $fromPart $whereClause";
        $st = $conn->prepare($sql);
        $st->bindValue(":categoryId", $categoryId, PDO::PARAM_INT);
        $st->execute();
        $totalRows = $st->fetch();
        $conn = null;
        
        return (array(
            "results" => $list,
            "totalRows" => $totalRows[0]
            )
        );
    }
    /**
     * Вставляем текущий объект Article в базу данных, устанавливаем его ID
     */
    public function insert() {

        // Есть уже у объекта Article ID?
        if ( !is_null( $this->id ) ) trigger_error ( "Article::insert(): Attempt to insert an Article object that already has its ID property set (to $this->id).", E_USER_ERROR );

        // Вставляем статью
        global $DB_DSN, $DB_USERNAME, $DB_PASSWORD;
        $conn = new PDO( $DB_DSN, $DB_USERNAME, $DB_PASSWORD );
        
        // Check if subcategoryId column exists in the database
        $columnCheck = $conn->query("SHOW COLUMNS FROM articles LIKE 'subcategoryId'");
        if ($columnCheck->rowCount() > 0) {
            // Column exists, include subcategoryId in the query
            $sql = "INSERT INTO articles ( publicationDate, categoryId, subcategoryId, title, summary, content, active ) VALUES ( FROM_UNIXTIME(:publicationDate), :categoryId, :subcategoryId, :title, :summary, :content, :active )";
            $st = $conn->prepare ( $sql );
            $st->bindValue( ":publicationDate", $this->publicationDate, PDO::PARAM_INT );
            $st->bindValue( ":categoryId", $this->categoryId, PDO::PARAM_INT );
            // Bind subcategoryId with proper handling of NULL values
            if ($this->subcategoryId !== null) {
                $st->bindValue( ":subcategoryId", $this->subcategoryId, PDO::PARAM_INT );
            } else {
                $st->bindValue( ":subcategoryId", null, PDO::PARAM_NULL );
            }
            $st->bindValue( ":title", $this->title, PDO::PARAM_STR );
            $st->bindValue( ":summary", $this->summary, PDO::PARAM_STR );
            $st->bindValue( ":content", $this->content, PDO::PARAM_STR );
            $st->bindValue( ":active", $this->active, PDO::PARAM_INT );
        } else {
            // Column doesn't exist, exclude subcategoryId from the query
            $sql = "INSERT INTO articles ( publicationDate, categoryId, title, summary, content, active ) VALUES ( FROM_UNIXTIME(:publicationDate), :categoryId, :title, :summary, :content, :active )";
            $st = $conn->prepare ( $sql );
            $st->bindValue( ":publicationDate", $this->publicationDate, PDO::PARAM_INT );
            $st->bindValue( ":categoryId", $this->categoryId, PDO::PARAM_INT );
            $st->bindValue( ":title", $this->title, PDO::PARAM_STR );
            $st->bindValue( ":summary", $this->summary, PDO::PARAM_STR );
            $st->bindValue( ":content", $this->content, PDO::PARAM_STR );
            $st->bindValue( ":active", $this->active, PDO::PARAM_INT );
        }
        $st->execute();
        $this->id = $conn->lastInsertId();
        
        // Добавляем связи с авторами
        if (!empty($this->authorIds)) {
            foreach ($this->authorIds as $userId) {
                $sql = "INSERT INTO article_authors (article_id, user_id) VALUES (:article_id, :user_id)";
                $st = $conn->prepare($sql);
                $st->bindValue(":article_id", $this->id, PDO::PARAM_INT);
                $st->bindValue(":user_id", $userId, PDO::PARAM_INT);
                $st->execute();
            }
        }
        
        $conn = null;
    }

/**
* Обновляем текущий объект статьи в базе данных
*/
public function update() {

  // Есть ли у объекта статьи ID?
  if ( is_null( $this->id ) ) trigger_error ( "Article::update(): "
          . "Attempt to update an Article object "
          . "that does not have its ID property set.", E_USER_ERROR );

  // Обновляем статью
  global $DB_DSN, $DB_USERNAME, $DB_PASSWORD;
  $conn = new PDO( $DB_DSN, $DB_USERNAME, $DB_PASSWORD );
  
  // Check if subcategoryId column exists in the database
  $columnCheck = $conn->query("SHOW COLUMNS FROM articles LIKE 'subcategoryId'");
  if ($columnCheck->rowCount() > 0) {
      // Column exists, include subcategoryId in the query
      $sql = "UPDATE articles SET publicationDate=FROM_UNIXTIME(:publicationDate),"
              . " categoryId=:categoryId, subcategoryId=:subcategoryId, title=:title, summary=:summary,"
              . " content=:content, active=:active WHERE id = :id";
      
      $st = $conn->prepare ( $sql );
      $st->bindValue( ":publicationDate", $this->publicationDate, PDO::PARAM_INT );
      $st->bindValue( ":categoryId", $this->categoryId, PDO::PARAM_INT );
      // Bind subcategoryId with proper handling of NULL values
      if ($this->subcategoryId !== null) {
          $st->bindValue( ":subcategoryId", $this->subcategoryId, PDO::PARAM_INT );
      } else {
          $st->bindValue( ":subcategoryId", null, PDO::PARAM_NULL );
      }
      $st->bindValue( ":title", $this->title, PDO::PARAM_STR );
      $st->bindValue( ":summary", $this->summary, PDO::PARAM_STR );
      $st->bindValue( ":content", $this->content, PDO::PARAM_STR );
      $st->bindValue( ":active", $this->active, PDO::PARAM_INT );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
  } else {
      // Column doesn't exist, exclude subcategoryId from the query
      $sql = "UPDATE articles SET publicationDate=FROM_UNIXTIME(:publicationDate),"
              . " categoryId=:categoryId, title=:title, summary=:summary,"
              . " content=:content, active=:active WHERE id = :id";
      
      $st = $conn->prepare ( $sql );
      $st->bindValue( ":publicationDate", $this->publicationDate, PDO::PARAM_INT );
      $st->bindValue( ":categoryId", $this->categoryId, PDO::PARAM_INT );
      $st->bindValue( ":title", $this->title, PDO::PARAM_STR );
      $st->bindValue( ":summary", $this->summary, PDO::PARAM_STR );
      $st->bindValue( ":content", $this->content, PDO::PARAM_STR );
      $st->bindValue( ":active", $this->active, PDO::PARAM_INT );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
  }
  $st->execute();
  
  // Удаляем существующие связи с авторами
  $sql = "DELETE FROM article_authors WHERE article_id = :article_id";
  $st = $conn->prepare($sql);
  $st->bindValue(":article_id", $this->id, PDO::PARAM_INT);
  $st->execute();
  
  // Добавляем новые связи с авторами
  if (!empty($this->authorIds)) {
      foreach ($this->authorIds as $userId) {
          $sql = "INSERT INTO article_authors (article_id, user_id) VALUES (:article_id, :user_id)";
          $st = $conn->prepare($sql);
          $st->bindValue(":article_id", $this->id, PDO::PARAM_INT);
          $st->bindValue(":user_id", $userId, PDO::PARAM_INT);
          $st->execute();
      }
  }
  
  $conn = null;
}



    /**
    * Удаляем текущий объект статьи из базы данных
    */
    public function delete() {

      // Есть ли у объекта статьи ID?
      if ( is_null( $this->id ) ) trigger_error ( "Article::delete(): Attempt to delete an Article object that does not have its ID property set.", E_USER_ERROR );

      // Удаляем статью
      global $DB_DSN, $DB_USERNAME, $DB_PASSWORD;
      $conn = new PDO( $DB_DSN, $DB_USERNAME, $DB_PASSWORD );
      $st = $conn->prepare ( "DELETE FROM articles WHERE id = :id LIMIT 1" );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->execute();
      $conn = null;
    }

    /**
    * Метод для мягкого удаления (деактивации) статьи
    */
    public function deactivate() {
        if ( is_null( $this->id ) ) trigger_error ( "Article::deactivate(): Attempt to deactivate an Article object that does not have its ID property set.", E_USER_ERROR );

        global $DB_DSN, $DB_USERNAME, $DB_PASSWORD;
        $conn = new PDO( $DB_DSN, $DB_USERNAME, $DB_PASSWORD );
        $st = $conn->prepare ( "UPDATE articles SET active = 0 WHERE id = :id" );
        $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
        $st->execute();
        $this->active = 0;
        $conn = null;
    }

    /**
    * Метод для активации статьи
    */
    public function activate() {
        if ( is_null( $this->id ) ) trigger_error ( "Article::activate(): Attempt to activate an Article object that does not have its ID property set.", E_USER_ERROR );

        global $DB_DSN, $DB_USERNAME, $DB_PASSWORD;
        $conn = new PDO( $DB_DSN, $DB_USERNAME, $DB_PASSWORD );
        $st = $conn->prepare ( "UPDATE articles SET active = 1 WHERE id = :id" );
        $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
        $st->execute();
        $this->active = 1;
        $conn = null;
    }
    
    /**
     * Get authors for this article as an array of user objects
     */
    public function getAuthors() {
        global $DB_DSN, $DB_USERNAME, $DB_PASSWORD;
        $conn = new PDO($DB_DSN, $DB_USERNAME, $DB_PASSWORD);
        
        $sql = "SELECT u.id, u.login FROM users u
                INNER JOIN article_authors aa ON u.id = aa.user_id
                WHERE aa.article_id = :articleId";
        $st = $conn->prepare($sql);
        $st->bindValue(":articleId", $this->id, PDO::PARAM_INT);
        $st->execute();
        
        $authors = array();
        while ($row = $st->fetch()) {
            $authors[] = $row;
        }
        
        $conn = null;
        return $authors;
    }
}