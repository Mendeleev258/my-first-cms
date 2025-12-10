-- Add subcategoryId column to articles table if it doesn't exist
SET @column_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                      WHERE TABLE_SCHEMA = 'cms'
                      AND TABLE_NAME = 'articles'
                      AND COLUMN_NAME = 'subcategoryId');

SET @sql = IF(@column_exists = 0,
              'ALTER TABLE articles ADD COLUMN subcategoryId SMALLINT UNSIGNED DEFAULT NULL, 
               ADD KEY `fk_articles_subcategory` (`subcategoryId`),
               ADD CONSTRAINT `fk_articles_subcategory` FOREIGN KEY (`subcategoryId`) REFERENCES `subcategories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
              'SELECT "Column subcategoryId already exists in articles table" as message');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;