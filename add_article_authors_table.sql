-- Create a table to handle the many-to-many relationship between articles and users (authors)
CREATE TABLE article_authors (
    article_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (article_id, user_id),
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert some sample data for existing articles
-- Assuming the current user (id=1, login='mendel') is the author of some articles
INSERT INTO article_authors (article_id, user_id) VALUES 
(1, 1),  -- Article 1 by user 1
(2, 1),  -- Article 2 by user 1
(3, 1);  -- Article 3 by user 1