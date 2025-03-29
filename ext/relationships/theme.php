<?php

class RelationshipsTheme extends Themelet
{
    public function relationship_info(Image $image)
    {
        global $page, $database;

        $string_output = "";
        $string_style = "strong";

        if (!is_null($image->source)) {
            $source_type = "EX FILE (Source Link)";
            if (strpos($image->source, '_hq.') !== false) {
                $source_type = "High Quality Ver.";
            }
            $string_output .= " <a href='$image->source' target='_blank'>$source_type</a>";
        }

        if ($image->parent_id !== null) {
            $string_output .= " ↓<a href='".make_link("post/view/".$image->parent_id)."'>$image->parent_id</a>";
        }

        if (bool_escape($image->has_children)) {
            $ids = $database->get_col("SELECT id FROM images WHERE parent_id = :iid", ["iid"=>$image->id]);

            foreach ($ids as $id) {
                $string_output .= " ↑<a href='".make_link('post/view/'.$id)."'>{$id}</a>";
            }
        }

        if ($string_output == "") {
            $string_output = " NONE";
            $string_style = "em";
        }

        $page->add_block(new Block(null, "<span style='color: #999999;'>>> RELATED IMAGES:</span><$string_style>$string_output</$string_style>", "main", 5));
    }

    public function get_parent_editor_html(Image $image): string
    {
        global $user;

        $h_parent_id = $image->parent_id;
        $s_parent_id = $h_parent_id ?: "None";

        $html = "<tr>\n".
                "	<th>Parent</th>\n".
                "	<td>\n".
                (
                    !$user->is_anonymous() ?
                    "		<span class='view' style='overflow: hidden; white-space: nowrap;'>{$s_parent_id}</span>\n".
                    "		<input class='edit' type='number' name='tag_edit__parent' type='number' value='{$h_parent_id}'>\n"
                :
                    $s_parent_id
                ).
                "	<td>\n".
                "</tr>\n";
        return $html;
    }


    public function get_help_html()
    {
        return '<p>Search for images that have parent/child relationships.</p>
        <div class="command_example">
        <pre>parent=any</pre>
        <p>Returns images that have a parent.</p>
        </div> 
        <div class="command_example">
        <pre>parent=none</pre>
        <p>Returns images that have no parent.</p>
        </div> 
        <div class="command_example">
        <pre>parent=123</pre>
        <p>Returns images that have image 123 set as parent.</p>
        </div> 
        <div class="command_example">
        <pre>child=any</pre>
        <p>Returns images that have at least 1 child.</p>
        </div> 
        <div class="command_example">
        <pre>child=none</pre>
        <p>Returns images that have no children.</p>
        </div> 
        ';
    }
}
