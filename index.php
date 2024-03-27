<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>grav-main-slot</title>
</head>
<body>
    <?php $plugins = glob("plugins/*/index.php");?>
    <div>Plugins (<?= count($plugins)?>):
        <ol>
            <?php foreach ($plugins as $filename):?>
                <li><?= include $filename; ?></li>
            <?php endforeach ?>
        </ol>
    </div>
</body>
</html>
<?php 

