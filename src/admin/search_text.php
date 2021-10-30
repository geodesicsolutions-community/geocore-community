<?php

class SearchText
{
    public function display_text_search()
    {
        $tpl_vars = array ();

        if (geoAjax::isAjax()) {
            //don't do rest of stuff...
            geoView::getInstance()->setRendered(true);
            return;
        }

        if (isset($_REQUEST['text'])) {
            $tpl_vars['text'] = trim($_REQUEST['text']);
            $tpl_vars['show_first'] = $_REQUEST['show_first'];
        } else {
            $tpl_vars['text'] = '';
            $tpl_vars['show_first'] = 1;
        }


        geoView::getInstance()->setBodyTpl('search/main.tpl')->setBodyVar($tpl_vars)
            ->addJScript(array('js/text_search.js'));
    }

    private function _buildSearchTerms($search_term)
    {
        //NOT USED :: Keeping this in case we decide to offer this "type of search"
        //in the future or decide to switch back to it.

        //find and remove sub-strings surrounded in quotes, to treat as one term

        preg_match_all("|([\"'`])(.+?)\\1|i", $search_term, $matches);

        //remove those from original search term
        $search_term = preg_replace("|([\"'`])(.+?)\\1|i", '', $search_term);
        //get rid of multiple whitespaces
        $search_term = preg_replace('/\s\s+/', ' ', $search_term);

        //array of exact term searches
        $search_terms = $matches[2];

        //separate all remaining words as place in array
        foreach (explode(' ', $search_term) as $word) {
            //skip zero length words
            if (0 == strlen($word)) {
                continue;
            }
            $search_terms[] = $word;
        }

        //echo "Search Terms: <pre>".print_r($search_terms,1)."</pre>";
        return $search_terms;
    }

    public function update_text_search()
    {
        $search_type = $_POST['search_type'];
        $search_term = trim($_POST['text']);
        $show_first = (bool)$_POST['show_first'];

        //parse for the parts to the search term
        if (strlen($search_term) == 0) {
            echo "<div class='page_note_error'>Please fill in what text to search for.</div>";
            return true;
        }

        $search_terms = array($search_term);

        //ALWAYS do an exact phrase match...  see _buildSearchTerms() for "old"
        //functionality

        //process search
        if ($search_type == 'filename') {
            //search through filenames
            $results = $this->_filename($search_terms);
            $tplFile = 'search/filename_results.tpl';
        } elseif ($search_type == 'content') {
            //search through contents

            $results = $this->_content($search_terms, $show_first);
            $tplFile = 'search/template_content_results.tpl';
        } elseif ($search_type == 'addon') {
            //search through text

            $results = $this->_addon($search_terms, $show_first);
            $tplFile = 'search/addon_results.tpl';
        } else {
            //search through text

            $results = $this->_text($search_terms, $show_first);
            $tplFile = 'search/text_results.tpl';
        }
        $tpl = new geoTemplate(geoTemplate::ADMIN);
        $tpl->assign('results', $results);

        echo $tpl->fetch($tplFile);
        return true;
    }

    private function _content($search_terms, $show_first)
    {
        $file = geoFile::getInstance(geoFile::TEMPLATES);

        //get template sets
        $skip = geoTemplate::getInvalidSetNames();
        $skip[] = '.';
        $skip[] = '..';
        $tsetsRaw = array_diff(scandir($file->absolutize('')), $skip);
        $tsets = array();

        $matches = array ();

        foreach ($tsetsRaw as $key => $tset) {
            //weed out no good tsets
            if (!is_dir($file->absolutize($tset . '/'))) {
                continue;
            }
            if (strpos($tset, '_') === 0) {
                //starts with _
                continue;
            }
            if (strpos($tset, '.') === 0) {
                //starts with .
                continue;
            }

            $allFiles = $file->scandir($tset . '/main_page/');
            foreach ($allFiles as $thisfile) {
                if (strpos($thisfile, 'attachments/') === 0) {
                    //don't look at attachments files
                    continue;
                }

                $filename = "$tset/main_page/$thisfile";
                $contents = file_get_contents($file->absolutize($filename));

                foreach ($search_terms as $term) {
                    if (stripos($contents, $term) !== false) {
                        //break up the folder from filename

                        $contents = $this->_highlightResult($contents, $search_terms, $show_first);
                        $matches[] = array('filename' => $filename, 'text' => $contents);
                        break;
                    }
                }
            }
        }
        return $matches;
    }

    private function _filename($search_terms)
    {
        $file = geoFile::getInstance(geoFile::TEMPLATES);

        //get template sets
        $skip = geoTemplate::getInvalidSetNames();
        $skip[] = '.';
        $skip[] = '..';
        $tsetsRaw = array_diff(scandir($file->absolutize('')), $skip);
        $tsets = array();

        $matches = array ();

        foreach ($tsetsRaw as $key => $tset) {
            //weed out no good tsets
            if (!is_dir($file->absolutize($tset . '/'))) {
                continue;
            }
            if (strpos($tset, '_') === 0) {
                //starts with _
                continue;
            }
            if (strpos($tset, '.') === 0) {
                //starts with .
                continue;
            }

            $allFiles = $file->scandir($tset . '/main_page/');
            foreach ($allFiles as $thisfile) {
                if (strpos($thisfile, 'attachments/') === 0) {
                    //don't look at attachments files
                    continue;
                }

                $filename = "$tset/main_page/$thisfile";
                foreach ($search_terms as $term) {
                    if (stripos($filename, $term) !== false) {
                        //break up the folder from filename
                        $folder = dirname($filename) . '/';
                        $filename = $this->_highlightResult($filename, $search_terms, false);
                        $matches[] = array('filename' => $filename, 'containingFolder' => $folder);
                        break;
                    }
                }
            }
            //also search external
            $allFiles = $file->scandir($tset . '/external/');
            foreach ($allFiles as $thisFile) {
                $filename = "$tset/external/$thisFile";
                foreach ($search_terms as $term) {
                    if (stripos($filename, $term) !== false) {
                        //break up the folder from filename
                        $folder = dirname($filename) . '/';
                        $filename = $this->_highlightResult($filename, $search_terms, false);
                        $matches[] = array('filename' => $filename, 'containingFolder' => $folder);
                        break;
                    }
                }
            }
        }
        return $matches;
    }

    private function _addon($search_terms, $show_first)
    {
        $db = DataAccess::getInstance();
        $addon = geoAddon::getInstance();

        $whereClauses = array();
        foreach ($search_terms as $word) {
            //BUILD QUERIES - escape % and _ for use in "like" query, one "encoded" and one not since
            //earlier text might not be encoded in DB

            $wordDB = $db->qstr('%' . addcslashes(geoString::toDB($word), '\\%_') . '%');
            //this time, need to escape it properly since no toDB to get rid of quotes
            $straightWord = $db->qstr('%' . addcslashes($word, '\\%_') . '%');

            $whereClauses[] = "`text` LIKE $wordDB";

            if ($wordDB != $straightWord) {
                $whereClauses[] = "`text` LIKE $straightWord";
            }
        }
        $pages_where_clause = " WHERE (" . implode(' OR ', $whereClauses) . ")";

        // Get languages
        $sql = "SELECT `language_id`, `language` FROM " . geoTables::pages_languages_table;

        $languages = $db->GetAssoc($sql);

        $sql = "SELECT * FROM " . geoTables::addon_text_table . " $pages_where_clause ORDER BY `auth_tag`, `addon`, `language_id`";

        $rawResults = $db->GetAll($sql);
        $results = array ();
        foreach ($rawResults as $row) {
            $addonAdmin = $addon->getTextAddons($row['addon']);
            if (!is_object($addonAdmin)) {
                //addon not enabled or something
                continue;
            }
            $textInfo = $addonAdmin->init_text($row['language_id']);
            $addonInfo = $addon->getInfoClass($row['addon']);
            if (!is_object($addonInfo)) {
                //something wrong with this one
                continue;
            }
            $row['addon_title'] = $addonInfo->title;
            $row['label'] = $textInfo[$row['text_id']]['name'];
            $row['language'] = (isset($languages[$row['language_id']])) ? $languages[$row['language_id']] : $row['language_id'];

            $row['text'] = $this->_highlightResult(geoString::fromDB($row['text']), $search_terms, $show_first);

            $results[] = $row;
        }
        return $results;
    }

    private function _text($search_terms, $show_first)
    {
        $db = DataAccess::getInstance();

        $whereClauses = array();
        foreach ($search_terms as $word) {
            //BUILD QUERIES - escape % and _ for use in "like" query, one "encoded" and one not since
            //earlier text might not be encoded in DB

            $wordDB = $db->qstr('%' . addcslashes(geoString::toDB($word), '\\%_') . '%');
            //this time, need to escape it properly since no toDB to get rid of quotes
            $straightWord = $db->qstr('%' . addcslashes($word, '\\%_') . '%');

            $whereClauses[] = "`text` LIKE $wordDB";

            if ($wordDB != $straightWord) {
                $whereClauses[] = "`text` LIKE $straightWord";
            }

            if (is_numeric($word) && (int)$word > 0) {
                //Make it possible to enter a text id, and it will pull up
                //text's that match that ID

                $checkId = (int)$word;

                $whereClauses[] = "`text_id` = $checkId";
            }
        }
        $pages_where_clause = " WHERE (" . implode(' OR ', $whereClauses) . ")";

        // Get languages
        $sql = "SELECT `language_id`, `language` FROM " . geoTables::pages_languages_table;

        $languages = $db->GetAssoc($sql);

        $sql = "SELECT * FROM " . geoTables::pages_text_languages_table . " $pages_where_clause";

        $rawResults = $db->GetAll($sql);

        //get the HTML and PHP id's to skip them...
        require_once ADMIN_DIR . 'admin_pages_class.php';
        $tempClass = Singleton::getInstance('Admin_pages');

        $results = array ();
        $pages = array ();
        foreach ($rawResults as $row) {
            if (!isset($pages[$row['page_id']])) {
                $sql = "SELECT `name`, `applies_to` FROM " . geoTables::pages_table . " WHERE `page_id` = " . $row["page_id"];
                $page_data = $db->GetRow($sql);
                if (!$page_data) {
                    //invalid page or something?
                    continue;
                }
                $pages[$row['page_id']] = $page_data;
            }
            if (!geoMaster::is('classifieds') && $pages[$row['page_id']]['applies_to'] == 1) {
                //text not used for this site
                continue;
            } elseif (!geoMaster::is('auctions') && $pages[$row['page_id']]['applies_to'] == 2) {
                //text not used for this site
                continue;
            }
            if (!$tempClass->isPageEditable($row['page_id'])) {
                //not an editable page for this product
                continue;
            }

            $row['page_name'] = $pages[$row['page_id']]['name'];
            $sql = "SELECT `name` FROM " . geoTables::pages_text_table . " WHERE `message_id` = " . $row["text_id"];
            $data = $db->GetRow($sql);
            $row['label'] = geoString::fromDB($data['name']);
            $row['language'] = (isset($languages[$row['language_id']])) ? $languages[$row['language_id']] : $row['language_id'];

            $row['text'] = $this->_highlightResult(geoString::fromDB($row['text']), $search_terms, $show_first);

            $results[] = $row;
        }
        return $results;
    }

    private function _highlightResult($text, $search_terms, $show_first)
    {
        //figure out shortened text
        $text = preg_replace('/\s\s+/', ' ', trim($text));

        if ($show_first) {
            $p_start = strlen($text);
            $first_term = '';
            foreach ($search_terms as $word) {
                $pointer = stripos($text, $word);
                if ($pointer < $p_start && $pointer !== false) {
                    $p_start = $pointer;
                    $first_term = $word;
                }
            }
            $p_end = $p_start + strlen($first_term);
            if ($p_start - 25 < 0) {
                $begin = 0;
                $beg_dots = '';
            } else {
                $begin = $p_start - 25;
                $beg_dots = "....";
            }
            if ($p_end + 25 > strlen($text)) {
                $end = strlen($text);
                $end_dots = '';
            } else {
                $end = $p_end + 25;
                $end_dots = '....';
            }
            $text = substr($text, $begin, $end - $begin);
        } else {
            $beg_dots = $end_dots = '';
        }
        //show it shows HTML code
        $text = geoString::specialChars($text);
        foreach ($search_terms as $word) {
            $word = geoString::specialChars($word);
            $text = preg_replace('/(' . preg_quote($word, '/') . ')/i', '<span style="background-color: yellow; font-weight: bold;">$1</span>', $text);
        }
        return $beg_dots . $text . $end_dots;
    }
}
