<?php
session_start();
include '../public/config.php';

// Ensure only patients can access this page
if (!isset($_SESSION['patient'])) {
    header('Location: ../public/login.php');
    exit;
}

$patient_id = (int) $_SESSION['patient'];

// Make sure the chat table exists before using it
$chatReady = false;
$tableCheck = $conn->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'Messages'");
if ($tableCheck && $tableCheck->fetch()) {
    $chatReady = true;
}

$stmt = $conn->prepare("SELECT Name FROM Patients WHERE PatientID = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

include '../public/header.php';
?>

<h2>Messages</h2>

<?php if (!$chatReady): ?>
    <p>The chat feature is not set up yet. Please make sure <code>database/schema.sql</code> is present and reload the page.</p>
<?php elseif (!$patient): ?>
    <p>Your patient account could not be found.</p>
<?php else: ?>
    <p>Chat with the clinic administrator, <?php echo htmlspecialchars($patient['Name']); ?>.</p>

    <div id="chat-app"
         data-role="patient"
         data-patient-id="<?php echo $patient_id; ?>"
         data-api="../public/chat_api.php"
         style="width: 100%; max-width: 700px;">
        <div id="chat-messages" style="height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 8px; margin-bottom: 8px;">
            <p id="chat-empty">No messages yet. Send a message to start the conversation.</p>
        </div>
        <div id="chat-status" role="status" aria-live="polite"></div>
        <form id="chat-form">
            <textarea id="chat-input" rows="3" maxlength="2000" placeholder="Type a message..." style="width: 100%; box-sizing: border-box;"></textarea>
            <button type="submit">Send</button>
        </form>
    </div>
<?php endif; ?>

<script src="../js/chat.js"></script>
<?php include '../public/footer.php'; ?>
