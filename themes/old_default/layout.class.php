<?php

class Layout {
	function display_page($page) {
		global $config;

		$theme_name = $config->get_string('theme', 'default');
		$data_href = get_base_href();
		$contact_link = $config->get_string('contact_link');

		$header_html = "";
		ksort($page->headers);
		foreach($page->headers as $line) {
			$header_html .= "\t\t$line\n";
		}

		$left_block_html = "";
		$main_block_html = "";
		$sub_block_html = "";

		foreach($page->blocks as $block) {
			switch($block->section) {
				case "left":
					$left_block_html .= $this->block_to_html($block, true, "left");
					break;
				case "main":
					$main_block_html .= $this->block_to_html($block, false, "main");
					break;
				case "subheading":
					$sub_block_html .= $block->body; // $this->block_to_html($block, true);
					break;
				default:
					print "<p>error: {$block->header} using an unknown section ({$block->section})";
					break;
			}
		}

		$debug = get_debug_info();

		$contact = empty($contact_link) ? "" : "<br><a href='$contact_link'>Contact</a>";
		$subheading = empty($page->subheading) ? "" : "<div id='subtitle'>{$page->subheading}</div>";

		$wrapper = "";
		if(strlen($page->heading) > 100) {
			$wrapper = ' style="height: 3em; overflow: auto;"';
		}

		print <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
	<head>
		<title>{$page->title}</title>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
		<link rel="stylesheet" href="$data_href/themes/$theme_name/style.css" type="text/css">
$header_html
		<script src='$data_href/themes/$theme_name/sidebar.js' type='text/javascript'></script>
		<script src='$data_href/themes/$theme_name/script.js' type='text/javascript'></script>
	</head>

	<body>
		<h1$wrapper>{$page->heading}</h1>
		$subheading
		$sub_block_html
		
		<div id="nav">$left_block_html</div>
		<div id="body">$main_block_html</div>

		<div id="footer">
			<hr>
			Images &copy; their respective owners,
			<a href="http://code.shishnet.org/shimmie2/">Shimmie</a> &copy;
			<a href="http://www.shishnet.org/">Shish</a> &amp; Co 2007-2011,
			based on the Danbooru concept.
			$debug
			$contact
		</div>
	</body>
</html>
EOD;
	}

	function block_to_html($block, $hidable=false, $salt="") {
		$h = $block->header;
		$b = $block->body;
		$html = "";
		$i = str_replace(' ', '_', $h) . $salt;
		if($hidable) {
			$toggle = " onclick=\"toggle('$i')\"";
		}
		else {
			$toggle = "";
		}
		if(!is_null($h)) $html .= "\n<h3 id='$i-toggle'$toggle>$h</h3>\n";
		if(!is_null($b)) $html .= "<div id='$i'>$b</div>\n";
		return $html;
	}
}
?>
