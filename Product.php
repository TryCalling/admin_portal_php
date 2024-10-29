<?php
class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $title;
    public $description;
    public $image;
    public $price;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Method to add a new product
    public function createProduct() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (title, description, image, price, created_at) 
                  VALUES (:title, :description, :image, :price, :created_at)";
        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->created_at = date('Y-m-d H:i:s');

        // Bind parameters
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':created_at', $this->created_at);

        // Execute the query
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
