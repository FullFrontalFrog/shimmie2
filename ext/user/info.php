<?php

class UserPageInfo extends ExtensionInfo
{
    public const KEY = "user";

    public $key = self::KEY;
    public $name = "User Management";
    public $authors = self::SHISH_AUTHOR;
    public $description = "Allows people to sign up to the website";
    public $core = true;
}
