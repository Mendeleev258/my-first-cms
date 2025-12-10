-- Check if subcategories table exists, and create it if it doesn't
SET @table_exists := (
    SELECT COUNT(*) 
    FROM information_schema.tables 
    WHERE table_schema = 'cms' 
    AND table_name = 'subcategories'
);

SET @sql := IF(@table_exists = 0,
    'CREATE TABLE subcategories (
        id              smallint unsigned NOT NULL auto_increment,
        name            varchar(255) NOT NULL,
        categoryId      smallint unsigned NOT NULL,
        
        PRIMARY KEY     (id),
        FOREIGN KEY     (categoryId) REFERENCES categories(id) ON DELETE CASCADE ON UPDATE CASCADE
    )',
    'SELECT "subcategories table already exists" as message'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;