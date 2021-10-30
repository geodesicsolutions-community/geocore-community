<?php

class rss_reader
{
    /*This class facilitates reading the geo news feed into the Admin home page
    *If desired at a later date, the first few words of the actual text of the entry may be
    *shown by un-commenting the appropriate code in this file.
    */
    private $filename, $full_text, $title, $max_entries;

    public function __construct($file_to_read)
    {
        $this->filename = $file_to_read;
        $this->full_text = geoPC::urlGetContents($this->filename);
        //echo "feed:<pre>".htmlspecialchars($this->full_text)."</pre>";
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setMaxEntries($max)
    {
        $this->max_entries = (int)$max;
    }

    /**
     * gets array of all values in $string between <$tag> and </$tag> if
     * $string not provided, defaults to full text of file
     *
     * @param $tag
     * @param $string
     */
    public function get_rss_tag_contents($tag, $string = '')
    {
        if (strlen($string) == 0) {
            $string = $this->full_text;
        }

        preg_match_all("/<" . $tag . ">(<!\[CDATA\[)?([\w\W]*?)(]]>)?<\/" . $tag . ">/", $string, $items);
        return($items[2]);
    }

    public function format_date($date)
    {
        $date = strtotime($date);
        return(date("D, d M Y", $date));
    }

    public function get_feed_html($wrap = true)
    {
        $items = array();
        $titles = array();
        //$descriptions = array();
        $links = array();
        $dates = array();

        $items_raw = $this->get_rss_tag_contents("item");
        if ($this->max_entries) {
            array_splice($items_raw, $this->max_entries);
        }
        $items = array();
        foreach ($items_raw as $i => $item) {
            $titles = $this->get_rss_tag_contents("title", $item);
            $links = $this->get_rss_tag_contents("link", $item);
            $dates = $this->get_rss_tag_contents("pubDate", $item);
            $descriptions = $this->get_rss_tag_contents("description", $item);

            $items[] = array (
                'title' => $titles[0],
                'link' => $links[0],
                'date' => strtotime($dates[0]),
                'description' => $descriptions[0],

            );
        }

        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $tpl->assign('items', $items);
        $tpl->assign('title', $this->title);

        return $tpl->fetch('rss_reader.tpl');
    }

    function link_the_dots($description, $link)
    {
        //turn the [...] at the end of description into a live link
        $dots = str_replace("[...]", "<a href=\"" . $link . "\" class=\"admin_rss_more\">(...)</a>", $description);
        return($dots);
    }
}
