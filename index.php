<?php
session_start();
require_once 'config/config.php';

if (!isset($_SESSION['fb_access_token'])) {
    header('Location: login.php');
    exit;
}

$searchTerm = $_GET['search'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$query = "SELECT * FROM leads WHERE lead_data LIKE ?";

if ($dateFilter) {
    $query .= " AND created_at = ?";
}

if ($statusFilter) {
    $query .= " AND status = ?";
}

$query .= " ORDER BY created_at DESC LIMIT 10";

$stmt = $conn->prepare($query);
$searchTerm = '%' . $searchTerm . '%';
if ($dateFilter && $statusFilter) {
    $stmt->bind_param("sss", $searchTerm, $dateFilter, $statusFilter);
} elseif ($dateFilter) {
    $stmt->bind_param("ss", $searchTerm, $dateFilter);
} elseif ($statusFilter) {
    $stmt->bind_param("ss", $searchTerm, $statusFilter);
} else {
    $stmt->bind_param("s", $searchTerm);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads Management</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="my-4">Manage Leads</h1>
        <form method="GET" class="mb-4">
            <input type="text" name="search" placeholder="Search leads..." class="form-control" value="<?php echo htmlspecialchars($searchTerm); ?>">
            <input type="date" name="date" placeholder="Filter by date..." class="form-control mt-2" value="<?php echo htmlspecialchars($dateFilter); ?>">
            <select name="status" class="form-control mt-2">
                <option value="">Filter by status...</option>
                <option value="New" <?php echo $statusFilter == 'New' ? 'selected' : ''; ?>>New</option>
                <option value="Contacted" <?php echo $statusFilter == 'Contacted' ? 'selected' : ''; ?>>Contacted</option>
                <option value="Converted" <?php echo $statusFilter == 'Converted' ? 'selected' : ''; ?>>Converted</option>
                <option value="Not Interested" <?php echo $statusFilter == 'Not Interested' ? 'selected' : ''; ?>>Not Interested</option>
            </select>
            <button type="submit" class="btn btn-primary mt-2">Search</button>
        </form>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Lead Data</th>
                    <th>Status</th>
                    <th>Follow Up</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['lead_data']); ?></td>
                        <td>
                            <select class="form-control" data-lead-id="<?php echo $row['id']; ?>">
                                <option value="New" <?php echo $row['status'] == 'New' ? 'selected' : ''; ?>>New</option>
                                <option value="Contacted" <?php echo $row['status'] == 'Contacted' ? 'selected' : ''; ?>>Contacted</option>
                                <option value="Converted" <?php echo $row['status'] == 'Converted' ? 'selected' : ''; ?>>Converted</option>
                                <option value="Not Interested" <?php echo $row['status'] == 'Not Interested' ? 'selected' : ''; ?>>Not Interested</option>
                            </select>
                        </td>
                        <td><input type="text" class="form-control" value="<?php echo htmlspecialchars($row['follow_up_details']); ?>" data-lead-id="<?php echo $row['id']; ?>"></td>
                        <td>
                            <button class="btn btn-danger delete-lead" data-lead-id="<?php echo $row['id']; ?>">Delete</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <nav>
            <ul class="pagination">
                <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                <li class="page-item"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">Next</a></li>
            </ul>
        </nav>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
