<?php

class CustomIndexTheme extends IndexTheme
{
    /**
     * #param Image[] $images
     */
    public function display_page(Page $page, array $images)
    {
        $this->display_page_header($page, $images);

        $nav = $this->build_navigation($this->page_number, $this->total_pages, $this->search_terms);
        $page->add_block(new Block("Search", $nav, "left", 0));

        $page_block = $this->build_page_nav_block($this->page_number, $this->total_pages, $this->search_terms);
        $page->add_block(new Block("Page Nav", $page_block, "left", 1));

        if (count($images) > 0) {
            $this->display_page_images($page, $images);
        } else {
            $this->display_error(404, "No Images Found", "No images were found to match the search criteria");
        }
    }

    /**
     * #param string[] $search_terms
     */
    protected function build_navigation(int $page_number, int $total_pages, array $search_terms): string
    {
		//KET RALUS CUSTOM begin

		$cat_art = -1;
		$cat_ref = -1;
		$cat_every = -1;
		$cat_other = FALSE;
		$radio_art = FALSE;
		$radio_ref = FALSE;
		$radio_every = FALSE;
		foreach($search_terms as $key => $value) {
			if($value[0] == '!') {
				if(strtoupper($value) == '!A') {
					$cat_art = $key;
				}
				else if(strtoupper($value) == '!R') {
					$cat_ref = $key;
				}
				else if($value == '!*') {
					$cat_every = $key;
				}
				else {
					$cat_other = TRUE;
				}
			}
			else if($value[0] == '-' && $value[1] == '!')
			{
			    $cat_other = TRUE;
			}
		}
		if($cat_other === FALSE) {
			if($cat_art > -1) {
				unset($search_terms[$cat_art]);
				$cat_art = -1;
				$radio_art = TRUE;
			}
			else if($cat_ref > -1) {
				unset($search_terms[$cat_ref]);
				$cat_ref = -1;
				$radio_ref = TRUE;
			}
			else if($cat_every > -1) {
				unset($search_terms[$cat_every]);
				$cat_every = -1;
				$radio_every = TRUE;
			}
		}

		//KET RALUS CUSTOM end

        $h_search_string = count($search_terms) == 0 ? "" : html_escape(implode(" ", $search_terms));
        $h_search_link = make_link();
        $h_search = "
			<p><form action='$h_search_link' method='GET'>
				<input name='search' type='text' value='$h_search_string' class='autocomplete_tags' placeholder=''  style='width:75%'/>
				<input type='hidden' name='q' value='/post/list'>
				<div class='radio'>
				    <input type='submit' value='Go' class='go-button'>
					<input type='radio' name='searchType' id='searchTypeArt' value='art'".($radio_art === TRUE ? " checked='checked'" : "")." onClick='this.form.submit();'><label for='searchTypeArt'>art</label>
					<input type='radio' name='searchType' id='searchTypeRef' value='ref'".($radio_ref === TRUE ? " checked='checked'" : "")." onClick='this.form.submit();'><label for='searchTypeRef'>ref</label>
					<input type='radio' name='searchType' id='searchTypeEvery' value='every'".($radio_every === TRUE ? " checked='checked'" : "")." onClick='this.form.submit();'><label for='searchTypeEvery'>every</label>
				</div>
			</form>
			<div id='search_completions'></div>";

        return $h_search;
    }

    /**
     * #param string[] $search_terms
     */
    protected function build_page_nav_block(int $page_number, int $total_pages, array $search_terms): string
    {
        $url_base = "/post/list/";
        $newer = "<span class='newer_disabled'>« Newer</span>";
        $older = "<span class='older_disabled'>Older »</span>";
        if (count($this->search_terms) > 0)
        {
            $query = url_escape(implode(' ', $this->search_terms));
            $url_base .= "$query/";
        }
        if ($page_number > 1)
        {
            $newer_url = $url_base.($page_number - 1);
            $newer = "<a class='newer_enabled' href='$newer_url'>« Newer</a>";
        }
        if ($page_number < $total_pages)
        {
            $older_url = $url_base.($page_number + 1);
            $older = "<a class='older_enabled' href='$older_url'>Older »</a>";
        }
        $pipe = "<span class='pipe'>&nbsp;|&nbsp;</span>";
        $block_html = "<p class='krg_page_nav_block'>$newer $pipe $older</p>";
        return $block_html;
    }

    /**
     * #param Image[] $images
     */
    protected function build_table(array $images, ?string $query): string
    {
        $h_query = html_escape($query);
        $table = "<div class='shm-image-list' data-query='$h_query'>";
        foreach ($images as $image) {
            $table .= "\t<span class=\"thumb\">" . $this->build_thumb_html($image) . "</span>\n";
        }
        $table .= "</div>";
        return $table;
    }

    /**
     * #param Image[] $images
     */
    protected function display_page_images(Page $page, array $images)
    {
        $page_paren = "<span style='opacity: 0.3'>(Page $this->page_number)</span>";
        if (count($this->search_terms) > 0) {
            if ($this->page_number > 3) {
                // only index the first pages of each term
                $page->add_html_header('<meta name="robots" content="noindex, nofollow">');
            }
            $query = url_escape(implode(' ', $this->search_terms));
            $query_text = urldecode($query);
            $block_title = "Query: $query_text $page_paren";
            $page->add_block(new Block(null, $this->build_table($images, "#search=$query"), "main", 10, "image-list"));
            $this->display_paginator($page, "post/list/$query", null, $this->page_number, $this->total_pages, true, $block_title);
        } else {
            $block_title = "All Posts $page_paren";
            $page->add_block(new Block(null, $this->build_table($images, null), "main", 10, "image-list"));
            $this->display_paginator($page, "post/list", null, $this->page_number, $this->total_pages, true, $block_title);
        }
    }
}
