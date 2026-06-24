<?php
// Redirect to combined gallery manager inside portfolio.php
$project_id = intval($_GET['project_id'] ?? $_GET['id'] ?? 0);
if ($project_id > 0) {
    header("Location: portfolio.php?action=gallery&id=" . $project_id);
} else {
    header("Location: portfolio.php");
}
exit;
