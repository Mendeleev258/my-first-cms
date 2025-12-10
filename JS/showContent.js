$(document).ready(function(){
    init_get();
    init_post();
});

function init_get() 
{
    $('a.showContentGETmethod').on('click', function(){
        var contentId = $(this).attr('data-contentId');
        showLoaderIdentity(contentId);
        $.ajax({
            url:'./ajax/showContentsHandler.php?articleId=' + contentId
        })
        .done (function(){
            hideLoaderIdentity(contentId);
            console.log('Ответ получен');
            // Перенаправляем на страницу просмотра статьи
            window.location.href = '.?action=viewArticle&articleId=' + contentId;
        })
        .fail(function(){
            hideLoaderIdentity(contentId);
            console.log('Ошибка соединения с сервером');
        });
        
        return false;
        
    });
}

function init_post() 
{
    $('a.showContentPOSTmethod').on('click', function(){
        var content = $(this).attr('data-contentId');
        showLoaderIdentity(content);
        $.ajax({
            url:'./ajax/showContentsHandler.php',
            data: ({articleId: content}),
            method: 'POST'
        })
        .done (function(){
            hideLoaderIdentity(content);
            console.log('Ответ получен');
            // Перенаправляем на страницу просмотра статьи
            window.location.href = '.?action=viewArticle&articleId=' + content;
        })
        .fail(function(){
            hideLoaderIdentity(content);
            console.log('Ошибка соединения с сервером');
        });
        
        return false;
        
    });  
}