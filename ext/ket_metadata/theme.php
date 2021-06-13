<?php
class KetMetadataTheme extends Themelet
{
	public function get_metadata_edit_html(string $filename, bool $can_set): string
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
