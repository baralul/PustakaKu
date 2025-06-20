<?php
class Book {
    private $conn;
    private $table_name = "books";

    public $id;
    public $title;
    public $author;
    public $isbn;
    public $publisher;
    public $publication_year;
    public $category;
    public $total_copies;
    public $available_copies;
    public $description;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE title LIKE ? OR author LIKE ? ORDER BY title ASC";
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(1, $keyword);
        $stmt->bindParam(2, $keyword);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->title = $row['title'];
            $this->author = $row['author'];
            $this->isbn = $row['isbn'];
            $this->publisher = $row['publisher'];
            $this->publication_year = $row['publication_year'];
            $this->category = $row['category'];
            $this->total_copies = $row['total_copies'];
            $this->available_copies = $row['available_copies'];
            $this->description = $row['description'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET title=:title, author=:author, isbn=:isbn, publisher=:publisher, publication_year=:publication_year, category=:category, total_copies=:total_copies, available_copies=:available_copies, description=:description";
        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->isbn = htmlspecialchars(strip_tags($this->isbn));
        $this->publisher = htmlspecialchars(strip_tags($this->publisher));
        $this->publication_year = htmlspecialchars(strip_tags($this->publication_year));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->total_copies = htmlspecialchars(strip_tags($this->total_copies));
        $this->available_copies = htmlspecialchars(strip_tags($this->available_copies));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":author", $this->author);
        $stmt->bindParam(":isbn", $this->isbn);
        $stmt->bindParam(":publisher", $this->publisher);
        $stmt->bindParam(":publication_year", $this->publication_year);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":total_copies", $this->total_copies);
        $stmt->bindParam(":available_copies", $this->available_copies);
        $stmt->bindParam(":description", $this->description);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET title=:title, author=:author, isbn=:isbn, publisher=:publisher, publication_year=:publication_year, category=:category, total_copies=:total_copies, available_copies=:available_copies, description=:description WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->isbn = htmlspecialchars(strip_tags($this->isbn));
        $this->publisher = htmlspecialchars(strip_tags($this->publisher));
        $this->publication_year = htmlspecialchars(strip_tags($this->publication_year));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->total_copies = htmlspecialchars(strip_tags($this->total_copies));
        $this->available_copies = htmlspecialchars(strip_tags($this->available_copies));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':author', $this->author);
        $stmt->bindParam(':isbn', $this->isbn);
        $stmt->bindParam(':publisher', $this->publisher);
        $stmt->bindParam(':publication_year', $this->publication_year);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':total_copies', $this->total_copies);
        $stmt->bindParam(':available_copies', $this->available_copies);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
