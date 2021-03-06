<?php

class AddAliasEvent extends Event
{
    /** @var string  */
    public $oldtag;
    /** @var string  */
    public $newtag;

    public function __construct(string $oldtag, string $newtag)
    {
        $this->oldtag = trim($oldtag);
        $this->newtag = trim($newtag);
    }
}

class AddAliasException extends SCoreException
{
}

class AliasEditor extends Extension
{
    public function onPageRequest(PageRequestEvent $event)
    {
        global $config, $database, $page, $user;

        if ($event->page_matches("alias")) {
            if ($event->get_arg(0) == "add") {
                if ($user->can(Permissions::MANAGE_ALIAS_LIST)) {
                    if (isset($_POST['oldtag']) && isset($_POST['newtag'])) {
                        try {
                            $aae = new AddAliasEvent($_POST['oldtag'], $_POST['newtag']);
                            send_event($aae);
                            $page->set_mode(PageMode::REDIRECT);
                            $page->set_redirect(make_link("alias/list"));
                        } catch (AddAliasException $ex) {
                            $this->theme->display_error(500, "Error adding alias", $ex->getMessage());
                        }
                    }
                }
            } elseif ($event->get_arg(0) == "remove") {
                if ($user->can(Permissions::MANAGE_ALIAS_LIST)) {
                    if (isset($_POST['oldtag'])) {
                        $database->execute("DELETE FROM aliases WHERE oldtag=:oldtag", ["oldtag" => $_POST['oldtag']]);
                        log_info("alias_editor", "Deleted alias for ".$_POST['oldtag'], "Deleted alias");

                        $page->set_mode(PageMode::REDIRECT);
                        $page->set_redirect(make_link("alias/list"));
                    }
                }
            } elseif ($event->get_arg(0) == "list") {
                if ($event->count_args() == 2) {
                    $page_number = $event->get_arg(1);
                    if (!is_numeric($page_number)) {
                        $page_number = 0;
                    } elseif ($page_number <= 0) {
                        $page_number = 0;
                    } else {
                        $page_number--;
                    }
                } else {
                    $page_number = 0;
                }

                $alias_per_page = $config->get_int('alias_items_per_page', 30);

                $query = "SELECT oldtag, newtag FROM aliases ORDER BY newtag ASC LIMIT :limit OFFSET :offset";
                $alias = $database->get_pairs(
                    $query,
                    ["limit"=>$alias_per_page, "offset"=>$page_number * $alias_per_page]
                );

                $total_pages = ceil($database->get_one("SELECT COUNT(*) FROM aliases") / $alias_per_page);

                $this->theme->display_aliases($alias, $page_number + 1, $total_pages);
            } elseif ($event->get_arg(0) == "export") {
                $page->set_mode(PageMode::DATA);
                $page->set_type("text/csv");
                $page->set_filename("aliases.csv");
                $page->set_data($this->get_alias_csv($database));
            } elseif ($event->get_arg(0) == "import") {
                if ($user->can(Permissions::MANAGE_ALIAS_LIST)) {
                    if (count($_FILES) > 0) {
                        $tmp = $_FILES['alias_file']['tmp_name'];
                        $contents = file_get_contents($tmp);
                        $this->add_alias_csv($database, $contents);
                        log_info("alias_editor", "Imported aliases from file", "Imported aliases"); # FIXME: how many?
                        $page->set_mode(PageMode::REDIRECT);
                        $page->set_redirect(make_link("alias/list"));
                    } else {
                        $this->theme->display_error(400, "No File Specified", "You have to upload a file");
                    }
                } else {
                    $this->theme->display_error(401, "Admins Only", "Only admins can edit the alias list");
                }
            }
        }
    }

    public function onAddAlias(AddAliasEvent $event)
    {
        global $database;
        $pair = ["oldtag" => $event->oldtag, "newtag" => $event->newtag];
        if ($database->get_row("SELECT * FROM aliases WHERE oldtag=:oldtag AND lower(newtag)=lower(:newtag)", $pair)) {
            throw new AddAliasException("That alias already exists");
        } elseif ($database->get_row("SELECT * FROM aliases WHERE oldtag=:newtag", ["newtag" => $event->newtag])) {
            throw new AddAliasException("{$event->newtag} is itself an alias");
        } else {
            $database->execute("INSERT INTO aliases(oldtag, newtag) VALUES(:oldtag, :newtag)", $pair);
            log_info("alias_editor", "Added alias for {$event->oldtag} -> {$event->newtag}", "Added alias");
        }
    }

    public function onPageSubNavBuilding(PageSubNavBuildingEvent $event)
    {
        if ($event->parent=="tags") {
            $event->add_nav_link("aliases", new Link('alias/list'), "Aliases", NavLink::is_active(["alias"]));
        }
    }

    public function onUserBlockBuilding(UserBlockBuildingEvent $event)
    {
        global $user;
        if ($user->can(Permissions::MANAGE_ALIAS_LIST)) {
            $event->add_link("Alias Editor", make_link("alias/list"));
        }
    }

    private function get_alias_csv(Database $database): string
    {
        $csv = "";
        $aliases = $database->get_pairs("SELECT oldtag, newtag FROM aliases ORDER BY newtag");
        foreach ($aliases as $old => $new) {
            $csv .= "\"$old\",\"$new\"\n";
        }
        return $csv;
    }

    private function add_alias_csv(Database $database, string $csv)
    {
        $csv = str_replace("\r", "\n", $csv);
        foreach (explode("\n", $csv) as $line) {
            $parts = str_getcsv($line);
            if (count($parts) == 2) {
                try {
                    $aae = new AddAliasEvent($parts[0], $parts[1]);
                    send_event($aae);
                } catch (AddAliasException $ex) {
                    $this->theme->display_error(500, "Error adding alias", $ex->getMessage());
                }
            }
        }
    }

    /**
     * Get the priority for this extension.
     *
     * Add alias *after* mass tag editing, else the MTE will
     * search for the images and be redirected to the alias,
     * missing out the images tagged with the old tag.
     */
    public function get_priority(): int
    {
        return 60;
    }
}
