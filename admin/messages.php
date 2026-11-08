<?php
session_start();
require_once __DIR__ . '/../config.php';

// Ensure only admin can access this page
if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
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

$getInitial = static function ($name) {
    $name = trim((string) $name);
    if ($name === '') {
        return '?';
    }

    $initial = function_exists('mb_substr')
        ? mb_substr($name, 0, 1, 'UTF-8')
        : substr($name, 0, 1);

    return function_exists('mb_strtoupper')
        ? mb_strtoupper($initial, 'UTF-8')
        : strtoupper($initial);
};

include __DIR__ . '/../header.php';
?>

<h2 class="text-3xl font-bold mb-6">Messages</h2>

<?php if (!$chatReady): ?>
    <div class="alert alert-info">The chat feature is not set up yet. Please make sure <code>database/schema.sql</code> is present and reload the page.</div>
<?php else: ?>
    <div class="chat-layout">
        <aside class="chat-conversations card card-border bg-base-100">
            <div class="card-body">
            <h3 class="card-title">Patients</h3>
            <ul class="menu p-0 w-full">
                <?php if (count($conversations) === 0): ?>
                    <li>No patients yet.</li>
                <?php else: ?>
                    <?php foreach ($conversations as $conversation): ?>
                        <li class="conversation-item w-full">
                            <a class="conversation-link w-full <?php echo (int) $conversation['PatientID'] === $selected_patient_id ? 'active' : ''; ?>" href="messages.php?patient_id=<?php echo (int) $conversation['PatientID']; ?>">
                                <div class="avatar avatar-placeholder conversation-avatar" aria-hidden="true">
                                    <div class="w-11 rounded-full bg-base-200 text-base-content"><?php echo htmlspecialchars($getInitial($conversation['Name']), ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                                <span class="conversation-content">
                                    <span class="conversation-heading">
                                        <span class="conversation-name"><?php echo htmlspecialchars($conversation['Name']); ?></span>
                                        <?php if ((int) $conversation['UnreadCount'] > 0): ?>
                                            <span class="badge badge-primary badge-sm"><?php echo (int) $conversation['UnreadCount']; ?> new</span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="conversation-preview">
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
                                    </span>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            </div>
        </aside>

        <section class="chat-panel card card-border bg-base-100">
            <div class="card-body">
            <?php if ($selected_patient): ?>
                <h3 class="card-title">Chat with <?php echo htmlspecialchars($selected_patient['Name']); ?></h3>

                <div id="chat-app"
                     data-role="admin"
                     data-patient-id="<?php echo (int) $selected_patient['PatientID']; ?>"
                     data-patient-initial="<?php echo htmlspecialchars($getInitial($selected_patient['Name']), ENT_QUOTES, 'UTF-8'); ?>"
                     data-admin-initial="A"
                     data-api="../chat_api.php">
                    <div id="chat-messages" class="chat-history">
                        <p id="chat-empty">No messages yet.</p>
                    </div>
                    <div id="chat-status" role="status" aria-live="polite"></div>
                    <form id="chat-form">
                        <textarea id="chat-input" class="textarea w-full" rows="3" maxlength="2000" placeholder="Type a message..."></textarea>
                        <button class="btn btn-primary" type="submit">Send</button>
                    </form>
                </div>
            <?php else: ?>
                <p>Select a patient to start chatting.</p>
            <?php endif; ?>
            </div>
        </section>
    </div>
<?php endif; ?>

<script src="../js/chat.js"></script>
<?php include __DIR__ . '/../footer.php'; ?>
