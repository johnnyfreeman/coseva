<?php

/**
* Coseva
* 
* A classy, object-oriented alternative for parsing CSV files with PHP.
*/
class CSV
{
	protected $_rows = array();
	protected $_columnFilters = array();
	protected $_rowFilters = array();
	protected $_file;

	function __construct($filename, $open_mode = 'r', $use_include_path = FALSE)
	{
		$this->_file = new SplFileObject($filename, $open_mode, $use_include_path);
		$this->_file->setFlags(SplFileObject::READ_CSV);
	}

	public function filterColumn($csv_column, $callable)
	{
		if (is_callable($callable)) {
			$this->_columnFilters[$csv_column][] = $callable;
		}
	}

	public function filterRow($callable)
	{
		if (is_callable($callable)) {
			$this->_rowFilters[] = $callable;
		}
	}

	public function toSQL()
	{
		
	}

	public function toTable()
	{
		// $num_rows = count($this->rows);

		// if (0 === $num_rows) {
		// 	return;
		// }

		// $num_cols = count($this->rows[0]);

		// // open table
		// $output = '<table border="1">';

		// // table header
		// $output = '<thead><tr><th>#</th><th>0</th><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9</th><th>10</th><th>11</th><th>12</th><th>13</th><th>14</th><th>15</th><th>16</th><th>17</th><th>18</th><th>19</th><th>20</th><th>21</th><th>22</th><th>23</th><th>24</th><th>25</th><th>26</th><th>27</th><th>28</th><th>29</th><th>30</th><th>31</th><th>32</th><th>33</th><th>34</th><th>35</th><th>36</th><th>37</th><th>38</th><th>39</th><th>40</th><th>41</th><th>42</th><th>43</th><th>44</th><th>45</th></tr></thead>';
		// $output = '<tbody>';
		// foreach ($this->rows as $row) {
			
		// }

	 //    while (($columns = fgetcsv($handle, 1000, ",")) !== FALSE)
	 //    {
	 //        $num_col = count($columns);

	 //        // echo '<tr>';
	 //        // echo '<td>' . $row . '</td>';

	 //        // for ($i=0; $i < $num_col; $i++)
	 //        // {
	 //        //     echo '<td>' . $columns[$i] . '</td>';
	 //        // }
	 //        // echo '</tr>';

	 //        $code 				= $columns[0];
	 //        $mod1 				= $columns[1];
	 //        $mod2 				= $columns[2];
	 //        $description 		= $columns[3];
	 //        $allowable_non_fac	= toNum($columns[41]);
	 //        $allowable_fac		= toNum($columns[42]);

	 //        // use php's binary calculater for accuracy
	 //        $twenty_non_fac		= bcmul($allowable_non_fac, '.2');
	 //        $twenty_fac			= bcmul($allowable_fac, '.2');
	 //        $self_pay_non_fac	= bcmul($allowable_non_fac, '1.2');
	 //        $self_pay_fac		= bcmul($allowable_fac, '1.2');

	 //        $work_rvu			= toNum($columns[6]);
	 //        $global_days		= (int) toNum($columns[24]);
	 //        $asst_surgeon		= toNum($columns[30]);
	 //        $dosage				= NULL;
	 //        $qty				= serialize(array('1', '2'));

	 //        // prepare
	 //        $code 				= prepare($code);
	 //        $mod1 				= prepare($mod1);
	 //        $mod2 				= prepare($mod2);
	 //        $description 		= prepare($description);
	 //        $allowable_non_fac	= prepare($allowable_non_fac);
	 //        $allowable_fac		= prepare($allowable_fac);
	 //        $twenty_non_fac		= prepare($twenty_non_fac);
	 //        $twenty_fac			= prepare($twenty_fac);
	 //        $self_pay_non_fac	= prepare($self_pay_non_fac);
	 //        $self_pay_fac		= prepare($self_pay_fac);
	 //        $work_rvu			= prepare($work_rvu);
	 //        $global_days		= prepare($global_days);
	 //        $asst_surgeon		= prepare($asst_surgeon);
	 //        $dosage				= prepare($dosage);
	 //        $qty				= prepare($qty);

	 //        $QRY = $DB->prepare("INSERT INTO [VOVN_Web].[dbo].[cpt_codes]
	 //           ([id]
	 //          ,[code]
		//       ,[mod1]
		//       ,[mod2]
		//       ,[description]
		//       ,[dosage]
		//       ,[qty]
		//       ,[allowable_non_fac]
		//       ,[allowable_fac]
		//       ,[twenty_non_fac]
		//       ,[twenty_fac]
		//       ,[self_pay_non_fac]
		//       ,[self_pay_fac]
		//       ,[work_rvu]
		//       ,[global_days]
		//       ,[asst_surgeon])
	 //     VALUES
	 //           ($row
	 //           ,$code
	 //           ,$mod1
	 //           ,$mod2
	 //           ,$description
	 //           ,$dosage
	 //           ,$qty
	 //           ,$allowable_non_fac
	 //           ,$allowable_fac
	 //           ,$twenty_non_fac
	 //           ,$twenty_fac
	 //           ,$self_pay_non_fac
	 //           ,$self_pay_fac
	 //           ,$work_rvu
	 //           ,$global_days
	 //           ,$asst_surgeon)");

	 //        // echo '<pre>'; print_r($QRY); echo '</pre>';

	 //        if (FALSE !== $QRY) {
	 //    		$QRY->execute();
	 //    	}

	 //        $row++;
	 //    }

	 //    echo '</tbody>';
		// echo '</table>';


		// return $output;
	}

	public function parse($offset = 0)
	{
		foreach(new LimitIterator($this->file, $offset) as $row => $columns)
		{
		    foreach ($columns as $col => $value)
		    {
		    	// run column filters
	    		if (isset($this->_columnFilters[$col]))
	    		{
	    			foreach ($this->_columnFilters[$col] as $filter) {
	    				$value = call_user_func($filter, $value);
	    			}
	    		}

	    		$this->_rows[$row][$col] = $value;
	    	}
		}
	}

	public function getRows()
	{
		return $this->_rows;
	}
}