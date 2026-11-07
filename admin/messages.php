<?php
session_start();
include '../public/config.php';

// Ensure only admin can access this page
if (!isset($_SESSION['admin'])) {
    header('Location: ../public/login.php');
    exit;
}

// Make sure the chat table exists before using it
$chatReady = false;
$tableCheck = $conn->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'Messages'");
if ($tableCheck && $tableCheck->fetch()) {
    $chatReady = true;
}

$selected_patient_id = isset($_GET['patient_id']) ? (int) $_GET['patient_id'] : 0;
$selected_patient = null;
$conversations = [];

if ($chatReady) {
    // List every patient with a preview of the latest message and unread count.
    $conversations_query = "
        SELECT p.PatientID, p.Name,
            (SELECT m.Message FROM Messages m WHERE m.PatientID = p.PatientID ORDER BY m.MessageID DESC LIMIT 1) AS LastMessage,
            (SELECT m.CreatedAt FROM Messages m WHERE m.PatientID = p.PatientID ORDER BY m.MessageID DESC LIMIT 1) AS LastMessageAt,
            (SELECT COUNT(*) FROM Messages m WHERE m.PatientID = p.PatientID AND m.Sender = 'patient' AND m.IsRead = 0) AS UnreadCount
        FROM Patients p
        ORDER BY LastMessageAt DESC, p.Name ASC
    ";
    $conversations = $conn->query($conversations_query)->fetchAll();

    if ($selected_patient_id > 0) {
        $stmt = $conn->prepare("SELECT PatientID, Name FROM Patients WHERE PatientID = ?");
        $stmt->execute([$selected_patient_id]);
        $selected_patient = $stmt->fetch();

        if (!$selected_patient) {
            $selected_patient_id = 0;
        }
    }
}

include '../public/header.php';
?>

<h2>Messages</h2>

<?php if (!$chatReady): ?>
    <p>The chat feature is not set up yet. Please make sure <code>database/schema.sql</code> is present and reload the page.</p>
<?php else: ?>
    <div class="chat-layout" style="display: flex; gap: 1rem; width: 100%; max-width: 1100px; align-items: flex-start;">
        <aside class="chat-conversations" style="flex: 0 0 280px; border: 1px solid #ccc; padding: 8px;">
            <h3>Patients</h3>
            <ul style="list-style: none; padding: 0; margin: 0;">
                <?php if (count($conversations) === 0): ?>
                    <li>No patients yet.</li>
                <?php else: ?>
                    <?php foreach ($conversations as $conversation): ?>
                        <li style="margin-bottom: 8px;">
                            <a href="messages.php?patient_id=<?php echo (int) $conversation['PatientID']; ?>">
                                <?php echo htmlspecialchars($conversation['Name']); ?>
                                <?php if ((int) $conversation['UnreadCount'] > 0): ?>
                                    <strong>(<?php echo (int) $conversation['UnreadCount']; ?> new)</strong>
                                <?php endif; ?>
                            </a>
                            <div style="font-size: 0.85em; color: #666;">
                                <?php
                                if ($conversation['LastMessage'] !== null) {
                                    $preview = $conversation['LastMessage'];
                                    if (function_exists('mb_strimwidth')) {
                                        $preview = mb_strimwidth($preview, 0, 50, '...');
                                    } elseif (strlen($preview) > 50) {
                                        $preview = substr($preview, 0, 50) . '...';
                                    }
                                    echo htmlspecialchars($preview);
                                } else {
                                    echo 'No messages yet';
                                }
                                ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </aside>

        <section class="chat-panel" style="flex: 1; min-width: 0;">
            <?php if ($selected_patient): ?>
                <h3>Chat with <?php echo htmlspecialchars($selected_patient['Name']); ?></h3>

                <div id="chat-app"
                     data-role="admin"
                     data-patient-id="<?php echo (int) $selected_patient['PatientID']; ?>"
                     data-api="../public/chat_api.php">
                    <div id="chat-messages" style="height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 8px; margin-bottom: 8px;">
                        <p id="chat-empty">No messages yet.</p>
                    </div>
                    <div id="chat-status" role="status" aria-live="polite"></div>
                    <form id="chat-form">
                        <textarea id="chat-input" rows="3" maxlength="2000" placeholder="Type a message..." style="width: 100%; box-sizing: border-box;"></textarea>
                        <button type="submit">Send</button>
                    </form>
                </div>
            <?php else: ?>
                <p>Select a patient to start chatting.</p>
            <?php endif; ?>
        </section>
    </div>
<?php endif; ?>

<script src="../js/chat.js"></script>
<?php include '../public/footer.php'; ?>
