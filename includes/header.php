<?php
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'El Agreval'; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    
    <?php if (isset($customStyles) && $customStyles): ?>
    <link href="assets/css/styles.css" rel="stylesheet" type="text/css" />
    <?php endif; ?>
    

    <script>
       
        document.documentElement.setAttribute('data-theme', '<?php echo isset($theme) ? $theme : 'light'; ?>')
    </script>
</head>
<body>
 
    <div class="min-h-screen bg-base-100">
 