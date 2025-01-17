<?php
// Function zur Ermittlung eines Linktyps 
if (!function_exists('infolink')) {
    function infolink($url)
    {
        // Wurde ein Wert für $url übergeben?
        if ($url) {
            // Prüfe ob es sich um eine URL handelt, dann weiter
            if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            }
            // Ist es eine Mediendatei?
            if (file_exists(rex_path::media($url)) === true) {
                $url = rex_url::media($url);
            } else {
                // Ist es keine Mediendatei oder URL, dann als Redaxo-Artikel-ID behandeln
                if (filter_var($url, FILTER_VALIDATE_URL) === FALSE and is_numeric($url)) {
                    $url = rex_getUrl($url);
                }
            }
            $link = $url;
            return $link;
        }
    }
}
// Function zur Generierung eines Navigationsarrays
// Benötigt für den Aufruf werden nur $start,$depth und $ignoreoffline
// Alle weiteren Angaben dienen der internen Verarbeitung
// Alle weiteren Informationen aus rex_structure findet man in catObject
if (!function_exists('structureArray')) {
    function structureArray($start = 0, $depth = 0, $ignoreOffline = true, $depth_saved = 0, $level = 0)
    {
        $result     = array();
        // Aktuelle Kategorie ermitteln
        $currentCat = rex_category::getCurrent();
        if ($currentCat) {
            $currentCatpath = $currentCat->getPathAsArray();
            $currentCat_id  = $currentCat->getId();
        } else {
            $currentCatpath = array();
            $currentCat_id  = 0;
        }
        if ($start != 0) {
            $startCat  = rex_category::get($start);
            $startPath = $startCat->getPathAsArray();
            $depth     = count($startPath) + $depth;
            $startCats = $startCat->getChildren($ignoreoffline);
            if ($depth_saved != 0) {
                $depth = $depth_saved;
            } else {
                $depth_saved = $depth;
            }
        } else {
            $startCats = rex_category::getRootCategories(true);
            $depth     = $depth;
        }
        if ($startCats) {
            foreach ($startCats as $cat) {
                $children['child'] = array();
                $hasChildren       = false;
                $catId             = $cat->getId();
                $path              = $cat->getPathAsArray();
                $listlevel         = count($path);
                if ($listlevel > $depth) {
                    continue;
                }
                if ($listlevel <= $depth && $depth != 0 && $cat && $cat->getChildren($ignoreoffline)) {
                    $level++;
                    $hasChildren       = true;
                    // Unterkategorien ermitteln, function ruft sich selbst auf
                    $children['child'] = structureArray($catId, $depth, $ignoreOffline = true, $depth_saved, $level);
                    $level--;
                }
                // Name der Kategorie
                $catName = $cat->getName();
                // Url ermitteln
                $catUrl  = $cat->getUrl();
                // Aktiven Pfad ermitteln
                $active  = false;
                if (in_array($catId, $currentCatpath) or $currentCat_id == $catId) {
                    $active = true;
                }
                // Ergebnis speichern
                $result[] = array(
                    'catId' => $catId,
                    'parentId' => $start,
                    'level' => $level,
                    'active' => $active,
                    'catName' => $catName,
                    'url' => $catUrl,
                    'hasChildren' => $hasChildren,
                    'children' => $children['child'],
                    'path' => $path,
                    'catObject' => $cat
                );
            }
        }
        return $result;
    }
}
