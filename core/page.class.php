<?php
/**
 * @package SCore
 */

/**
 * A data structure for holding all the bits of data that make up a page.
 *
 * The various extensions all add whatever they want to this structure,
 * then layout.class.php turns it into HTML
 */
class GenericPage {
	var $mode = "page";
	var $type = "text/html";

	/**
	 * Set what this page should do; page, data, or redirect.
	 */
	public function set_mode($mode) {
		$this->mode = $mode;
	}

	/**
	 * Set the page's MIME type
	 */
	public function set_type($type) {
		$this->type = $type;
	}


	// ==============================================

	// data
	var $data = "";
	var $filename = null;

	/**
	 * If the page is in "data" mode, this will set the data to be sent
	 */
	public function set_data($data) {
		$this->data = $data;
	}

	/**
	 * If the page is in "data" mode, this will set the recommended download filename
	 */
	public function set_filename($filename) {
		$this->filename = $filename;
	}


	// ==============================================

	// redirect
	var $redirect = "";

	/**
	 * If the page is in "redirect" mode, this will set where to redirect to
	 */
	public function set_redirect($redirect) {
		$this->redirect = $redirect;
	}


	// ==============================================

	// page
	var $title = "";
	var $heading = "";
	var $subheading = "";
	var $quicknav = "";
	var $headers = array();
	var $blocks = array();

	/**
	 * If the page is in "page" mode, set the window title
	 */
	public function set_title($title) {
		$this->title = $title;
	}

	/**
	 * If the page is in "page" mode, set the main heading
	 */
	public function set_heading($heading) {
		$this->heading = $heading;
	}

	/**
	 * If the page is in "page" mode, set the sub heading
	 */
	public function set_subheading($subheading) {
		$this->subheading = $subheading;
	}

	/**
	 * If the page is in "page" mode, add a line to the HTML head section
	 */
	public function add_header($line, $position=50) {
		while(isset($this->headers[$position])) $position++;
		$this->headers[$position] = $line;
	}

	/**
	 * If the page is in "page" mode, add a block of data
	 */
	public function add_block($block) {
		$this->blocks[] = $block;
	}

	// ==============================================

	/**
	 * display the page according to the mode and data given
	 */
	public function display() {
		global $page;

		header("Content-type: {$this->type}");
		header("X-Powered-By: SCore-".SCORE_VERSION);

		switch($this->mode) {
			case "page":
				header("Cache-control: no-cache");
				usort($this->blocks, "blockcmp");
				$data_href = get_base_href();
				foreach(glob("lib/*.js") as $js) {
					$this->add_header("<script src='$data_href/$js' type='text/javascript'></script>");
				}
				$layout = new Layout();
				$layout->display_page($page);
				break;
			case "data":
				if(!is_null($this->filename)) {
					header('Content-Disposition: attachment; filename='.$this->filename);
				}
				print $this->data;
				break;
			case "redirect":
				header("Location: {$this->redirect}");
				print "You should be redirected to <a href='{$this->redirect}'>{$this->redirect}</a>";
				break;
			default:
				print "Invalid page mode";
				break;
		}
	}
}
?>
