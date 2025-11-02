<?php

class CustomViewImageTheme extends ViewImageTheme
{
    public function display_page(Image $image, $editor_parts)
    {
        global $page;
        $page->set_heading(html_escape($image->get_tag_list()));
        $page->add_block(new Block("Search", $this->build_navigation($image), "left", 0));
        $page->add_block(new Block("Image Nav", $this->build_image_nav_block($image), "left", 1));
        $page->add_block(new Block("Image Tags", $this->build_image_tags($image), "left", 2));
        // related tags
        $page->add_block(new Block("Information", $this->build_information($image), "left", 15));
        $page->add_block(new Block($this->build_artist_and_rating($image), $this->build_title($image), "main", 0));
        $page->add_block(new Block(null, $this->build_info($image, $editor_parts), "main", 15));
    }

    private function build_artist_and_rating(Image $image): string
    {
        $html = "";
        $spaces = "&nbsp;&nbsp;&nbsp;&nbsp;";
        $style_gray = "style='color: #AAA;'";
        $style_gray_on_white = "style='color: #AAA; background-color: white;'";
        $style_white_on_red = "style='color: white; background-color: red;'";

        $tags = $image->get_tag_list();
        $matches = [];
        preg_match_all("/\@[\w-]+/", $tags, $matches);
        $artists = $matches[0];
        $artist_count = count($artists);
        if ($artist_count == 0)
        {
            $html = "<span $style_gray>Artist Unknown</span>";
        }
        else
        {
            $html = "Art by ";
            for ($i = 0; $i < $artist_count; $i++)
            {
                $url = "";
                $a = $artists[$i];
                if ($i > 0)
                {
                    $html .= ", ";
                }
                if ($a == "@KetRalus")
                {
                    $url = "https://x.com/KetRalus";
                    $a = "Ket Ralus";
                }
                else
                {
                    $url = $this->build_artist_link($a);
                    $a = str_replace(["@", "_"], ["", " "], $a);
                }
                $html .= "<a href='$url' target='blank'>$a</a>";
            }
        }

        $rating = $image->rating;
        if ($rating == "d")
        {
            $html .= "$spaces<span $style_gray_on_white>&nbsp;unlisted&nbsp;</span>";
        }
        else if ($rating == "p")
        {
            $html .= "$spaces<span $style_white_on_red>&nbsp;PRIVATE&nbsp;</span>";
        }

        return $html;
    }

    private function build_artist_link(string $artist): string
    {
        /*
            Table creation code:
            CREATE TABLE `gallery2`.`artist_links` ( `artist_tag_id` INT(11) NOT NULL , `link` VARCHAR(255) NOT NULL ) ENGINE = InnoDB;
            ALTER TABLE `artist_links` CHANGE `link` `link` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
            ALTER TABLE `gallery2`.`artist_links` ADD UNIQUE `artist_tag_id_idx` (`artist_tag_id`);
        */
        global $database;
        $result = $database->get_row("SELECT link FROM artist_links al JOIN tags t ON al.artist_tag_id = t.id WHERE t.tag LIKE :artist", ['artist'=>$artist]);
        if ($result['link'] != null)
        {
            return $result['link'];
        }
        $twitter = "https://x.com/";
        return str_replace("@", $twitter, $artist);
    }

    private function build_image_nav_block(Image $image): string
    {
        $url_base = "/post/view/";
        $newer = "<span class='newer_disabled'>« Newer</span>";
        $older = "<span class='older_disabled'>Older »</span>";
        if (true) // TODO: get most recent KRID for comparison
        {
            $newer_url = $url_base.($image->id + 1);
            $newer = "<a class='newer_enabled' href='$newer_url'>« Newer</a>";
        }
        if ($image->id > 1)
        {
            $older_url = $url_base.($image->id - 1);
            $older = "<a class='older_enabled' href='$older_url'>Older »</a>";
        }
        $pipe = "<span class='pipe'>&nbsp;|&nbsp;</span>";
        $block_html = "<p class='krg_image_nav_block'>$newer $pipe $older</p>";
        return $block_html;
    }

    private function build_image_tags(Image $image): string
    {
        $tag_links = [];
        $head_active = false;
        $head_category = false;
        $head_artist = false;
        $head_tag = false;
        foreach ($image->get_tag_array() as $tag)
        {
            $type = "";
            $h_type = "";
            if (substr($tag, 0, 1) === "!")
            {
                $type = "Category:";
                if ($head_category == false)
                {
                    $head_category = true;
                    $head_active = true;
                }
            }
            else if (substr($tag, 0, 1) === "@")
            {
                $type = "Artist:";
                if ($head_artist == false)
                {
                    $head_artist = true;
                    $head_active = true;
                }
            }
            else
            {
                $type = "Tag:";
                if ($head_tag == false)
                {
                    $head_tag = true;
                    $head_active = true;
                }
            }
            if ($head_active == true)
            {
                $head_active = false;
                $h_type = "<span>$type</span><br />";
            }
            $u_tag_a = url_escape("!A " . $tag);
            $h_link_a = make_link("post/list/$u_tag_a/1");
            $u_tag_r = url_escape("!R " . $tag);
            $h_link_r = make_link("post/list/$u_tag_r/1");
            $u_tag_e = url_escape("!* " . $tag);
            $h_link_e = make_link("post/list/$u_tag_e/1");
            $h_tag_e = html_escape(str_replace("_", " ", $tag));
            $l_class = "class='lesser'";
            $g_style = "style='font-weight: bold;'";
            $tag_links[] = $h_type .
                "<a $l_class href='$h_link_a'>art</a> " .
                "<a $l_class href='$h_link_r'>ref</a> " .
                "<a $g_style href='$h_link_e'>$h_tag_e</a><br />";
        }
        $h_tag_links = implode($tag_links);

        return "<span class='view'>$h_tag_links</span>";
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
		KRID: {$image->id}
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

    private function build_title(Image $image): string
    {
        $title = "";
        if (Extension::is_enabled(PostTitlesInfo::KEY)) {
            $title = PostTitles::get_title($image);
        }
        $year_open = "<span style='opacity: 0.3'>";
        $year_close = "</span>";
        $matches = [];
        if (preg_match("/\(\d{4}\+?\)/", $title, $matches))
        {
            $title_year = $matches[0];
            $title = str_replace($title_year, $year_open.$title_year.$year_close, $title);
        }
        else
        {
            $year = date("Y");
            $posted_year = substr($image->posted, 0, 4);
            if ($year != $posted_year)
            {
                $title .= " $year_open($posted_year)$year_close";
            }
        }
        $html = "
        <h1 style='line-height: 75%;'>$title</h1>
        ";
        return $html;
    }
}
