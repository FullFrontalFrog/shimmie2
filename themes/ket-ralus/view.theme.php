<?php

class CustomViewImageTheme extends ViewImageTheme
{
    public function display_page(Image $image, $editor_parts)
    {
        global $page;
        $page->set_heading(html_escape($image->get_tag_list()));
        $page->add_block(new Block("Search", $this->build_navigation($image), "left", 0));
        $page->add_block(new Block("Information", $this->build_information($image), "left", 15));
        $page->add_block(new Block($this->build_artist($image), $this->build_title($image), "main", 0));
        $page->add_block(new Block(null, $this->build_info($image, $editor_parts), "main", 15));
    }

    private function build_information(Image $image): string
    {
        $h_owner = html_escape($image->get_owner()->name);
        $h_ownerlink = "<a href='".make_link("user/$h_owner")."'>$h_owner</a>";
        $h_ip = html_escape($image->owner_ip);
        $h_type = html_escape($image->get_mime_type());
        $h_date = $image->posted."<br />(".autodate($image->posted).")";
        $h_filesize = to_shorthand_int($image->filesize);

        global $user;
        if ($user->can(Permissions::VIEW_IP)) {
            $h_ownerlink .= " ($h_ip)";
        }

        $html = "
		ID: {$image->id}
		<br>Uploader: $h_ownerlink
		<br>Date: $h_date
		<br>Size: $h_filesize ({$image->width}x{$image->height})
		<br>Type: $h_type
		";

        if ($image->length!=null) {
            $h_length = format_milliseconds($image->length);
            $html .= "<br/>Length: $h_length";
        }


        if (!is_null($image->source)) {
            $h_source = html_escape($image->source);
            if (substr($image->source, 0, 7) != "http://" && substr($image->source, 0, 8) != "https://") {
                $h_source = "http://" . $h_source;
            }
            $html .= "<br>Source: <a href='$h_source'>link</a>";
        }

        if (Extension::is_enabled(RatingsInfo::KEY)) {
            if ($image->rating == null || $image->rating == "?") {
                $image->rating = "?";
            }
            if (Extension::is_enabled(RatingsInfo::KEY)) {
                $h_rating = Ratings::rating_to_human($image->rating);
                $html .= "<br>Rating: $h_rating";
            }
        }

        return $html;
    }

    protected function build_navigation(Image $image): string
    {
        //$h_pin = $this->build_pin($image);
        $h_search = "
			<form action='".make_link()."' method='GET'>
				<input name='search' type='text'  style='width:75%'>
				<input type='submit' value='Go' style='width:20%'>
				<input type='hidden' name='q' value='/post/list'>
				<input type='submit' value='Find' style='display: none; width:20%'>
			</form>
		";

        return "$h_search";
    }

    private function build_artist(Image $image): string
    {
        $tags = $image->get_tag_list();
        $matches = [];
        preg_match_all("/\@\w+/", $tags, $matches);
        $artists = $matches[0];
        $artist_count = count($artists);
        $artist_string = "";
        for ($i = 0; $i < $artist_count; $i++)
        {
            $a = $artists[$i];
            if ($i > 0)
            {
                $artist_string = $artist_string.", ";
            }
            if ($a == "@KetRalus")
            {
                $artist_string = $artist_string."Ket✦Ralus";
                if ($artist_count == 1)
                {
                    $artist_string = $artist_string." <span style='opacity: 0.3'>(@KetRalus)</span>";
                }
            }
            else
            {
                $artist_string = $artist_string.$a;
            }
        }
        return "Art By ".$artist_string;
    }

    private function build_title(Image $image): string
    {
        $year = date("Y");
        $posted_year = substr($image->posted, 0, 4);
        $h_year = "";
        if ($year != $posted_year)
        {
            $h_year = " <span style='opacity: 0.3'>($posted_year)</span>";
        }
        $html = "
        <h1 style='line-height: 75%;'>".$image->title.$h_year."</h1>
        ";
        return $html;
    }
}
