<?php

class JXGoogleMapPresenter
{
    private $db;
    private $shop;
    private $hookName;
    private $moduleName;
    private $db_prefix;
    private $multilangual = false;

    public function __construct(Db $db, Shop $shop, $moduleName, $multilangual = false)
    {
        $this->db = $db;
        $this->db_prefix = $db->getPrefix();
        $this->shop = $shop;
        $this->moduleName = $moduleName;
        $this->multilangual = $multilangual;
    }

    public function setHook($hookName)
    {
        $this->hookName = $hookName;

        return $this;
    }

    public function getAllModuleHooks()
    {
        $sql = "SELECT DISTINCT h.id_hook as id, h.name as name
                FROM {$this->db_prefix}hook h
                LEFT JOIN {$this->db_prefix}hook_module hm
                ON(h.id_hook = hm.`id_hook`)
                INNER JOIN {$this->db_prefix}module m
                ON(hm.`id_module` = m.`id_module`)
                WHERE (lower(h.`name`) LIKE 'display%')
                AND m.`name` = '{$this->moduleName}'
                ORDER BY h.name ASC
            ";
        $hooks = $this->db->executeS($sql);
        foreach ($hooks as $key => $hook) {
            if (preg_match('/admin/i', $hook['name'])
                || preg_match('/backoffice/i', $hook['name'])
            ) {
                unset($hooks[$key]);
            }
        }

        sort($hooks);

        if ((bool)count($hooks)) {
            return $hooks;
        } else {
            return false;
        }
    }

    public function present($stores, array $result = [])
    {
        if (!is_array($stores)) {
            return false;
        }
        $languageId = Context::getContext()->language->id;
        foreach ($stores as $store) {
            $s = new Store((int)$store['id_store']);
            if ($this->multilangual) {
                $result[$store['id_tab']]['hours'] = $this->convertHours($s->hours[$languageId]);
                $result[$store['id_tab']]['address1'] = $s->address1[$languageId];
                $result[$store['id_tab']]['address2'] = $s->address2[$languageId];
                $result[$store['id_tab']]['note'] = $s->note[$languageId];
            } else {
                $result[$store['id_tab']]['hours'] = $this->convertHours($s->hours);
                $result[$store['id_tab']]['address1'] = $s->address1;
                $result[$store['id_tab']]['address2'] = $s->address2;
                $result[$store['id_tab']]['note'] = $s->note;
            }
            $result[$store['id_tab']]['id_tab'] = $store['id_tab'];
            $result[$store['id_tab']]['id_store'] = $store['id_store'];
            $result[$store['id_tab']]['marker'] = $store['marker'];
            $result[$store['id_tab']]['name'] = $store['name'];
            $result[$store['id_tab']]['content'] = $store['content'];
            $result[$store['id_tab']]['id_country'] = $s->id_country;
            $result[$store['id_tab']]['id_state'] = $s->id_state;
            $result[$store['id_tab']]['postcode'] = $s->postcode;
            $result[$store['id_tab']]['city'] = $s->city;
            $result[$store['id_tab']]['latitude'] = $s->latitude;
            $result[$store['id_tab']]['longitude'] = $s->longitude;
            $result[$store['id_tab']]['phone'] = $s->phone;
            $result[$store['id_tab']]['fax'] = $s->fax;
            $result[$store['id_tab']]['email'] = $s->email;
            $result[$store['id_tab']]['id_image'] = $s->id_image;
        }

        return $result;
    }

    public function convertHours($hours)
    {
        $hours = explode(',', str_replace(str_split('\'[]" '), '', $hours));

        return $hours;
    }
}
