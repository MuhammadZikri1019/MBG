<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Under Maintenance - MBG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .maintenance-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .icon-wrapper {
            width: 100px;
            height: 100px;
            background: #e3f2fd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .icon-wrapper i {
            font-size: 50px;
            color: #2196f3;
        }
        h1 {
            color: #333;
            margin-bottom: 15px;
            font-weight: 700;
        }
        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .btn-login {
            background: #2196f3;
            color: white;
            padding: 10px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: #1976d2;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(33, 150, 243, 0.3);
        }
    </style>
</head>
<body>
    <div class="maintenance-card">
        <div class="icon-wrapper">
            <i class="bi bi-tools"></i>
        </div>
        <h1>Sedang Dalam Perbaikan</h1>
        <p>Maaf, sistem sedang dalam mode maintenance untuk peningkatan performa. Silakan kembali lagi nanti.</p>
        
        <?php if(isset($_GET['login'])): ?>
            <div class="alert alert-info mt-3">
                <small>Admin login access only</small>
            </div>
            <a href="login.php" class="btn-login">Login Admin</a>
        <?php else: ?>
            <a href="?login=true" class="text-muted text-decoration-none" style="font-size: 12px;">Admin Access</a>
        <?php endif; ?>
    </div>
</body>
</html>
