<?php
// Test database connection script
$env = function (string $key, mixed $default = null): mixed {
    $value = getenv($key);
    return ($value === false) ? $default : $value;
};

$host = $env('SUPABASE_DB_HOST', 'aws-1-eu-west-3.pooler.supabase.com');
$port = (int) $env('SUPABASE_DB_PORT', 5432);
$dbname = $env('SUPABASE_DB_DATABASE', 'postgres');
$username = $env('SUPABASE_DB_USERNAME', 'postgres.hvglzmviiynfvkvwdhvt');
$password = $env('SUPABASE_DB_PASSWORD', 'AVLsS7NXI60FzwvN');

echo "Testing connection to: $host:$port\n";
echo "Database: $dbname\n";
echo "Username: $username\n\n";

try {
    // Try full connection string format
    $connStr = "host=$host port=$port dbname=$dbname user=$username password=$password sslmode=require";
    echo "Connection string: $connStr\n\n";
    
    $pdo = new PDO("pgsql:" . $connStr);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    echo "✅ Database connection successful!\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "PostgreSQL version: " . $version . "\n";
    
    // Check if universities table exists
    $stmt = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'universities')");
    $exists = $stmt->fetchColumn();
    echo "Universities table exists: " . ($exists ? "Yes" : "No") . "\n";
    
    if ($exists) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM universities");
        $count = $stmt->fetchColumn();
        echo "Total universities in database: " . $count . "\n";
        
        // Show some sample data
        $stmt = $pdo->query("SELECT * FROM universities LIMIT 1");
        $uni = $stmt->fetch();
        echo "\nSample university structure:\n";
        echo "Columns: " . implode(", ", array_keys($uni)) . "\n";
        
        // Show actual sample data
        $stmt = $pdo->query("SELECT * FROM universities LIMIT 5");
        $universities = $stmt->fetchAll();
        echo "\nSample universities:\n";
        foreach ($universities as $uni) {
            echo "- " . ($uni['name'] ?? 'N/A') . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
