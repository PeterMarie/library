<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Update this if your MySQL password is set
$dbname = 'library_db';

// Connect to MySQL
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create Database
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// Create Tables
$schema = <<<SQL
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS subcategories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS formats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS reading_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subcategory_id INT,
    publisher VARCHAR(255),
    publication_year YEAR,
    edition VARCHAR(50),
    isbn VARCHAR(20),
    language_id INT,
    page_count INT,
    format_id INT,
    status_id INT,
    summary TEXT,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subcategory_id) REFERENCES subcategories(id),
    FOREIGN KEY (language_id) REFERENCES languages(id),
    FOREIGN KEY (format_id) REFERENCES formats(id),
    FOREIGN KEY (status_id) REFERENCES reading_status(id)
);

CREATE TABLE IF NOT EXISTS authors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS document_authors (
    document_id INT,
    author_id INT,
    PRIMARY KEY (document_id, author_id),
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS keywords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS document_keywords (
    document_id INT,
    keyword_id INT,
    PRIMARY KEY (document_id, keyword_id),
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (keyword_id) REFERENCES keywords(id) ON DELETE CASCADE
);
SQL;

$conn->multi_query($schema);
while ($conn->more_results() && $conn->next_result()) {;}

// Populate Categories
$conn->query("INSERT IGNORE INTO categories (name) VALUES 
('Fiction'), ('Non-Fiction'), ('Textbook'), ('Journal Article'), 
('Comic / Graphic Novel'), ('Manual / Technical Document'), 
('Religious / Sacred Text'), ('Legal Document'), ('Other')");

// Get category IDs
$category_ids = [];
$res = $conn->query("SELECT id, name FROM categories");
while ($row = $res->fetch_assoc()) {
    $category_ids[$row['name']] = $row['id'];
}

// Populate Subcategories (grouped by category)
$subs = [
    'Fiction' => ['Literary Fiction','Science Fiction','Fantasy','Mystery / Thriller','Romance','Horror','Historical Fiction','Adventure','Satire / Humor','Young Adult','Children’s Fiction'],
    'Non-Fiction' => ['Biography / Memoir','History','Politics','Philosophy','Psychology','Sociology','Religion / Theology','Self-help','Travel','True Crime','Journalism','Personal Development'],
    'Textbook' => ['Mathematics','Physics','Chemistry','Biology','Engineering','Medicine','Law','Economics','Accounting','Computer Science','Education','Environmental Science'],
    'Journal Article' => ['Peer-reviewed Paper','Conference Proceedings','Case Study','White Paper'],
    'Comic / Graphic Novel' => ['Manga','Superhero','Webtoon','Anthology'],
    'Manual / Technical Document' => ['Software Guide','Programming Manual','Product Manual','Process / Industrial Docs'],
    'Religious / Sacred Text' => ['Scripture','Commentary','Devotional','Apologetics'],
    'Legal Document' => ['Statute','Case Law','Contract','Legal Brief'],
    'Other' => ['Essay','Speech','Poetry','Cookbook','Art Book','Diary / Journal','Letter Collection']
];

foreach ($subs as $cat => $sublist) {
    $cat_id = $category_ids[$cat];
    foreach ($sublist as $sub) {
        $stmt = $conn->prepare("INSERT IGNORE INTO subcategories (category_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $cat_id, $sub);
        $stmt->execute();
        $stmt->close();
    }
}

// Languages
$conn->query("INSERT IGNORE INTO languages (name) VALUES
('English'), ('French'), ('Spanish'), ('German'), ('Italian'),
('Chinese'), ('Japanese'), ('Arabic'), ('Russian'), ('Portuguese'),
('Latin'), ('Greek'), ('Hindi'), ('Swahili'), ('Other')");

// Formats
$conn->query("INSERT IGNORE INTO formats (type) VALUES
('PDF'), ('EPUB'), ('MOBI'), ('DOCX'), ('TXT'), 
('HTML'), ('CBZ'), ('CBR'), ('ODT'), ('RTF'), ('Other')");

// Reading Status
$conn->query("INSERT IGNORE INTO reading_status (status) VALUES
('Unread'), ('Reading'), ('Completed'), ('Abandoned'), ('To Review')");

echo "Database setup complete with tables and default data.";

$conn->close();
?>
