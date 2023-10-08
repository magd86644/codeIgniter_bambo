<?php

namespace App\Controllers;
use CodeIgniter\Exceptions\PageNotFoundException;

class Pages extends BaseController
{
    private $parser;
    public function __construct()
    {
        helper(['User', 'date', 'form','Visitors']); 
        $this->parser = \Config\Services::parser();
     
    }

    private function _init_data($page_title = null)
    {
        $data['title'] = isset($page_title) ? ucfirst($page_title) : 'BeeCreative';
        save_visitors_data_h($this->request,$this->db_model); // save in db visitors
        return $data;
    }
    
    public function index()
    {
        return view('welcome_message');
    }

    public function view($page = 'home')
    {
        $data = $this->_init_data($page);
        if (!is_file(APPPATH . 'Views/pages/' . $page . '_view.php')) {
            // Whoops, we don't have a page for that!
            throw new PageNotFoundException($page);
        }
        echo view('front/pages/home_view',$data);
    }
}
