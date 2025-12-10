DROP TABLE IF EXISTS articles;
CREATE TABLE articles
(
  id              smallint unsigned NOT NULL auto_increment,
  publicationDate date NOT NULL,
  categoryId      smallint unsigned NOT NULL,
  subcategoryId   smallint unsigned DEFAULT NULL,
  title           varchar(255) NOT NULL,
  summary         text NOT NULL,
  content         mediumtext NOT NULL,
 
  PRIMARY KEY     (id),
  FOREIGN KEY     (categoryId) REFERENCES categories(id),
  FOREIGN KEY     (subcategoryId) REFERENCES subcategories(id) ON DELETE SET NULL ON UPDATE CASCADE
);


DROP TABLE IF EXISTS categories;
CREATE TABLE categories
(
  id              smallint unsigned NOT NULL auto_increment,
  name            varchar(255) NOT NULL,
  description     text NOT NULL, 
  
  PRIMARY KEY     (id)
);

DROP TABLE IF EXISTS subcategories;
CREATE TABLE subcategories
(
  id              smallint unsigned NOT NULL auto_increment,
  name            varchar(255) NOT NULL,
  categoryId      smallint unsigned NOT NULL,
  
  PRIMARY KEY     (id),
  FOREIGN KEY     (categoryId) REFERENCES categories(id) ON DELETE CASCADE ON UPDATE CASCADE
);
