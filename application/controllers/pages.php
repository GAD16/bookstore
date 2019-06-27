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
        $data['java_script'] = array('/js/jquery.min.js', '/js/homescript.js');




        $genres = array('фантастика', 'роман');
        $data['compare'] = $this->books_model->save_book ($genres, 'Гари Гаррисон', 'Стальная крыса', 1969);


        $this->load->view('templates/header');
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

}
