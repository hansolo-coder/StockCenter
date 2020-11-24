<?php
	/**
	 * manages data restore process
	 */
	class restoreData
	{
		/**
		 * displays the restore data form
		 */
		function step1()
		{
			include_once './classes/widgets/formRestoreData.class.php';

			$form = new formRestoreData();
			$form->display();
		}

		/**
		 * processes the restore data form
		 */
		function step2()
		{
			include_once './classes/widgets/formRestoreData.class.php';

			$form = new formRestoreData();
			$form->process();
		}
	}
?>