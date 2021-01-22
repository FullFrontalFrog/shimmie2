<?php
class Themelet extends BaseThemelet
{
    public function display_paginator(Page $page, string $base, ?string $query, int $page_number, int $total_pages, bool $show_random = false, ?string $block_title = null)
    {
        if ($total_pages == 0) {
            $total_pages = 1;
        }
        $body = $this->build_paginator($page_number, $total_pages, $base, $query);
        $page->add_block(new Block($block_title, $body, "main", 7));
        $page->add_block(new Block(null, "<div class='ket-paginator-bottom'>".$body."</div>", "main", 100));
    }

    private function gen_page_link(string $base_url, ?string $query, string $page, string $name): string
    {
        $link = make_link("$base_url/$page", $query);
        return "<a href='$link'>$name</a>";
    }

    private function gen_page_link_block(string $base_url, ?string $query, int $page, int $current_page, string $name): string
    {
        $paginator = "";
        if ($page == $current_page) {
            $paginator .= "<b>$page</b>";
        } else {
            $paginator .= $this->gen_page_link($base_url, $query, $page, $name);
        }
        return $paginator;
    }

    private function build_paginator(int $current_page, int $total_pages, string $base_url, ?string $query): string
    {
        $at_start = ($current_page <= 3 || $total_pages <= 3);
        $at_end = ($current_page >= $total_pages -2);
        $space = " <span class='space'>&nbsp;</span> ";
        $first_space = "";
        $last_space = "";

        $first_html  = $at_start ? "" : $this->gen_page_link($base_url, $query, 1, "1");
        $last_html   = $at_end   ? "" : $this->gen_page_link($base_url, $query, $total_pages, "$total_pages");

        $start = $current_page-2 > 1 ? $current_page-2 : 1;
        $end   = $current_page+2 <= $total_pages ? $current_page+2 : $total_pages;

        $pages = [];
        foreach (range($start, $end) as $i) {
            $pages[] = $this->gen_page_link_block($base_url, $query, $i, $current_page, $i);
        }
        $pages_html = implode(" ", $pages);

        if (strlen($first_html) > 0 && $current_page > 4) {
            $pdots = " <span class='dots'>...</span> ";
        } else {
            $pdots = "";
        }

        if (strlen($last_html) > 0 && $current_page < $total_pages - 3) {
            $ndots = " <span class='dots'>...</span> ";
        } else {
            $ndots = "";
        }

        if ($current_page < 5)
        {
            for ($i = $current_page; $i < 5; $i++)
            {
                $first_space .= $space;
            }
        }

        if ($current_page > $total_pages - 4)
        {
            for ($i = $current_page; $i > $total_pages - 4; $i--)
            {
                $last_space .= $space;
            }
        }

        return "<div id='paginator'>$first_space $first_html $pdots $pages_html $ndots $last_html $last_space</div>";
    }
}
