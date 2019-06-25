<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Библиотека</title>
    <script src=<?php echo $java_script[0]; ?>></script>
    <script src=<?php echo $java_script[1]; ?>></script>
    <link href="<?php echo $style_css[0]; ?>" rel="stylesheet">
</head>
<body>
<!--<div class = 'base_url' hidden = 'true'>--><?php //echo $base_url[0]; ?><!--</div>-->
<h1>Здесь будет база книжек</h1>
<div class = "tableblock">
    <?php
    echo ($lib);
     ?>
</div>


<?php

?>
<?php
//foreach ($authors as $column => $value) { ?>
<!---->
<!--    <h3> --><?php //echo($value['author_id'] . $value['full_name'] . '<br>'); ?><!-- </h3>-->
<!---->
<?php //}





?>
</body>
</html>

