<?php
require_once '../includes/functions.php';
require_once '../includes/db.php';

// Require login
requireLogin();

// Get current user
$userId = getCurrentUserId();
$userRole = getCurrentUserRole();

// Mentorship ID (passed via ?mentorship_id=)
$mentorshipId = isset($_GET['mentorship_id']) ? intval($_GET['mentorship_id']) : 0;

// Check if mentorship exists and user is part of it
$mentorship = selectRecord(
    "SELECT * FROM mentorships WHERE id = :id AND (mentor_id = :uid OR mentee_id = :uid)",
    ['id' => $mentorshipId, 'uid' => $userId]
);
if (!$mentorship) {
    die("<p style='color:red;'>Access denied or invalid mentorship ID.</p>");
}

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = sanitizeInput($_POST['message']);
    if (!empty($message)) {
        $receiverId = ($mentorship['mentor_id'] == $userId) ? $mentorship['mentee_id'] : $mentorship['mentor_id'];

        $inserted = insertRecord('messages', [
            'mentorship_id' => $mentorshipId,
            'sender_id' => $userId,
            'receiver_id' => $receiverId,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($inserted) {
            logActivity("Sent message", ["to" => $receiverId, "mentorship_id" => $mentorshipId]);
            setFlashMessage("Message sent successfully!", "success");
            header("Location: messages.php?mentorship_id=$mentorshipId");
            exit;
        } else {
            setFlashMessage("Failed to send message.", "error");
        }
    }
}

// Fetch messages for this mentorship
$messages = selectRecords(
    "SELECT m.*, u.full_name AS sender_name
     FROM messages m
     JOIN users u ON m.sender_id = u.id
     WHERE m.mentorship_id = :mid
     ORDER BY m.created_at ASC",
    ['mid' => $mentorshipId]
);

// Mark unread messages as read (for the receiver)
executeQuery(
    "UPDATE messages SET is_read = 1, read_at = NOW()
     WHERE mentorship_id = :mid AND receiver_id = :uid AND is_read = 0",
    ['mid' => $mentorshipId, 'uid' => $userId]
);

$pageTitle = "Messages - Mentorship #$mentorshipId";
include '../includes/header.php';
?>

<div class="container">
    <h2>Conversation: <?= htmlspecialchars($mentorship['subject_area'] ?? 'Mentorship Chat') ?></h2>
    <div class="messages-box" style="border:1px solid #ccc; padding:15px; height:400px; overflow-y:auto; background:#fafafa;">
        <?php if ($messages): ?>
            <?php foreach ($messages as $msg): ?>
                <div style="margin-bottom:10px; <?= ($msg['sender_id'] == $userId) ? 'text-align:right;' : '' ?>">
                    <strong><?= htmlspecialchars($msg['sender_name']) ?>:</strong><br>
                    <span><?= nl2br(htmlspecialchars($msg['message'])) ?></span><br>
                    <small style="color:gray;"><?= formatDate($msg['created_at']) ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:gray;">No messages yet. Start the conversation below.</p>
        <?php endif; ?>
    </div>

    <form method="post" style="margin-top:20px;">
        <textarea name="message" rows="3" required class="form-control" placeholder="Type your message here..."></textarea><br>
        <button type="submit" class="btn btn-primary">Send Message</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
