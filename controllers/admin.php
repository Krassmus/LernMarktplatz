<?php

require_once 'app/controllers/plugin_controller.php';

class AdminController extends PluginController {

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem("/admin/config/lernmarktplatz");
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException();
        }
    }

    public function hosts_action()
    {
        //init
        LernmarktplatzHost::thisOne();
        $this->hosts = LernmarktplatzHost::findAll();

        if (!function_exists("curl_init")) {
            PageLayout::postMessage(MessageBox::error(_("Ihr PHP hat kein aktiviertes cURL-Modul.")));
        }
        $plugin = PluginManager::getInstance()->getPluginInfo(get_class($this->plugin));
        $plugin_roles = RolePersistence::getAssignedPluginRoles($plugin['id']);
        $nobody_allowed = false;
        foreach ($plugin_roles as $role) {
            if (strtolower($role->rolename) === "nobody") {
                $nobody_allowed = true;
            }
        }
        if (!$nobody_allowed) {
            PageLayout::postMessage(MessageBox::error(_("Dieses Plugin ist nicht für nobody freigegeben. Damit kann sich dieser Marktplatz nicht mit anderen Stud.IP verbinden.")));
        }

        //zufällig einen Host nach Neuigkeiten fragen:
        if (count($this->hosts) > 1) {
            $index = rand(0, count($this->hosts) - 1);
            while($this->hosts[$index]->isMe()) {
                $index++;
                if ($index >= count($this->hosts)) {
                    $index = 0;
                }
            }
            $this->askForHosts($this->hosts[$index]);
        }
    }

    public function add_new_host_action() {
        PageLayout::setTitle(_("Neue Lernmaterialien einstellen"));
        if (Request::submitted("nothanx")) {
            $_SESSION['Lernmarktplatz_no_thanx'] = true;
            $this->redirect("admin/hosts");
        } elseif (Request::isPost()) {
            $host = LernmarktplatzHost::findOneByUrl(trim(Request::get("url")));
            if (!$host) {
                $host = new LernmarktplatzHost();
                $host['url'] = trim(Request::get("url"));
                $host['last_updated'] = time();
                $host->fetchPublicKey();
                if ($host['public_key']) {
                    $host->store();
                    PageLayout::postMessage(MessageBox::success(_("Server wurde gefunden und hinzugefügt.")));
                } else {
                    PageLayout::postMessage(MessageBox::error(_("Server ist nicht erreichbar oder hat die Anfrage abgelehnt.")));
                }
            } else {
                $host->fetchPublicKey();
                PageLayout::postMessage(MessageBox::info(_("Server ist schon in Liste.")));
            }

            $this->redirect("admin/hosts");
        }
    }

    public function ask_for_hosts_action($host_id) {
        $host = new LernmarktplatzHost($host_id);
        $added = $this->askForHosts($host);
        die();
        if ($added > 0) {
            PageLayout::postMessage(MessageBox::success(sprintf(_("%s neue Server hinzugefügt."), $added)));
        } else {
            PageLayout::postMessage(MessageBox::info(_("Keine neuen Server gefunden.")));
        }
        $this->redirect("admin/hosts");
    }

    protected function askForHosts($host) {
        $data = $host->askKnownHosts();
        $added = 0;
        if ($data['hosts']) {
            foreach ($data['hosts'] as $host_data) {
                $host = LernmarktplatzHost::findByUrl($host_data['url']);
                if (!$host) {
                    $host = new LernmarktplatzHost();
                    $host['url'] = $host_data['url'];
                    $host->fetchPublicKey();
                    if ($host['public_key']) {
                        $added++;
                        $host->store();
                    }
                } else {
                    var_dump("hey");
                    $host->fetchPublicKey();
                }
            }
        }
        return $added;
    }

    public function toggle_index_server_action() {
        if (Request::isPost()) {
            $host = new LernmarktplatzHost(Request::option("host_id"));
            if ($host->isMe()) {
                $host['index_server'] = Request::int("active", 0);
                $host->store();
                //distribute this info to adjacent server
                $data = array(
                    'data' => array(
                        'public_key' => $host['public_key'],
                        'url' => $host['url'],
                        'name' => $host['name'],
                        'index_server' => $host['index_server']
                    )
                );

                foreach (LernmarktplatzHost::findAll() as $remote) {
                    if (!$remote->isMe()) {
                        $remote->pushDataToEndpoint("update_server_info", $data);
                    }
                }
                /*$curl_multi_handle = curl_multi_init();
                $requests = array();
                foreach (LernmarktplatzHost::findAll() as $remote) {
                    if (!$remote->isMe()) {
                        $request = $remote->pushDataToEndpoint("update_server_info", $data, true);
                        curl_multi_add_handle($curl_multi_handle, $request);
                        $requests[] = $request;
                    }
                }
                $active = null;
                do {
                    $mrc = curl_multi_exec($curl_multi_handle, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                while ($active && $mrc == CURLM_OK) {
                    if (curl_multi_select($curl_multi_handle) != -1) {
                        do {
                            $mrc = curl_multi_exec($curl_multi_handle, $active);
                        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
                    }
                }
                foreach ($requests as $request) {
                    curl_multi_remove_handle($curl_multi_handle, $request);
                }
                curl_multi_close($curl_multi_handle);
                */

            } else {
                $host['allowed_as_index_server'] = Request::int("active", 0);
                $host->store();
            }
        }

        $this->render_text((
            Assets::img("icons/20/blue/checkbox-".(Request::int("active") ? "" : "un")."checked")
        ));
    }

}