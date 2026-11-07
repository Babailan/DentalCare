<?php
// SQLite database stored inside the project's ./database folder.
$databaseDir = __DIR__ . '/database';
$databaseFile = $databaseDir . '/dental_appointment_system.db';

if (!is_dir($databaseDir)) {
    mkdir($databaseDir, 0775, true);
}

try {
    $conn = new PDO('sqlite:' . $databaseFile);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->exec('PRAGMA foreign_keys = ON');
    $conn->exec('PRAGMA busy_timeout = 5000');

    // Create the tables automatically on first run.
    $schemaFile = $databaseDir . '/schema.sql';
    if (is_file($schemaFile)) {
        $schema = file_get_contents($schemaFile);
        foreach (explode(';', $schema) as $statement) {
            $statement = trim($statement);
            if ($statement !== '') {
                $conn->exec($statement);
            }
        }
    }

    // Keep databases created with earlier versions of the schema usable.
    $appointmentColumns = $conn->query('PRAGMA table_info(Appointments)')->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array('ServiceID', $appointmentColumns, true)) {
        $conn->exec('ALTER TABLE Appointments ADD COLUMN ServiceID INTEGER REFERENCES Services(ServiceID) ON DELETE SET NULL');
    }
    if (!in_array('Price', $appointmentColumns, true)) {
        $conn->exec('ALTER TABLE Appointments ADD COLUMN Price REAL NOT NULL DEFAULT 0');
    }
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
