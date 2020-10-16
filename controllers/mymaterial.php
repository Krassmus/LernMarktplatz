<?php

require_once 'app/controllers/plugin_controller.php';

class MymaterialController extends PluginController
{

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setTitle(_("Lernmaterialien"));
        if (!$GLOBALS['perm']->have_perm(Config::get()->LERNMARKTPLATZ_UPLOAD_STATUS)) {
            throw new AccessDeniedException();
        }
    }

    public function overview_action()
    {
        $main_navigation = Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION !== "/"
            ? Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION
            : "";
        Navigation::activateItem($main_navigation."/lernmarktplatz/mymaterial");
        $this->materialien = LernmarktplatzMaterial::findMine();
    }

    public function details_action($material_id)
    {
        $main_navigation = Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION !== "/"
            ? Config::get()->LERNMARKTPLATZ_MAIN_NAVIGATION
            : "";
        Navigation::activateItem($main_navigation."/lernmarktplatz/overview");
        $this->material = new LernmarktplatzMaterial($material_id);
    }


    public function edit_action($material_id = null)
    {
        $this->material = new LernmarktplatzMaterial($material_id);
        Pagelayout::setTitle($this->material->isNew() ? _("Neues Material hochladen") : _("Material bearbeiten"));
        if ($material_id && !$this->material->isMine() && !$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException();
        }
        if (Request::submitted("delete") && Request::isPost()) {
            $this->material->pushDataToIndexServers("delete");
            $this->material->delete();
            PageLayout::postMessage(MessageBox::success(_("Das Material wurde gelöscht.")));
            $this->redirect("market/overview");
        } elseif (Request::isPost()) {
            $was_new = $this->material->isNew();
            $this->material->setData(Request::getArray("data"));
            $this->material['host_id'] = null;
            $this->material['license'] = "CC BY 4.0";
            if ($_FILES['file']['tmp_name']) {
                $this->material['content_type'] = $_FILES['file']['type'];
                if (in_array($this->material['content_type'], array("application/x-zip-compressed", "application/zip", "application/x-zip"))) {
                    $tmp_folder = $GLOBALS['TMP_PATH']."/temp_folder_".md5(uniqid());
                    mkdir($tmp_folder);
                    \Studip\ZipArchive::extractToPath($_FILES['file']['tmp_name'], $tmp_folder);
                    $this->material['structure'] = $this->getFolderStructure($tmp_folder);
                    rmdirr($tmp_folder);
                } else {
                    $this->material['structure'] = null;
                }
                $this->material['filename'] = $_FILES['file']['name'];
                move_uploaded_file($_FILES['file']['tmp_name'], $this->material->getFilePath());
            } elseif(Request::get("tmp_file")) {
                $this->material['content_type'] = Request::get("mime_type") ?: get_mime_type(Request::get("tmp_file"));

                if (in_array($this->material['content_type'], array("application/x-zip-compressed", "application/zip", "application/x-zip"))) {
                    $tmp_folder = $GLOBALS['TMP_PATH']."/temp_folder_".md5(uniqid());
                    mkdir($tmp_folder);
                    \Studip\ZipArchive::extractToPath(Request::get("tmp_file"), $tmp_folder);
                    $this->material['structure'] = $this->getFolderStructure($tmp_folder);
                    rmdirr($tmp_folder);
                } else {
                    $this->material['structure'] = null;
                }
                $this->material['filename'] = Request::get("filename");
                move_uploaded_file(Request::get("tmp_file"), $this->material->getFilePath());
            }
            if ($_FILES['image']['tmp_name']) {
                $this->material['front_image_content_type'] = $_FILES['image']['type'];
                move_uploaded_file($_FILES['image']['tmp_name'], $this->material->getFrontImageFilePath());
            } elseif (Request::get("logo_tmp_file")) {
                $this->material['front_image_content_type'] = get_mime_type(Request::get("logo_tmp_file"));
                copy(Request::get("logo_tmp_file"), $this->material->getFrontImageFilePath());
            }
            if (Request::get("delete_front_image")) {
                $this->material['front_image_content_type'] = null;
            }
            if ($this->material->isNew() && $this->material['category'] === "auto") {
                $this->material['category'] = $this->material->autoDetectCategory();
            }
            $this->material->store();

            if ($was_new) {
                $materialuser = new LernmarktplatzMaterialUser();
                $materialuser['material_id'] = $this->material->getId();
                $materialuser['user_id'] = $GLOBALS['user']->id;
                $materialuser['external_contact'] = 0;
                $materialuser['position'] = 1;
                $materialuser->store();

                $this->material->notifyFollowersAboutNewMaterial();
            }
            foreach (Request::getArray("remove_users") as $index => $user) {
                if (!$index && count(Request::getArray("remove_users") === count($this->material->users))) {
                    continue;
                }
                list($external, $user_id) = explode("_", $user);
                LernmarktplatzMaterialUser::deleteBySQL("user_id = ? AND material_id = ? AND external_contact = ?", [$user_id, $this->material->getId(), $external]);
            }
            if (Request::get("new_user")) {
                list($external, $user_id) = explode("_", Request::get("new_user"));
                $materialuser = new LernmarktplatzMaterialUser();
                $materialuser['user_id'] = $user_id;
                $materialuser['material_id'] = $this->material->getId();
                $materialuser['external_contact'] = $external;
                $materialuser['position'] = count($this->material->users) + 1;
                $materialuser->store();
            }




            //Topics:
            $topics = Request::getArray("tags");
            foreach ($topics as $key => $topic) {
                if (!trim($topic)) {
                    unset($topics[$key]);
                }
            }
            $this->material->setTopics($topics);

            $this->material->pushDataToIndexServers();

            PageLayout::postMessage(MessageBox::success(_("Lernmaterial erfolgreich gespeichert.")));

            if (Request::get("redirect_url")) {
                $this->redirect(URLHelper::getURL(Request::get("redirect_url"), array(
                    'material_id' => $this->material->getId(),
                    'url' => PluginEngine::getURL($this->plugin, array(), "market/details/".$this->material->getId())
                )));
            } else {
                $this->redirect("market/details/" . $this->material->getId());
            }
        }
        if (isset($_SESSION['LernMarktplatz_CREATE_TEMPLATE'])) {
            $this->template = $_SESSION['LernMarktplatz_CREATE_TEMPLATE'];
            unset($_SESSION['LernMarktplatz_CREATE_TEMPLATE']);
        }

        $this->usersearch = new SQLSearch("
            SELECT DISTINCT CONCAT('0_', auth_user_md5.user_id), CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname, ' (', auth_user_md5.username, ')')
            FROM auth_user_md5 LEFT JOIN user_info ON (user_info.user_id = auth_user_md5.user_id)
            WHERE (CONCAT(auth_user_md5.Vorname, ' ', auth_user_md5.Nachname) LIKE REPLACE(:input, ' ', '% ')
                OR CONCAT(auth_user_md5.Nachname, ' ', auth_user_md5.Vorname) LIKE REPLACE(:input, ' ', '% ')
                OR CONCAT(auth_user_md5.Nachname, ', ', auth_user_md5.Vorname) LIKE :input
                OR auth_user_md5.username LIKE :input) AND " . get_vis_query() . "
            UNION SELECT CONCAT('1_', name), name
            FROM lernmarktplatz_user
            WHERE name LIKE :input
        ", _("Person hinzufügen"), "user_id");
    }

    public function statistics_action($material_id)
    {
        $this->material = new LernmarktplatzMaterial($material_id);
        Pagelayout::setTitle(sprintf(_("Zugriffszahlen für %s"), $this->material['name']));
        if (!$GLOBALS['perm']->have_perm("root") && $this->material['user_id'] && $this->material['user_id'] !== $GLOBALS['user']->id) {
            throw new AccessDeniedException();
        }
        if (Request::get("export")) {
            $this->counter = LernmarktplatzDownloadcounter::findBySQL("material_id = ? ORDER BY mkdate DESC", array($material_id));
            $output = array(
                array("Datum", "Longitude", "Latitude")
            );
            foreach ($this->counter as $counter) {
                $output[] = array(
                    date("Y-m-d H:i:s", $counter['mkdate']),
                    $counter['longitude'],
                    $counter['latitude']
                );
            }

            $this->render_csv($output, "Zugriffszahlen ".$this->material['name'].".csv");
            return;
        }
        $this->counter = LernmarktplatzDownloadcounter::countBySQL("material_id = ?", array($material_id));
        $this->counter_today = LernmarktplatzDownloadcounter::countBySQL("material_id = :material_id AND mkdate >= :start", array(
            'material_id' => $material_id,
            'start' => mktime(0, 0, 0)
        ));
    }

    /**
     * Render given data as csv, data is assumed to be utf-8.
     * The first row of data may contain column titles.
     *
     * @param array $data       data as two dimensional array
     * @param string $filename  download file name (optional)
     * @param string $delimiter field delimiter char (optional)
     * @param string $enclosure field enclosure char (optional)
     */
    public function render_csv($data, $filename = null, $delimiter = ';', $enclosure = '"')
    {
        $this->set_content_type('text/csv; charset=UTF-8');

        $output = fopen('php://temp', 'rw');
        fputs($output, "\xEF\xBB\xBF");

        foreach ($data as $row) {
            fputcsv($output, $row, $delimiter, $enclosure);
        }

        rewind($output);
        $csv_data = stream_get_contents($output);
        fclose($output);

        if (isset($filename)) {
            $this->response->add_header('Content-Disposition', 'attachment; ' . $this->encode_header_parameter('filename', $filename));
        }

        $this->response->add_header('Content-Length', strlen($csv_data));

        return $this->render_text($csv_data);
    }

    protected function encode_header_parameter($name, $value)
    {
        if (preg_match('/[\200-\377]/', $value)) {
            // use RFC 5987 encoding (ext-parameter)
            return $name . "*=UTF-8''" . rawurlencode($value);
        } else {
            // use RFC 2616 encoding (quoted-string)
            return $name . '="' . addslashes($value) . '"';
        }
    }

    protected function getFolderStructure($folder)
    {
        $structure = array();
        foreach (scandir($folder) as $file) {
            if (!in_array($file, array(".", ".."))) {
                $attributes = array(
                    'is_folder' => is_dir($folder."/".$file) ? 1 : 0
                );
                if (is_dir($folder."/".$file)) {
                    $attributes['structure'] = $this->getFolderStructure($folder."/".$file);
                } else {
                    $attributes['size'] = filesize($folder."/".$file);
                }
                $structure[$file] = $attributes;
            }
        }
        return $structure;
    }

    public function add_tag_action()
    {
        if (!Request::isPost()) {
            throw new AccessDeniedException();
        }
        $this->material = new LernmarktplatzMaterial(Request::option("material_id"));
        $this->render_nothing();
    }

}
