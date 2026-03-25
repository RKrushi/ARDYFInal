<?php
/* ══════════════════════════════════════════════════════════════
   Admin Panel - View Contact Submissions
   Simple admin interface to view database entries
══════════════════════════════════════════════════════════════ */

require_once 'db-config.php';

// Simple password protection (change this!)
$ADMIN_PASSWORD = 'ardy2026'; // Change this password!

session_start();

// Check login
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === $ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: admin-panel.php');
            exit;
        } else {
            $login_error = 'Incorrect password';
        }
    }
    
    // Show login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - ARDY Real Estate</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
                background: linear-gradient(135deg, #3d2a23 0%, #523a31 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .login-box {
                background: white;
                padding: 40px;
                border-radius: 8px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.4);
                width: 100%;
                max-width: 400px;
            }
            h1 {
                color: #3d2a23;
                font-size: 24px;
                margin-bottom: 8px;
            }
            .subtitle {
                color: #635840;
                font-size: 14px;
                margin-bottom: 30px;
            }
            input[type="password"] {
                width: 100%;
                padding: 12px 15px;
                border: 2px solid #e0e0e0;
                border-radius: 6px;
                font-size: 14px;
                margin-bottom: 15px;
            }
            input[type="password"]:focus {
                outline: none;
                border-color: #c4b693;
            }
            .btn {
                width: 100%;
                padding: 12px;
                background: #3d2a23;
                color: white;
                border: none;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.3s;
            }
            .btn:hover {
                background: #2a1c17;
            }
            .error {
                background: #f8d7da;
                color: #721c24;
                padding: 12px;
                border-radius: 6px;
                margin-bottom: 15px;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h1>🔐 Admin Panel</h1>
            <p class="subtitle">ARDY Real Estate - Contact Submissions</p>
            
            <?php if (isset($login_error)): ?>
                <div class="error">❌ <?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="password" name="password" placeholder="Enter admin password" required autofocus>
                <button type="submit" class="btn">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-panel.php');
    exit;
}

// Handle status update
if (isset($_POST['update_status'])) {
    $id = intval($_POST['submission_id']);
    $status = $_POST['status'];
    
    try {
        $pdo = getDBConnection();
        $sql = "UPDATE contact_submissions SET status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $update_message = "Status updated successfully!";
    } catch (PDOException $e) {
        $update_error = "Failed to update status: " . $e->getMessage();
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Fetch submissions
try {
    $pdo = getDBConnection();
    
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }
    
    $sql = "SELECT * FROM contact_submissions WHERE 1=1";
    
    if ($filter !== 'all') {
        $sql .= " AND status = :status";
    }
    
    if ($search) {
        $sql .= " AND (name LIKE :search OR email LIKE :search OR phone LIKE :search)";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    
    if ($filter !== 'all') {
        $stmt->bindParam(':status', $filter);
    }
    
    if ($search) {
        $search_param = "%{$search}%";
        $stmt->bindParam(':search', $search_param);
    }
    
    $stmt->execute();
    $submissions = $stmt->fetchAll();
    
    // Get counts
    $count_sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new,
                    SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed
                  FROM contact_submissions";
    $count_result = $pdo->query($count_sql)->fetch();
    
} catch (Exception $e) {
    $db_error = $e->getMessage();
    $submissions = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Contact Submissions</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #f7f5f4;
            color: #3d2a23;
        }
        .header {
            background: linear-gradient(135deg, #3d2a23 0%, #523a31 100%);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 300;
        }
        .header h1 span {
            color: #c4b693;
        }
        .logout-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            padding: 8px 16px;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
            transition: background 0.3s;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 4px solid #c4b693;
        }
        .stat-card h3 {
            font-size: 14px;
            color: #635840;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: #3d2a23;
        }
        .controls {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-btn {
            padding: 8px 16px;
            border: 2px solid #e0e0e0;
            background: white;
            color: #635840;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }
        .filter-btn.active {
            background: #3d2a23;
            color: white;
            border-color: #3d2a23;
        }
        .filter-btn:hover {
            border-color: #c4b693;
        }
        .search-box {
            flex: 1;
            min-width: 250px;
        }
        .search-box input {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }
        .search-box input:focus {
            outline: none;
            border-color: #c4b693;
        }
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f7f5f4;
            padding: 15px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #635840;
            border-bottom: 2px solid #e5dfd7;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #f0ebe5;
            font-size: 14px;
        }
        tr:hover {
            background: #fafaf9;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge.new {
            background: #d4edda;
            color: #155724;
        }
        .badge.contacted {
            background: #cce5ff;
            color: #004085;
        }
        .badge.closed {
            background: #e2e3e5;
            color: #383d41;
        }
        .badge.success {
            background: #d4edda;
            color: #155724;
        }
        .badge.failed {
            background: #f8d7da;
            color: #721c24;
        }
        .actions select {
            padding: 5px 10px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
        }
        .message-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #635840;
        }
        .no-data {
            padding: 60px;
            text-align: center;
            color: #9b8972;
        }
        .no-data svg {
            width: 64px;
            height: 64px;
            margin-bottom: 16px;
            opacity: 0.3;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        @media(max-width: 768px) {
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
            .controls {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="#home" class="nav-logo">
    <img src="images/logo.png" alt="ARDY Real Estate"/>
       </a>
        <h1>🏢 ARDY Real Estate <span>Admin Panel</span></h1>
        <a href="?logout=1" class="logout-btn">Logout</a>
    </div>
    
    <div class="container">
        
        <?php if (isset($update_message)): ?>
            <div class="alert success">✅ <?php echo $update_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($update_error)): ?>
            <div class="alert error">❌ <?php echo $update_error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($db_error)): ?>
            <div class="alert error">
                ❌ <strong>Database Error:</strong> <?php echo htmlspecialchars($db_error); ?><br>
                <small>Make sure you've run setup-database.sql in phpMyAdmin</small>
            </div>
        <?php endif; ?>
        
        <!-- Stats -->
        <div class="stats">
            <div class="stat-card">
                <h3>Total Submissions</h3>
                <div class="number"><?php echo $count_result['total'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h3>New</h3>
                <div class="number"><?php echo $count_result['new'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h3>Contacted</h3>
                <div class="number"><?php echo $count_result['contacted'] ?? 0; ?></div>
            </div>
            <div class="stat-card">
                <h3>Closed</h3>
                <div class="number"><?php echo $count_result['closed'] ?? 0; ?></div>
            </div>
        </div>
        
        <!-- Controls -->
        <div class="controls">
            <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
            <a href="?filter=new" class="filter-btn <?php echo $filter === 'new' ? 'active' : ''; ?>">New</a>
            <a href="?filter=contacted" class="filter-btn <?php echo $filter === 'contacted' ? 'active' : ''; ?>">Contacted</a>
            <a href="?filter=closed" class="filter-btn <?php echo $filter === 'closed' ? 'active' : ''; ?>">Closed</a>
            
            <form class="search-box" method="GET">
                <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                <input type="text" name="search" placeholder="Search by name, email, or phone..." value="<?php echo htmlspecialchars($search); ?>">
            </form>
        </div>
        
        <!-- Table -->
        <div class="table-container">
            <?php if (empty($submissions)): ?>
                <div class="no-data">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M20 6h-2.18c.11-.31.18-.65.18-1 0-1.66-1.34-3-3-3-1.05 0-1.96.54-2.5 1.35l-.5.67-.5-.68C10.96 2.54 10.05 2 9 2 7.34 2 6 3.34 6 5c0 .35.07.69.18 1H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-5-2c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zM9 4c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm11 15H4v-2h16v2zm0-5H4V8h5.08L7 10.83 8.62 12 11 8.76l1-1.36 1 1.36L15.38 12 17 10.83 14.92 8H20v6z"/>
                    </svg>
                    <h3>No submissions found</h3>
                    <p>Contact form submissions will appear here</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date & Time</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Message</th>
                            <th>Email Status</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $sub): ?>
                            <tr>
                                <td><strong>#<?php echo $sub['id']; ?></strong></td>
                                <td><?php echo date('M d, Y', strtotime($sub['created_at'])); ?><br>
                                    <small style="color:#9b8972;"><?php echo date('H:i', strtotime($sub['created_at'])); ?></small>
                                </td>
                                <td><strong><?php echo htmlspecialchars($sub['name']); ?></strong></td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($sub['email']); ?>" style="color:#3d2a23;text-decoration:none;">
                                        <?php echo htmlspecialchars($sub['email']); ?>
                                    </a><br>
                                    <small style="color:#635840;"><?php echo htmlspecialchars($sub['phone']); ?></small>
                                </td>
                                <td>
                                    <div class="message-preview" title="<?php echo htmlspecialchars($sub['message']); ?>">
                                        <?php echo htmlspecialchars($sub['message'] ?: '(no message)'); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $sub['email_sent'] ? 'success' : 'failed'; ?>">
                                        <?php echo $sub['email_sent'] ? '✓ Sent' : '✗ Failed'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $sub['status']; ?>">
                                        <?php echo ucfirst($sub['status']); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="submission_id" value="<?php echo $sub['id']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="">Change...</option>
                                            <option value="new">New</option>
                                            <option value="contacted">Contacted</option>
                                            <option value="closed">Closed</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
    </div>
</body>
</html>
