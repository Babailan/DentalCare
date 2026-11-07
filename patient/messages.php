<?php
session_start();
require_once __DIR__ . '/../config.php';

// Ensure only patients can access this page
if (!isset($_SESSION['patient'])) {
    header('Location: ../login.php');
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

include __DIR__ . '/../header.php';
?>

<h2 class="text-3xl font-bold mb-3">Messages</h2>

<?php if (!$chatReady): ?>
    <div class="alert alert-info">The chat feature is not set up yet. Please make sure <code>database/schema.sql</code> is present and reload the page.</div>
<?php elseif (!$patient): ?>
    <div class="alert alert-warning">Your patient account could not be found.</div>
<?php else: ?>
    <p class="mb-5 text-base-content/70">Chat with your clinic doctor, <?php echo htmlspecialchars($patient['Name']); ?>.</p>

    <div id="chat-app"
         class="chat-panel card card-border bg-base-100"
         data-role="patient"
         data-patient-id="<?php echo $patient_id; ?>"
         data-api="../chat_api.php">
        <div class="card-body">
            <div id="chat-messages" class="chat-history">
                <p id="chat-empty">No messages yet. Send a message to start the conversation.</p>
            </div>
            <div id="chat-status" role="status" aria-live="polite"></div>
            <form id="chat-form">
                <textarea id="chat-input" class="textarea w-full" rows="3" maxlength="2000" placeholder="Type a message..."></textarea>
                <button class="btn btn-primary" type="submit">Send</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<script src="../js/chat.js"></script>
<?php include __DIR__ . '/../footer.php'; ?>
