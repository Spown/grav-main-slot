<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>grav-main-slot</title>
</head>
<body>
    <?php $plugins = glob("user/plugins/*/index.php");?>
    <div>Plugins (<?= count($plugins)?>):
        <ol>
        <?php foreach ($plugins as $plugin):?>
            <li><?php echo $plugin . ': ' ?><?php include_once $plugin ?></li>
        <?php endforeach ?>
        </ol>
    </div>
</body>
</html>
