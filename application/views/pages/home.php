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

            $result =('');

            $result .= '   <table border="0" cellpadding="4" cellspacing="0">
                            <thead>
                                <tr>
                                    <td>Жанр</td><td>Автор</td><td>Название</td><td>Год издания</td><td>Удалить</td>
                                 </tr>
                             </thead>
                             <tbody>';
            foreach ($lib as $row) {
                $result .= '<tr>';
                foreach ($row as $fild => $cel) {
                    if ($fild == 'id'){
                        $result .= "<td class = 'setID' data-id = '$cel'><img class = 'delButton' src='/img/x.png' alt='x'></td>";
                    }
                    else {
                        $result .= "<td><div class = 'cell'>$cel</div></td>";
                    }
                }
                $result .= '</tr>';
            }
            $result .= '</tbody>
                    </table>';
            echo $result;
             ?>
        </div>
    </div>
</body>
</html>

