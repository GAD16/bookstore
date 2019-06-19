<?php

class books_model extends CI_Model {




    public function __construct()    {

        $this->load->database();
    }

   public function get_authors ($id = false)   {

        if ($id === false)    {

            $query = $this->db->get('authors');
            return $query->result_array();
        }

       $query = $this->db->get_where('authors', array('id' => $id));
       return $query->row_array();
   }

   public function get_lib() {
        //Выбирает жанр(через запятую если их несколько), автор, название, год
       $query = $this->db->query('
            SELECT GROUP_CONCAT(genres.genre) as genre, full_name AS author, name, year
            FROM books
            JOIN book_genres ON book_genres.book_id = books.id
            JOIN genres ON genres.genre_id = book_genres.genre_id
            JOIN book_authors ON book_authors.book_id = books.id
            JOIN authors ON authors.author_id = book_authors.author_id
            GROUP BY books.id');

       return $query->result_array();

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
                $aarr = array('author_id', $author_id);
                $this->db->delete('authors', $aarr);
                $ret++;
            }
        }
        $this->db->select('genre_id');
        $this->db->from('book_genres');
        $this->db->where('book_id', $id);
        $query = $this->db->get();
        $result = $query->result_array();
        $genre_ids = $result->genre_id;

        if ($genre_ids) {
            foreach ($genre_ids as $genre_id) {
                $this->db->from('book_genres');
                $this->db->where('genre_id', $genre_id);
                $query = $this->db->get();
                $result = $query->result_array();
                $count = (count($result));
                if ($count == 1) {
                    $garr = array('genre_id', $genre_id);
                    $this->db->delete('genres', $garr);

                }
            }
            $ret = $ret + 2;
        }
        $arr = array('id' => $id);
        $this->db->delete('books', $arr);
        $arr = array('book_id' => $id);
        $this->db->delete('book_genres', $arr);
        $this->db->delete('book_authors', $arr);

        return ( $ret);
    }


    //добавляет запись в базу, если такой книги еще нет. Возвращает ID новой или уже существующей
    public function save_book ($genre, $author, $book, $year) {
    $check = $this->chek_received_data($genre, $author, $book, $year);
    if ($check == 'ok'){
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
            if (!isset($genre) or !isset($author) or !isset($book) or !isset($year)) {
                return ("не все поля заполнены");
            }

            if (!is_string($genre) or !is_string($author) or !is_string($book) or !is_int($year)) {
                return ("не верный формат введенных данных");
            }

            if (strlen($year) <> 4) {
                return ("не верный формат года");
            }

            if ($year > date('Y')) {
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

            $book_id = $row->id;
            if ($book_id) {
                array_push($cbr, $book_id);
            }
            else {
                array_push($cbr, false);
            }

            //проверяем нет ли в базе такого жанра

            foreach ($genre as $g) {
                $this->db->select('genre_id');
                $this->db->from('genres');
                $this->db->where('genre', $g);
                $query = $this->db->get();
                $row = $query->row();
                $genre_id = $row->genre_id;
                if ($genre_id) {
                    array_push($g_ids, $genre_id);
                } else {
                    array_push($g_ids, false);
                }
            }

            //проверяем нет ли в базе такого автора

            $this->db->select('author_id');
            $this->db->from('authors');
            $this->db->where('full_name', $author);
            $query = $this->db->get();
            $row = $query->row();
            $author_id = $row->author_id;
            if ($author_id){
                array_push($cbr, $author_id);
            }
            else {
                array_push($cbr, false);
            }
            return ($cbr);
        }

    private    function add_genre($genre, $cbr){
        //Функция вставляет в базу значение genre если в массиве CBR не получает уже существующий id этого жанра
        //Возвращает ID вставленной строки, или ID из CBR.
            if (!$cbr[1]){
                $data = array('genre' => $genre);
                $this->db->insert('genres', $data);
                $this->db->select('genre_id');
                $this->db->from('genres');
                $this->db->where('genre',$genre);
                $query = $this->db->get();
                $row = $query->row();
                $result = $row->genre_id;

                return ($result);
            }
            else {
                return ($cbr[1]);
            }
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

        $data = array(
            'book_id' => $book_id,
            'genre_id' => $ids[0]
        );

        $this->db->insert('book_genres', $data);

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


    private     function normal_genre($n){
        //функция приводит строку к виду словосочетания или одного слова. То есть делит на отдельные слова, удаляет знаки
        //припинания и пробелы,первое слово пишет с большой буквы.
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
        return $string;

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