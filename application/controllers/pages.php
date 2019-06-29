<?php
class Pages extends CI_Controller {

    public function __construct()
    {
        parent::__construct($page = 'home');
        $this->load->model('books_model');

    }

    /**
     * @param string $page
     */
    public function view($page = 'home')
    {
        if ( ! file_exists('application/views/pages/'.$page.'.php'))
        {
            // Упс, у нас нет такой страницы!
            show_404();

        }

        $data['title'] = ucfirst($page); // Сделать первую букву заглавной
        //$data['authors'] = $this->books_model->get_authors();
        $books = $this->books_model->table();

        $data['lib'] = $books;

        $data['style_css'] = array('/css/pagestyles.css');
        $data['java_script'] = array('/js/jquery.min.js', '/js/homescript.js', '/js/jquery.json.js');




        $genres = array('Мистика', 'интервью');
        $data['compare'] = $this->books_model->save_book ($genres, 'Владимир Серкин', 'Хохот шамана', 2007);


        $this->load->view('templates/header', $data);
        $this->load->view('pages/'.$page, $data);
        $this->load->view('templates/footer');

    }


    public function delButton () {
        $id = $_GET['id'];
        $result = $this->books_model->del_book($id);
        if ($result) {
            $lib = $this->books_model->table();
            echo ($lib);
        }
        else {
            echo ('');
    }
  }
    public function updateButton () {
        $data = $_POST['data'];
        $data = json_decode($data);
        $result = $this->books_model->upd_book (
            $data[0]->value,
            $data[1]->value,
            $data[2]->value,
            (int)$data[3]->value,
            $data[4]->value);
        $r = $this->books_model->row_by_id($data[4]->value);
        echo ($r);
    }

    public function addButton () {
        $data = $_POST['data'];
        $data = json_decode($data);
        $result = '';
        $result = $this->books_model->save_book (
            $data[0]->value,
            $data[1]->value,
            $data[2]->value,
            (int)$data[3]->value);
        switch ($result) {
            default: $result = $this->books_model->row_by_id($result);
                echo ($result);
                break;
            case 'не все поля заполнены':
                echo ($result);
                break;
            case 'такая книга уже добавлена':
                echo ($result);
                break;
            case 'не верный формат введенных данных':
                echo ($result);
                break;
            case 'не верный формат года':
                echo ($result);
                break;
            case 'год написания книги не может быть больше текущего':
                echo ($result);
                break;

        }

    }

}
