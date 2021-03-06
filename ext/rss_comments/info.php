<?php

class RSSCommentsInfo extends ExtensionInfo
{
    public const KEY = "rss_comments";

    public $key = self::KEY;
    public $name = "RSS for Comments";
    public $url = self::SHIMMIE_URL;
    public $authors = self::SHISH_AUTHOR;
    public $license = self::LICENSE_GPLV2;
    public $description = "Self explanatory";
    public $db_support = [DatabaseDriver::MYSQL, DatabaseDriver::SQLITE];  // pgsql has no UNIX_TIMESTAMP
}
