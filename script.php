<?php
class Script
{
    private $pdo;

    // Constructor to inject the PDO connection
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Fetch all products from the database
    public function getAllProducts()
    {
        $sql = "SELECT * FROM products ORDER BY id ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Convert products data to JSON and return it
    public function convertToJson()
    {
        try {
            $products = $this->getAllProducts(); // Get products from the database

            if (empty($products)) {
                throw new Exception("No products found to convert.");
            }

            // Convert the products array to JSON format
            return json_encode($products, JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            // Return error message as JSON response
            return json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>
