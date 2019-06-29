<!DOCTYPE html>
<head>
</head>
<body>
    <div class="bodyBack">
        <form><input type="button" value="Добавить книгу" class="addBtnOnPage"></form>
        <div class="popup">
            <div class="popup_bg"></div>
            <div class="form">
                <form>
                    <p>Если у книги несколько жанров - введите их через запятую</p>
                    <div class = 'inputBox'>
                        <input class="updInput" type="text" name="genres">
                        <input class="updInput" type="text" name="author">
                        <input class="updInput" type="text" name="book">
                        <input class="updInput" type="text" name="year">
                    </div>
                    <input type="button" value="Заменить данные" class = "updateButton">
                    <input type="button" value="Добавить книгу" class="addButton">
                </form>
            </div>
        </div>
        <div class = 'tableBlock'>
            <?php
            echo ($lib);
             ?>
        </div>
    </div>
</body>
</html>

