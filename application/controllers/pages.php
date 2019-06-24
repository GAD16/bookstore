<?php
class Pages extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('books_model');
        $this->load->library('table');
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
        $books = $this->books_model->get_lib();
        $data['lib'] = $this->table->generate($books);



        $genres = array('Приключения', 'роман');
        $data['compare'] = $this->books_model->upd_book ($genres, 'Сергей Георгиевич Лукьяненко', null, 2008, 7);


        $this->load->view('templates/header');
        $this->load->view('pages/'.$page, $data);
        $this->load->view('templates/footer');

    }
}

?>