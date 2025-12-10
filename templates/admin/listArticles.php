<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>
	  
    <h1>All Articles</h1>

    <?php if ( isset( $results['errorMessage'] ) ) { ?>
            <div class="errorMessage"><?php echo $results['errorMessage'] ?></div>
    <?php } ?>

    <?php if ( isset( $results['statusMessage'] ) ) { ?>
            <div class="statusMessage"><?php echo $results['statusMessage'] ?></div>
    <?php } ?>

          <table>
            <tr>
              <th>Publication Date</th>
              <th>Article</th>
              <th>Category</th>
              <th>Subcategory</th>  <!-- Добавляем заголовок колонки подкатегории -->
              <th>Active</th>  <!-- Добавляем заголовок колонки -->
            </tr>

<!--<?php echo "<pre>"; print_r ($results['articles'][2]->publicationDate); echo "</pre>"; ?> Обращаемся к дате массива $results. Дата = 0 -->
            
    <?php foreach ( $results['articles'] as $article ) { ?>

            <tr onclick="location='admin.php?action=editArticle&amp;articleId=<?php echo $article->id?>'">
              <td><?php echo date('j M Y', $article->publicationDate)?></td>
              <td>
                <?php echo $article->title?>
              </td>
              <td>
                <?php 
                if(isset ($article->categoryId)) {
                    echo $results['categories'][$article->categoryId]->name;                        
                }
                else {
                echo "Без категории";
                }?>
              </td>
              <td>
                <?php
                if(isset ($article->subcategoryId) && $article->subcategoryId) {
                    if (isset($results['subcategories'][$article->subcategoryId])) {
                        echo $results['subcategories'][$article->subcategoryId]->name;
                    } else {
                        echo "N/A";
                    }
                }
                else {
                    echo "Без подкатегории";
                }?>
              </td>
              <td>
                <!-- Добавляем отображение статуса активности -->
                <?php
                if (isset($article->active)) {
                    echo $article->active ? 'Да' : 'Нет';
                } else {
                    echo 'Да'; // Значение по умолчанию, если поле не установлено
                }
                ?>
              </td>
            </tr>

    <?php } ?>

          </table>

          <p><?php echo $results['totalRows']?> article<?php echo ( $results['totalRows'] != 1 ) ? 's' : '' ?> in total.</p>

          <p><a href="admin.php?action=newArticle">Add a New Article</a></p>

<?php include "templates/include/footer.php" ?>