<?php
require_once 'config.php';
requireRole('superadmin');
$pageTitle = 'UPHSD Test Bank - Database Editor';

$queryResult = null;
$queryError = '';
$queryText = '';
$affectedRows = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sql_query'])) {
    $queryText = trim($_POST['sql_query']);
    if ($queryText) {
        $result = $conn->query($queryText);
        if ($result === false) {
            $queryError = $conn->error;
        } elseif ($result === true) {
            $affectedRows = $conn->affected_rows;
        } else {
            $queryResult = $result;
        }
    }
}

include 'includes/header.php';
?>
<div class="main-content">
    <a href="dashboard.php" class="btn btn-ghost mb-4"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Back to Dashboard</a>

    <div class="card sql-editor mb-6">
        <div class="card-header"><h2 class="card-title">SQL Query Editor</h2><p class="card-description">Execute raw SQL queries on the database</p></div>
        <div class="card-content">
            <form method="POST">
                <div class="form-group">
                    <textarea name="sql_query" class="form-control" placeholder="SELECT * FROM users_new LIMIT 10;" style="font-family:'Consolas',monospace;background:#1e1e2e;color:#cdd6f4;min-height:160px;"><?= htmlspecialchars($queryText) ?></textarea>
                </div>
                <button type="submit" class="btn btn-maroon" onclick="return confirm('Execute this query?')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    Execute Query
                </button>
            </form>
        </div>
    </div>

    <?php if ($queryError): ?>
    <div class="flash flash-error mb-4"><?= htmlspecialchars($queryError) ?></div>
    <?php endif; ?>

    <?php if ($queryResult && $queryResult instanceof mysqli_result): ?>
    <div class="card sql-results">
        <div class="card-header"><h3 class="card-title">Results (<?= $queryResult->num_rows ?> rows)</h3></div>
        <div class="card-content"><div class="table-wrapper">
            <table>
                <thead><tr>
                    <?php $fields = $queryResult->fetch_fields(); foreach ($fields as $f): ?>
                    <th><?= htmlspecialchars($f->name) ?></th>
                    <?php endforeach; ?>
                </tr></thead>
                <tbody>
                    <?php while ($row = $queryResult->fetch_assoc()): ?>
                    <tr>
                        <?php foreach ($row as $val): ?>
                        <td><?= htmlspecialchars($val ?? 'NULL') ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div></div>
    </div>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$queryError): ?>
    <div class="flash flash-success">Query executed successfully. <?= $affectedRows ?> row(s) affected.</div>
    <?php endif; ?>
</div>
</body></html>
