<?php

class TagEditInfo extends ExtensionInfo
{
    public const KEY = "tag_edit";

    public $key = self::KEY;
    public $name = "Tag Editor";
    public $core = true;
    public $url = self::SHIMMIE_URL;
    public $authors = self::SHISH_AUTHOR;
    public $description = "Allow images to have tags assigned to them";
    public $documentation =
"  Here is a list of the tagging metatags available out of the box;
   Shimmie extensions may provide other metatags:
   <ul>
     <li>source=(*, none) eg -- using this metatag will ignore anything set in the \"Source\" box
       <ul>
         <li>source=http://example.com -- set source to http://example.com
         <li>source=none -- set source to NULL
       </ul>
   </ul>
   <p>Metatags can be followed by \":\" rather than \"=\" if you prefer.
   <br />I.E: \"source:http://example.com\", \"source=http://example.com\" etc.
   <p>Some tagging metatags provided by extensions:
   <ul>
     <li>Numeric Score
       <ul>
         <li>vote=(up, down, remove) -- vote, or remove your vote on an image
       </ul>
     <li>Pools
       <ul>
         <li>pool=(PoolID, PoolTitle, lastcreated) -- add post to pool (if exists)
         <li>pool=(PoolID, PoolTitle, lastcreated):(PoolOrder) -- add post to pool (if exists) with set pool order
         <ul>
           <li>pool=50 -- add post to pool with ID of 50
           <li>pool=10:25 -- add post to pool with ID of 10 and with order 25
           <li>pool=This_is_a_Pool -- add post to pool with a title of \"This is a Pool\"
           <li>pool=lastcreated -- add post to the last pool the user created
         </ul>
       </ul>
     <li>Post Relationships
       <ul>
         <li>parent=(parentID, none) -- set parent ID of current image
        <li>child=(childID) -- set parent ID of child image to current image ID
       </ul>
   </ul>";
}