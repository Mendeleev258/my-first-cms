<?php include "templates/include/header.php" ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="/ajax/loadArticle.js"></script>
    <ul id="headlines">
    <?php
    $id = array();
    $content = array();
    foreach ($results['articles'] as $article) { ?>
        
        <li class='<?php echo $article->id?>'>
            <h2>
                <span class="pubDate">
                    <?php echo date('j F', $article->publicationDate)?>
                </span>
                
                <a href=".?action=viewArticle&articleId=<?php
                                                           echo $article->id?>">
                    <?php echo htmlspecialchars($article->title)?>
                </a>
            </h2>
                
            <?php if (isset($article->categoryId) && isset($results['categories'][$article->categoryId])) { ?>
                <span class="category">
                    Категория
                    <a href=".?action=archive&categoryId=<?php
                                               echo $article->categoryId?>">
                        <?php echo htmlspecialchars($results['categories']
                                             [$article->categoryId]->name)?>
                    </a>
                </span>
            <?php }
            else { ?>
                <span class="category">
                    <?php echo "Без категории"?>
                </span>
            <?php } ?>
            <?php if (isset($article->subcategoryId) && isset($results['subcategories'][$article->subcategoryId])) { ?>
            <span class="subcategory">
                Подкатегория
                <a href=".?action=viewArticleSubcategory&subcategoryId=<?php
                                            echo $article->subcategoryId?>">
                    <?php echo htmlspecialchars($results['subcategories']
                                          [$article->subcategoryId]->name)?>
                </a>
            </span>
            <?php } ?>
            <p class="summary"><?php
                            // Получаем краткое содержание статьи
                            $shortContent = '';
                            if (isset($article->content50char)) {
                                $shortContent = $article->content50char;
                            } else {
                                // Если content50char не установлен, берем первые 50 символов из content
                                $content = isset($article->content) ? $article->content : '';
                                $shortContent = mb_substr($content, 0, 50, 'UTF-8');
                                if (mb_strlen($content, 'UTF-8') > 50) {
                                    $shortContent .= '...';
                                }
                            }
                            echo htmlspecialchars($shortContent)?></p>
            
            <img id="loader-identity<?=$article->id?>" class="loader-identity"
                 accesskey="              " src="/JS/ajax-loader.gif" alt="gif">
            <a href=".?action=viewArticle&articleId=<?php echo $article->id?>" class="showContentPOSTmethod"
                data-contentId="<?php
                                echo $article->id?>">Запросить методом POST</a>
            
            <a href=".?action=viewArticle&articleId=<?php echo $article->id?>" class="showContentGETmethod"
                data-contentId="<?php
                                echo $article->id?>">Запросить методом GET</a>

           <p class="loadArticle" style="cursor:pointer"
                                  data-contentId="<?=$article->id?>">NEW POST</p>
            <div class="summary" id="article<?=$article->id?>">
            </div>
            
            <a href=".?action=viewArticle&articleId=<?php
                echo $article->id?>" class="showContent"
                data-contentId="<?php echo $article->id?>">Показать полностью</a>
        </li>
    <?php } ?>
    </ul>
    <p><a href="./?action=archive">Article Archive</a></p>
<?php include "templates/include/footer.php" ?>

<script src="/JS/showContent.js"></sctipt>