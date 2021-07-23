<?php
class KetMetadataTheme extends Themelet
{
	public function get_date_posted_html(string $date_posted, bool $can_set): string
	{
        $html = "
			<tr class='date_posted'>
				<th>Date Posted</th>
				<td>
					<span class='view'>".html_escape($date_posted)."</span>
		".($can_set ? "
					<input class='edit' type='text' name='date_posted' value='".html_escape($date_posted)."' />
		" : "")."
				</td>
			</tr>
		";
        return $html;
	}

	public function get_filename_html(string $filename, bool $can_set): string
	{
        $html = "
			<tr class='filename'>
				<th>Filename</th>
				<td>
					<span class='view'>".html_escape($filename)."</span>
		".($can_set ? "
					<input class='edit' type='text' name='filename' value='".html_escape($filename)."' />
		" : "")."
				</td>
			</tr>
		";
        return $html;
	}
}
