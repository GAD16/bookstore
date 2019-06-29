<?php

class books_model extends CI_Model {




    public function __construct()    {

        $this->load->database();
    }
    public function row_by_id ($id) {
        $query = $this->db->query("
            SELECT GROUP_CONCAT(genres.genre) as genre, full_name AS author, name, year, id
            FROM books
            JOIN book_genres ON book_genres.book_id = books.id
            JOIN genres ON genres.genre_id = book_genres.genre_id
            JOIN book_authors ON book_authors.book_id = books.id
            JOIN authors ON authors.author_id = book_authors.author_id
            WHERE books.id = $id
            GROUP BY books.id");
        $row = $query->row();
        $result = ('');
        foreach ($row as $fild => $cel) {
            if ($fild == 'id'){
                $result .= "<td class = 'setID' data-id = '$cel'><img class = 'delButton' src='/img/x.png' alt='x'></td>";
            }
            else {
                $result .= "<td><div class = 'cell'>$cel</div></td>";
            }
        }
        return ($result);
    }
    public function table () {
        $lib = $this->get_lib();
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
        return ($result);
    }

    private function get_lib() {
        //Выбирает жанр(через запятую если их несколько), автор, название, год
       $query = $this->db->query('
            SELECT GROUP_CONCAT(genres.genre) as genre, full_name AS author, name, year, id
            FROM books
            JOIN book_genres ON book_genres.book_id = books.id
            JOIN genres ON genres.genre_id = book_genres.genre_id
            JOIN book_authors ON book_authors.book_id = books.id
            JOIN authors ON authors.author_id = book_authors.author_id
            GROUP BY books.id');
       $result = $query->result_array();

       return $result;

    }

    public function upd_book ($genres, $author, $book, $year, $id){
        //обновляет записи во всех таблицах
        //если нет ID возвращает 0
        //при обновлении только жанров возвратит 1
        //при обновлении только авторов возвратит 2
        //при обновлении только книги или (и) года возвратит 4
        // при обновлении нескольких параметров возвратит суммарный результат 1-7

    if (!$id) {
        return (0);
    }
    $chek = $this->chek_received_data($genres, $author, $book, $year);
    if (!($chek == 'ok')) {
        return ($chek);
    }
    $res = 0;
    if ($genres) {
        $res = $this->upd_genres($genres, $id);
    }
    if ($author) {
        $res = $res + $this->upd_author($author, $id);
    }
    if (!$book and !$year) {
        return ($res);
    }
    else {
        if ($book) {
            $book = $this->normal_book($book);
            $data = array('name' => $book);
            $this->db->where('id', $id);
            $this->db->update('books', $data);
        }
        if ($year) {
            $data = array('year' => $year);
            $this->db->where('id', $id);
            $this->db->update('books', $data);
        }
        return ($res + 4);
    }
    }

    private function upd_genres ($genres, $id) {
        //функция проверяет наличие жанров в базе, добавляет их в базу если нужно
        //удаляет жанры которые больше нигде не используются
        //обновляет ссылочные таблицы, добавляет в них новые записи если нужно, удаляет неиспользуемые
        //возвращает 0 если полученые жанры полностью совпали с данными базы
        //возвращает 1 если хоть один жанр был обновлен
        if(!$genres or !$id){
            return (0);
        }
        $genres = $this->normal_genre($genres);
        $query = $this->db->query("SELECT genres.genre_id, genres.genre
                          FROM genres
                          JOIN book_genres ON genres.genre_id = book_genres.genre_id
                          WHERE book_genres.book_id = $id");

        $old_ids = $query->result_array();
        // сравнение новых и старых жанров. ключи совпадающих в массивах delkeys
        $oldDelKeys = array();
        $newDelKeys = array();
        foreach ($old_ids as $oldkey => $old_id) {
            foreach ($genres as $newkey => $genre) {
                if ($genre == $old_id['genre']) {
                    $oldDelKeys [] = $oldkey;
                    $newDelKeys [] = $newkey;
                }

            }
        }

        foreach ($newDelKeys as $key){
            array_splice($genres, $key,1);
        }

        if (!$genres){
            return (0);
        }

        foreach ($oldDelKeys as $key){
            array_splice($old_ids, $key,1);
        }


        foreach ($genres as $key => $genre){
            $this->db->select('genre_id');
            $this->db->from('genres');
            $this->db->where('genre', $genre);
            $query = $this->db->get();
            $row = $query->row();
            if ($row){
                $result = $row->genre_id;
                $genres = array_reverse($genres, true);
                $genres [$result] = $genres[$key];
                $genres = array_reverse($genres, true);
                array_splice($genres, $key,1);
            }
            else {
                if ($old_ids[0]) {
                    $data = array('genre' => $genre);
                    $this->db->where('genre_id', $old_ids[0]['genre_id']);
                    $this->db->update('genres', $data);
                    unset ($old_ids[0]);
                    unset ($genres[$key]);
                }
                else {
                    $data = array('genre' => $genre);
                    $this->db->insert('genres', $data);
                    $this->db->select('genre_id');
                    $this->db->from('genres');
                    $this->db->where('genre', $genre);
                    $query = $this->db->get();
                    $row = $query->row();
                    $result = $row->genre_id;
                    $genres = array_reverse($genres, true);
                    $genres [$result] = $genres[$key];
                    $genres = array_reverse($genres, true);
                    unset ($genres[$key]);
                }
            }
        }
        if ($old_ids) {
            foreach ($old_ids as $old_id) {

                $this->db->from('book_genres');
                $this->db->where('genre_id', $old_id['genre_id']);
                $query = $this->db->get();
                $result_arr = $query->result_array();
                $count = (count($result_arr));
                if ($count == 1) {
                    $garr = array('genre_id' => $old_id['genre_id']);
                    $this->db->delete('genres', $garr);
                }

            }
        }
        if ($genres) {
            foreach ($genres as $key => $genre) {
                $data = array('book_id' => $id,
                              'genre_id' => $key);
                $this->db->insert('book_genres', $data);
            }
        }
        return (1);
    }

    private function upd_author ($author, $id) {
        //функция проверяет наличие автора в базе, добавляет его в базу если нужно
        //удаляет автора которые больше нигде не используется
        //обновляет ссылочную таблицу
        //возвращает 0 если автор совпал с данными базы
        //возвращает 2 если данные обновились
        if(!$author or !$id){
            return (0);
        }
        $author = $this->normal_name($author);
        $query = $this->db->query("SELECT authors.author_id, authors.full_name
                          FROM authors
                          JOIN book_authors ON authors.author_id = book_authors.author_id
                          WHERE book_id = $id");

        $old_author = $query->result_array();
        if ($old_author[0]['full_name'] == $author) {
            return(0);
        }
        $this->db->select('author_id');
        $this->db->from('authors');
        $this->db->where('full_name', $author);
        $query = $this->db->get();
        $row = $query->row();
        if ($row){
            $result = $row->author_id;
            $author = array($result, $author);
        }
        else {
            $data = array('full_name' => $author);
            $this->db->insert('authors', $data);
            $this->db->select('author_id');
            $this->db->from('authors');
            $this->db->where('full_name', $author);
            $query = $this->db->get();
            $row = $query->row();
            $result = $row->author_id;
            $author = array($result, $author);
        }
        $data = array('author_id' => $author[0]);
                $this->db->where('book_id', $id);
                $this->db->update('book_authors', $data);

        $this->db->select('book_id');
        $this->db->from('book_authors');
        $this->db->where('author_id', $old_author[0]['author_id']);
        $query = $this->db->get();
        $result = $query->result_array();
        if (!$result) {
            $data = array('author_id' => $old_author[0]['author_id']);
            $this->db->delete('authors', $data);
        }
         return(2);
    }



    public function del_book ($id){

        //Удаляет книгу и записи о ней из ссылочных таблиц.
        //Если у автора книги нет других ссылок удалает и автора
        //Если у жанра нет других ссылок - удаляет и жанр
        //возвращает
        // 0 - удалена только книга и ссылки;
        // 1 - удален автор
        // 2 - удален жанр
        // 3 - удален автор и жанр
        // false -нет книги с таким ID

        $ret = 0;
        $this->db->select('name');
        $this->db->from('books');
        $this->db->where('id', $id);
        $query = $this->db->get();
        $row = $query->row();
        $book = $row->name;
        if ($book == null) {
            return (false);
        }
        $this->db->select('author_id');
        $this->db->from('book_authors');
        $this->db->where('book_id', $id);
        $query = $this->db->get();
        $row = $query->row();
        $author_id = $row->author_id;


        if ($author_id) {
            $this->db->from('book_authors');
            $this->db->where('author_id', $author_id);
            $query = $this->db->get();
            $result = $query->result_array();
            $count = (count($result));
            if ($count == 1) {
                $aarr = array('author_id' => $author_id);
                $this->db->delete('authors', $aarr);
                $ret++;
            }
        }
        $this->db->select('genre_id');
        $this->db->from('book_genres');
        $this->db->where('book_id', $id);
        $query = $this->db->get();
        $result = $query->result_array();


        if ($result) {
            foreach ($result as $row) {

                $this->db->from('book_genres');
                $this->db->where('genre_id', $row['genre_id']);
                $query = $this->db->get();
                $result_arr = $query->result_array();
                $count = (count($result_arr));
                if ($count == 1) {
                    $garr = array('genre_id' => $row['genre_id']);
                    $this->db->delete('genres', $garr);
                    $flag = true;

                }
            }
            if ($flag = true) {
                $ret = $ret + 2;
            }
        }
        $arr = array('id' => $id);
        $this->db->delete('books', $arr);


        return ( $ret);
    }


    //добавляет запись в базу, если такой книги еще нет. Возвращает ID новой или уже существующей
    public function save_book ($genre, $author, $book, $year) {

    if ($genre=='' or $author=='' or $book=='' or $year==0) {
            return ("не все поля заполнены");
        }

    $check = $this->chek_received_data($genre, $author, $book, $year);
    if ($check == 'ok'){
        $g = array();
        $genre = $this->normal_genre($genre);
        $author = $this->normal_name($author);
        $book = $this->normal_book($book);

        $cbr = $this->compare_base($genre, $author, $book);
        if (!($cbr[0]==false)){
            return ('такая книга уже добавлена');
        }
        else {
            $i = $this->add_genre($genre, $cbr);
            $d = $this->add_author($author, $cbr);
            $id = array($i, $d);
            $result = $this->add_book($book, $year, $id);

            return ($result);
        }
    }
    else {
        return ($check);
    }
    }

    private function chek_received_data ($genre, $author, $book, $year)  {

        if ($genre and intval($genre)<>0) {
            return ("не верный формат введенных данных");
        }

        if ($author and intval($genre)<>0){
            return ("не верный формат введенных данных");
        }
        if ($book and intval($genre)<>0){
            return ("не верный формат введенных данных");
        }
        if ($year and (!is_int($year) | (strlen($year) <> 4))) {
            return ("не верный формат года");
        }

        if ($year and ($year > date('Y'))) {
            return ("год написания книги не может быть больше текущего");
        }
        return ('ok');
        }


    private function compare_base ($genre, $author, $book) {
        //Функция проверяет наличие полученных значений в базе. Если такие есть - возвращает их ID в массиве $CBR
            $cbr = array();
            //Проверяем нет ли в базе уже такой книги

            $this->db->select('id');
            $this->db->from('books');
            $this->db->where('name', $book);
            $query = $this->db->get();
            $row = $query->row();

            if ($row) {
                $book_id = $row->id;
                array_push($cbr, $book_id);
            }
            else {
                array_push($cbr, false);
            }

            //проверяем нет ли в базе такого жанра

            $g_ids = array();
            foreach ($genre as $g) {
                $this->db->select('genre_id');
                $this->db->from('genres');
                $this->db->where('genre', $g);
                $query = $this->db->get();
                $row = $query->row();

                if ($row) {
                    $genre_id = $row->genre_id;
                    array_push($g_ids, $genre_id);
                } else {
                    array_push($g_ids, false);
                }
            }
            array_push($cbr, $g_ids);

            //проверяем нет ли в базе такого автора

            $this->db->select('author_id');
            $this->db->from('authors');
            $this->db->where('full_name', $author);
            $query = $this->db->get();
            $row = $query->row();

            if ($row){
                $author_id = $row->author_id;
                array_push($cbr, $author_id);
            }
            else {
                array_push($cbr, false);
            }
            return ($cbr);
        }

    private    function add_genre($genre, $cbr)
    {
        //Функция вставляет в базу значение genre если в массиве CBR не получает уже существующий id этого жанра
        //Возвращает ID вставленной строки, или ID из CBR.
        $i = 0;
        $result = array();
        foreach ($cbr[1] as $g) {
            if (!$g) {
                $data = array('genre' => $genre[$i]);
                $this->db->insert('genres', $data);
                $this->db->select('genre_id');
                $this->db->from('genres');
                $this->db->where('genre', $genre[$i]);
                $query = $this->db->get();
                $row = $query->row();
                $id = $row->genre_id;
                array_push($result, $id);

            } else {
                array_push($result, $g);
            }
            $i++;
        }
        return ($result);
    }

    private    function add_author($author, $cbr){
        if (!$cbr[2]){
            $data = array('full_name' => $author);
            $this->db->insert('authors', $data);
            $this->db->select('author_id');
            $this->db->from('authors');
            $this->db->where('full_name',$author);
            $query = $this->db->get();
            $row = $query->row();
            $result = $row->author_id;

            return ($result);
        }
        else {
            return ($cbr[2]);
        }
}

    private    function add_book($book, $year, $ids) {
        $data = array(
            'name' => $book,
            'year' => $year,
        );

        $this->db->insert('books', $data);
        $this->db->select('id');
        $this->db->from('books');
        $this->db->where('name',$book);
        $query = $this->db->get();
        $row = $query->row();
        $book_id = $row->id;

        foreach ($ids[0] as $elem) {

            $data = array(
                'book_id' => $book_id,
                'genre_id' => $elem
            );

            $this->db->insert('book_genres', $data);
        }

        $data = array(
            'book_id' => $book_id,
            'author_id' => $ids[1]
        );

        $this->db->insert('book_authors', $data);

        return ($book_id);
    }





    private   function normal_name($n){
       //функция приводит строку к виду ФИО. То есть делит на отдельные слова, удаляет знаки припинания и пробелы,
        //каждое новое слово пишет с большой буквы.
        $n = preg_replace('/[^а-яёa-z\s]/iu', ' ', $n);

        $n = mb_strtolower($n, 'UTF-8');


        for ($i = 0, $arr = [], $size = strlen($n); $i < $size; $i++) {
            $b = mb_substr($n, $i, 1, 'UTF-8');
            if ($b == '') {
            } else {
                array_push($arr, $b);
            }
        }


        $uper = true;
        $del = true;
        $result = [];
        foreach ($arr as $liter) {
            if ($liter == " ") {
                $uper = true;
                if ($del == false) {
                    $del = true;
                    array_push($result, $liter);
                    //var_dump("пробел найден но не удален" . '"' . $liter . '"');
                }
                //var_dump("пробел найден И удален" . '"' . $liter . '"');
            } else {
                $del = false;
                if ($uper == true) {
                    $uper = false;
                    array_push($result, mb_strtoupper($liter, 'UTF-8'));
                    //var_dump("Буква сделана большой и помещена в моссив" . '"' . $liter . '"');
                } else {
                    array_push($result, $liter);
                    //var_dump("просто помещена в массив как есть" . '"' . $liter . '"');
                }
            }
        }

        $size = count($result);
        $string = '';
        if ($result[$size - 1] == ' ') {
            array_pop($result);
            //var_dump('выполняется c пробелом в конце строки');
            foreach ($result as $liter) {

                $string = $string . $liter;

            }
        } else {
            //var_dump('выполняется без пробелоа  в конце строки');
            foreach ($result as $liter) {

                $string = $string . $liter;
            }
        }
        // $string = preg_replace('/[^а-яёa-z\s]/iu', '', $string);
        return $string;

    }


    private function normal_genre($genres){
        //функция приводит строку к виду словосочетания или одного слова. То есть делит на отдельные слова, удаляет знаки
        //припинания и пробелы,первое слово пишет с большой буквы.
        $genres = explode(",", $genres);
        $resultarray = array();
        foreach ($genres as $n) {
            $n = preg_replace('/[^а-яёa-z\s]/iu', ' ', $n);

            $n = mb_strtolower($n, 'UTF-8');


            for ($i = 0, $arr = [], $size = strlen($n); $i < $size; $i++) {
                $b = mb_substr($n, $i, 1, 'UTF-8');
                if ($b == '') {
                } else {
                    array_push($arr, $b);
                }
            }


            $uper = true;
            $del = true;
            $result = [];
            foreach ($arr as $liter) {
                if ($liter == " ") {
                    //$uper = true;  если раскоментировать - каждое новое слово будет начинаться с бошьшой буквы
                    if ($del == false) {
                        $del = true;
                        array_push($result, $liter);
                        //var_dump("пробел найден но не удален" . '"' . $liter . '"');
                    }
                    //var_dump("пробел найден И удален" . '"' . $liter . '"');
                } else {
                    $del = false;
                    if ($uper == true) {
                        $uper = false;
                        array_push($result, mb_strtoupper($liter, 'UTF-8'));
                        //var_dump("Буква сделана большой и помещена в моссив" . '"' . $liter . '"');
                    } else {
                        array_push($result, $liter);
                        //var_dump("просто помещена в массив как есть" . '"' . $liter . '"');
                    }
                }
            }

            $size = count($result);
            $string = null;
            if ($result[$size - 1] == ' ') {
                array_pop($result);
                //var_dump('выполняется c пробелом в конце строки');
                foreach ($result as $liter) {

                    $string = $string . $liter;

                }
            } else {
                //var_dump('выполняется без пробелоа  в конце строки');
                foreach ($result as $liter) {

                    $string = $string . $liter;
                }
            }
            // $string = preg_replace('/[^а-яёa-z\s]/iu', '', $string);
            $resultarray [] = $string;
        }
        return ($resultarray);
    }


    private     function normal_book($n){
        //функция удаляет из строки знаки припинания кроме точек и запятых.
        //первое слово делает с большой буквы. Слово после точки делает с большой буквы
        $n = preg_replace('/[^а-яёa-z\s\.\,]/iu', ' ', $n);

        $first = mb_substr($n,0,1, 'UTF-8');//первая буква
        $last = mb_substr($n,1);//все кроме первой буквы
        $first = mb_strtoupper($first, 'UTF-8');
        $n = $first.$last;

        //$n = mb_strtolower($n, 'UTF-8');


        for ($i = 0, $arr = [], $size = strlen($n); $i < $size; $i++) {
            $b = mb_substr($n, $i, 1, 'UTF-8');
            if ($b == '') {
            } else {
                array_push($arr, $b);
            }
        }


        $point = true;
        $del = true;
        $result = [];
        foreach ($arr as $liter) {
            if ($liter == " ") {
                //$uper = true;  если раскоментировать - каждое новое слово будет начинаться с бошьшой буквы
                if ($del == false) {
                    $del = true;
                    array_push($result, $liter);
                    //var_dump("пробел найден но не удален" . '"' . $liter . '"');
                }
                //var_dump("пробел найден И удален" . '"' . $liter . '"');
            } else {

                if ($liter == '.'){
                    $point = true;
                    array_push($result, $liter);
                }
                else {
                    if ($del == true){

                        if ($point == true){
                            $point = false;
                            array_push($result, mb_strtoupper($liter, 'UTF-8'));
                            //var_dump("Буква сделана большой и помещена в моссив" . '"' . $liter . '"');
                    }
                        else {
                            array_push($result, $liter);
                            //var_dump("просто помещена в массив как есть" . '"' . $liter . '"');
                        }
                       $del = false;
                    }
                    else {
                        if ($point == true) {
                            $point = false;
                            array_push($result, ' ');
                            array_push($result, mb_strtoupper($liter, 'UTF-8'));
                            //После точки добавлен пробел, буква сделана большой и помещена в массив

                        } else {
                            array_push($result, $liter);
                            //var_dump("просто помещена в массив как есть" . '"' . $liter . '"');
                        }
                    }
                }
            }
        }

        $size = count($result);
        $string = null;
        if ($result[$size - 1] == ' ') {
            array_pop($result);
            //var_dump('выполняется c пробелом в конце строки');
            foreach ($result as $liter) {

                $string = $string . $liter;

            }
        } else {
            //var_dump('выполняется без пробелоа  в конце строки');
            foreach ($result as $liter) {

                $string = $string . $liter;
            }
        }
        // $string = preg_replace('/[^а-яёa-z\s]/iu', '', $string);
        return $string;


    }
}