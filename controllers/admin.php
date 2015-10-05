<?php

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
    }

    public function add_new_host_action() {
        if (Request::isPost()) {
            $host = MarketHost::findByUrl(Request::get("url"));
            if (!$host) {
                $host = new MarketHost();
                $host->fetchPublicKey();
            }
            if ($host['public_key']) {
                $host->store();
                PageLayout::postMessage(MessageBox::success(_("Server wurde gefunden und hinzugefügt.")));
            } else {
                PageLayout::postMessage(MessageBox::error(_("Server ist nicht erreichbar oder hat die Anfrage abgelehnt.")));
            }
            $this->redirect("admin/hosts");
        }
    }

    public function ask_for_hosts_action($host_id) {
        $host = new MarketHost($host_id);
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
        if ($added > 0) {
            PageLayout::postMessage(MessageBox::success(sprintf(_("%s neue Server hinzugefügt."), $added)));
        } else {
            PageLayout::postMessage(MessageBox::info(_("Keine neuen Server gefunden.")));
        }
        $this->redirect("admin/hosts");
    }

}