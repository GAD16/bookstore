<?php
class Pages extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
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
        $data['authors'] = $this->books_model->get_authors();
        $data['lib'] = $this->books_model->get_lib();


        $data['compare'] = $this->books_model->save_book ('мистика', 'Владимир серкин', 'Хохот шамана', 2007);


        $this->load->view('templates/header');
        $this->load->view('pages/'.$page, $data);
        $this->load->view('templates/footer');

    }
}

?>