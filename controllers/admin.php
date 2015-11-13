<?php

require_once 'app/controllers/plugin_controller.php';

class AdminController extends PluginController {

    function before_filter($action, $args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem("/admin/config/lehrmarktplatz");
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException();
        }
    }

    public function hosts_action()
    {
        //init
        MarketHost::thisOne();
        $this->hosts = MarketHost::findAll();

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
        PageLayout::setTitle(_("Neue Lehrmaterialien einstellen"));
        if (Request::submitted("nothanx")) {
            $_SESSION['Lehrmarktplatz_no_thanx'] = true;
        } elseif (Request::isPost()) {
            $host = MarketHost::findByUrl(trim(Request::get("url")));
            if (!$host) {
                $host = new MarketHost();
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
        $host = new MarketHost($host_id);
        $added = $this->askForHosts($host);
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
                $host = MarketHost::findByUrl($host_data['url']);
                if (!$host) {
                    $host = new MarketHost();
                    $host['url'] = $host_data['url'];
                    $host->fetchPublicKey();
                    if ($host['public_key']) {
                        $added++;
                        $host->store();
                    }
                }
            }
        }
        return $added;
    }

    public function toggle_index_server_action() {
        if (Request::isPost()) {
            $host = new MarketHost(Request::option("host_id"));
            if ($host->isMe()) {
                $host['index_server'] = Request::int("active", 0);
                $host->store();
                //distribute this info to adjacent server
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