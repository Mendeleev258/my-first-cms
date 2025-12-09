-- Добавление столбца active в таблицу articles, если он не существует
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = 'cms' 
                      AND TABLE_NAME = 'articles' 
                      AND COLUMN_NAME = 'active');

SET @sql = IF(@column_exists = 0, 
              'ALTER TABLE articles ADD COLUMN active TINYINT NOT NULL DEFAULT 1', 
              'SELECT "Column active already exists" as message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;