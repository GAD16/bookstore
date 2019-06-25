<?php
class Pages extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('books_model');
        $this->load->library('table');
        $this->load->helper('url');
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

//        $template = array(
//            'table_open'            => '<table class = "libery">',
//
//            'thead_open'            => '<thead>',
//            'thead_close'           => '</thead>',
//
//            'heading_row_start'     => '<tr>',
//            'heading_row_end'       => '</tr>',
//            'heading_cell_start'    => '<th>',
//            'heading_cell_end'      => '</th class = "headID">',
//
//            'tbody_open'            => '<tbody>',
//            'tbody_close'           => '</tbody>',
//
//            'row_start'             => '<tr>',
//            'row_end'               => '</tr>',
//            'cell_start'            => '<td>',
//            'cell_end'              => '</td>',
//
//            'row_alt_start'         => '<tr>',
//            'row_alt_end'           => '</tr>',
//            'cell_alt_start'        => '<td>',
//            'cell_alt_end'          => '</td>',
//
//            'table_close'           => '</table>'
//        );
        //$this->table->set_template($template);

        $this->table->set_heading(array('Жанр', 'Автор', 'Название', 'Год выпуска', 'Удалить'));
        $data['lib'] = $this->table->generate($books);
       // $gata['base_url'] = array(base_url());
        $data['style_css'] = array(base_url() . 'css\pagestyles.css');
        $data['java_script'] = array(base_url() . 'js\jquery.min.js', base_url() . 'js\homescript.js');




        //$genres = array('Приключения', 'роман');
        //$data['compare'] = $this->books_model->upd_book ($genres, 'Сергей Георгиевич Лукьяненко', null, 2008, 7);


        $this->load->view('templates/header');
        $this->load->view('pages/'.$page, $data);
        $this->load->view('templates/footer');

    }
}

?>