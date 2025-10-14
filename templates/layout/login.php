<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->fetch('title') ?: 'Admin Login' ?> - Hospital Platform</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Login CSS -->
    <?php echo $this->Html->css('/assets/admin/css/login.css') ?>
    
    <?php echo $this->Html->meta('icon') ?>
    <?php echo $this->fetch('meta') ?>
    <?php echo $this->fetch('css') ?>
</head>
<body class="bg-white">
    <!-- Login Content Only -->
    <?php echo $this->fetch('content') ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Admin Login JS -->
    <?php echo $this->Html->script('/assets/admin/js/login.js') ?>
    
    <?php echo $this->fetch('script') ?>
</body>
</html>