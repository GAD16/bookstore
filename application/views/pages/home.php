<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Библиотека</title>
</head>
<body>
<h1>Здесь будет база книжек</h1>
<?php
echo (count($lib));
  foreach ($lib as $column => $value) { ?>

   <h3> <?php echo($value['genre'] . $value['author'] . $value['name'] . $value['year'] . '<br>'); ?> </h3>

<?php }

?>
<?php
foreach ($authors as $column => $value) { ?>

    <h3> <?php echo($value['author_id'] . $value['full_name'] . '<br>'); ?> </h3>

<?php }

var_dump($compare);



?>
</body>
</html>

