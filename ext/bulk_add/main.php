<?php

class BulkAddEvent extends Event
{
    public $dir;
    public $results;

    public function __construct(string $dir)
    {
        $this->dir = $dir;
        $this->results = [];
    }
}

class BulkAdd extends Extension
{
    public function onPageRequest(PageRequestEvent $event)
    {
        global $page, $user;
        if ($event->page_matches("bulk_add")) {
            if ($user->can(Permissions::BULK_ADD) && $user->check_auth_token() && isset($_POST['dir'])) {
                set_time_limit(0);
                $bae = new BulkAddEvent($_POST['dir']);
                send_event($bae);
                foreach ($bae->results as $result) {
                    $this->theme->add_status("Adding files", $result);
                }
                $this->theme->display_upload_results($page);
            }
        }
    }

    public function onCommand(CommandEvent $event)
    {
        if ($event->cmd == "help") {
            print "\tbulk-add [directory]\n";
            print "\t\tImport this directory\n\n";
        }
        if ($event->cmd == "bulk-add") {
            if (count($event->args) == 1) {
                $bae = new BulkAddEvent($event->args[0]);
                send_event($bae);
                print(implode("\n", $bae->results));
            }
        }
    }

    public function onAdminBuilding(AdminBuildingEvent $event)
    {
        $this->theme->display_admin_block();
    }

    public function onBulkAdd(BulkAddEvent $event)
    {
        if (is_dir($event->dir) && is_readable($event->dir)) {
            $event->results = add_dir($event->dir);
        } else {
            $h_dir = html_escape($event->dir);
            $event->results[] = "Error, $h_dir is not a readable directory";
        }
    }
}
