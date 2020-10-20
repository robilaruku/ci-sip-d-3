<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class TransactionController extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Transaction');   
    }


	public function index()
	{
        $transaction = $this->Transaction;

        $transactions = $transaction->getAll();

		$this->load->view('admin/transaction/index', compact('transactions'));
    }

    public function create()
    {
        $this->load->view('admin/transaction/create');
    }

    public function import()
    {
        $config['upload_path']   = './uploads/transactions/';
        $config['allowed_types'] = 'xls|xlsx';
        $config['max_size']      = 100;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('excel')) {
            $errors = $this->upload->display_errors();
            $this->session->set_flashdata('errors', $errors);
            return redirect('transactions/create');
        }
        $excel = $this->upload->data('full_path');
        $arr_file = explode('.', $excel);
        $extension = end($arr_file);

        if ('xls' == $extension) {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        }elseif('xlsx' == $extension){
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }

        $spreadsheet = $reader->load($excel);
        $data = $spreadsheet->getActiveSheet()->toArray();

        foreach ($data as $key => $value) {
            
            if ($key == 0) {
                continue;
            }

            $product_id = $value[0];
            $trx_date = $value[1];
            $price = $value[2];

            $this->Transaction->insert($product_id, $trx_date, $price);
        
        }
        
        $this->session->set_flashdata('message', 'Transactions has been imported');
        return redirect('transactions/index');

    }

}
