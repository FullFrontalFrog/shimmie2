<?php
class PostTitlesTheme extends Themelet
{
    public function get_title_set_html(string $title, bool $can_set): string
    {
        $html = "
			<tr class='post_title'>
				<th>Title</th>
				<td>
					<span class='view'>".html_escape($title)."</span>
		".($can_set ? "
					<input class='edit' type='text' name='post_title' value='".html_escape($title)."' />
		" : "")."
				</td>
			</tr>
		";
        return $html;
    }
}
