<?php
// Include necessary files
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/check_session.php';

$userId = getCurrentUserId();
$userGender = getUserGender($userId); // Returns 'male' or 'female'
$csrfToken = generateCSRFToken(); // Generate token for the form

// 1. Query Same-Gender Mentors (B)
$sql = "
    SELECT u.id, u.full_name, u.email, u.bio, u.skills, u.experience_level,
           COUNT(DISTINCT m.id) as mentee_count
    FROM users u
    LEFT JOIN mentorships m ON u.id = m.mentor_id AND m.status = 'active'
    WHERE u.role = 'mentor' 
      AND u.gender = :user_gender      -- ⭐ Gender filter here!
      AND u.is_active = 1
      AND u.id != :user_id              -- Exclude self
      AND u.id NOT IN (                 -- Exclude mentors already requested/active
          SELECT mentor_id 
          FROM mentorships 
          WHERE mentee_id = :user_id 
          AND status IN ('pending', 'active')
      )
    GROUP BY u.id
    ORDER BY u.full_name
";

$mentors = selectRecords($sql, [
    'user_gender' => $userGender,
    'user_id' => $userId
]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Your Mentor - SU Mentoring</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1>Find Your Mentor</h1>

    <div class="search-bar">
        <input type="text" 
               id="mentor-search" 
               placeholder="Search by name, skills, or expertise..."
               onkeyup="filterMentors()">
        
        <select id="experience-filter" onchange="filterMentors()">
            <option value="">All Experience Levels</option>
            <option value="beginner">Beginner</option>
            <option value="intermediate">Intermediate</option>
            <option value="advanced">Advanced</option>
        </select>
    </div>

    <?php if (empty($mentors)): ?>
        <p>No available mentors of your gender found at this time.</p>
    <?php else: ?>
        <div class="mentor-grid">
            <?php foreach ($mentors as $mentor): ?>
                <div class="mentor-card">
                    <h3><?= htmlspecialchars($mentor['full_name']) ?></h3>
                    <p class="experience-badge">
                        <?= ucfirst($mentor['experience_level']) ?>
                    </p>
                    <p class="bio"><?= htmlspecialchars($mentor['bio']) ?></p>
                    <p class="skills">Skills: <?= htmlspecialchars($mentor['skills']) ?></p>
                    <p class="mentees">Current Mentees: <?= $mentor['mentee_count'] ?></p>
                    
                    <form method="POST" action="api/request-mentorship.php">
                        <input type="hidden" name="mentor_id" value="<?= $mentor['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <button type="submit" class="btn btn-primary">
                            Request Mentorship
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <script>
    function filterMentors() {
        const searchText = document.getElementById('mentor-search').value.toLowerCase();
        const expLevel = document.getElementById('experience-filter').value;
        const cards = document.querySelectorAll('.mentor-card');
        
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            const exp = card.querySelector('.experience-badge').textContent.toLowerCase();
            
            const matchesSearch = text.includes(searchText);
            const matchesExp = !expLevel || exp.includes(expLevel);
            
            card.style.display = (matchesSearch && matchesExp) ? 'block' : 'none';
        });
    }
    </script>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>