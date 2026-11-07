<?php
session_start();
include 'config.php';

header('Content-Type: application/json; charset=utf-8');

// Send a JSON response and stop.
function respond($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

$isAdmin = isset($_SESSION['admin']);
$isPatient = isset($_SESSION['patient']);

if (!$isAdmin && !$isPatient) {
    respond(401, ['error' => 'Not authenticated.']);
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$sender = $isAdmin ? 'admin' : 'patient';

// Check that the requested patient exists (used by the admin side).
function patient_exists($conn, $patient_id) {
    $stmt = $conn->prepare("SELECT PatientID FROM Patients WHERE PatientID = ?");
    $stmt->execute([$patient_id]);
    return $stmt->fetch() !== false;
}

// Return messages for the current conversation, newest last.
if ($action === 'fetch') {
    $after = isset($_GET['after']) ? (int) $_GET['after'] : 0;
    if ($after < 0) {
        $after = 0;
    }

    if ($isAdmin) {
        $patient_id = isset($_GET['patient_id']) ? (int) $_GET['patient_id'] : 0;
        if ($patient_id <= 0) {
            respond(400, ['error' => 'patient_id is required.']);
        }
        if (!patient_exists($conn, $patient_id)) {
            respond(404, ['error' => 'Patient not found.']);
        }
    } else {
        $patient_id = (int) $_SESSION['patient'];
    }

    // Mark the other side's messages as read while the conversation is open.
    $otherSender = $isAdmin ? 'patient' : 'admin';
    $stmt = $conn->prepare("UPDATE Messages SET IsRead = 1 WHERE PatientID = ? AND Sender = ? AND IsRead = 0");
    $stmt->execute([$patient_id, $otherSender]);

    $stmt = $conn->prepare("
        SELECT MessageID, Sender, Message, CreatedAt
        FROM Messages
        WHERE PatientID = ? AND MessageID > ?
        ORDER BY MessageID ASC
    ");
    $stmt->execute([$patient_id, $after]);
    $result = $stmt->fetchAll();

    $messages = [];
    foreach ($result as $row) {
        $messages[] = [
            'id' => (int) $row['MessageID'],
            'sender' => $row['Sender'],
            'message' => $row['Message'],
            'created_at' => $row['CreatedAt'],
        ];
    }

    respond(200, ['messages' => $messages]);
}

// Store a new message from the logged-in user.
if ($action === 'send') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(405, ['error' => 'POST required.']);
    }

    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    if ($message === '') {
        respond(422, ['error' => 'Message cannot be empty.']);
    }

    $length = function_exists('mb_strlen') ? mb_strlen($message) : strlen($message);
    if ($length > 2000) {
        respond(422, ['error' => 'Message is too long (max 2000 characters).']);
    }

    if ($isAdmin) {
        $patient_id = isset($_POST['patient_id']) ? (int) $_POST['patient_id'] : 0;
        if ($patient_id <= 0) {
            respond(400, ['error' => 'patient_id is required.']);
        }
        if (!patient_exists($conn, $patient_id)) {
            respond(404, ['error' => 'Patient not found.']);
        }
    } else {
        $patient_id = (int) $_SESSION['patient'];
    }

    try {
        $stmt = $conn->prepare("INSERT INTO Messages (PatientID, Sender, Message, IsRead) VALUES (?, ?, ?, 0)");
        $stmt->execute([$patient_id, $sender, $message]);
    } catch (PDOException $e) {
        respond(500, ['error' => 'Could not save the message.']);
    }

    respond(200, ['success' => true, 'id' => (int) $conn->lastInsertId()]);
}

respond(400, ['error' => 'Unknown action.']);
