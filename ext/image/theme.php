<?php
class ImageIOTheme {
	/*
	 * Display a link to delete an image
	 *
	 * $image_id = the image to delete
	 */
	public function get_deleter_html($image_id) {
		global $user;

		$i_image_id = int_escape($image_id);
		$html = "
			".make_form(make_link("image_admin/delete"))."
				<input type='hidden' name='image_id' value='$i_image_id'>
				<input type='submit' value='Delete'>
			</form>
		";
		return $html;
	}
}
?>
